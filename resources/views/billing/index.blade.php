@extends('layouts.app')

@section('title', 'Billing - Smart Retail POS')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}">
@endsection

@section('content')
<div class="billing-page">

    {{-- LEFT SIDE: Calculator --}}
    <div class="calculator-panel">

        <div class="calculator-display">
            <div class="display-mode" id="displayMode">NORMAL MODE</div>
            <div class="display-value" id="displayValue">0</div>
        </div>

        <div class="calculator-buttons">
            <button class="calc-btn calc-btn--product" data-action="product">PRODUCT</button>
            <button class="calc-btn calc-btn--clear" data-action="clear">C</button>
            <button class="calc-btn calc-btn--backspace" data-action="backspace">⌫</button>

            <button class="calc-btn" data-key="7">7</button>
            <button class="calc-btn" data-key="8">8</button>
            <button class="calc-btn" data-key="9">9</button>

            <button class="calc-btn" data-key="4">4</button>
            <button class="calc-btn" data-key="5">5</button>
            <button class="calc-btn" data-key="6">6</button>

            <button class="calc-btn" data-key="1">1</button>
            <button class="calc-btn" data-key="2">2</button>
            <button class="calc-btn" data-key="3">3</button>

            <button class="calc-btn" data-key="0">0</button>
            <button class="calc-btn" data-key=".">.</button>
            <button class="calc-btn calc-btn--enter" data-action="enter">ENTER</button>
        </div>

        {{-- Product search box - hidden until PRODUCT is pressed --}}
        <div class="product-search-box" id="productSearchBox" style="display:none;">
            <input type="text" id="productSearchInput" placeholder="Type product name...">
            <div class="product-suggestions" id="productSuggestions"></div>
        </div>

    </div>

    {{-- RIGHT SIDE: Bill panel --}}
    <div class="bill-panel">

        <div class="bill-header">
            <h2 id="shopName">ABC STORE</h2>
            <p>Bill No: <span id="billNumber">000001</span></p>
            <p>Date: <span id="billDate"></span></p>
        </div>

        <div class="bill-customer">
            <input type="text" id="customerName" placeholder="Customer name (optional)">
            <input type="text" id="customerPhone" placeholder="Customer phone (optional)">
        </div>

        <div class="bill-items" id="billItems">
            {{-- Bill items will be added here by JavaScript --}}
            <p class="empty-bill-message" id="emptyBillMessage">No items yet. Enter an amount and press ENTER.</p>
        </div>

        <div class="bill-totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span id="subtotalValue">Rs. 0</span>
            </div>
            <div class="total-row">
                <span>Discount</span>
                <input type="number" id="discountInput" value="0" min="0">
            </div>
            <div class="total-row total-row--grand">
                <span>TOTAL</span>
                <span id="grandTotalValue">Rs. 0</span>
            </div>
            <div class="total-row">
                <span>Cash Received</span>
                <input type="number" id="cashInput" value="0" min="0">
            </div>
            <div class="total-row">
                <span>Change</span>
                <span id="changeValue">Rs. 0</span>
            </div>
        </div>

        <button class="btn-show-bill" id="showBillBtn">SHOW BILL</button>

    </div>

</div>

{{-- RECEIPT MODAL — hidden until SHOW BILL is clicked --}}
<div class="receipt-overlay" id="receiptOverlay" style="display:none;">
    <div class="receipt-box" id="receiptBox">

        <div class="receipt-content" id="receiptContent">
            {{-- Filled dynamically by JavaScript --}}
        </div>

        <div class="receipt-actions">
            <button class="btn-close-receipt" id="closeReceiptBtn">Close</button>
            <button class="btn-print-receipt" id="printReceiptBtn">Print</button>
        </div>

    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('js/data/dummy-products.js') }}"></script>
    <script src="{{ asset('js/billing.js') }}"></script>
@endsection