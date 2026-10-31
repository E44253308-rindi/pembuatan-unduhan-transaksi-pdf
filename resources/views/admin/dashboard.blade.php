@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="h3 mb-0">Dashboard Admin</h1>
    <a class="btn btn-primary" href="{{ route('admin.comics.create') }}">+ Tambah produk</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary h-100">
            <div class="card-body">
                <h2 class="h6">Penjualan hari ini</h2>
                <p class="h3 mb-0">Rp {{ number_format($dailySales, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success h-100">
            <div class="card-body">
                <h2 class="h6">Total produk</h2>
                <p class="h3 mb-0">{{ $totalComics }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-warning h-100">
            <div class="card-body">
                <h2 class="h6">Total transaksi</h2>
                <p class="h3 mb-0">{{ $totalTransactions }}</p>
            </div>
        </div>
    </div>
</div>

<section aria-labelledby="recent-products-heading">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 id="recent-products-heading" class="h4 mb-1">Produk terbaru</h2>
            <p class="text-muted mb-0">Produk yang baru ditambahkan langsung tampil di sini.</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('admin.comics.index') }}">Kelola semua produk</a>
    </div>

    <div class="row g-3">
        @forelse($recentComics as $comic)
            <div class="col-6 col-md-4 col-xl-3">
                <article class="card h-100 shadow-sm">
                    <img
                        src="{{ $comic->cover ? \Illuminate\Support\Facades\Storage::disk('public')->url($comic->cover) : asset('images/no-cover.svg') }}"
                        class="card-img-top"
                        alt="Sampul {{ $comic->title }}"
                        loading="lazy"
                        style="height: 220px; object-fit: cover; background: #f1f3f5;"
                    >
                    <div class="card-body d-flex flex-column">
                        <h3 class="h6 card-title">{{ $comic->title }}</h3>
                        <p class="small text-muted mb-2">{{ $comic->category->name }} · {{ $comic->author }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-auto gap-2">
                            <strong class="text-primary">Rp {{ number_format($comic->price, 0, ',', '.') }}</strong>
                            <span class="badge {{ $comic->stock > 0 ? 'text-bg-success' : 'text-bg-secondary' }}">
                                Stok {{ $comic->stock }}
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <a class="btn btn-sm btn-outline-secondary w-100"
                           href="{{ route('admin.comics.edit', $comic) }}">Edit produk</a>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="card card-body text-center py-5">
                    <p class="text-muted mb-3">Belum ada produk. Tambahkan produk pertama beserta foto sampulnya.</p>
                    <a class="btn btn-primary align-self-center" href="{{ route('admin.comics.create') }}">Tambah produk</a>
                </div>
            </div>
        @endforelse
    </div>
</section>
@endsection
