@extends('layouts.app')

@section('title', $comic->exists ? 'Edit Komik' : 'Tambah Komik')

@section('content')
<h1 class="h3 mb-3">{{ $comic->exists ? 'Edit Komik' : 'Tambah Komik' }}</h1>

<form method="POST" enctype="multipart/form-data"
      action="{{ $comic->exists ? route('admin.comics.update', $comic) : route('admin.comics.store') }}"
      class="card card-body">
    @csrf
    @if($comic->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="title">Judul</label>
            <input id="title" class="form-control" name="title" value="{{ old('title', $comic->title) }}" required maxlength="255">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="category">Kategori</label>
            <select id="category" class="form-select" name="category_id" required>
                <option value="">Pilih kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $comic->category_id) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="author">Penulis</label>
            <input id="author" class="form-control" name="author" value="{{ old('author', $comic->author) }}" required maxlength="255">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="publisher">Penerbit</label>
            <input id="publisher" class="form-control" name="publisher" value="{{ old('publisher', $comic->publisher) }}" required maxlength="255">
        </div>
        <div class="col-md-6">
            <label class="form-label" for="barcode">Barcode (opsional)</label>
            <input id="barcode" class="form-control" name="barcode" value="{{ old('barcode', $comic->barcode) }}" maxlength="100">
            <div class="form-text">Setiap barcode harus unik. Scanner POS akan mencari komik dari kode ini.</div>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="price">Harga (Rp)</label>
            <input id="price" class="form-control" type="number" name="price" min="0" step="0.01"
                   value="{{ old('price', $comic->price) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label" for="stock">Stok</label>
            <input id="stock" class="form-control" type="number" name="stock" min="0"
                   value="{{ old('stock', $comic->stock) }}" required>
        </div>
        <div class="col-12">
            <label class="form-label" for="cover">Foto produk / sampul</label>
            <input id="cover" class="form-control" type="file" name="cover" accept="image/jpeg,image/png,image/webp,image/gif">
            <div class="form-text">JPG, PNG, WebP, atau GIF; maksimal 2 MB. Foto akan langsung tampil di dashboard setelah produk disimpan.</div>
            <img id="coverPreview"
                 src="{{ $comic->cover ? \Illuminate\Support\Facades\Storage::disk('public')->url($comic->cover) : asset('images/no-cover.svg') }}"
                 alt="Pratinjau foto {{ $comic->title ?: 'produk' }}"
                 class="mt-3 rounded border"
                 style="width: 160px; height: 210px; object-fit: cover;">
        </div>
        <div class="col-12">
            <label class="form-label" for="synopsis">Sinopsis</label>
            <textarea id="synopsis" class="form-control" name="synopsis" rows="4">{{ old('synopsis', $comic->synopsis) }}</textarea>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary">Simpan</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.comics.index') }}">Kembali</a>
    </div>
</form>
@endsection

@section('scripts')
<script>
    const coverInput = document.getElementById('cover');
    const coverPreview = document.getElementById('coverPreview');

    coverInput.addEventListener('change', () => {
        const [file] = coverInput.files;
        if (file) coverPreview.src = URL.createObjectURL(file);
    });
</script>
@endsection
