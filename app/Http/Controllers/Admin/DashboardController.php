<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $startOfWeek = now()->startOfWeek();
        $startOfMonth = now()->startOfMonth();

        $todaySales = Sale::where('created_at', '>=', $today)->sum('total');
        $weekSales = Sale::where('created_at', '>=', $startOfWeek)->sum('total');
        $monthSales = Sale::where('created_at', '>=', $startOfMonth)->sum('total');

        $totalBills = Sale::count();
        $totalRevenue = Sale::sum('total');

        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock_level')
            ->orderBy('stock')
            ->limit(8)
            ->get();

        $bestSellingProducts = SaleItem::select('product_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('SUM(line_total) as total_revenue')
            ->whereNotNull('product_id')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->with('product:id,name')
            ->limit(5)
            ->get();

        $recentTransactions = Sale::with('customer')
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact(
            'todaySales',
            'weekSales',
            'monthSales',
            'totalBills',
            'totalRevenue',
            'lowStockProducts',
            'bestSellingProducts',
            'recentTransactions'
        ));
    }
}