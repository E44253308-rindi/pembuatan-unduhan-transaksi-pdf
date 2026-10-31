<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Comic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class ComicController extends Controller
{
    public function index()
    {
        $comics = Comic::with('category')->latest()->paginate(10);

        return view('admin.comics.index', compact('comics'));
    }

    public function create()
    {
        return view('admin.comics.form', [
            'comic' => new Comic,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateComic($request);

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('covers', 'public');

            if (! is_string($data['cover'])) {
                throw new RuntimeException('Foto produk gagal disimpan. Periksa izin folder penyimpanan.');
            }
        }

        Comic::create($data);

        return redirect()->route('admin.dashboard')->with('success', 'Produk berhasil ditambahkan dan tampil di dashboard.');
    }

    public function show(Comic $comic)
    {
        return view('admin.comics.show', compact('comic'));
    }

    public function edit(Comic $comic)
    {
        return view('admin.comics.form', [
            'comic' => $comic,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Comic $comic)
    {
        $data = $this->validateComic($request, $comic);
        $previousCover = $comic->cover;
        $newCover = null;

        if ($request->hasFile('cover')) {
            $newCover = $request->file('cover')->store('covers', 'public');

            if (! is_string($newCover)) {
                throw new RuntimeException('Foto produk gagal disimpan. Periksa izin folder penyimpanan.');
            }

            $data['cover'] = $newCover;
        }

        $comic->update($data);

        if ($newCover && $previousCover) {
            Storage::disk('public')->delete($previousCover);
        }

        return redirect()->route('admin.comics.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Comic $comic)
    {
        if ($comic->cover) {
            Storage::disk('public')->delete($comic->cover);
        }

        $comic->delete();

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    private function validateComic(Request $request, ?Comic $comic = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'synopsis' => ['nullable', 'string'],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('comics', 'barcode')->ignore($comic?->id)],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
