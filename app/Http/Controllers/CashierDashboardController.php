<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Sale;
class CashierDashboardController extends Controller {
 public function index(Request $request) { $sales=Sale::where('user_id',$request->user()->id)->whereDate('created_at',today())->get(); return view('cashier.dashboard',['dailySales'=>$sales->sum('total'),'todayTransactions'=>$sales->count(),'sales'=>$sales->load('items.comic')]); }
}
