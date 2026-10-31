<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $sale->invoice }}</title>
    <style>
        body { margin: 20px auto; max-width: 420px; padding: 16px; font: 14px/1.45 monospace; color: #111; }
        .center { text-align: center; }
        .line { border-top: 1px dashed #555; margin: 10px 0; }
        .row { display: flex; justify-content: space-between; gap: 12px; }
        .item { margin: 8px 0; }
        .actions { margin: 20px 0; font-family: Arial, sans-serif; }
        @media print { body { width: 58mm; max-width: none; margin: 0; padding: 2mm; } .actions, .notice { display: none; } }
    </style>
</head>
<body>
    <div class="actions">
        <a href="{{ route('kasir.history') }}">Kembali ke riwayat</a>
        <button type="button" onclick="window.print()">Cetak dari browser</button>
        @if(config('escpos.connection') !== 'none')
            <form method="POST" action="{{ route('kasir.receipt.print', $sale) }}" style="display:inline">
                @csrf
                <button type="submit">Cetak thermal</button>
            </form>
        @else
            <span>Atur ESCPOS_CONNECTION untuk mengaktifkan printer thermal.</span>
        @endif
    </div>

    @if(session('success'))<p class="notice">{{ session('success') }}</p>@endif
    @if($errors->any())<p class="notice">{{ $errors->first() }}</p>@endif

    <header class="center">
        <strong>{{ config('escpos.store_name', 'KomikStore') }}</strong><br>
        @if(config('escpos.store_address')){{ config('escpos.store_address') }}<br>@endif
        @if(config('escpos.store_phone')){{ config('escpos.store_phone') }}<br>@endif
        {{ $sale->created_at->format('d/m/Y H:i') }}
    </header>
    <div class="line"></div>
    <div>Invoice: {{ $sale->invoice }}</div>
    <div>Kasir: {{ $sale->user->name }}</div>
    @if($sale->customer_name)<div>Pelanggan: {{ $sale->customer_name }}</div>@endif
    @if($sale->customer_contact)<div>Kontak: {{ $sale->customer_contact }}</div>@endif
    <div class="line"></div>

    @foreach($sale->items as $item)
        <div class="item">
            <div>{{ $item->comic->title }}</div>
            <div class="row">
                <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
            </div>
        </div>
    @endforeach

    @php($percentageDiscount = (float) $sale->subtotal * (float) $sale->discount_percent / 100)
    <div class="line"></div>
    <div class="row"><span>Subtotal</span><span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span></div>
    @if($percentageDiscount + (float) $sale->discount_amount > 0)
        <div class="row"><span>Diskon</span><span>-Rp {{ number_format($percentageDiscount + (float) $sale->discount_amount, 0, ',', '.') }}</span></div>
    @endif
    @if((float) $sale->tax > 0)
        <div class="row"><span>Pajak</span><span>Rp {{ number_format($sale->tax, 0, ',', '.') }}</span></div>
    @endif
    @if((float) $sale->other_fee > 0)
        <div class="row"><span>Biaya lain</span><span>Rp {{ number_format($sale->other_fee, 0, ',', '.') }}</span></div>
    @endif
    <div class="row"><strong>Total</strong><strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong></div>
    <div class="row"><span>Bayar</span><span>Rp {{ number_format($sale->paid, 0, ',', '.') }}</span></div>
    <div class="row"><span>Kembali</span><span>Rp {{ number_format($sale->change, 0, ',', '.') }}</span></div>
    <div>Metode: {{ strtoupper(str_replace('_', ' ', $sale->payment_method)) }}</div>
    <div class="line"></div>
    <div class="center">Terima kasih. Selamat membaca!</div>

    <script>
        @if(!session('thermal_print'))
            window.print();
        @endif
    </script>
</body>
</html>
