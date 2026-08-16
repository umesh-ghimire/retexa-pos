<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $yesterdayStart = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();

        // ---- Today vs yesterday summary numbers ----
        $todaySales = (float) Sale::whereBetween('created_at', [$todayStart, $todayEnd])->sum('total');
        $yesterdaySales = (float) Sale::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->sum('total');
        $salesChangePct = $this->percentChange($todaySales, $yesterdaySales);

        $todayBillsCount = Sale::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $yesterdayBillsCount = Sale::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->count();
        $billsChangePct = $this->percentChange($todayBillsCount, $yesterdayBillsCount);

        $newCustomersToday = Customer::whereBetween('created_at', [$todayStart, $todayEnd])->count();
        $newCustomersYesterday = Customer::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])->count();
        $customersChangePct = $this->percentChange($newCustomersToday, $newCustomersYesterday);

        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock_level')->count();

        $averageOrderValue = $todayBillsCount > 0 ? $todaySales / $todayBillsCount : 0;

        $totalItemsSoldToday = (float) SaleItem::whereHas('sale', function ($query) use ($todayStart, $todayEnd) {
            $query->whereBetween('created_at', [$todayStart, $todayEnd]);
        })->sum('quantity');

        // ---- Sales Overview (today, hour by hour) ----
        $todaySalesRows = Sale::whereBetween('created_at', [$todayStart, $todayEnd])->get(['total', 'created_at']);

        $hourlyTotals = array_fill(0, 24, 0.0);
        foreach ($todaySalesRows as $row) {
            $hourlyTotals[(int) $row->created_at->format('G')] += (float) $row->total;
        }

        $chartLabels = [];
        $chartValues = [];
        foreach ($hourlyTotals as $hour => $total) {
            $chartLabels[] = Carbon::createFromTime($hour)->format('g A');
            $chartValues[] = round($total, 2);
        }

        $peakHour = array_search(max($hourlyTotals), $hourlyTotals);
        $bestSellingTime = max($hourlyTotals) > 0
            ? Carbon::createFromTime($peakHour)->format('g A') . ' - ' . Carbon::createFromTime(($peakHour + 1) % 24)->format('g A')
            : '--';

        // ---- Low stock alerts ----
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock_level')
            ->orderBy('stock')
            ->limit(6)
            ->get(['id', 'name', 'stock', 'min_stock_level', 'image']);

        // ---- Recent activity, merged from real events (bills, stock movements, new customers) ----
        $recentBills = Sale::latest()->limit(5)->get(['id', 'bill_number', 'created_at'])
            ->map(fn ($sale) => [
                'type' => 'bill',
                'title' => 'New bill created',
                'subtitle' => 'Bill #' . $sale->bill_number,
                'time' => $sale->created_at,
            ]);

        $recentStock = StockMovement::with('product:id,name')->latest()->limit(5)->get()
            ->map(function ($movement) {
                $qty = rtrim(rtrim((string) $movement->quantity, '0'), '.');
                $verb = match ($movement->type) {
                    'in' => 'Restocked',
                    'out' => 'Sold/removed',
                    'set' => 'Adjusted to',
                    default => 'Updated',
                };

                return [
                    'type' => 'stock',
                    'title' => 'Stock updated',
                    'subtitle' => ($movement->product->name ?? 'Product') . ' — ' . $verb . ' ' . $qty,
                    'time' => $movement->created_at,
                ];
            });

        $recentCustomers = Customer::latest()->limit(5)->get(['id', 'name', 'created_at'])
            ->map(fn ($customer) => [
                'type' => 'customer',
                'title' => 'New customer added',
                'subtitle' => $customer->name ?: 'Walk-in customer',
                'time' => $customer->created_at,
            ]);

        $recentActivity = $recentBills->concat($recentStock)->concat($recentCustomers)
            ->sortByDesc('time')
            ->take(6)
            ->values();

        $shopName = Setting::get('shop_name', config('app.name', 'RETEXA'));

        return view('admin.dashboard', compact(
            'todaySales',
            'salesChangePct',
            'todayBillsCount',
            'billsChangePct',
            'lowStockCount',
            'newCustomersToday',
            'customersChangePct',
            'averageOrderValue',
            'totalItemsSoldToday',
            'bestSellingTime',
            'chartLabels',
            'chartValues',
            'lowStockProducts',
            'recentActivity',
            'shopName'
        ));
    }

    /**
     * Percentage change from $previous to $current, safe against
     * division by zero (no data yesterday isn't a crash, just 0/blank).
     */
    private function percentChange(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}