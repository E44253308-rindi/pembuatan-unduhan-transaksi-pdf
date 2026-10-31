<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use App\Models\HeldTransaction;
use App\Models\Sale;
use App\Models\SaleItem;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $comics = Comic::with('category')
            ->where('stock', '>', 0)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('barcode', $search)
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $oldInput = $request->old();
        $requestedHeldId = $request->integer('held');
        if ($requestedHeldId === 0) {
            $requestedHeldId = filter_var($oldInput['held_transaction_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
        }

        $held = $requestedHeldId > 0
            ? $request->user()->heldTransactions()->findOrFail($requestedHeldId)
            : null;
        $draft = $held?->draft ?? [];

        if (isset($oldInput['items']) && is_array($oldInput['items'])) {
            $draft = array_merge($draft, array_intersect_key($oldInput, array_flip([
                'items',
                'discount_percent',
                'discount_amount',
                'tax',
                'other_fee',
                'paid',
                'payment_method',
                'customer_name',
                'customer_contact',
            ])));
        }

        $draftItems = collect($draft['items'] ?? [])
            ->filter(fn ($item) => is_array($item) && isset($item['comic_id']))
            ->values();
        $comicIds = $draftItems->pluck('comic_id')
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false)
            ->unique();
        $heldComics = Comic::whereIn('id', $comicIds)->get()->keyBy('id');
        $initialCart = [];
        $unavailableItems = 0;

        foreach ($draftItems as $item) {
            $comic = $heldComics->get($item['comic_id']);

            if (! $comic) {
                $unavailableItems++;

                continue;
            }

            $initialCart[$comic->id] = [
                'id' => $comic->id,
                'title' => $comic->title,
                'price' => (float) $comic->price,
                'qty' => max(1, (int) ($item['quantity'] ?? 1)),
                'stock' => $comic->stock,
            ];
        }

        return view('cashier.pos', [
            'comics' => $comics,
            'held' => $held,
            'initialCart' => $initialCart,
            'draft' => $draft,
            'unavailableItems' => $unavailableItems,
        ]);
    }

    public function held(Request $request)
    {
        $transactions = $request->user()->heldTransactions()->latest()->paginate(10);
        $comicIds = $transactions->getCollection()
            ->flatMap(fn ($transaction) => collect($transaction->draft['items'] ?? [])->pluck('comic_id'))
            ->unique();
        $comics = Comic::whereIn('id', $comicIds)->get(['id', 'price'])->keyBy('id');

        $transactions->getCollection()->transform(function ($transaction) use ($comics) {
            $transaction->estimated_total = collect($transaction->draft['items'] ?? [])
                ->sum(fn ($item) => (float) ($comics->get($item['comic_id'])?->price ?? 0) * (int) $item['quantity']);

            return $transaction;
        });

        return view('cashier.held', compact('transactions'));
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'max:100'],
        ]);
        $keyword = trim($data['q']);

        $comics = Comic::query()
            ->where('stock', '>', 0)
            ->where(function ($query) use ($keyword) {
                $query->where('barcode', $keyword)
                    ->orWhere('title', 'like', "%{$keyword}%");
            })
            ->orderByRaw('CASE WHEN barcode = ? THEN 0 ELSE 1 END', [$keyword])
            ->orderBy('title')
            ->limit(10)
            ->get(['id', 'title', 'barcode', 'price', 'stock']);

        return response()->json($comics);
    }

    public function hold(Request $request)
    {
        $data = $this->validateTransaction($request, paidIsRequired: false);
        $held = null;

        if (! empty($data['held_transaction_id'])) {
            $held = $request->user()->heldTransactions()->findOrFail($data['held_transaction_id']);
        }

        $draft = [
            'items' => array_map(fn ($item) => [
                'comic_id' => (int) $item['comic_id'],
                'quantity' => (int) $item['quantity'],
            ], $data['items']),
            'discount_percent' => (float) ($data['discount_percent'] ?? 0),
            'discount_amount' => (float) ($data['discount_amount'] ?? 0),
            'tax' => (float) ($data['tax'] ?? 0),
            'other_fee' => (float) ($data['other_fee'] ?? 0),
            'paid' => (float) ($data['paid'] ?? 0),
            'payment_method' => $data['payment_method'],
            'customer_name' => $data['customer_name'] ?? null,
            'customer_contact' => $data['customer_contact'] ?? null,
        ];

        if ($held) {
            $held->update(['draft' => $draft]);
        } else {
            $held = $request->user()->heldTransactions()->create([
                'reference' => 'HOLD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5)),
                'draft' => $draft,
            ]);
        }

        return redirect()->route('kasir.pos', ['held' => $held->id])
            ->with('success', 'Transaksi berhasil ditahan.');
    }

    public function destroyHeld(Request $request, HeldTransaction $heldTransaction)
    {
        abort_unless($heldTransaction->user_id === $request->user()->id, 404);
        $heldTransaction->delete();

        return back()->with('success', 'Transaksi ditahan berhasil dihapus.');
    }

    public function history(Request $request)
    {
        $sales = Sale::where('user_id', $request->user()->id)
            ->with('items.comic')
            ->latest()
            ->paginate(10);

        return view('cashier.history', compact('sales'));
    }

    public function receipt(Request $request, Sale $sale)
    {
        abort_unless($sale->user_id === $request->user()->id, 404);

        return view('cashier.receipt', ['sale' => $sale->load('items.comic', 'user')]);
    }

    public function downloadReceipt(Request $request, Sale $sale, PDF $pdf)
    {
        abort_unless($sale->user_id === $request->user()->id, 404);

        $sale->load('items.comic', 'user');
        $pdf->loadView('cashier.invoice-pdf', [
            'sale' => $sale,
            'store' => config('escpos'),
        ])->setPaper([0, 0, 226.77, 720]);

        return $pdf->download("invoice-{$sale->invoice}.pdf");
    }

    public function checkout(Request $request)
    {
        $data = $this->validateTransaction($request);

        if (! empty($data['held_transaction_id'])) {
            $request->user()->heldTransactions()->findOrFail($data['held_transaction_id']);
        }

        $sale = DB::transaction(function () use ($data, $request) {
            $quantities = collect($data['items'])->keyBy('comic_id');
            $comics = Comic::whereIn('id', $quantities->keys())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $lines = [];
            $subtotal = 0;

            foreach ($quantities as $comicId => $item) {
                $comic = $comics->get($comicId);

                if (! $comic || $comic->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => 'Stok komik tidak mencukupi. Perbarui keranjang sebelum melanjutkan.',
                    ]);
                }

                $lineSubtotal = round((float) $comic->price * (int) $item['quantity'], 2);
                $subtotal += $lineSubtotal;
                $lines[] = [
                    'comic' => $comic,
                    'quantity' => (int) $item['quantity'],
                    'price' => $comic->price,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $subtotal = round($subtotal, 2);
            $discountPercent = (float) ($data['discount_percent'] ?? 0);
            $discountAmount = round((float) ($data['discount_amount'] ?? 0), 2);
            $percentageDiscount = round($subtotal * $discountPercent / 100, 2);
            $tax = round((float) ($data['tax'] ?? 0), 2);
            $otherFee = round((float) ($data['other_fee'] ?? 0), 2);

            if ($percentageDiscount + $discountAmount > $subtotal) {
                throw ValidationException::withMessages([
                    'discount_amount' => 'Diskon tidak boleh melebihi subtotal.',
                ]);
            }

            $total = round($subtotal - $percentageDiscount - $discountAmount + $tax + $otherFee, 2);
            $paid = $data['payment_method'] === 'cash' ? (float) $data['paid'] : $total;

            if ($paid < $total) {
                throw ValidationException::withMessages([
                    'paid' => 'Jumlah pembayaran kurang dari total transaksi.',
                ]);
            }

            $sale = Sale::create([
                'user_id' => $request->user()->id,
                'invoice' => 'INV-'.now()->format('YmdHis').'-'.Str::upper(Str::random(6)),
                'subtotal' => $subtotal,
                'discount_percent' => $discountPercent,
                'discount_amount' => $discountAmount,
                'tax' => $tax,
                'other_fee' => $otherFee,
                'total' => $total,
                'paid' => $paid,
                'change' => round($paid - $total, 2),
                'payment_method' => $data['payment_method'],
                'customer_name' => $data['customer_name'] ?? null,
                'customer_contact' => $data['customer_contact'] ?? null,
            ]);

            foreach ($lines as $line) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'comic_id' => $line['comic']->id,
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                    'subtotal' => $line['subtotal'],
                ]);
                $line['comic']->decrement('stock', $line['quantity']);
            }

            if (! empty($data['held_transaction_id'])) {
                $request->user()->heldTransactions()
                    ->whereKey($data['held_transaction_id'])
                    ->delete();
            }

            return $sale;
        });

        $sale->load('items.comic', 'user');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Transaksi berhasil disimpan.',
                'invoice' => [
                    'invoice' => $sale->invoice,
                    'date' => $sale->created_at->format('d/m/Y H:i'),
                    'cashier' => $sale->user->name,
                    'customer_name' => $sale->customer_name,
                    'customer_contact' => $sale->customer_contact,
                    'subtotal' => (float) $sale->subtotal,
                    'discount_percent' => (float) $sale->discount_percent,
                    'discount_amount' => (float) $sale->discount_amount,
                    'tax' => (float) $sale->tax,
                    'other_fee' => (float) $sale->other_fee,
                    'total' => (float) $sale->total,
                    'paid' => (float) $sale->paid,
                    'change' => (float) $sale->change,
                    'payment_method' => $sale->payment_method,
                    'items' => $sale->items->map(fn (SaleItem $item) => [
                        'title' => $item->comic->title,
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                    ])->values(),
                ],
                'download_url' => route('kasir.receipt.download', $sale),
                'print_url' => route('kasir.receipt.print', $sale),
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('kasir.receipt', $sale)->with('success', 'Transaksi berhasil disimpan.');
    }

    private function validateTransaction(Request $request, bool $paidIsRequired = true): array
    {
        return $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.comic_id' => ['required', 'integer', 'distinct', 'exists:comics,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'other_fee' => ['nullable', 'numeric', 'min:0'],
            'paid' => [$paidIsRequired ? 'required' : 'nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,qris,debit,credit,e_wallet,transfer'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_contact' => ['nullable', 'string', 'max:50'],
            'held_transaction_id' => ['nullable', 'integer', 'exists:held_transactions,id'],
        ]);
    }
}
