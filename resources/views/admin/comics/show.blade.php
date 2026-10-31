@extends('layouts.app')

@section('title', $comic->title)

@section('content')
<a href="{{ route('admin.comics.index') }}">&larr; Kembali ke daftar komik</a>

<div class="card card-body mt-3">
    <h1 class="h3">{{ $comic->title }}</h1>
    <p><strong>Kategori:</strong> {{ $comic->category->name }}</p>
    <p><strong>Penulis:</strong> {{ $comic->author }} · <strong>Penerbit:</strong> {{ $comic->publisher }}</p>
    <p><strong>Barcode:</strong> {{ $comic->barcode ?: '—' }}</p>
    <p><strong>Harga:</strong> Rp {{ number_format($comic->price, 0, ',', '.') }} · <strong>Stok:</strong> {{ $comic->stock }}</p>
    @if($comic->synopsis)<p>{{ $comic->synopsis }}</p>@endif
    @if($comic->cover)
        <img src="{{ asset('storage/'.$comic->cover) }}" alt="Sampul {{ $comic->title }}" class="img-fluid" style="max-height:300px">
    @endif
</div>
@endsection
