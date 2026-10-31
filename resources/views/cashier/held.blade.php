@extends('layouts.app')

@section('title', 'Transaksi Ditahan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Transaksi ditahan</h1>
    <a class="btn btn-primary" href="{{ route('kasir.pos') }}">Transaksi baru</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr><th>Referensi</th><th>Waktu ditahan</th><th>Jumlah komik</th><th>Estimasi subtotal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    @php
                        $items = $transaction->draft['items'] ?? [];
                        $quantity = collect($items)->sum('quantity');
                    @endphp
                    <tr>
                        <td>{{ $transaction->reference }}</td>
                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $quantity }} item</td>
                        <td>Rp {{ number_format($transaction->estimated_total, 0, ',', '.') }}</td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-primary" href="{{ route('kasir.pos', ['held' => $transaction->id]) }}">Lanjutkan</a>
                            <form class="d-inline" method="POST" action="{{ route('kasir.held.destroy', $transaction) }}"
                                  onsubmit="return confirm('Hapus transaksi yang ditahan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada transaksi yang ditahan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $transactions->links() }}</div>
@endsection
