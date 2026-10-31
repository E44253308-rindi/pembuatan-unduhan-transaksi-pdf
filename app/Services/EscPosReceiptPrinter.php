<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Contracts\Config\Repository;
use LogicException;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class EscPosReceiptPrinter
{
    public function __construct(
        private Repository $config,
        private WindowsPrinterDiscovery $printerDiscovery,
    ) {}

    public function printSale(Sale $sale, ?string $printerName = null): void
    {
        $connection = $printerName ? 'windows' : $this->config->get('escpos.connection');
        $connector = match ($connection) {
            'windows' => $this->windowsConnector($printerName),
            'network' => $this->networkConnector(),
            default => throw new LogicException('Koneksi printer ESC/POS belum dikonfigurasi.'),
        };

        $printer = new Printer($connector);

        try {
            $sale->loadMissing('items.comic', 'user');
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text($this->config->get('escpos.store_name')."\n");
            $printer->setEmphasis(false);

            foreach (['store_address', 'store_phone'] as $setting) {
                $value = $this->config->get("escpos.{$setting}");
                if ($value) {
                    $printer->text($value."\n");
                }
            }

            $printer->text("--------------------------------\n");
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("Invoice: {$sale->invoice}\n");
            $printer->text("Kasir: {$sale->user->name}\n");
            $printer->text($sale->created_at->format('d-m-Y H:i')."\n");

            foreach ($sale->items as $item) {
                $printer->text("\n".$item->comic->title."\n");
                $printer->text($item->quantity.' x '.number_format((float) $item->price, 0, ',', '.'));
                $printer->text(' = Rp '.number_format((float) $item->subtotal, 0, ',', '.')."\n");
            }

            $percentageDiscount = (float) $sale->subtotal * (float) $sale->discount_percent / 100;
            $printer->text("--------------------------------\n");
            $printer->text('Subtotal: Rp '.number_format((float) $sale->subtotal, 0, ',', '.')."\n");

            if ($percentageDiscount + (float) $sale->discount_amount > 0) {
                $printer->text('Diskon: -Rp '.number_format($percentageDiscount + (float) $sale->discount_amount, 0, ',', '.')."\n");
            }

            if ((float) $sale->tax > 0) {
                $printer->text('Pajak: Rp '.number_format((float) $sale->tax, 0, ',', '.')."\n");
            }

            if ((float) $sale->other_fee > 0) {
                $printer->text('Biaya lain: Rp '.number_format((float) $sale->other_fee, 0, ',', '.')."\n");
            }

            $printer->setEmphasis(true);
            $printer->text('TOTAL: Rp '.number_format((float) $sale->total, 0, ',', '.')."\n");
            $printer->setEmphasis(false);
            $printer->text('Bayar: Rp '.number_format((float) $sale->paid, 0, ',', '.')."\n");
            $printer->text('Kembali: Rp '.number_format((float) $sale->change, 0, ',', '.')."\n");
            $printer->text('Metode: '.strtoupper(str_replace('_', ' ', $sale->payment_method))."\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("\nTerima kasih\n");
            $printer->feed(3);
            $printer->cut();
        } finally {
            $printer->close();
        }
    }

    private function windowsConnector(?string $printerName): WindowsPrintConnector
    {
        $name = $printerName ?: $this->config->get('escpos.printer_name');

        if (! is_string($name) || trim($name) === '') {
            throw new LogicException('ESCPOS_PRINTER_NAME harus diisi untuk koneksi Windows.');
        }

        if (! $this->printerDiscovery->isActive($name)) {
            throw new LogicException('Printer yang dipilih tidak aktif atau tidak lagi tersedia.');
        }

        return new WindowsPrintConnector($name);
    }

    private function networkConnector(): NetworkPrintConnector
    {
        $host = $this->config->get('escpos.printer_host');
        $port = $this->config->get('escpos.printer_port');

        if (! is_string($host) || trim($host) === '' || ! is_numeric($port) || (int) $port < 1 || (int) $port > 65535) {
            throw new LogicException('ESCPOS_PRINTER_HOST dan ESCPOS_PRINTER_PORT harus dikonfigurasi dengan benar.');
        }

        return new NetworkPrintConnector($host, (int) $port);
    }
}
