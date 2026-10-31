<?php
namespace App\Http\Controllers;
use App\Models\Category; use Illuminate\Http\Request;
class CategoryController extends Controller {
 public function index(){ return view('admin.categories.index',['categories'=>Category::withCount('comics')->latest()->paginate(10)]); }
 public function store(Request $request){ $request->validate(['name'=>'required|max:100|unique:categories']); Category::create(['name'=>$request->name]); return back()->with('success','Kategori berhasil ditambahkan'); }
 public function update(Request $request,Category $category){ $request->validate(['name'=>'required|max:100|unique:categories,name,'.$category->id]); $category->update(['name'=>$request->name]); return back()->with('success','Kategori berhasil diperbarui'); }
 public function destroy(Category $category){ $category->delete(); return back()->with('success','Kategori berhasil dihapus'); }
}
