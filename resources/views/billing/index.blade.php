@extends('layouts.app')

@section('title', 'Billing - Smart Retail POS')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}">
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

        <div class="barcode-not-found-overlay" id="barcodeNotFoundOverlay" style="display:none;">
            <div class="barcode-not-found-box">
                <p class="barcode-not-found-title">Barcode not found</p>
                <p class="barcode-not-found-code" id="barcodeNotFoundCode"></p>
                <div class="barcode-not-found-actions">
                    @if ($isOwner)
                        <a href="{{ url('/admin/products') }}" target="_blank" class="btn-create-product">Create Product</a>
                    @endif
                    <button type="button" class="btn-search-instead" id="barcodeSearchInsteadBtn">Try Again</button>
                    <button type="button" class="btn-cancel-barcode" id="barcodeCancelBtn">Cancel</button>
                </div>
            </div>
        </div>

        <div class="product-search-box" id="productSearchBox">
            <div class="product-search-box__header">
                <label for="productScanInput">Search or Scan Product</label>
                <button type="button" class="product-search-close-btn" id="productSearchCloseBtn" aria-label="Close search">✕</button>
            </div>
            <input type="text" id="productScanInput" placeholder="Type a product name or scan a barcode..." autocomplete="off">
            <div class="product-suggestions" id="productSuggestions"></div>
        </div>

    </div>

    {{-- RIGHT SIDE: Bill panel --}}
    <div class="bill-panel">

        <div class="bill-header">
            <h2 id="shopName">{{ $shopName }}</h2>
            <p>Bill No: <span id="billNumber">New</span></p>
            <p>Date: <span id="billDate"></span></p>
        </div>

        <div class="bill-customer">
            <input type="text" id="customerName" placeholder="Customer name (required)" required>
            <input type="text" id="customerPhone" placeholder="Customer phone (optional)">
        </div>

        <div class="bill-items" id="billItems">
            <p class="empty-bill-message" id="emptyBillMessage">No items yet. Enter an amount and press ENTER.</p>
        </div>

        <div class="bill-totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span id="subtotalValue">Rs. 0</span>
            </div>
            <div class="total-row">
                <span>Discount</span>
                <input type="number" id="discountInput" value="{{ $defaultDiscount ?: 0 }}" min="0">
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
            <div class="total-row payment-method-row">
                <span>Payment Method</span>
                <span>
                    <label><input type="radio" name="paymentMethod" value="cash" checked> Cash</label>
                    <label><input type="radio" name="paymentMethod" value="qr"> QR</label>
                    <label><input type="radio" name="paymentMethod" value="credit"> Credit</label>
                </span>
            </div>
            <div class="total-row payment-method-row">
                <span>Show QR on Receipt</span>
                <span>
                    <label><input type="checkbox" id="showQrCheckbox" {{ (!$template || $template->show_qr) ? 'checked' : '' }}> Show</label>
                </span>
            </div>
        </div>

        <div class="bill-action-buttons">
            <button class="btn-new-bill" id="newBillBtn">New Bill</button>
            <button class="btn-show-bill" id="showBillBtn">SHOW BILL</button>
        </div>

    </div>

</div>

<div class="receipt-overlay" id="receiptOverlay" style="display:none;">
    <div class="receipt-box" id="receiptBox">
        <div class="receipt-content" id="receiptContent"></div>
        <div class="receipt-actions">
            <button class="btn-close-receipt" id="closeReceiptBtn">Close</button>
            <button class="btn-print-receipt" id="printReceiptBtn">Print</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    <script>
        const activeTemplate = @json($template);
        const paymentQrImageUrl = @json($paymentQrUrl);
        const defaultShowQr = @json(!$template || $template->show_qr);
        const printerPaperWidthMm = {{ $printerPaperWidthMm }};
        const printerCopies = {{ $printerVars['copies'] }};
    </script>
    <script src="{{ asset('js/receipt-renderer.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/JsBarcode/3.11.5/JsBarcode.all.min.js"></script>
    <script src="{{ asset('js/billing.js') }}"></script>
@endsection