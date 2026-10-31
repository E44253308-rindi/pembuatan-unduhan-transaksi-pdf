<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comic;
use App\Models\Sale;
use App\Models\User;
use App\Services\EscPosReceiptPrinter;
use App\Services\WindowsPrinterDiscovery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_search_by_barcode_and_title(): void
    {
        $cashier = $this->cashier();
        $comic = $this->comic(['barcode' => '8991234567890']);

        $this->actingAs($cashier)
            ->getJson(route('kasir.products.search', ['q' => '8991234567890']))
            ->assertOk()
            ->assertJsonPath('0.id', $comic->id);

        $this->getJson(route('kasir.products.search', ['q' => 'Komik Contoh']))
            ->assertOk()
            ->assertJsonPath('0.title', 'Komik Contoh');
    }

    public function test_cashier_checkout_applies_discounts_fees_and_reduces_stock(): void
    {
        $cashier = $this->cashier();
        $comic = $this->comic(['price' => 10000, 'stock' => 5]);

        $response = $this->actingAs($cashier)->post(route('kasir.checkout'), [
            'items' => [['comic_id' => $comic->id, 'quantity' => 2]],
            'discount_percent' => 10,
            'discount_amount' => 500,
            'tax' => 1000,
            'other_fee' => 500,
            'paid' => 25000,
            'payment_method' => 'cash',
            'customer_name' => 'Pelanggan Contoh',
        ]);

        $sale = Sale::firstOrFail();
        $response->assertRedirect(route('kasir.receipt', $sale));
        $this->assertSame('20000.00', $sale->subtotal);
        $this->assertSame('19000.00', $sale->total);
        $this->assertSame('6000.00', $sale->change);
        $this->assertSame(3, $comic->fresh()->stock);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'comic_id' => $comic->id,
            'quantity' => 2,
            'subtotal' => 20000,
        ]);
    }

    public function test_ajax_checkout_returns_invoice_and_invoice_can_be_downloaded_as_pdf(): void
    {
        $cashier = $this->cashier();
        $comic = $this->comic(['price' => 15000]);

        $response = $this->actingAs($cashier)->postJson(route('kasir.checkout'), [
            'items' => [['comic_id' => $comic->id, 'quantity' => 2]],
            'discount_percent' => 10,
            'paid' => 30000,
            'payment_method' => 'cash',
            'customer_name' => 'Pelanggan AJAX',
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Transaksi berhasil disimpan.')
            ->assertJsonPath('invoice.customer_name', 'Pelanggan AJAX')
            ->assertJsonPath('invoice.items.0.title', 'Komik Contoh')
            ->assertJsonPath('invoice.total', 27000);

        $sale = Sale::firstOrFail();
        $this->get(route('kasir.receipt.download', $sale))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload("invoice-{$sale->invoice}.pdf");
    }

    public function test_cashier_can_load_active_printers_and_request_thermal_print_as_json(): void
    {
        $cashier = $this->cashier();
        $sale = Sale::create([
            'user_id' => $cashier->id,
            'invoice' => 'INV-PRINT-JSON-001',
            'subtotal' => 10000,
            'total' => 10000,
            'paid' => 10000,
            'change' => 0,
            'payment_method' => 'cash',
        ]);

        $this->instance(WindowsPrinterDiscovery::class, \Mockery::mock(WindowsPrinterDiscovery::class, function ($mock) {
            $mock->shouldReceive('activePrinters')->once()->andReturn([
                ['name' => 'POS80', 'status' => 'Normal', 'default' => true, 'thermal' => true],
            ]);
        }));
        $this->actingAs($cashier)
            ->getJson(route('kasir.printers'))
            ->assertOk()
            ->assertJsonPath('printers.0.name', 'POS80')
            ->assertJsonPath('printers.0.thermal', true)
            ->assertJsonPath('printers.0.default', true);

        $this->mock(EscPosReceiptPrinter::class, function ($mock) use ($sale) {
            $mock->shouldReceive('printSale')
                ->once()
                ->withArgs(fn (Sale $printedSale, ?string $printerName) => $printedSale->is($sale) && $printerName === 'POS80');
        });
        $this->postJson(route('kasir.receipt.print', $sale), ['printer_name' => 'POS80'])
            ->assertOk()
            ->assertJsonPath('message', 'Struk berhasil dikirim ke printer.');
    }

    public function test_non_cash_payment_is_recorded_at_the_exact_total(): void
    {
        $cashier = $this->cashier();
        $comic = $this->comic(['price' => 12000]);

        $this->actingAs($cashier)->post(route('kasir.checkout'), [
            'items' => [['comic_id' => $comic->id, 'quantity' => 1]],
            'paid' => 0,
            'payment_method' => 'qris',
        ])->assertRedirect();

        $sale = Sale::firstOrFail();
        $this->assertSame('12000.00', $sale->paid);
        $this->assertSame('0.00', $sale->change);
        $this->assertSame('qris', $sale->payment_method);
    }

    public function test_checkout_rejects_insufficient_payment_and_excess_discount(): void
    {
        $cashier = $this->cashier();
        $comic = $this->comic(['price' => 10000]);

        $this->actingAs($cashier)->from(route('kasir.pos'))->post(route('kasir.checkout'), [
            'items' => [['comic_id' => $comic->id, 'quantity' => 1]],
            'paid' => 5000,
            'payment_method' => 'cash',
        ])->assertSessionHasErrors('paid');

        $this->get(route('kasir.pos'))
            ->assertOk()
            ->assertViewHas('initialCart', fn ($cart) => isset($cart[$comic->id]) && $cart[$comic->id]['qty'] === 1);

        $this->from(route('kasir.pos'))->post(route('kasir.checkout'), [
            'items' => [['comic_id' => $comic->id, 'quantity' => 1]],
            'discount_percent' => 50,
            'discount_amount' => 6000,
            'paid' => 10000,
            'payment_method' => 'cash',
        ])->assertSessionHasErrors('discount_amount');

        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(10, $comic->fresh()->stock);
    }

    public function test_cashier_can_hold_resume_and_complete_their_transaction(): void
    {
        $cashier = $this->cashier();
        $comic = $this->comic(['stock' => 4]);

        $this->actingAs($cashier)->post(route('kasir.hold'), [
            'items' => [['comic_id' => $comic->id, 'quantity' => 2]],
            'discount_percent' => 5,
            'payment_method' => 'cash',
            'customer_name' => 'Pelanggan Ditahan',
        ])->assertRedirect();

        $held = $cashier->heldTransactions()->firstOrFail();
        $this->get(route('kasir.pos', ['held' => $held->id]))
            ->assertOk()
            ->assertSee($held->reference)
            ->assertSee('Komik Contoh');

        $this->post(route('kasir.checkout'), [
            'held_transaction_id' => $held->id,
            'items' => [['comic_id' => $comic->id, 'quantity' => 2]],
            'discount_percent' => 5,
            'paid' => 19000,
            'payment_method' => 'cash',
            'customer_name' => 'Pelanggan Ditahan',
        ])->assertRedirect();

        $this->assertDatabaseMissing('held_transactions', ['id' => $held->id]);
        $this->assertSame(2, $comic->fresh()->stock);
        $this->assertSame(19000.0, (float) Sale::firstOrFail()->total);
    }

    public function test_cashier_cannot_open_another_cashiers_held_transaction_or_sale(): void
    {
        $owner = $this->cashier();
        $otherCashier = $this->cashier();
        $comic = $this->comic();
        $held = $owner->heldTransactions()->create([
            'reference' => 'HOLD-OWNER-001',
            'draft' => ['items' => [['comic_id' => $comic->id, 'quantity' => 1]]],
        ]);
        $sale = Sale::create([
            'user_id' => $owner->id,
            'invoice' => 'INV-OWNER-001',
            'subtotal' => 10000,
            'total' => 10000,
            'paid' => 10000,
            'change' => 0,
            'payment_method' => 'cash',
        ]);

        $this->actingAs($otherCashier)
            ->get(route('kasir.pos', ['held' => $held->id]))
            ->assertNotFound();

        $this->get(route('kasir.receipt', $sale))
            ->assertNotFound();
    }

    public function test_admin_is_blocked_from_cashier_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('kasir.pos'))
            ->assertForbidden();
    }

    public function test_cashier_is_blocked_from_admin_routes(): void
    {
        $this->actingAs($this->cashier())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_cannot_delete_another_admin_through_cashier_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_admin_can_manage_comics_and_barcode_values_must_be_unique(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $existing = $this->comic(['barcode' => '8991234567890']);
        $payload = [
            'category_id' => $existing->category_id,
            'title' => 'Komik Baru',
            'author' => 'Penulis Baru',
            'publisher' => 'Penerbit Baru',
            'price' => 15000,
            'stock' => 4,
            'barcode' => '8991234567890',
        ];

        $this->actingAs($admin)->from(route('admin.comics.create'))
            ->post(route('admin.comics.store'), $payload)
            ->assertSessionHasErrors('barcode');

        $payload['barcode'] = '8991234567891';
        $this->post(route('admin.comics.store'), $payload)
            ->assertRedirect(route('admin.dashboard'));

        $created = Comic::where('title', 'Komik Baru')->firstOrFail();
        $this->put(route('admin.comics.update', $created), array_merge($payload, ['barcode' => $created->barcode]))
            ->assertRedirect(route('admin.comics.index'));

        $this->delete(route('admin.comics.destroy', $created))
            ->assertRedirect();
        $this->assertDatabaseMissing('comics', ['id' => $created->id]);
    }

    public function test_unconfigured_thermal_printer_returns_a_visible_error(): void
    {
        $cashier = $this->cashier();
        $sale = Sale::create([
            'user_id' => $cashier->id,
            'invoice' => 'INV-PRINT-001',
            'subtotal' => 10000,
            'total' => 10000,
            'paid' => 10000,
            'change' => 0,
            'payment_method' => 'cash',
        ]);

        $this->actingAs($cashier)->from(route('kasir.receipt', $sale))
            ->post(route('kasir.receipt.print', $sale))
            ->assertRedirect(route('kasir.receipt', $sale))
            ->assertSessionHasErrors('printer');
    }

    private function cashier(): User
    {
        return User::factory()->create(['role' => 'kasir']);
    }

    private function comic(array $attributes = []): Comic
    {
        $category = Category::create(['name' => 'Kategori Uji '.uniqid()]);

        return Comic::create(array_merge([
            'category_id' => $category->id,
            'title' => 'Komik Contoh',
            'author' => 'Penulis Uji',
            'publisher' => 'Penerbit Uji',
            'price' => 10000,
            'stock' => 10,
        ], $attributes));
    }
}
