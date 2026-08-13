@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">

        <h2 class="mb-4">Dashboard</h2>

        {{-- STAT CARDS --}}
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted" style="font-size:0.8rem; text-transform:uppercase;">Today's Sales</div>
                        <h3 class="mt-2 mb-0">Rs. {{ number_format($todaySales) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted" style="font-size:0.8rem; text-transform:uppercase;">This Week</div>
                        <h3 class="mt-2 mb-0">Rs. {{ number_format($weekSales) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted" style="font-size:0.8rem; text-transform:uppercase;">This Month</div>
                        <h3 class="mt-2 mb-0">Rs. {{ number_format($monthSales) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted" style="font-size:0.8rem; text-transform:uppercase;">Total Bills</div>
                        <h3 class="mt-2 mb-0">{{ number_format($totalBills) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted" style="font-size:0.8rem; text-transform:uppercase;">Total Revenue (All Time)</div>
                        <h3 class="mt-2 mb-0">Rs. {{ number_format($totalRevenue) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="text-muted" style="font-size:0.8rem; text-transform:uppercase;">Low Stock Products</div>
                        <h3 class="mt-2 mb-0">{{ $lowStockProducts->count() }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            {{-- BEST SELLING PRODUCTS --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Best-Selling Products</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Qty Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($bestSellingProducts as $row)
                                    <tr>
                                        <td>{{ $row->product->name ?? 'Deleted product' }}</td>
                                        <td>{{ rtrim(rtrim($row->total_quantity, '0'), '.') }}</td>
                                        <td>Rs. {{ number_format($row->total_revenue) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No sales yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- LOW STOCK PRODUCTS --}}
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Low Stock Products</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Stock</th>
                                    <th>Min Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lowStockProducts as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td><span class="badge badge-warning">{{ rtrim(rtrim($product->stock, '0'), '.') }}</span></td>
                                        <td>{{ rtrim(rtrim($product->min_stock_level, '0'), '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No low-stock products.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- RECENT TRANSACTIONS --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Recent Transactions</h4>
                <a href="{{ route('admin.bills.index') }}" class="btn btn-sm btn-light">View All Bills</a>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Bill No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTransactions as $sale)
                            <tr>
                                <td>{{ $sale->bill_number }}</td>
                                <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                <td>Rs. {{ number_format($sale->total) }}</td>
                                <td>
                                    @if ($sale->payment_method === 'cash')
                                        <span class="badge badge-success">Cash</span>
                                    @else
                                        <span class="badge badge-info">QR</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No transactions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection