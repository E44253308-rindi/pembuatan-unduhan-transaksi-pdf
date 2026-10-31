<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 14px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1f2937; }
        h1 { margin: 0 0 3px; color: #111827; font-size: 16px; text-align: center; }
        .center { text-align: center; }
        .muted { color: #64748b; line-height: 1.5; }
        .document-label { margin-top: 4px; color: #475569; font-size: 8px; font-weight: bold; letter-spacing: 1px; }
        .rule { border-top: 1px dashed #94a3b8; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 5px 2px; vertical-align: top; }
        th { border-bottom: 1px solid #cbd5e1; color: #475569; font-size: 8px; text-align: left; }
        .number { text-align: right; white-space: nowrap; }
        .summary td { border: 0; color: #475569; }
        .summary td.number { color: #1f2937; }
        .total td { border-top: 1px solid #94a3b8; color: #111827; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $store['store_name'] ?: 'KomikStore' }}</h1>
    <div class="center muted">
        @if($store['store_address']){{ $store['store_address'] }}<br>@endif
        @if($store['store_phone']){{ $store['store_phone'] }}<br>@endif
        <div class="document-label">BUKTI PEMBAYARAN</div>
    </div>
    <div class="rule"></div>
    <table class="summary">
        <tr><td>Invoice</td><td class="number">{{ $sale->invoice }}</td></tr>
        <tr><td>Tanggal</td><td class="number">{{ $sale->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td class="number">{{ $sale->user->name }}</td></tr>
        @if($sale->customer_name)<tr><td>Pelanggan</td><td class="number">{{ $sale->customer_name }}</td></tr>@endif
        @if($sale->customer_contact)<tr><td>Kontak</td><td class="number">{{ $sale->customer_contact }}</td></tr>@endif
    </table>
    <div class="rule"></div>
    <table>
        <thead><tr><th>Produk</th><th class="number">Qty</th><th class="number">Harga</th><th class="number">Jumlah</th></tr></thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->comic->title }}</td>
                    <td class="number">{{ $item->quantity }}</td>
                    <td class="number">{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="number">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="rule"></div>
    @php($percentageDiscount = (float) $sale->subtotal * (float) $sale->discount_percent / 100)
    <table class="summary">
        <tr><td>Subtotal</td><td class="number">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td></tr>
        @if($percentageDiscount + (float) $sale->discount_amount > 0)
            <tr><td>Diskon</td><td class="number">-Rp {{ number_format($percentageDiscount + (float) $sale->discount_amount, 0, ',', '.') }}</td></tr>
        @endif
        @if((float) $sale->tax > 0)<tr><td>Pajak</td><td class="number">Rp {{ number_format($sale->tax, 0, ',', '.') }}</td></tr>@endif
        @if((float) $sale->other_fee > 0)<tr><td>Biaya lain</td><td class="number">Rp {{ number_format($sale->other_fee, 0, ',', '.') }}</td></tr>@endif
        <tr class="total"><td>Total</td><td class="number">Rp {{ number_format($sale->total, 0, ',', '.') }}</td></tr>
        <tr><td>Bayar</td><td class="number">Rp {{ number_format($sale->paid, 0, ',', '.') }}</td></tr>
        <tr><td>Kembalian</td><td class="number">Rp {{ number_format($sale->change, 0, ',', '.') }}</td></tr>
        <tr><td>Metode</td><td class="number">{{ strtoupper(str_replace('_', ' ', $sale->payment_method)) }}</td></tr>
    </table>
    <div class="rule"></div>
    <div class="center muted">Terima kasih telah berbelanja di KomikStore.</div>
</body>
</html>
