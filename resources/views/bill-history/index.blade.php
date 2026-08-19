@extends('admin.layouts.admin')

@section('title', 'Bill History')

@section('styles')
    {{-- Reusing the receipt styling from the billing screen so the "View Bill" modal looks like a real receipt --}}
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}?v={{ filemtime(public_path('css/billing.css')) }}">
    <style>
        :root {
            --print-paper-width: {{ $printerVars['width'] }};
            --print-page-length: {{ $printerVars['length'] }};
            --print-font-size: {{ $printerVars['font_size'] }};
            --print-font-weight: {{ $printerVars['font_weight'] }};
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        <div class="card">
            <div class="card-header">
                <h4>Bill History</h4>
            </div>
            <div class="card-body">

                <form method="GET" class="form-row mb-3">
                    <div class="col-md-3 form-group">
                        <label>Search (bill no. / customer)</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Payment</label>
                        <select name="payment_method" class="form-control">
                            <option value="">All</option>
                            <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="qr" {{ request('payment_method') === 'qr' ? 'selected' : '' }}>QR</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">Filter</button>
                        <a href="{{ route('admin.bills.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Bill No</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Due</th>
                                <th>Payment</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sales as $sale)
                                <tr>
                                    <td>{{ $sale->bill_number }}</td>
                                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                    <td>{{ $sale->items->count() }}</td>
                                    <td>Rs. {{ number_format($sale->total) }}</td>
<td>
    @if ($sale->due_amount > 0)
        <span class="badge badge-danger">Rs. {{ number_format($sale->due_amount) }}</span>
    @else
        —
    @endif
</td>
<td>
    @if ($sale->payment_method === 'cash')
        <span class="badge badge-success">Cash</span>
    @elseif ($sale->payment_method === 'qr')
        <span class="badge badge-info">QR</span>
    @else
        <span class="badge badge-warning">Credit</span>
    @endif
</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary"
                                                onclick='openBillModal(@json($sale), @json($sale->billTemplate))'>
                                            View
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No bills found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $sales->links('pagination::bootstrap-4') }}

            </div>
        </div>

    </div>
</div>

{{-- VIEW BILL MODAL --}}
<div class="modal fade" id="billModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bill Details</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="display:flex; justify-content:center;">
                <div class="receipt-content" id="billModalContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="printBillModal()">Print</button>
            </div>
        </div>
    </div>
</div>
<script>const printerCopies = {{ $printerVars['copies'] }};</script>
<script>const printerPaperWidthMm = {{ $printerPaperWidthMm }};</script>
<script>const printerVars = @json($printerVars);</script>
@endsection

@section('scripts')
    <script src="{{ asset('js/receipt-renderer.js') }}?v={{ filemtime(public_path('js/receipt-renderer.js')) }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/JsBarcode/3.11.5/JsBarcode.all.min.js"></script>
    <script src="{{ asset('admin-assets/js/admin-bills.js') }}?v={{ filemtime(public_path('admin-assets/js/admin-bills.js')) }}"></script>
    <script>
        const paymentQrImageUrl = @json($paymentQrUrl);
    </script>
@endsection