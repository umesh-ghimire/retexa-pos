<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
</head>
<body>
    <table border="1">
        <tr>
            <td colspan="8"><strong>RETEXA — Sales Report</strong></td>
        </tr>
        <tr>
            <td colspan="8">{{ $rangeLabel }} ({{ $start->format('Y-m-d') }} to {{ $end->format('Y-m-d') }})</td>
        </tr>
        <tr><td colspan="8"></td></tr>
        <tr>
            <th>Bill No.</th>
            <th>Date &amp; Time</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Subtotal</th>
            <th>Discount</th>
            <th>Total Amount</th>
            <th>Payment Method</th>
        </tr>
        @forelse ($sales as $sale)
            <tr>
                <td>{{ $sale->bill_number }}</td>
                <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                <td>{{ $sale->items_count }}</td>
                <td>{{ number_format((float) $sale->subtotal, 2) }}</td>
                <td>{{ number_format((float) $sale->discount, 2) }}</td>
                <td>{{ number_format((float) $sale->total, 2) }}</td>
                <td>{{ $paymentLabel($sale->payment_method) }}</td>
            </tr>
        @empty
            <tr><td colspan="8">No transactions in this period.</td></tr>
        @endforelse
    </table>
</body>
</html>