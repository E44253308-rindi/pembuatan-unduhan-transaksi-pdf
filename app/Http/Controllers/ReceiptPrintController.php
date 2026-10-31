<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\EscPosReceiptPrinter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReceiptPrintController extends Controller
{
    public function __invoke(Request $request, Sale $sale, EscPosReceiptPrinter $printer)
    {
        abort_unless($sale->user_id === $request->user()->id, 404);
        $data = $request->validate([
            'printer_name' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $printer->printSale($sale, $data['printer_name'] ?? null);
        } catch (Throwable $exception) {
            report($exception);
            Log::error('Thermal receipt printing failed.', [
                'sale_id' => $sale->id,
                'message' => $exception->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Gagal mencetak struk: '.$exception->getMessage(),
                ], 422);
            }

            return back()
                ->with('thermal_print', true)
                ->withErrors([
                    'printer' => 'Gagal mencetak struk. Periksa konfigurasi dan koneksi printer ESC/POS.',
                ]);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Struk berhasil dikirim ke printer.']);
        }

        return back()
            ->with('thermal_print', true)
            ->with('success', 'Struk berhasil dikirim ke printer thermal.');
    }
}
