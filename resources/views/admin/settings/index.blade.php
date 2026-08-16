@extends('admin.layouts.admin')

@section('title', 'Settings')

@section('styles')
<style>
    .printer-tabs .nav-link {
        font-weight: 600;
        color: var(--color-text, #444);
    }
    .printer-tabs .nav-link.active {
        color: #1e3a8a;
        border-color: #dee2e6 #dee2e6 #fff;
    }
    .printer-tabs .nav-link.disabled {
        color: #bbb;
        cursor: not-allowed;
    }
    .settings-field-row {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    .settings-field-row > .form-group {
        flex: 1;
        min-width: 200px;
    }
    .settings-hint {
        display: block;
        font-size: 0.78rem;
        color: #888;
        margin-top: 4px;
    }
    .mm-suffix-group {
        position: relative;
        max-width: 220px;
    }
    .mm-suffix-group input {
        padding-right: 38px;
    }
    .mm-suffix-group span {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        font-size: 0.85rem;
        pointer-events: none;
    }
    .margins-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(140px, 1fr));
        gap: 16px;
        max-width: 480px;
    }
    .profiles-placeholder {
        border: 1px dashed #cfd6e0;
        border-radius: 8px;
        padding: 28px;
        text-align: center;
        color: #888;
    }
    .test-print-hint {
        font-size: 0.82rem;
        color: #888;
        margin-left: 10px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card">
                <div class="card-header">
                    <h4>Shop Profile</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Shop Name</label>
                        <input type="text" class="form-control" name="shop_name" value="{{ old('shop_name', $settings['shop_name']) }}">
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" class="form-control" name="shop_address" value="{{ old('shop_address', $settings['shop_address']) }}">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" name="shop_phone" value="{{ old('shop_phone', $settings['shop_phone']) }}">
                    </div>
                    <p class="text-muted" style="font-size:0.85rem;">
                        This is a general reference profile. Your printed receipts use whichever details are set in each individual <a href="{{ route('admin.bill-templates.index') }}">Bill Design</a>.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Billing Preferences</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Default Discount (Rs.)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="default_discount" value="{{ old('default_discount', $settings['default_discount'] ?? 0) }}" style="max-width:200px;">
                        <small class="text-muted">Pre-fills the discount field on the billing screen. Cashiers can still change it per bill.</small>
                    </div>
                    <div class="form-group">
                        <label>Low Stock Alert Threshold</label>
                        <input type="number" step="0.001" min="0" class="form-control" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 5) }}" style="max-width:200px;">
                        <small class="text-muted">Suggested minimum stock level when adding a new product. You can still set a different value per product.</small>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Digital Wallet QR</h4>
                </div>
                <div class="card-body">
                    @if ($settings['payment_qr_path'])
                        <img src="{{ asset('storage/' . $settings['payment_qr_path']) }}" style="width:120px; height:120px; object-fit:contain; border:1px solid var(--color-border); border-radius:4px; padding:6px; margin-bottom:10px; display:block;">
                    @endif
                    <div class="form-group">
                        <label>Upload QR Image</label>
                        <input type="file" class="form-control-file" name="payment_qr" accept="image/*">
                        <small class="text-muted">This replaces the demo QR on all printed receipts.</small>
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 PRINTER SETTINGS
                 Controls PHYSICAL printer output only (paper/label size,
                 font scaling, copies). Receipt content & layout stay in
                 Bill Designer — this section never duplicates that.
            ============================================================ --}}
            <div class="card">
                <div class="card-header">
                    <h4>Printer Settings</h4>
                </div>
                <div class="card-body">

                    <ul class="nav nav-tabs printer-tabs" id="printerTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-receipt-btn" data-toggle="tab" href="#tab-receipt" role="tab">
                                Receipt Printer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-label-btn" data-toggle="tab" href="#tab-label" role="tab">
                                Label Printer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled" id="tab-profiles-btn" data-toggle="tab" href="#tab-profiles" role="tab" tabindex="-1" aria-disabled="true">
                                Printer Profiles
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content pt-4" id="printerTabsContent">

                        {{-- ---------------- RECEIPT PRINTER ---------------- --}}
                        <div class="tab-pane fade show active" id="tab-receipt" role="tabpanel">

                            <div class="settings-field-row">
                                <div class="form-group">
                                    <label>Paper Width</label>
                                    <div class="mm-suffix-group">
                                        <input type="number" step="1" min="20" max="200" class="form-control"
                                               name="printer_paper_width_mm"
                                               value="{{ old('printer_paper_width_mm', $settings['printer_paper_width_mm'] ?? 72) }}">
                                        <span>mm</span>
                                    </div>
                                    <small class="settings-hint">
                                        Enter the actual printable width of your printer, e.g. 48, 55, 63, 72, 80. Range: 20–200mm.
                                        The PT210 default is 72mm.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label>Paper Length</label>
                                    @php
                                        $lengthMode = old('printer_page_length_mode', $settings['printer_page_length_mode'] ?? 'auto');
                                    @endphp
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="printer_page_length_mode"
                                               id="lengthAuto" value="auto" {{ $lengthMode === 'auto' ? 'checked' : '' }}
                                               onchange="document.getElementById('customLengthInput').disabled = true;">
                                        <label class="form-check-label" for="lengthAuto">
                                            Auto / Continuous <span class="text-muted"></span>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="printer_page_length_mode"
                                               id="lengthCustom" value="custom" {{ $lengthMode === 'custom' ? 'checked' : '' }}
                                               onchange="document.getElementById('customLengthInput').disabled = false;">
                                        <label class="form-check-label" for="lengthCustom">Custom Length</label>
                                    </div>
                                    <div class="mm-suffix-group">
                                        <input type="number" step="1" min="20" max="500" class="form-control"
                                               id="customLengthInput"
                                               name="printer_page_length_mm"
                                               value="{{ old('printer_page_length_mm', $settings['printer_page_length_mm'] ?? 200) }}"
                                               {{ $lengthMode !== 'custom' ? 'disabled' : '' }}>
                                        <span>mm</span>
                                    </div>
                                    <small class="settings-hint">Auto lets the receipt cut after the content ends. Custom range: 20–500mm.</small>
                                </div>
                            </div>

                            <div class="settings-field-row">
                                <div class="form-group">
                                    <label>Print Size (Font Scaling)</label>
                                    <select class="form-control" name="printer_size_preset" style="max-width:260px;">
                                        <option value="small" {{ old('printer_size_preset', $settings['printer_size_preset'] ?? 'medium') == 'small' ? 'selected' : '' }}>Small</option>
                                        <option value="medium" {{ old('printer_size_preset', $settings['printer_size_preset'] ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium (recommended)</option>
                                        <option value="large" {{ old('printer_size_preset', $settings['printer_size_preset'] ?? 'medium') == 'large' ? 'selected' : '' }}>Large</option>
                                    </select>
                                    <small class="settings-hint">Adjusts the content size on the real printed receipt.</small>
                                </div>

                                <div class="form-group">
                                    <label>Receipt Copies</label>
                                    <input type="number" class="form-control" name="printer_copies" min="1" max="5"
                                           value="{{ old('printer_copies', $settings['printer_copies'] ?? 1) }}" style="max-width:120px;">
                                    <small class="settings-hint">Same copies behavior in Billing, Admin reprint, and Test Print.</small>
                                </div>
                            </div>

                            <a href="{{ route('admin.settings.testPrint') }}" target="_blank" class="btn" style="background:#0d9488; color:#fff;">
                                Test Print Receipt
                            </a>
                            <span class="test-print-hint">Save settings first, then test print to check the real output.</span>
                        </div>

                        {{-- ---------------- LABEL PRINTER ---------------- --}}
                        <div class="tab-pane fade" id="tab-label" role="tabpanel">

                            <div class="settings-field-row">
                                <div class="form-group">
                                    <label>Label Width</label>
                                    <div class="mm-suffix-group">
                                        <input type="number" step="1" min="10" max="200" class="form-control"
                                               name="label_width_mm"
                                               value="{{ old('label_width_mm', $settings['label_width_mm'] ?? 50) }}">
                                        <span>mm</span>
                                    </div>
                                    <small class="settings-hint">Printable label width. Range: 10–200mm.</small>
                                </div>

                                <div class="form-group">
                                    <label>Label Height</label>
                                    <div class="mm-suffix-group">
                                        <input type="number" step="1" min="10" max="200" class="form-control"
                                               name="label_height_mm"
                                               value="{{ old('label_height_mm', $settings['label_height_mm'] ?? 25) }}">
                                        <span>mm</span>
                                    </div>
                                    <small class="settings-hint">Printable label height. Range: 10–200mm.</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Margins</label>
                                <div class="margins-grid">
                                    <div class="mm-suffix-group">
                                        <input type="number" step="1" min="0" max="50" class="form-control"
                                               name="label_margin_top_mm" placeholder="Top"
                                               value="{{ old('label_margin_top_mm', $settings['label_margin_top_mm'] ?? 0) }}">
                                        <span>mm</span>
                                    </div>
                                    <div class="mm-suffix-group">
                                        <input type="number" step="1" min="0" max="50" class="form-control"
                                               name="label_margin_right_mm" placeholder="Right"
                                               value="{{ old('label_margin_right_mm', $settings['label_margin_right_mm'] ?? 0) }}">
                                        <span>mm</span>
                                    </div>
                                    <div class="mm-suffix-group">
                                        <input type="number" step="1" min="0" max="50" class="form-control"
                                               name="label_margin_bottom_mm" placeholder="Bottom"
                                               value="{{ old('label_margin_bottom_mm', $settings['label_margin_bottom_mm'] ?? 0) }}">
                                        <span>mm</span>
                                    </div>
                                    <div class="mm-suffix-group">
                                        <input type="number" step="1" min="0" max="50" class="form-control"
                                               name="label_margin_left_mm" placeholder="Left"
                                               value="{{ old('label_margin_left_mm', $settings['label_margin_left_mm'] ?? 0) }}">
                                        <span>mm</span>
                                    </div>
                                </div>
                                <small class="settings-hint">Top / Right / Bottom / Left inner spacing. Range: 0–50mm each.</small>
                            </div>

                            <div class="settings-field-row">
                                <div class="form-group">
                                    <label>Label Gap</label>
                                    <div class="mm-suffix-group">
                                        <input type="number" step="1" min="0" max="50" class="form-control"
                                               name="label_gap_mm"
                                               value="{{ old('label_gap_mm', $settings['label_gap_mm'] ?? 2) }}">
                                        <span>mm</span>
                                    </div>
                                    <small class="settings-hint">Space between two labels.</small>
                                </div>

                                <div class="form-group">
                                    <label>Print Size (Content Scaling)</label>
                                    <select class="form-control" name="label_size_preset" style="max-width:260px;">
                                        <option value="small" {{ old('label_size_preset', $settings['label_size_preset'] ?? 'medium') == 'small' ? 'selected' : '' }}>Small</option>
                                        <option value="medium" {{ old('label_size_preset', $settings['label_size_preset'] ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium (recommended)</option>
                                        <option value="large" {{ old('label_size_preset', $settings['label_size_preset'] ?? 'medium') == 'large' ? 'selected' : '' }}>Large</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Label Copies</label>
                                    <input type="number" class="form-control" name="label_copies" min="1" max="100"
                                           value="{{ old('label_copies', $settings['label_copies'] ?? 1) }}" style="max-width:120px;">
                                    <small class="settings-hint">Default number of labels to print.</small>
                                </div>
                            </div>

                            <a href="{{ route('admin.settings.testLabel') }}" target="_blank" class="btn" style="background:#0d9488; color:#fff;">
                                Print Test Label
                            </a>
                            <span class="test-print-hint">Save settings first, then test print to check the real output.</span>
                        </div>

                        {{-- ---------------- PRINTER PROFILES (placeholder only) ---------------- --}}
                        <div class="tab-pane fade" id="tab-profiles" role="tabpanel">
                            <div class="profiles-placeholder">
                                <strong>Printer Profiles — Coming soon</strong>
                                <p class="mb-0" style="font-size:0.85rem;">
                                    Save and switch between multiple printer configurations (e.g. a receipt printer and a label printer)
                                    without re-entering settings each time. For now, the Receipt and Label settings above apply directly.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save All Settings</button>

        </form>

    </div>
</div>
@endsection
