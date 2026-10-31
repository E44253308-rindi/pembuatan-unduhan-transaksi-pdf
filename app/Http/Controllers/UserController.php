<?php
namespace App\Http\Controllers;
use App\Models\User; use Illuminate\Http\Request; use Illuminate\Support\Facades\Hash;
class UserController extends Controller {
 public function index(){ return view('admin.users.index',['users'=>User::where('role','kasir')->latest()->paginate(10)]); }
 public function store(Request $request){ $data=$request->validate(['name'=>'required|max:255','email'=>'required|email|unique:users','password'=>'required|min:6']); $data['password']=Hash::make($data['password']); $data['role']='kasir'; User::create($data); return back()->with('success','Akun kasir berhasil dibuat'); }
 public function update(Request $request,User $user){ abort_unless($user->role === 'kasir', 404); $data=$request->validate(['name'=>'required|max:255','email'=>'required|email|unique:users,email,'.$user->id,'password'=>'nullable|min:6']); if(!empty($data['password'])) $data['password']=Hash::make($data['password']); else unset($data['password']); $user->update($data); return back()->with('success','Akun kasir berhasil diperbarui'); }
 public function destroy(User $user){ abort_unless($user->role === 'kasir', 404); $user->delete(); return back()->with('success','Akun kasir berhasil dihapus'); }
}
