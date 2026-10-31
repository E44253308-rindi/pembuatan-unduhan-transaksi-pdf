<?php
namespace App\Http\Controllers;
use App\Models\Sale; use Illuminate\Http\Request;
class ReportController extends Controller {
 public function index(Request $request){ $sales=Sale::with('user','items.comic')->when($request->from,fn($q,$v)=>$q->whereDate('created_at','>=',$v))->when($request->to,fn($q,$v)=>$q->whereDate('created_at','<=',$v))->latest()->paginate(15)->withQueryString(); return view('admin.reports.index',compact('sales')); }
 public function export(Request $request){ $sales=Sale::with('user')->when($request->from,fn($q,$v)=>$q->whereDate('created_at','>=',$v))->when($request->to,fn($q,$v)=>$q->whereDate('created_at','<=',$v))->latest()->get(); $filename='laporan_penjualan.csv'; return response()->streamDownload(function() use($sales){ $out=fopen('php://output','w'); fputcsv($out,['Invoice','Tanggal','Kasir','Metode Pembayaran','Subtotal','Diskon Persen','Diskon Nominal','Pajak','Biaya Lain','Total']); foreach($sales as $s) fputcsv($out,[$s->invoice,$s->created_at,$s->user->name,$s->payment_method,$s->subtotal,$s->discount_percent,$s->discount_amount,$s->tax,$s->other_fee,$s->total]); fclose($out); },$filename,['Content-Type'=>'text/csv']); }
 public function print(Request $request){ $sales=Sale::with('user')->when($request->from,fn($q,$v)=>$q->whereDate('created_at','>=',$v))->when($request->to,fn($q,$v)=>$q->whereDate('created_at','<=',$v))->latest()->get(); return view('admin.reports.print',compact('sales')); }
}
