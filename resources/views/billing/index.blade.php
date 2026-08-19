@extends('layouts.app')

@section('title', 'Billing - Smart Retail POS')

@section('styles')
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
<div class="billing-page">

    {{-- LEFT SIDE: Smart Calculator --}}
    <div class="calculator-panel">

        <div class="smart-input-wrap">
            <div class="smart-input-box" id="smartInputBox">
                <input type="text" id="smartInput" placeholder="Enter product name / scan barcode" autocomplete="off">
                <span class="smart-input-amount" id="smartInputAmount"></span>
            </div>
            <p class="smart-input-tip">
                <span class="tip-icon">&#128161;</span>
                <span id="smartInputTipText">Type product name or scan barcode and press ENTER.</span>
            </p>

            <div class="smart-suggestions" id="smartSuggestions"></div>
        </div>

        <div class="calculator-buttons calculator-buttons--top">
            <button type="button" class="calc-btn calc-btn--product" data-action="focus-input">
                <span class="calc-btn-icon">+</span> PRODUCT
                <small>F1</small>
            </button>
            <button type="button" class="calc-btn calc-btn--clear" data-action="clear">
                C CLEAR
            </button>
            <button type="button" class="calc-btn calc-btn--backspace" data-action="backspace">
                &#9003; BACKSPACE
            </button>
        </div>

        <div class="calculator-buttons calculator-buttons--pad">
            <button type="button" class="calc-btn" data-key="7">7</button>
            <button type="button" class="calc-btn" data-key="8">8</button>
            <button type="button" class="calc-btn" data-key="9">9</button>
            <button type="button" class="calc-btn calc-btn--op" data-key="&#215;">&#215;</button>

            <button type="button" class="calc-btn" data-key="4">4</button>
            <button type="button" class="calc-btn" data-key="5">5</button>
            <button type="button" class="calc-btn" data-key="6">6</button>
            <button type="button" class="calc-btn calc-btn--op" data-key="&#8722;">&#8722;</button>

            <button type="button" class="calc-btn" data-key="1">1</button>
            <button type="button" class="calc-btn" data-key="2">2</button>
            <button type="button" class="calc-btn" data-key="3">3</button>
            <button type="button" class="calc-btn calc-btn--op" data-key="+">+</button>

            <button type="button" class="calc-btn" data-key="0">0</button>
            <button type="button" class="calc-btn" data-key=".">.</button>
            <button type="button" class="calc-btn calc-btn--enter" data-action="enter">ENTER &#9166;</button>
        </div>

        <div class="calculator-quick-actions">
            <button type="button" class="quick-action-btn" id="newBillBtn">
                <span class="quick-action-icon quick-action-icon--new">&#128196;</span>
                <span class="quick-action-label">New Bill</span>
                <small>F2</small>
            </button>
            <button type="button" class="quick-action-btn" id="holdBillBtn">
                <span class="quick-action-icon quick-action-icon--hold">&#10074;&#10074;</span>
                <span class="quick-action-label">Hold Bill</span>
                <small>F3</small>
                <span class="held-bills-badge" id="heldBillsBadge" style="display:none;">0</span>
            </button>
            <a href="{{ url('/bill-history') }}" class="quick-action-btn" id="billHistoryLink">
                <span class="quick-action-icon quick-action-icon--history">&#128203;</span>
                <span class="quick-action-label">Bill History</span>
                <small>F4</small>
            </a>
            @if ($isOwner)
                <a href="{{ url('/admin/settings') }}" target="_blank" class="quick-action-btn" id="settingsLink">
                    <span class="quick-action-icon quick-action-icon--settings">&#9881;</span>
                    <span class="quick-action-label">Settings</span>
                    <small>F5</small>
                </a>
            @else
                <span class="quick-action-btn quick-action-btn--disabled" title="Owner access required">
                    <span class="quick-action-icon quick-action-icon--settings">&#9881;</span>
                    <span class="quick-action-label">Settings</span>
                    <small>F5</small>
                </span>
            @endif
        </div>

        <div class="barcode-not-found-overlay" id="barcodeNotFoundOverlay" style="display:none;">
            <div class="barcode-not-found-box">
                <div class="popup-icon-badge popup-icon-badge--danger">&#10005;</div>
                <p class="barcode-not-found-title">Barcode not found</p>
                <p class="barcode-not-found-subtitle">We couldn't match this code to a product in inventory.</p>
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

    </div>

    {{-- RIGHT SIDE: Current Bill --}}
    <div class="bill-panel">

        <div class="bill-header">
            <div class="bill-header-shop">
                <div class="bill-shop-logo">
                    @if ($shopLogoUrl)
                        <img src="{{ $shopLogoUrl }}" alt="{{ $shopName }}">
                    @else
                        <span>&#127978;</span>
                    @endif
                </div>
                <div class="bill-shop-info">
                    <h2 id="shopName">{{ $shopName }}</h2>
                    @if ($template && $template->address)
                        <p class="bill-shop-address">{{ $template->address }}</p>
                    @endif
                    @if ($template && $template->phone)
                        <p class="bill-shop-phone">{{ $template->phone }}</p>
                    @endif
                    @if ($template && $template->vat_pan_number)
                        <span class="bill-vat-pan">VAT/PAN No. {{ $template->vat_pan_number }}</span>
                    @endif
                </div>
            </div>

            <div class="bill-header-meta-table">
                <div class="bill-meta-row">
                    <span class="meta-label">Bill No.</span>
                    <span class="meta-colon">:</span>
                    <b id="billNumber" class="meta-highlight">NEW</b>
                </div>
                <div class="bill-meta-row">
                    <span class="meta-label">Date</span>
                    <span class="meta-colon">:</span>
                    <b id="billDate"></b>
                </div>
                <div class="bill-meta-row">
                    <span class="meta-label">Time</span>
                    <span class="meta-colon">:</span>
                    <b id="billTime"></b>
                </div>
                <div class="bill-meta-row">
                    <span class="meta-label">Cashier</span>
                    <span class="meta-colon">:</span>
                    <b id="cashierName">{{ auth()->user()->name }}</b>
                </div>
            </div>

            <div class="bill-payment-qr">
                <p class="bill-payment-qr-label">PAYMENT QR</p>
                <div class="bill-payment-qr-box">
                    @if ($paymentQrUrl)
                        <img src="{{ $paymentQrUrl }}" alt="Payment QR">
                    @else
                        <span class="bill-payment-qr-placeholder">No QR set</span>
                    @endif
                </div>
                <p class="bill-payment-qr-caption">Scan to Pay</p>
                <label class="toggle-switch-row">
                    <span class="toggle-switch-row-label">
                        Show QR on Bill
                        <span class="qr-help-icon" title="Controls whether the payment QR appears on this bill.">?</span>
                    </span>
                    <span class="toggle-switch">
                        <input type="checkbox" id="showQrCheckbox" {{ (!$template || $template->show_qr) ? 'checked' : '' }}>
                        <span class="toggle-switch-slider"></span>
                    </span>
                </label>
            </div>
        </div>

        <div class="bill-customer">
            <input type="text" id="customerName" placeholder="Customer name (required)" required>
            <input type="text" id="customerPhone" placeholder="Customer phone (optional)">
        </div>

        <div class="bill-items-table-wrap">
            <div class="bill-items-header">
                <span class="col-num">#</span>
                <span class="col-name">ITEM NAME</span>
                <span class="col-qty">QTY</span>
                <span class="col-price">UNIT PRICE</span>
                <span class="col-total">TOTAL</span>
                <span class="col-action">ACTION</span>
            </div>
            <div class="bill-items" id="billItems">
                <div class="empty-bill-message" id="emptyBillMessage">
                    <div class="empty-bill-icon">&#128722;</div>
                    <p>No items yet.</p>
                    <p class="empty-bill-subtext">Add products to get started.</p>
                </div>
            </div>
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
            <div class="total-row" id="vatRow" style="display:none;">
                <span id="vatLabel">VAT</span>
                <span id="vatValue">Rs. 0</span>
            </div>
            <div class="total-row total-row--grand">
                <span>TOTAL</span>
                <span id="grandTotalValue">Rs. 0</span>
            </div>
        </div>

        <div class="bill-action-buttons">
            <button type="button" class="btn-new-bill" id="newBillBtnBottom">+ New Bill</button>
            <button type="button" class="btn-show-bill" id="showBillBtn">&#128424; SHOW BILL (F8)</button>
        </div>

    </div>

