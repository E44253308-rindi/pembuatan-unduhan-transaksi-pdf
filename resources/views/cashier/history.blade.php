@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<h1 class="h3 mb-3">Riwayat transaksi</h1>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Invoice</th><th>Tanggal</th><th>Total</th><th>Metode</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice }}</td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                        <td>{{ strtoupper(str_replace('_', ' ', $sale->payment_method)) }}</td>
                        <td><a target="_blank" rel="noopener" class="btn btn-sm btn-primary"
                               href="{{ route('kasir.receipt', $sale) }}">Lihat / cetak struk</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $sales->links() }}</div>
@endsection
