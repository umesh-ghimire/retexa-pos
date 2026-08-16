@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('styles')
<style>
    .dash-page{max-width:100%;}

    /* ---- Header ---- */
    .dash-header{
        display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;
        margin-bottom:24px;
    }
    .dash-header h4{margin-bottom:4px;}
    .dash-date-chip{
        display:flex;align-items:center;gap:10px;
        background:#fff;border:1px solid #eceef3;border-radius:10px;padding:10px 16px;
    }
    .dash-date-chip .date-icon{
        width:32px;height:32px;border-radius:8px;background:#eef0fb;color:#6777ef;
        display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0;
    }
    .dash-date-chip .date-main{font-size:13.5px;font-weight:700;color:#191d21;line-height:1.25;}
    .dash-date-chip .date-sub{font-size:11.5px;color:#98a6ad;line-height:1.25;}

    /* ---- Section headings ---- */
    .dash-section-title{font-size:15px;font-weight:700;color:#191d21;margin-bottom:14px;}

    /* ---- Quick actions ---- */
    .quick-actions-grid{
        display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:16px;
        margin-bottom:26px;
    }
    .quick-action-card{
        background:#fff;border-radius:14px;padding:22px 16px;text-align:center;
        border:1px solid #eceef3;
        display:flex;flex-direction:column;align-items:center;gap:10px;
        transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        text-decoration:none;
    }
    .quick-action-card:hover, .quick-action-card:focus{
        transform:translateY(-3px);
        box-shadow:0 1rem 2.5rem rgba(90,97,105,0.14);
        text-decoration:none;border-color:transparent;
    }
    .quick-action-card .qa-icon{
        width:52px;height:52px;border-radius:14px;
        display:flex;align-items:center;justify-content:center;font-size:21px;
    }
    .quick-action-card .qa-title{font-size:14.5px;font-weight:700;color:#191d21;}
    .quick-action-card .qa-sub{font-size:11.5px;color:#98a6ad;}

    .qa-bill .qa-icon{background:#e5f0ff;color:#3b82f6;}
    .qa-products .qa-icon{background:#e5f9ea;color:#1ca54a;}
    .qa-inventory .qa-icon{background:#fff3e0;color:#c9790a;}
    .qa-customers .qa-icon{background:#efe9fe;color:#8a5bf2;}
    .qa-bills .qa-icon{background:#feeceb;color:#e1362c;}
    .qa-categories .qa-icon{background:#e1f8f5;color:#0fb2a3;}

    /* ---- Summary mini cards ---- */
    .summary-row{
        display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:18px;
        margin-bottom:26px;
    }
    .summary-card{
        background:#fff;border-radius:12px;padding:18px 20px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        display:flex;align-items:flex-start;justify-content:space-between;gap:10px;min-width:0;
    }
    .summary-card .sc-left{display:flex;align-items:flex-start;gap:12px;min-width:0;}
    .summary-card .sc-icon{
        flex-shrink:0;width:42px;height:42px;border-radius:10px;
        display:flex;align-items:center;justify-content:center;font-size:16px;
    }
    .summary-card .sc-label{font-size:12.5px;color:#98a6ad;font-weight:600;margin-bottom:4px;}
    .summary-card .sc-value{font-size:21px;font-weight:700;color:#191d21;line-height:1.2;white-space:nowrap;}
    .summary-card .sc-change{font-size:11.5px;font-weight:600;margin-top:4px;}
    .summary-card .sc-change.up{color:#1ca54a;}
    .summary-card .sc-change.down{color:#e1362c;}
    .summary-card .sc-change.neutral{color:#98a6ad;}
    .summary-card .sc-sparkline{flex-shrink:0;align-self:center;}

    .sc-icon.bg-sales{background:#e5f0ff;color:#3b82f6;}
    .sc-icon.bg-bills{background:#e5f9ea;color:#1ca54a;}
    .sc-icon.bg-lowstock{background:#fff3e0;color:#c9790a;}
    .sc-icon.bg-customers{background:#efe9fe;color:#8a5bf2;}

    /* ---- Main grid: chart / activity / low stock ---- */
    .dash-main-grid{
        display:grid;grid-template-columns:1.6fr 1fr 1fr;gap:20px;margin-bottom:24px;align-items:start;
    }
    @media (max-width: 1199.98px){
        .dash-main-grid{grid-template-columns:1fr 1fr;}
        .chart-card{grid-column:1 / -1;}
    }
    @media (max-width: 767.98px){
        .dash-main-grid{grid-template-columns:1fr;}
    }

    .dash-card{
        background:#fff;border-radius:12px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        padding:20px;min-width:0;
    }
    .dash-card-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;}
    .dash-card-head h6{margin:0;font-size:14.5px;font-weight:700;color:#191d21;}
    .dash-card-head a{font-size:12.5px;font-weight:600;color:#6777ef;text-decoration:none;}
    .dash-card-head a:hover{text-decoration:underline;}

    .chart-total-label{font-size:12px;color:#98a6ad;margin-top:6px;}
    .chart-total-value{font-size:22px;font-weight:700;color:#191d21;margin-bottom:6px;}

    .insight-chips{display:flex;gap:10px;margin-top:16px;flex-wrap:wrap;}
    .insight-chip{
        flex:1 1 140px;background:#fafbfc;border:1px solid #f1f2f6;border-radius:10px;
        padding:12px 14px;display:flex;align-items:center;gap:10px;min-width:0;
    }
    .insight-chip .ic-icon{
        flex-shrink:0;width:32px;height:32px;border-radius:8px;
        display:flex;align-items:center;justify-content:center;font-size:13px;
    }
    .insight-chip .ic-label{font-size:11px;color:#98a6ad;white-space:nowrap;}
    .insight-chip .ic-value{font-size:13.5px;font-weight:700;color:#191d21;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

    /* ---- Activity list ---- */
    .activity-list, .lowstock-list{list-style:none;margin:14px 0 0;padding:0;}
    .activity-item{display:flex;gap:12px;padding:11px 0;border-bottom:1px solid #f4f5f8;}
    .activity-item:last-child{border-bottom:none;padding-bottom:0;}
    .activity-item .ai-icon{
        flex-shrink:0;width:34px;height:34px;border-radius:9px;
        display:flex;align-items:center;justify-content:center;font-size:13px;
    }
    .activity-item .ai-title{font-size:13px;font-weight:700;color:#191d21;}
    .activity-item .ai-sub{font-size:12px;color:#98a6ad;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;}
    .activity-item .ai-time{font-size:11px;color:#c2c9d1;white-space:nowrap;flex-shrink:0;margin-left:auto;padding-left:8px;}
    .activity-item .ai-text{min-width:0;flex:1;}

    .ai-bill{background:#e5f9ea;color:#1ca54a;}
    .ai-stock{background:#fff3e0;color:#c9790a;}
    .ai-customer{background:#efe9fe;color:#8a5bf2;}

    /* ---- Low stock list ---- */
    .lowstock-item{display:flex;align-items:center;gap:12px;padding:11px 0;border-bottom:1px solid #f4f5f8;}
    .lowstock-item:last-child{border-bottom:none;padding-bottom:0;}
    .lowstock-item .ls-icon{
        flex-shrink:0;width:34px;height:34px;border-radius:9px;background:#fff3e0;color:#c9790a;
        display:flex;align-items:center;justify-content:center;font-size:13px;
    }
    .lowstock-item .ls-text{min-width:0;flex:1;}
    .lowstock-item .ls-name{font-size:13px;font-weight:700;color:#191d21;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .lowstock-item .ls-sub{font-size:11.5px;color:#98a6ad;}
    .lowstock-item .ls-badge{
        flex-shrink:0;font-size:10.5px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;
    }
    .ls-badge.low{background:#fff3e0;color:#c9790a;}
    .ls-badge.critical{background:#feeceb;color:#e1362c;}

    .dash-empty{text-align:center;color:#98a6ad;font-size:13px;padding:24px 6px;}
    .dash-empty i{font-size:26px;display:block;margin-bottom:8px;color:#c7cbe0;}

    /* ---- Security banner ---- */
    .security-banner{
        background:#eef2ff;border:1px solid #d7defb;border-radius:12px;
        padding:18px 22px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;
    }
    .security-banner .sb-left{display:flex;align-items:center;gap:14px;}
    .security-banner .sb-icon{
        flex-shrink:0;width:44px;height:44px;border-radius:10px;background:#dbe4ff;color:#3b5bfd;
        display:flex;align-items:center;justify-content:center;font-size:18px;
    }
    .security-banner .sb-title{font-size:14.5px;font-weight:700;color:#191d21;}
    .security-banner .sb-sub{font-size:12.5px;color:#5c6b8a;}
</style>
@endsection

@section('content')
<div class="dash-page">

    @php
        $ownerName = auth()->user()->name ?? 'Administrator';
        $hour = now()->hour;
        $greetingEmoji = $hour < 12 ? '☀️' : ($hour < 17 ? '👋' : '🌙');
    @endphp

    {{-- HEADER --}}
    <div class="dash-header">
        <div>
            <h4>Welcome back, {{ explode(' ', $ownerName)[0] }}! {{ $greetingEmoji }}</h4>
            <p class="text-muted" style="margin-bottom:0; font-size:13px;">Here's what's happening in {{ $shopName }} today.</p>
        </div>
        <div class="dash-date-chip">
            <span class="date-icon"><i class="fas fa-calendar-alt"></i></span>
            <div>
                <div class="date-main">{{ now()->format('F j, Y') }}</div>
                <div class="date-sub">{{ now()->format('l, g:i A') }}</div>
            </div>
        </div>
    </div>

    {{-- QUICK ACTIONS --}}
    <div class="dash-section-title">Quick Actions</div>
    <div class="quick-actions-grid">
        <a href="{{ url('/billing') }}" class="quick-action-card qa-bill">
            <span class="qa-icon"><i class="fas fa-file-invoice"></i></span>
            <span class="qa-title">New Bill</span>
            <span class="qa-sub">Create a new invoice</span>
        </a>
        <a href="{{ route('admin.products.index') }}" class="quick-action-card qa-products">
            <span class="qa-icon"><i class="fas fa-box"></i></span>
            <span class="qa-title">Products</span>
            <span class="qa-sub">Add or manage products</span>
        </a>
        <a href="{{ route('admin.inventory.index') }}" class="quick-action-card qa-inventory">
            <span class="qa-icon"><i class="fas fa-boxes"></i></span>
            <span class="qa-title">Inventory</span>
            <span class="qa-sub">Stock in / out</span>
        </a>
        <a href="{{ route('admin.customers.index') }}" class="quick-action-card qa-customers">
            <span class="qa-icon"><i class="fas fa-users"></i></span>
            <span class="qa-title">Customers</span>
            <span class="qa-sub">Add or manage customers</span>
        </a>
        <a href="{{ route('admin.bills.index') }}" class="quick-action-card qa-bills">
            <span class="qa-icon"><i class="fas fa-receipt"></i></span>
            <span class="qa-title">Bills</span>
            <span class="qa-sub">View all transactions</span>
        </a>
        <a href="{{ route('admin.categories.index') }}" class="quick-action-card qa-categories">
            <span class="qa-icon"><i class="fas fa-tags"></i></span>
            <span class="qa-title">Categories</span>
            <span class="qa-sub">Organize your catalog</span>
        </a>
    </div>

    {{-- TODAY'S SUMMARY --}}
    <div class="dash-section-title">Today's Summary</div>
    @php
        $renderChange = function ($pct) {
            if ($pct === null) {
                return '<span class="sc-change neutral">No data yesterday</span>';
            }
            if ($pct >= 0) {
                return '<span class="sc-change up"><i class="fas fa-arrow-up" style="font-size:9px;"></i> ' . $pct . '% vs yesterday</span>';
            }
            return '<span class="sc-change down"><i class="fas fa-arrow-down" style="font-size:9px;"></i> ' . abs($pct) . '% vs yesterday</span>';
        };
    @endphp
    <div class="summary-row">
        <div class="summary-card">
            <div class="sc-left">
                <span class="sc-icon bg-sales"><i class="fas fa-rupee-sign"></i></span>
                <div>
                    <div class="sc-label">Today's Sales</div>
                    <div class="sc-value">Rs. {{ number_format($todaySales, 2) }}</div>
                    {!! $renderChange($salesChangePct) !!}
                </div>
            </div>
        </div>
        <div class="summary-card">
            <div class="sc-left">
                <span class="sc-icon bg-bills"><i class="fas fa-shopping-bag"></i></span>
                <div>
                    <div class="sc-label">Today's Bills</div>
                    <div class="sc-value">{{ number_format($todayBillsCount) }}</div>
                    {!! $renderChange($billsChangePct) !!}
                </div>
            </div>
        </div>
        <div class="summary-card">
            <div class="sc-left">
                <span class="sc-icon bg-lowstock"><i class="fas fa-box-open"></i></span>
                <div>
                    <div class="sc-label">Low Stock Items</div>
                    <div class="sc-value">{{ number_format($lowStockCount) }}</div>
                    <span class="sc-change {{ $lowStockCount > 0 ? 'down' : 'neutral' }}">
                        {{ $lowStockCount > 0 ? 'Requires attention' : 'All stocked up' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="summary-card">
            <div class="sc-left">
                <span class="sc-icon bg-customers"><i class="fas fa-user-plus"></i></span>
                <div>
                    <div class="sc-label">New Customers</div>
                    <div class="sc-value">{{ number_format($newCustomersToday) }}</div>
                    {!! $renderChange($customersChangePct) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- MAIN GRID: CHART / ACTIVITY / LOW STOCK --}}
    <div class="dash-main-grid">

        {{-- SALES OVERVIEW --}}
        <div class="dash-card chart-card">
            <div class="dash-card-head">
                <h6>Sales Overview (Today)</h6>
                <span style="font-size:11.5px; color:#98a6ad; font-weight:600; background:#f1f2f6; padding:4px 10px; border-radius:6px;">Today</span>
            </div>
            <div class="chart-total-label">Total Sales</div>
            <div class="chart-total-value">Rs. {{ number_format($todaySales, 2) }}</div>
            <div id="salesOverviewChart"></div>

            <div class="insight-chips">
                <div class="insight-chip">
                    <span class="ic-icon" style="background:#fff3d9; color:#d8a326;"><i class="fas fa-coins"></i></span>
                    <div>
                        <div class="ic-label">Average Order Value</div>
                        <div class="ic-value">Rs. {{ number_format($averageOrderValue, 0) }}</div>
                    </div>
                </div>
                <div class="insight-chip">
                    <span class="ic-icon" style="background:#e5f0ff; color:#3b82f6;"><i class="fas fa-clock"></i></span>
                    <div>
                        <div class="ic-label">Best Selling Time</div>
                        <div class="ic-value">{{ $bestSellingTime }}</div>
                    </div>
                </div>
                <div class="insight-chip">
                    <span class="ic-icon" style="background:#efe9fe; color:#8a5bf2;"><i class="fas fa-cubes"></i></span>
                    <div>
                        <div class="ic-label">Total Items Sold</div>
                        <div class="ic-value">{{ rtrim(rtrim(number_format($totalItemsSoldToday, 2), '0'), '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RECENT ACTIVITY --}}
        <div class="dash-card">
            <div class="dash-card-head">
                <h6>Recent Activity</h6>
                <a href="{{ route('admin.bills.index') }}">View All</a>
            </div>
            @if ($recentActivity->count())
                <ul class="activity-list">
                    @foreach ($recentActivity as $activity)
                        @php
                            $iconClass = match ($activity['type']) {
                                'bill' => ['ai-bill', 'fa-file-invoice'],
                                'stock' => ['ai-stock', 'fa-boxes'],
                                'customer' => ['ai-customer', 'fa-user-plus'],
                                default => ['ai-bill', 'fa-circle'],
                            };
                        @endphp
                        <li class="activity-item">
                            <span class="ai-icon {{ $iconClass[0] }}"><i class="fas {{ $iconClass[1] }}"></i></span>
                            <div class="ai-text">
                                <div class="ai-title">{{ $activity['title'] }}</div>
                                <div class="ai-sub">{{ $activity['subtitle'] }}</div>
                            </div>
                            <span class="ai-time">{{ $activity['time']->diffForHumans(null, true) }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="dash-empty">
                    <i class="fas fa-stream"></i>
                    No recent activity yet.
                </div>
            @endif
        </div>

        {{-- LOW STOCK ALERTS --}}
        <div class="dash-card">
            <div class="dash-card-head">
                <h6>Low Stock Alerts</h6>
                <a href="{{ route('admin.inventory.index') }}">View All</a>
            </div>
            @if ($lowStockProducts->count())
                <ul class="lowstock-list">
                    @foreach ($lowStockProducts as $product)
                        @php
                            $isCritical = $product->stock <= 0 || $product->stock <= ($product->min_stock_level / 2);
                            $stockDisplay = rtrim(rtrim((string) $product->stock, '0'), '.');
                            $minDisplay = rtrim(rtrim((string) $product->min_stock_level, '0'), '.');
                        @endphp
                        <li class="lowstock-item">
                            <span class="ls-icon"><i class="fas fa-box"></i></span>
                            <div class="ls-text">
                                <div class="ls-name" title="{{ $product->name }}">{{ $product->name }}</div>
                                <div class="ls-sub">Stock: {{ $stockDisplay }} &middot; Min: {{ $minDisplay }}</div>
                            </div>
                            <span class="ls-badge {{ $isCritical ? 'critical' : 'low' }}">
                                {{ $isCritical ? 'Critical' : 'Low' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="dash-empty">
                    <i class="fas fa-check-circle"></i>
                    All products are well stocked.
                </div>
            @endif
        </div>
    </div>

    {{-- SECURITY / SETTINGS REMINDER --}}
    <div class="security-banner">
        <div class="sb-left">
            <span class="sb-icon"><i class="fas fa-shield-alt"></i></span>
            <div>
                <div class="sb-title">Keep your business secure</div>
                <div class="sb-sub">Regularly review your shop settings, printer setup and staff accounts.</div>
            </div>
        </div>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-light" style="border:1px solid #c9cffa; color:#6777ef; font-weight:600; border-radius:8px;">
            <i class="fas fa-cog" style="margin-right:6px;"></i>System Settings
        </a>
    </div>

</div>
@endsection

@section('scripts')
<script src="{{ asset('admin-assets/bundles/apexcharts/apexcharts.min.js') }}"></script>
<script>
    (function () {
        if (window.__retexaDashboardChartInit) { return; }
        window.__retexaDashboardChartInit = true;

        var el = document.querySelector('#salesOverviewChart');
        if (!el || typeof ApexCharts === 'undefined') { return; }

        var labels = @json($chartLabels);
        var values = @json($chartValues);

        var chart = new ApexCharts(el, {
            chart: {
                type: 'area',
                height: 260,
                toolbar: { show: false },
                fontFamily: 'inherit',
            },
            series: [{ name: 'Sales', data: values }],
            xaxis: {
                categories: labels,
                tickAmount: 6,
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
            colors: ['#6777ef'],
            stroke: { curve: 'smooth', width: 2.5 },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 90, 100] }
            },
            grid: { borderColor: '#f1f2f6', strokeDashArray: 4 },
            dataLabels: { enabled: false },
            tooltip: {
                y: { formatter: function (val) { return 'Rs. ' + Number(val).toLocaleString(); } }
            },
            markers: { size: 0, hover: { size: 5 } },
        });

        chart.render();
    })();
</script>
@endsection