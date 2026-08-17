<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Reports dashboard: KPI cards, sales trend, payment breakdown,
     * top products, low stock, recent transactions — all scoped to
     * the selected date range.
     */
    public function index(Request $request)
    {
        [$start, $end, $range, $rangeLabel] = $this->resolveRange($request);

        $data = $this->buildReportData($start, $end);

        return view('admin.reports.index', array_merge($data, [
            'range' => $range,
            'rangeLabel' => $rangeLabel,
            'dateFrom' => $start->toDateString(),
            'dateTo' => $end->toDateString(),
        ]));
    }

    /**
     * CSV export of the recent transactions for the selected range.
     * Zero dependencies — Laravel can stream CSV natively.
     */
    public function exportCsv(Request $request)
    {
        [$start, $end] = $this->resolveRange($request);

        $sales = Sale::with('customer')
            ->whereBetween('created_at', [$start, $end])
            ->withCount('items')
            ->latest()
            ->get();

        $filename = 'sales-report-' . $start->toDateString() . '-to-' . $end->toDateString() . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Bill No.', 'Date & Time', 'Customer', 'Items', 'Subtotal', 'Discount', 'Total Amount', 'Payment Method', 'Status']);

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->bill_number,
                    $sale->created_at->format('Y-m-d H:i'),
                    $sale->customer->name ?? 'Walk-in Customer',
                    $sale->items_count,
                    number_format((float) $sale->subtotal, 2, '.', ''),
                    number_format((float) $sale->discount, 2, '.', ''),
                    number_format((float) $sale->total, 2, '.', ''),
                    $this->paymentLabel($sale->payment_method),
                    $sale->due_amount > 0 ? 'Due' : 'Completed',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * "Excel" export. No maatwebsite/excel dependency is installed, and the
     * user asked to avoid adding packages where avoidable — an HTML table
     * served with the Excel MIME type opens natively in Excel, Google
     * Sheets, and LibreOffice without any composer package.
     */
    public function exportExcel(Request $request)
    {
        [$start, $end, , $rangeLabel] = $this->resolveRange($request);

        $sales = Sale::with('customer')
            ->whereBetween('created_at', [$start, $end])
            ->withCount('items')
            ->latest()
            ->get();

        $filename = 'sales-report-' . $start->toDateString() . '-to-' . $end->toDateString() . '.xls';

        $html = view('admin.reports.export-table', [
            'sales' => $sales,
            'rangeLabel' => $rangeLabel,
            'start' => $start,
            'end' => $end,
            'paymentLabel' => fn ($m) => $this->paymentLabel($m),
        ])->render();

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response($html, 200, $headers);
    }

    /**
     * PDF export. Laravel has no built-in PDF generator, so this uses
     * barryvdh/laravel-dompdf if it's installed. If it isn't yet, we
     * fail gracefully with instructions instead of a hard crash.
     */
    public function exportPdf(Request $request)
    {
        if (! class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->with('error', 'PDF export needs one package. Run: composer require barryvdh/laravel-dompdf');
        }

        [$start, $end, , $rangeLabel] = $this->resolveRange($request);

        $data = $this->buildReportData($start, $end);
        $data['rangeLabel'] = $rangeLabel;
        $data['start'] = $start;
        $data['end'] = $end;
        $data['paymentLabel'] = fn ($m) => $this->paymentLabel($m);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports.export-pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download('sales-report-' . $start->toDateString() . '-to-' . $end->toDateString() . '.pdf');
    }

    /**
     * Build every number the Reports page (and both exports) need,
     * scoped to [$start, $end]. Nothing here is fabricated — every
     * figure comes from Sale / SaleItem / Product rows.
     */
    private function buildReportData(Carbon $start, Carbon $end): array
    {
        $salesQuery = fn () => Sale::whereBetween('created_at', [$start, $end]);

        // ---- KPI cards ----
        $totalSales = (float) $salesQuery()->sum('total');
        $totalBills = $salesQuery()->count();
        $totalDiscount = (float) $salesQuery()->sum('discount');
        $averageBillValue = $totalBills > 0 ? $totalSales / $totalBills : 0;

        $itemsSold = (float) SaleItem::whereHas('sale', function ($q) use ($start, $end) {
            $q->whereBetween('created_at', [$start, $end]);
        })->sum('quantity');

        // Real profit: revenue - cost, using each product's cost_price.
        // Manually-entered line items (no product_id) have no known cost,
        // so they're treated as zero cost rather than guessed at.
        $totalProfit = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(sale_items.line_total - COALESCE(products.cost_price, 0) * sale_items.quantity), 0) as profit')
            ->value('profit');

        // ---- Previous period, for the "vs last period" deltas ----
        $days = $start->diffInDays($end) + 1;
        $prevEnd = (clone $start)->subSecond();
        $prevStart = (clone $prevEnd)->subDays($days - 1)->startOfDay();

        $prevSales = (float) Sale::whereBetween('created_at', [$prevStart, $prevEnd])->sum('total');
        $prevBills = Sale::whereBetween('created_at', [$prevStart, $prevEnd])->count();
        $prevDiscount = (float) Sale::whereBetween('created_at', [$prevStart, $prevEnd])->sum('discount');
        $prevItemsSold = (float) SaleItem::whereHas('sale', function ($q) use ($prevStart, $prevEnd) {
            $q->whereBetween('created_at', [$prevStart, $prevEnd]);
        })->sum('quantity');
        $prevProfit = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$prevStart, $prevEnd])
            ->selectRaw('COALESCE(SUM(sale_items.line_total - COALESCE(products.cost_price, 0) * sale_items.quantity), 0) as profit')
            ->value('profit');
        $prevAvgBill = $prevBills > 0 ? $prevSales / $prevBills : 0;

        $salesChangePct = $this->percentChange($totalSales, $prevSales);
        $profitChangePct = $this->percentChange($totalProfit, $prevProfit);
        $billsChangePct = $this->percentChange($totalBills, $prevBills);
        $avgBillChangePct = $this->percentChange($averageBillValue, $prevAvgBill);
        $discountChangePct = $this->percentChange($totalDiscount, $prevDiscount);
        $itemsSoldChangePct = $this->percentChange($itemsSold, $prevItemsSold);

        // ---- Sales & profit trend ----
        $isSingleDay = $start->isSameDay($end);
        [$chartLabels, $chartSales, $chartProfit] = $isSingleDay
            ? $this->hourlyTrend($start, $end)
            : $this->dailyTrend($start, $end);

        // ---- Payment method breakdown ----
        $paymentRows = Sale::whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $paymentBreakdown = $paymentRows->map(function ($row) use ($totalSales) {
            return [
                'method' => $row->payment_method,
                'label' => $this->paymentLabel($row->payment_method),
                'total' => (float) $row->total,
                'count' => (int) $row->cnt,
                'pct' => $totalSales > 0 ? round(((float) $row->total / $totalSales) * 100, 1) : 0,
            ];
        })->values();

        // ---- Top selling products ----
        $topProducts = SaleItem::select('product_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(line_total) as revenue'))
            ->whereNotNull('product_id')
            ->whereHas('sale', function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            })
            ->groupBy('product_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->with('product:id,name,image')
            ->get();

        // ---- Low stock products (current state, not date-scoped) ----
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock_level')
            ->orderBy('stock')
            ->limit(6)
            ->get(['id', 'name', 'stock', 'min_stock_level', 'image']);

        // ---- Recent transactions within the selected range ----
        $recentTransactions = Sale::with('customer')
            ->withCount('items')
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->limit(8)
            ->get();

        return compact(
            'totalSales', 'totalBills', 'averageBillValue', 'totalDiscount', 'itemsSold', 'totalProfit',
            'salesChangePct', 'profitChangePct', 'billsChangePct', 'avgBillChangePct', 'discountChangePct', 'itemsSoldChangePct',
            'chartLabels', 'chartSales', 'chartProfit',
            'paymentBreakdown',
            'topProducts',
            'lowStockProducts',
            'recentTransactions'
        );
    }

    /**
     * Turn the request's range/date params into a concrete [start, end]
     * window. Defaults to "today" when nothing valid is given.
     */
    private function resolveRange(Request $request): array
    {
        $range = $request->query('range', 'today');

        if (! in_array($range, ['today', 'week', 'month', 'custom'], true)) {
            $range = 'today';
        }

        if ($range === 'custom' && $request->filled('date_from') && $request->filled('date_to')) {
            try {
                $start = Carbon::parse($request->query('date_from'))->startOfDay();
                $end = Carbon::parse($request->query('date_to'))->endOfDay();

                if ($end->lt($start)) {
                    [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
                }
            } catch (\Exception $e) {
                $range = 'today';
            }
        }

        if ($range !== 'custom' || ! isset($start)) {
            switch ($range) {
                case 'week':
                    $start = now()->startOfWeek();
                    $end = now()->endOfWeek();
                    $rangeLabel = 'This Week';
                    break;
                case 'month':
                    $start = now()->startOfMonth();
                    $end = now()->endOfMonth();
                    $rangeLabel = 'This Month';
                    break;
                default:
                    $range = 'today';
                    $start = now()->startOfDay();
                    $end = now()->endOfDay();
                    $rangeLabel = 'Today';
            }
        } else {
            $rangeLabel = $start->format('M d, Y') . ' - ' . $end->format('M d, Y');
        }

        return [$start, $end, $range, $rangeLabel];
    }

    /**
     * Hour-by-hour sales & profit for a single-day range.
     */
    private function hourlyTrend(Carbon $start, Carbon $end): array
    {
        $salesRows = Sale::whereBetween('created_at', [$start, $end])->get(['total', 'created_at']);
        $itemRows = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->select('sales.created_at', DB::raw('(sale_items.line_total - COALESCE(products.cost_price, 0) * sale_items.quantity) as profit'))
            ->get();

        $hourlySales = array_fill(0, 24, 0.0);
        $hourlyProfit = array_fill(0, 24, 0.0);

        foreach ($salesRows as $row) {
            $hourlySales[(int) Carbon::parse($row->created_at)->format('G')] += (float) $row->total;
        }
        foreach ($itemRows as $row) {
            $hourlyProfit[(int) Carbon::parse($row->created_at)->format('G')] += (float) $row->profit;
        }

        $labels = [];
        $sales = [];
        $profit = [];
        foreach ($hourlySales as $hour => $total) {
            $labels[] = Carbon::createFromTime($hour)->format('g A');
            $sales[] = round($total, 2);
            $profit[] = round($hourlyProfit[$hour], 2);
        }

        return [$labels, $sales, $profit];
    }

    /**
     * Day-by-day sales & profit across a multi-day range. Every day in
     * the range gets a point, even with zero sales, so the line is
     * continuous.
     */
    private function dailyTrend(Carbon $start, Carbon $end): array
    {
        $salesRows = Sale::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as d, SUM(total) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $profitRows = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$start, $end])
            ->selectRaw('DATE(sales.created_at) as d, SUM(sale_items.line_total - COALESCE(products.cost_price, 0) * sale_items.quantity) as profit')
            ->groupBy('d')
            ->pluck('profit', 'd');

        $labels = [];
        $sales = [];
        $profit = [];

        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('M d');
            $sales[] = round((float) ($salesRows[$key] ?? 0), 2);
            $profit[] = round((float) ($profitRows[$key] ?? 0), 2);
            $cursor->addDay();
        }

        return [$labels, $sales, $profit];
    }

    private function paymentLabel(string $method): string
    {
        return match ($method) {
            'cash' => 'Cash',
            'qr' => 'QR / Digital',
            'credit' => 'Credit',
            default => ucfirst($method),
        };
    }

    private function percentChange(float $current, float $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}