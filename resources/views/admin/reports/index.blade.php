@extends('admin.layouts.admin')

@section('title', 'Reports')

@section('styles')
<style>
    .rep-page{max-width:100%;}
    .rep-alert{background:#feeceb;border:1px solid #f8c9c5;color:#c0392b;border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:13.5px;}

    /* ---- Header ---- */
    .rep-header{
        display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;
        margin-bottom:22px;
    }
    .rep-header h4{margin-bottom:2px;}
    .rep-export-group{display:flex;gap:10px;flex-wrap:wrap;}
    .rep-export-btn{
        display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #eceef3;
        border-radius:9px;padding:9px 16px;font-size:13px;font-weight:600;color:#4a5568;text-decoration:none;
        transition:border-color .15s ease, color .15s ease, background-color .15s ease;
    }
    .rep-export-btn:hover{text-decoration:none;background:#fafbff;}
    .rep-export-btn.pdf{color:#e1362c;border-color:#f8c9c5;}
    .rep-export-btn.pdf:hover{background:#feeceb;}
    .rep-export-btn.excel{color:#1ca54a;border-color:#bfe9cb;}
    .rep-export-btn.excel:hover{background:#e5f9ea;}
    .rep-export-btn.csv{color:#6777ef;border-color:#c9cffa;}
    .rep-export-btn.csv:hover{background:#eef0fb;}

    /* ---- Filter bar ---- */
    .rep-filter-bar{
        display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:24px;
    }
    .rep-date-chip{
        display:flex;align-items:center;gap:10px;
        background:#fff;border:1px solid #eceef3;border-radius:10px;padding:10px 16px;
    }
    .rep-date-chip .date-icon{
        width:32px;height:32px;border-radius:8px;background:#eef0fb;color:#6777ef;
        display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;
    }
    .rep-date-chip .date-main{font-size:13.5px;font-weight:700;color:#191d21;line-height:1.25;}

    .rep-tabs{display:flex;gap:8px;flex-wrap:wrap;}
    .rep-tab{
        display:inline-flex;align-items:center;background:#fff;border:1px solid #eceef3;color:#4a5568;
        font-size:13px;font-weight:600;border-radius:9px;padding:9px 16px;text-decoration:none;
        transition:background-color .15s ease, color .15s ease, border-color .15s ease;
    }
    .rep-tab:hover{text-decoration:none;background:#fafbff;color:#191d21;}
    .rep-tab.active{background:#6777ef;border-color:#6777ef;color:#fff;}

    .rep-custom-form{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .rep-custom-form input[type="date"]{
        border:1px solid #eceef3;border-radius:9px;padding:8px 12px;font-size:13px;color:#4a5568;
    }
    .rep-custom-form button{
        border:1px solid #c9cffa;background:#6777ef;color:#fff;font-size:13px;font-weight:600;
        border-radius:9px;padding:9px 16px;cursor:pointer;
    }

    /* ---- KPI cards ---- */
    .rep-stats-row{
        display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:18px;
        margin-bottom:24px;
    }
    .rep-stat-card{
        background:#fff;border-radius:12px;padding:18px 20px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        min-width:0;
    }
    .rep-stat-card .stat-icon{
        width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;
        font-size:16px;margin-bottom:12px;
    }
    .rep-stat-card .stat-label{font-size:12.5px;color:#98a6ad;font-weight:600;}
    .rep-stat-card .stat-value{font-size:22px;font-weight:700;color:#191d21;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .rep-stat-card .stat-change{font-size:11.5px;font-weight:600;margin-top:4px;}
    .rep-stat-card .stat-change.up{color:#1ca54a;}
    .rep-stat-card .stat-change.down{color:#e1362c;}
    .rep-stat-card .stat-change.neutral{color:#98a6ad;}

    .bg-sales{background:#eef0fb;color:#6777ef;}
    .bg-profit{background:#e5f9ea;color:#1ca54a;}
    .bg-bills{background:#e5f0ff;color:#3b82f6;}
    .bg-items{background:#fff3e0;color:#c9790a;}
    .bg-avg{background:#e1f8f5;color:#0fb2a3;}
    .bg-discount{background:#feeceb;color:#e1362c;}

    /* ---- Generic section card ---- */
    .rep-card{
        background:#fff;border-radius:12px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        padding:20px;min-width:0;margin-bottom:24px;
    }
    .rep-card-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:6px;}
    .rep-card-head h6{margin:0;font-size:14.5px;font-weight:700;color:#191d21;}
    .rep-card-head a{font-size:12.5px;font-weight:600;color:#6777ef;text-decoration:none;}
    .rep-card-head a:hover{text-decoration:underline;}
    .rep-card-badge{font-size:11px;font-weight:700;color:#6777ef;background:#eef0fb;border-radius:20px;padding:4px 12px;}

    /* ---- Main grid: chart + payment breakdown ---- */
    .rep-main-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:20px;margin-bottom:0;align-items:start;}
    @media (max-width: 1199.98px){ .rep-main-grid{grid-template-columns:1fr;} }

    .chart-total-label{font-size:12px;color:#98a6ad;margin-top:2px;}
    .chart-total-value{font-size:21px;font-weight:700;color:#191d21;margin-bottom:4px;}

    /* ---- Payment breakdown ---- */
    .pm-donut-wrap{display:flex;flex-direction:column;align-items:center;}
    .pm-legend{list-style:none;margin:16px 0 0;padding:0;width:100%;}
    .pm-legend li{display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f4f5f8;font-size:13px;}
    .pm-legend li:last-child{border-bottom:none;}
    .pm-dot{width:9px;height:9px;border-radius:50%;display:inline-block;margin-right:8px;flex-shrink:0;}
    .pm-label{display:flex;align-items:center;color:#4a5568;font-weight:600;}
    .pm-value{font-weight:700;color:#191d21;}
    .pm-pct{color:#98a6ad;font-size:11.5px;margin-left:8px;}
    .pm-total-row{display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:14px;border-top:1px solid #eceef3;}
    .pm-total-label{font-size:12px;color:#98a6ad;}
    .pm-total-value{font-size:19px;font-weight:700;color:#191d21;}

    /* ---- Lists grid: top products + low stock ---- */
    .rep-lists-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:0;}
    @media (max-width: 991.98px){ .rep-lists-grid{grid-template-columns:1fr;} }

    /* ---- Tables ---- */
    .rep-table{width:100%;border-collapse:collapse;}
    .rep-table thead th{
        text-align:left;font-size:11px;font-weight:700;letter-spacing:.4px;color:#98a6ad;text-transform:uppercase;
        padding:12px 10px;border-bottom:1px solid #eceef3;white-space:nowrap;
    }
    .rep-table tbody td{padding:12px 10px;border-bottom:1px solid #f1f2f6;vertical-align:middle;font-size:13.5px;}
    .rep-table tbody tr:last-child td{border-bottom:none;}
    .rep-table tbody tr:hover{background:#fafbff;}

    .prod-cell{display:flex;align-items:center;gap:10px;min-width:0;}
    .prod-thumb{
        flex-shrink:0;width:34px;height:34px;border-radius:8px;object-fit:cover;
        background:#f1f2f6;display:flex;align-items:center;justify-content:center;color:#c2c9d1;font-size:12px;
    }
    .prod-name{font-size:13.5px;font-weight:700;color:#191d21;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;}

    .rank-badge{
        width:22px;height:22px;border-radius:6px;background:#eef0fb;color:#6777ef;font-size:11px;font-weight:700;
        display:inline-flex;align-items:center;justify-content:center;
    }

    .status-pill{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:700;padding:4px 11px;border-radius:20px;white-space:nowrap;}
    .status-pill i{font-size:6px;}
    .status-pill.ok{background:#e5f9ea;color:#1ca54a;}
    .status-pill.low{background:#fff3e0;color:#c9790a;}
    .status-pill.due{background:#feeceb;color:#e1362c;}

    .pay-badge{display:inline-block;font-size:11px;font-weight:700;padding:4px 11px;border-radius:20px;white-space:nowrap;}
    .pay-badge.cash{background:#e5f9ea;color:#1ca54a;}
    .pay-badge.qr{background:#e5f0ff;color:#3b82f6;}
    .pay-badge.credit{background:#fff3e0;color:#c9790a;}

    .rep-empty{text-align:center;padding:40px 20px;color:#98a6ad;}
    .rep-empty i{font-size:30px;display:block;margin-bottom:10px;color:#c7cbe0;}

    @media (max-width: 767.98px){
        .rep-table thead{display:none;}
        .rep-table, .rep-table tbody, .rep-table tbody tr, .rep-table tbody td{display:block;width:100%;}
        .rep-table tbody tr{padding:12px 0;border-bottom:1px solid #f1f2f6;}
        .rep-table tbody td{padding:4px 0;border-bottom:none;}
    }
</style>
@endsection

@section('content')
<div class="rep-page">

    @if (session('error'))
        <div class="rep-alert">{{ session('error') }}</div>
    @endif

    <div class="rep-header">
        <div>
            <h4>Reports</h4>
            <p class="text-muted" style="margin-bottom:0; font-size:13px;">Track your business performance and analyze key metrics.</p>
        </div>
        <div class="rep-export-group">
            <a href="{{ route('admin.reports.export.pdf', request()->only(['range','date_from','date_to'])) }}" class="rep-export-btn pdf">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
            <a href="{{ route('admin.reports.export.excel', request()->only(['range','date_from','date_to'])) }}" class="rep-export-btn excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('admin.reports.export.csv', request()->only(['range','date_from','date_to'])) }}" class="rep-export-btn csv">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>

    <div class="rep-filter-bar">
        <div class="rep-date-chip">
            <span class="date-icon"><i class="fas fa-calendar-alt"></i></span>
            <span class="date-main">{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} &ndash; {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</span>
        </div>

        <div class="rep-tabs">
            <a href="{{ route('admin.reports.index', ['range' => 'today']) }}" class="rep-tab {{ $range === 'today' ? 'active' : '' }}">Today</a>
            <a href="{{ route('admin.reports.index', ['range' => 'week']) }}" class="rep-tab {{ $range === 'week' ? 'active' : '' }}">This Week</a>
            <a href="{{ route('admin.reports.index', ['range' => 'month']) }}" class="rep-tab {{ $range === 'month' ? 'active' : '' }}">This Month</a>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="rep-custom-form">
            <input type="hidden" name="range" value="custom">
            <input type="date" name="date_from" value="{{ $range === 'custom' ? $dateFrom : '' }}" required>
            <input type="date" name="date_to" value="{{ $range === 'custom' ? $dateTo : '' }}" required>
            <button type="submit">Custom</button>
        </form>
    </div>

    {{-- KPI CARDS --}}
    <div class="rep-stats-row">
        @php
            $kpis = [
                ['label' => 'Total Sales', 'value' => 'Rs. ' . number_format($totalSales, 2), 'icon' => 'fa-shopping-cart', 'bg' => 'bg-sales', 'change' => $salesChangePct],
                ['label' => 'Total Profit', 'value' => 'Rs. ' . number_format($totalProfit, 2), 'icon' => 'fa-chart-line', 'bg' => 'bg-profit', 'change' => $profitChangePct],
                ['label' => 'Total Bills', 'value' => number_format($totalBills), 'icon' => 'fa-file-invoice', 'bg' => 'bg-bills', 'change' => $billsChangePct],
                ['label' => 'Items Sold', 'value' => rtrim(rtrim(number_format($itemsSold, 2), '0'), '.'), 'icon' => 'fa-shopping-bag', 'bg' => 'bg-items', 'change' => $itemsSoldChangePct],
                ['label' => 'Average Bill', 'value' => 'Rs. ' . number_format($averageBillValue, 2), 'icon' => 'fa-receipt', 'bg' => 'bg-avg', 'change' => $avgBillChangePct],
                ['label' => 'Total Discount', 'value' => 'Rs. ' . number_format($totalDiscount, 2), 'icon' => 'fa-percentage', 'bg' => 'bg-discount', 'change' => $discountChangePct],
            ];
        @endphp
        @foreach ($kpis as $kpi)
            <div class="rep-stat-card">
                <span class="stat-icon {{ $kpi['bg'] }}"><i class="fas {{ $kpi['icon'] }}"></i></span>
                <div class="stat-label">{{ $kpi['label'] }}</div>
                <div class="stat-value">{{ $kpi['value'] }}</div>
                @if ($kpi['change'] === null)
                    <div class="stat-change neutral">No data last period</div>
                @else
                    <div class="stat-change {{ $kpi['change'] >= 0 ? 'up' : 'down' }}">
                        <i class="fas {{ $kpi['change'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        {{ number_format(abs($kpi['change']), 1) }}% vs last period
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- SALES OVERVIEW + PAYMENT BREAKDOWN --}}
    <div class="rep-main-grid" style="margin-bottom:24px;">
        <div class="rep-card">
            <div class="rep-card-head">
                <h6>Sales Overview</h6>
                <span class="rep-card-badge">{{ $rangeLabel }}</span>
            </div>
            <div class="chart-total-label">Total Sales</div>
            <div class="chart-total-value">Rs. {{ number_format($totalSales, 2) }}</div>
            <div id="salesOverviewChart"></div>
        </div>

        <div class="rep-card">
            <div class="rep-card-head">
                <h6>Sales by Payment Method</h6>
            </div>
            <div class="pm-donut-wrap">
                <div id="paymentDonutChart"></div>

                @if ($paymentBreakdown->count())
                    <ul class="pm-legend">
                        @foreach ($paymentBreakdown as $row)
                            @php
                                $dotColor = match ($row['method']) { 'cash' => '#1ca54a', 'qr' => '#6777ef', 'credit' => '#c9790a', default => '#98a6ad' };
                            @endphp
                            <li>
                                <span class="pm-label"><span class="pm-dot" style="background:{{ $dotColor }};"></span>{{ $row['label'] }}</span>
                                <span>
                                    <span class="pm-value">Rs. {{ number_format($row['total'], 2) }}</span>
                                    <span class="pm-pct">{{ $row['pct'] }}%</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="rep-empty"><i class="fas fa-chart-pie"></i>No sales in this period.</div>
                @endif

                <div class="pm-total-row" style="width:100%;">
                    <span class="pm-total-label">Total</span>
                    <span class="pm-total-value">Rs. {{ number_format($totalSales, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- TOP PRODUCTS + LOW STOCK --}}
    <div class="rep-lists-grid" style="margin-bottom:24px;">
        <div class="rep-card">
            <div class="rep-card-head">
                <h6>Top Selling Products</h6>
                <span class="rep-card-badge">{{ $rangeLabel }}</span>
            </div>
            <div style="overflow-x:auto; margin-top:10px;">
                @if ($topProducts->count())
                    <table class="rep-table">
                        <thead>
                            <tr><th>#</th><th>Product</th><th>Quantity</th><th>Revenue</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($topProducts as $i => $row)
                                @php $qty = rtrim(rtrim((string) $row->qty, '0'), '.'); @endphp
                                <tr>
                                    <td><span class="rank-badge">{{ $i + 1 }}</span></td>
                                    <td>
                                        <div class="prod-cell">
                                            @if ($row->product && $row->product->image)
                                                <img src="{{ asset('storage/' . $row->product->image) }}" alt="" class="prod-thumb">
                                            @else
                                                <span class="prod-thumb"><i class="fas fa-box"></i></span>
                                            @endif
                                            <span class="prod-name">{{ $row->product->name ?? 'Deleted product' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $qty }}</td>
                                    <td style="font-weight:700; color:#191d21;">Rs. {{ number_format($row->revenue, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="rep-empty"><i class="fas fa-box-open"></i>No products sold in this period.</div>
                @endif
            </div>
        </div>

        <div class="rep-card">
            <div class="rep-card-head">
                <h6>Low Stock Products</h6>
                <a href="{{ route('admin.inventory.index') }}">View All</a>
            </div>
            <div style="overflow-x:auto; margin-top:10px;">
                @if ($lowStockProducts->count())
                    <table class="rep-table">
                        <thead>
                            <tr><th>Product</th><th>Current Stock</th><th>Min Level</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($lowStockProducts as $product)
                                @php
                                    $isCritical = $product->stock <= 0 || $product->stock <= ($product->min_stock_level / 2);
                                    $stockDisplay = rtrim(rtrim((string) $product->stock, '0'), '.');
                                    $minDisplay = rtrim(rtrim((string) $product->min_stock_level, '0'), '.');
                                @endphp
                                <tr>
                                    <td>
                                        <div class="prod-cell">
                                            @if ($product->image)
                                                <img src="{{ asset('storage/' . $product->image) }}" alt="" class="prod-thumb">
                                            @else
                                                <span class="prod-thumb"><i class="fas fa-box"></i></span>
                                            @endif
                                            <span class="prod-name">{{ $product->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $stockDisplay }}</td>
                                    <td>{{ $minDisplay }}</td>
                                    <td>
                                        <span class="status-pill {{ $isCritical ? 'due' : 'low' }}"><i class="fas fa-circle"></i>{{ $isCritical ? 'Critical' : 'Low' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="rep-empty"><i class="fas fa-check-circle"></i>All products are well stocked.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- RECENT TRANSACTIONS --}}
    <div class="rep-card" style="margin-bottom:0;">
        <div class="rep-card-head">
            <h6>Recent Transactions</h6>
            <a href="{{ route('admin.bills.index', request()->only(['date_from', 'date_to'])) }}">View All</a>
        </div>
        <div style="overflow-x:auto; margin-top:10px;">
            @if ($recentTransactions->count())
                <table class="rep-table">
                    <thead>
                        <tr>
                            <th>Bill No.</th><th>Date &amp; Time</th><th>Customer</th><th>Items</th>
                            <th>Total Amount</th><th>Payment Method</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentTransactions as $sale)
                            <tr>
                                <td style="font-weight:700; color:#191d21;">#{{ $sale->bill_number }}</td>
                                <td style="white-space:nowrap; color:#4a5568;">{{ $sale->created_at->format('M d, Y g:i A') }}</td>
                                <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                                <td>{{ $sale->items_count }}</td>
                                <td style="font-weight:700; color:#191d21;">Rs. {{ number_format($sale->total, 2) }}</td>
                                <td><span class="pay-badge {{ $sale->payment_method }}">{{ $sale->payment_method === 'qr' ? 'QR / Digital' : ucfirst($sale->payment_method) }}</span></td>
                                <td>
                                    @if ($sale->due_amount > 0)
                                        <span class="status-pill due"><i class="fas fa-circle"></i>Due</span>
                                    @else
                                        <span class="status-pill ok"><i class="fas fa-circle"></i>Completed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="rep-empty"><i class="fas fa-receipt"></i>No transactions in this period.</div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="{{ asset('admin-assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
<script>
    (function () {
        if (typeof ApexCharts === 'undefined') { return; }

        // ---- Sales / Profit trend ----
        var trendEl = document.querySelector('#salesOverviewChart');
        if (trendEl) {
            var labels = @json($chartLabels);
            var salesData = @json($chartSales);
            var profitData = @json($chartProfit);

            var trendChart = new ApexCharts(trendEl, {
                chart: { type: 'area', height: 260, toolbar: { show: false }, fontFamily: 'inherit' },
                series: [
                    { name: 'Sales (Rs.)', data: salesData },
                    { name: 'Profit (Rs.)', data: profitData }
                ],
                xaxis: {
                    categories: labels,
                    tickAmount: Math.min(8, labels.length),
                    labels: { style: { colors: '#98a6ad', fontSize: '11px' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: {
                        style: { colors: '#98a6ad', fontSize: '11px' },
                        formatter: function (val) {
                            if (val >= 1000) { return (val / 1000).toFixed(0) + 'K'; }
                            return val.toFixed(0);
                        }
                    }
                },
                colors: ['#6777ef', '#1ca54a'],
                stroke: { curve: 'smooth', width: 2.5 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.02, stops: [0, 90, 100] }
                },
                grid: { borderColor: '#f1f2f6', strokeDashArray: 4 },
                dataLabels: { enabled: false },
                legend: { position: 'top', horizontalAlign: 'left', fontSize: '12px', markers: { radius: 4 } },
                tooltip: { y: { formatter: function (val) { return 'Rs. ' + Number(val).toLocaleString(); } } },
                markers: { size: 0, hover: { size: 5 } },
            });
            trendChart.render();
        }

        // ---- Payment method donut ----
        var donutEl = document.querySelector('#paymentDonutChart');
        if (donutEl) {
            var pmLabels = @json($paymentBreakdown->pluck('label'));
            var pmValues = @json($paymentBreakdown->pluck('total'));
            var pmColorMap = { 'Cash': '#1ca54a', 'QR / Digital': '#6777ef', 'Credit': '#c9790a' };
            var pmColors = pmLabels.map(function (l) { return pmColorMap[l] || '#98a6ad'; });

            if (pmValues.length) {
                var donutChart = new ApexCharts(donutEl, {
                    chart: { type: 'donut', height: 220, fontFamily: 'inherit' },
                    series: pmValues,
                    labels: pmLabels,
                    colors: pmColors,
                    stroke: { show: true, width: 3, colors: ['#fff'] },
                    dataLabels: { enabled: false },
                    legend: { show: false },
                    tooltip: { y: { formatter: function (val) { return 'Rs. ' + Number(val).toLocaleString(); } } },
                    plotOptions: { pie: { donut: { size: '68%' } } },
                });
                donutChart.render();
            } else {
                donutEl.innerHTML = '';
            }
        }
    })();
</script>
@endsection