<?php
namespace App\Http\Controllers;

use App\Models\Comic;
use App\Models\Sale;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'dailySales' => Sale::whereDate('created_at', today())->sum('total'),
            'totalComics' => Comic::count(),
            'totalTransactions' => Sale::count(),
            'recentComics' => Comic::with('category')->latest()->take(8)->get(),
        ]);
    }
}
