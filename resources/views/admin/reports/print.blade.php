<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan KomikStore</title>
    <style>
        body { font: 13px Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <h1>Laporan penjualan</h1>
    <table>
        <thead>
            <tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th>Metode</th><th>Subtotal</th><th>Diskon (%)</th><th>Total</th></tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice }}</td>
                    <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $sale->user->name }}</td>
                    <td>{{ strtoupper(str_replace('_', ' ', $sale->payment_method)) }}</td>
                    <td>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                    <td>{{ number_format($sale->discount_percent, 2, ',', '.') }}%</td>
                    <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p><strong>Total:</strong> Rp {{ number_format($sales->sum('total'), 0, ',', '.') }}</p>
    <button class="no-print" type="button" onclick="window.print()">Cetak</button>
</body>
</html>