</div>

{{-- PAYMENT POPUP --}}
<div class="payment-modal-overlay" id="paymentModalOverlay" style="display:none;">
    <div class="payment-modal-box">
        <button type="button" class="payment-modal-close" id="paymentModalCloseBtn">&#10005;</button>

        <div class="payment-modal-total">
            <span>Total Due</span>
            <b id="paymentModalTotal">Rs. 0</b>
        </div>

        <div class="payment-step" id="paymentStepMethod">
            <p class="payment-step-intro">Choose a payment method</p>

            <button type="button" class="payment-option-row" id="paymentMethodCashBtn">
                <span class="payment-option-icon payment-option-icon--cash">&#128181;</span>
                <span class="payment-option-text">
                    <span class="payment-option-title">Cash</span>
                    <span class="payment-option-subtitle">Customer pays with physical cash</span>
                </span>
                <span class="payment-option-chevron">&#8250;</span>
            </button>

            <button type="button" class="payment-option-row" id="paymentMethodQrBtn">
                <span class="payment-option-icon payment-option-icon--qr">&#128241;</span>
                <span class="payment-option-text">
                    <span class="payment-option-title">QR / Online</span>
                    <span class="payment-option-subtitle">Scan to pay the full amount</span>
                </span>
                <span class="payment-option-chevron">&#8250;</span>
            </button>

            <button type="button" class="payment-option-row" id="paymentMethodCreditBtn">
                <span class="payment-option-icon payment-option-icon--credit">&#128179;</span>
                <span class="payment-option-text">
                    <span class="payment-option-title">Credit</span>
                    <span class="payment-option-subtitle">Recorded as due, no cash collected</span>
                </span>
                <span class="payment-option-chevron">&#8250;</span>
            </button>

            <button type="button" class="payment-modal-cancel" id="paymentModalCancelBtn">Cancel</button>
        </div>

        <div class="payment-step" id="paymentStepCash" style="display:none;">
            <div class="payment-step-header">
                <button type="button" class="payment-step-back" id="backFromCashBtn" aria-label="Back">&#8592;</button>
                <span class="payment-step-title">Cash Payment</span>
            </div>

            <label class="payment-field-label" for="cashReceivedInput">Cash received</label>
            <input type="text" inputmode="decimal" id="cashReceivedInput" class="payment-cash-input" placeholder="0" autocomplete="off">
            <div class="payment-change-row">
                <span id="cashChangeLabel">Change</span>
                <b id="cashChangeValue">Rs. 0</b>
            </div>
            <button type="button" class="payment-complete-btn" id="completeCashBtn">Complete Sale</button>
        </div>

        <div class="payment-step" id="paymentStepQr" style="display:none;">
            <div class="payment-step-header">
                <button type="button" class="payment-step-back" id="backFromQrBtn" aria-label="Back">&#8592;</button>
                <span class="payment-step-title">QR / Online Payment</span>
            </div>

            <div class="payment-qr-display" id="paymentQrDisplay">
                @if ($paymentQrUrl)
                    <img src="{{ $paymentQrUrl }}" alt="Payment QR">
                @else
                    <p class="payment-qr-missing">No payment QR has been uploaded yet. Add one from Admin &rarr; Settings.</p>
                @endif
            </div>
            <p class="payment-qr-caption">Have the customer scan to pay the full total.</p>
            <button type="button" class="payment-complete-btn" id="completeQrBtn">Complete Sale</button>
        </div>

        <div class="payment-step" id="paymentStepCredit" style="display:none;">
            <div class="payment-step-header">
                <button type="button" class="payment-step-back" id="backFromCreditBtn" aria-label="Back">&#8592;</button>
                <span class="payment-step-title">Credit Sale</span>
            </div>

            <p class="payment-credit-note">
                This sale will be recorded as <b>credit / due</b> for
                <b id="paymentCreditCustomerName">the customer</b>.
                No cash is collected now.
            </p>
            <button type="button" class="payment-complete-btn" id="completeCreditBtn">Complete Sale</button>
        </div>
    </div>
</div>

{{-- HELD BILLS --}}
<div class="held-bills-overlay" id="heldBillsOverlay" style="display:none;">
    <div class="held-bills-box">
        <button type="button" class="payment-modal-close" id="closeHeldBillsBtn">&#10005;</button>
        <div class="held-bills-header">
            <span class="popup-icon-badge popup-icon-badge--hold">&#10074;&#10074;</span>
            <div>
                <h3>Held Bills</h3>
                <p class="held-bills-subtitle">Pick a bill up where you left off</p>
            </div>
        </div>
        <div class="held-bills-list" id="heldBillsList"></div>
        <p class="held-bills-empty" id="heldBillsEmptyMessage" style="display:none;">No bills are on hold right now.</p>
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
        const printerVars = @json($printerVars);
        const printerCopies = {{ $printerVars['copies'] }};
    </script>
    <script src="{{ asset('js/receipt-renderer.js') }}?v={{ filemtime(public_path('js/receipt-renderer.js')) }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/JsBarcode/3.11.5/JsBarcode.all.min.js"></script>
    <script src="{{ asset('js/billing.js') }}?v={{ filemtime(public_path('js/billing.js')) }}"></script>
@endsection