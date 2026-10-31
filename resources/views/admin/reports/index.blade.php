@extends('layouts.app')

@section('title', 'Laporan Penjualan')

@section('content')
<h1 class="h3 mb-3">Laporan penjualan</h1>

<form class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label" for="from">Dari tanggal</label>
            <input id="from" type="date" class="form-control" name="from" value="{{ request('from') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label" for="to">Sampai tanggal</label>
            <input id="to" type="date" class="form-control" name="to" value="{{ request('to') }}">
        </div>
        <div class="col-md-6">
            <button class="btn btn-primary">Filter</button>
            <a class="btn btn-success" href="{{ route('admin.reports.export', request()->query()) }}">Ekspor CSV</a>
            <a target="_blank" rel="noopener" class="btn btn-secondary"
               href="{{ route('admin.reports.print', request()->query()) }}">Cetak laporan</a>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th>Metode</th><th>Subtotal</th><th>Diskon (%)</th><th>Total</th></tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice }}</td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->user->name }}</td>
                        <td>{{ strtoupper(str_replace('_', ' ', $sale->payment_method)) }}</td>
                        <td>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                        <td>{{ number_format($sale->discount_percent, 2, ',', '.') }}%</td>
                        <td>Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada transaksi pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $sales->links() }}</div>
@endsection
