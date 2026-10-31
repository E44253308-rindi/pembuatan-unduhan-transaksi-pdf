@extends('layouts.app')

@section('title', 'Data Komik')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Data komik</h1>
    <a class="btn btn-primary" href="{{ route('admin.comics.create') }}">Tambah komik</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead>
                <tr><th>Sampul</th><th>Judul</th><th>Kategori</th><th>Barcode</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($comics as $comic)
                    <tr>
                        <td>
                            @if($comic->cover)
                                <img src="{{ asset('storage/'.$comic->cover) }}" alt="Sampul {{ $comic->title }}" width="50">
                            @endif
                        </td>
                        <td>{{ $comic->title }}</td>
                        <td>{{ $comic->category->name }}</td>
                        <td>{{ $comic->barcode ?: '—' }}</td>
                        <td>Rp {{ number_format($comic->price, 0, ',', '.') }}</td>
                        <td>{{ $comic->stock }}</td>
                        <td class="text-nowrap">
                            <a class="btn btn-sm btn-info" href="{{ route('admin.comics.show', $comic) }}">Detail</a>
                            <a class="btn btn-sm btn-warning" href="{{ route('admin.comics.edit', $comic) }}">Edit</a>
                            <form class="d-inline" method="POST" action="{{ route('admin.comics.destroy', $comic) }}"
                                  onsubmit="return confirm('Hapus komik ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data komik.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $comics->links() }}</div>
@endsection
