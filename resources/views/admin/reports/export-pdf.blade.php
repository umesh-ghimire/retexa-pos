<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body{font-family: DejaVu Sans, sans-serif; font-size:11px; color:#191d21;}
        h1{font-size:18px;margin:0 0 2px;}
        .sub{color:#666;font-size:11px;margin-bottom:16px;}
        table{width:100%;border-collapse:collapse;margin-bottom:18px;}
        th, td{border:1px solid #ddd;padding:6px 8px;text-align:left;}
        th{background:#f5f6fa;font-size:10px;text-transform:uppercase;color:#666;}
        .kpi-table td{border:none;padding:4px 12px 4px 0;}
        .section-title{font-size:13px;font-weight:bold;margin:18px 0 8px;}
    </style>
</head>
<body>
    <h1>RETEXA — Sales Report</h1>
    <div class="sub">{{ $rangeLabel }} &nbsp;({{ $start->format('Y-m-d') }} to {{ $end->format('Y-m-d') }})</div>

    <table class="kpi-table">
        <tr>
            <td><strong>Total Sales:</strong> Rs. {{ number_format($totalSales, 2) }}</td>
            <td><strong>Total Profit:</strong> Rs. {{ number_format($totalProfit, 2) }}</td>
            <td><strong>Total Bills:</strong> {{ number_format($totalBills) }}</td>
        </tr>
        <tr>
            <td><strong>Average Bill:</strong> Rs. {{ number_format($averageBillValue, 2) }}</td>
            <td><strong>Total Discount:</strong> Rs. {{ number_format($totalDiscount, 2) }}</td>
            <td><strong>Items Sold:</strong> {{ rtrim(rtrim(number_format($itemsSold, 2), '0'), '.') }}</td>
        </tr>
    </table>

    <div class="section-title">Sales by Payment Method</div>
    <table>
        <tr><th>Method</th><th>Amount</th><th>Bills</th><th>% of Total</th></tr>
        @forelse ($paymentBreakdown as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td>Rs. {{ number_format($row['total'], 2) }}</td>
                <td>{{ $row['count'] }}</td>
                <td>{{ $row['pct'] }}%</td>
            </tr>
        @empty
            <tr><td colspan="4">No sales in this period.</td></tr>
        @endforelse
    </table>

    <div class="section-title">Top Selling Products</div>
    <table>
        <tr><th>#</th><th>Product</th><th>Quantity</th><th>Revenue</th></tr>
        @forelse ($topProducts as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row->product->name ?? 'Deleted product' }}</td>
                <td>{{ rtrim(rtrim((string) $row->qty, '0'), '.') }}</td>
                <td>Rs. {{ number_format($row->revenue, 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="4">No products sold in this period.</td></tr>
        @endforelse
    </table>

    <div class="section-title">Recent Transactions</div>
    <table>
        <tr>
            <th>Bill No.</th><th>Date &amp; Time</th><th>Customer</th><th>Items</th>
            <th>Total</th><th>Payment</th><th>Status</th>
        </tr>
        @forelse ($recentTransactions as $sale)
            <tr>
                <td>{{ $sale->bill_number }}</td>
                <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                <td>{{ $sale->items_count }}</td>
                <td>Rs. {{ number_format($sale->total, 2) }}</td>
                <td>{{ $paymentLabel($sale->payment_method) }}</td>
                <td>{{ $sale->due_amount > 0 ? 'Due' : 'Completed' }}</td>
            </tr>
        @empty
            <tr><td colspan="7">No transactions in this period.</td></tr>
        @endforelse
    </table>
</body>
</html>