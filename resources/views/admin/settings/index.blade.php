@extends('admin.layouts.admin')

@section('title', 'Settings')

@section('styles')
<style>
    .set-page{max-width:100%;}
    .set-header{margin-bottom:22px;}
    .set-header h4{margin-bottom:2px;}
    .set-header p{margin-bottom:0; font-size:13px;}

    .set-alert{border-radius:10px; font-size:13.5px;}

    .set-shell{
        background:#fff;border-radius:12px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        overflow:hidden;
    }

    /* ---- Top-level tab bar ---- */
    .set-tabbar{
        display:flex;flex-wrap:wrap;gap:4px;padding:0 24px;border-bottom:1px solid #eceef3;
    }
    .set-tabbar .set-tab{
        display:inline-flex;align-items:center;gap:8px;padding:16px 6px;margin-right:26px;
        font-size:13.5px;font-weight:600;color:#8a94a6;border-bottom:2px solid transparent;
        cursor:pointer;background:none;border-left:none;border-right:none;border-top:none;
        transition:color .15s ease;white-space:nowrap;
    }
    .set-tabbar .set-tab i{font-size:13px;}
    .set-tabbar .set-tab:hover{color:#4a5568;}
    .set-tabbar .set-tab.active{color:#6777ef;border-bottom-color:#6777ef;}

    .set-body{padding:26px;}

    /* ---- Section intro (icon + heading) ---- */
    .set-section-head{display:flex;align-items:flex-start;gap:16px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid #f1f2f6;}
    .set-section-icon{
        width:46px;height:46px;border-radius:50%;background:#eef0fb;color:#6777ef;
        display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;
    }
    .set-section-head h5{margin:0 0 3px;font-size:16px;font-weight:700;color:#191d21;}
    .set-section-head p{margin:0;font-size:13px;color:#8a94a6;}

    /* ---- Form grid ---- */
    .set-grid{display:grid;grid-template-columns:repeat(3, 1fr);gap:20px;}
    .set-grid.cols-2{grid-template-columns:repeat(2, 1fr);}
    @media (max-width: 991.98px){ .set-grid, .set-grid.cols-2{grid-template-columns:1fr;} }

    .set-field label{font-size:13px;font-weight:600;color:#191d21;margin-bottom:6px;display:block;}
    .set-field .form-control{border-radius:9px;border:1px solid #e1e4ea;padding:10px 14px;font-size:13.5px;height:auto;}
    .set-field .form-control:focus{border-color:#6777ef;box-shadow:0 0 0 3px rgba(103,119,239,.12);}
    .set-field small.set-hint{display:block;margin-top:6px;font-size:12px;color:#98a6ad;}
    .set-input-icon{position:relative;}
    .set-input-icon input{padding-right:38px;}
    .set-input-icon i{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#98a6ad;font-size:13px;}
    .set-suffix{position:relative;}
    .set-suffix input{padding-right:42px;}
    .set-suffix span{position:absolute;right:14px;top:50%;transform:translateY(-50%);color:#98a6ad;font-size:12.5px;}

    .set-note{
        display:flex;align-items:flex-start;gap:10px;background:#eef0fb;border:1px solid #dfe3fb;
        border-radius:10px;padding:14px 16px;font-size:12.5px;color:#4a5568;margin-top:22px;
    }
    .set-note i{color:#6777ef;margin-top:1px;}
    .set-note strong{color:#191d21;}

    /* ---- Shop logo / QR upload ---- */
    .set-upload-row{display:flex;align-items:center;gap:16px;flex-wrap:wrap;}
    .set-upload-preview{
        width:84px;height:84px;border-radius:10px;border:1px solid #e1e4ea;background:#fafbff;
        display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;color:#c2c9d1;font-size:22px;
    }
    .set-upload-preview img{width:100%;height:100%;object-fit:contain;}
    .set-file-btn{
        display:inline-flex;align-items:center;gap:8px;background:#eef0fb;color:#6777ef;border:1px solid #dfe3fb;
        border-radius:8px;padding:9px 16px;font-size:13px;font-weight:600;cursor:pointer;
    }
    .set-file-name{font-size:12.5px;color:#8a94a6;margin-left:8px;}
    .set-qr-preview{width:130px;height:130px;object-fit:contain;border:1px solid #e1e4ea;border-radius:10px;padding:8px;background:#fff;}

    /* ---- Printer sub-tabs ---- */
    .set-subtabs{display:flex;gap:8px;border-bottom:1px solid #eceef3;margin-bottom:22px;flex-wrap:wrap;}
    .set-subtabs .set-subtab{
        display:inline-flex;align-items:center;gap:7px;padding:11px 4px;margin-right:22px;font-size:13px;font-weight:600;
        color:#8a94a6;border-bottom:2px solid transparent;cursor:pointer;background:none;border-left:none;border-right:none;border-top:none;
    }
    .set-subtabs .set-subtab.active{color:#6777ef;border-bottom-color:#6777ef;}
    .set-subtabs .set-subtab.disabled{color:#c2c9d1;cursor:not-allowed;}

    .set-printer-grid{display:grid;grid-template-columns:1.05fr 1.15fr 0.85fr;gap:22px;align-items:start;}
    @media (max-width: 1199.98px){ .set-printer-grid{grid-template-columns:1fr;} }

    .set-subcard{border:1px solid #eceef3;border-radius:11px;padding:18px 20px;}
    .set-subcard h6{font-size:13.5px;font-weight:700;color:#191d21;margin-bottom:2px;}
    .set-subcard .set-subcard-desc{font-size:12px;color:#98a6ad;margin-bottom:16px;}

    .set-radio-row{display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:13px;color:#4a5568;}
    .set-radio-row input{margin:0;}

    .set-preview-toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;gap:10px;flex-wrap:wrap;}
    .set-preview-toolbar h6{margin-bottom:0;}
    .set-preview-note{font-size:11.5px;color:#98a6ad;margin-bottom:12px;}
    .set-preview-frame-wrap{border:1px solid #eceef3;border-radius:10px;overflow:hidden;background:#f4f6f8;}
    .set-preview-frame{width:100%;height:460px;border:0;display:block;background:#f4f6f8;}
    .set-preview-actions{display:flex;gap:8px;margin-top:12px;}
    .set-btn-ghost{
        display:inline-flex;align-items:center;gap:7px;background:#fff;border:1px solid #e1e4ea;color:#4a5568;
        font-size:12.5px;font-weight:600;border-radius:8px;padding:8px 14px;cursor:pointer;text-decoration:none;
    }
    .set-btn-ghost:hover{background:#fafbff;text-decoration:none;color:#191d21;}

    .set-info-row{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f1f2f6;font-size:12.5px;}
    .set-info-row:last-child{border-bottom:none;}
    .set-info-row span:first-child{color:#98a6ad;}
    .set-info-row span:last-child{font-weight:700;color:#191d21;}

    .set-btn-primary-outline{
        display:inline-flex;align-items:center;gap:8px;background:#fff;border:1px solid #c9cffa;color:#6777ef;
        font-size:13px;font-weight:700;border-radius:9px;padding:10px 18px;cursor:pointer;text-decoration:none;margin-top:16px;
    }
    .set-btn-primary-outline:hover{background:#eef0fb;text-decoration:none;color:#6777ef;}
    .set-inline-hint{font-size:11.5px;color:#98a6ad;margin-left:10px;}

    .set-margins-grid{display:grid;grid-template-columns:repeat(2, 1fr);gap:14px;}

    /* ---- Coming-soon placeholder (same honest pattern as before) ---- */
    .set-placeholder{border:1px dashed #d7dbe8;border-radius:12px;padding:44px 24px;text-align:center;color:#8a94a6;}

    /* ---- Other Settings tab ---- */
    .set-other-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
    @media (max-width: 991.98px){ .set-other-grid{grid-template-columns:1fr;} }

    .set-toggle-row{display:flex;justify-content:space-between;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid #f1f2f6;}
    .set-toggle-row label{font-size:13px;font-weight:600;color:#191d21;margin-bottom:2px;display:inline-block;}
    .set-toggle-row small{display:block;color:#98a6ad;font-size:11.5px;}
    .set-soon-badge{font-size:10px;font-weight:700;color:#c9790a;background:#fff3e0;border-radius:20px;padding:2px 9px;margin-left:6px;vertical-align:middle;}

    .set-switch{position:relative;display:inline-block;width:42px;height:24px;flex-shrink:0;}
    .set-switch input{opacity:0;width:0;height:0;}
    .set-switch span{position:absolute;cursor:pointer;inset:0;background:#d7dbe8;border-radius:24px;transition:.15s;}
    .set-switch span:before{content:"";position:absolute;height:18px;width:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.15s;}
    .set-switch input:checked + span{background:#6777ef;}
    .set-switch input:checked + span:before{transform:translateX(18px);}
    .set-switch input:disabled + span{background:#eceef3;cursor:not-allowed;}
    .set-switch input:disabled + span:before{background:#f8f9fb;}
    .set-placeholder i{font-size:30px;color:#c2c9d1;display:block;margin-bottom:14px;}
    .set-placeholder strong{color:#191d21;font-size:14px;display:block;margin-bottom:8px;}
    .set-placeholder p{font-size:12.5px;max-width:520px;margin:0 auto;line-height:1.6;}

    .set-footer{
        display:flex;align-items:center;gap:14px;padding:20px 26px;border-top:1px solid #eceef3;background:#fafbff;
    }
    .set-save-btn{
        display:inline-flex;align-items:center;gap:9px;background:#6777ef;border:none;color:#fff;
        font-size:13.5px;font-weight:700;border-radius:9px;padding:12px 26px;cursor:pointer;
    }
    .set-save-btn:hover{background:#5867de;}
    .set-footer-hint{font-size:12.5px;color:#98a6ad;}
</style>
@endsection

@section('content')
<div class="set-page">

    <div class="set-header">
        <h4>Settings</h4>
        <p class="text-muted">Manage your shop, billing, and printer preferences.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success set-alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger set-alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" id="settingsForm">
        @csrf
        @method('PUT')

        <div class="set-shell">

            <div class="set-tabbar" id="setTabbar">
                <button type="button" class="set-tab active" data-target="pane-shop"><i class="fas fa-store"></i> Shop Profile</button>
                <button type="button" class="set-tab" data-target="pane-billing"><i class="fas fa-file-invoice"></i> Billing Preferences</button>
                <button type="button" class="set-tab" data-target="pane-qr"><i class="fas fa-qrcode"></i> Digital Wallet QR</button>
                <button type="button" class="set-tab" data-target="pane-printer"><i class="fas fa-print"></i> Printer Settings</button>
                <button type="button" class="set-tab" data-target="pane-other"><i class="fas fa-cog"></i> Other Settings</button>
            </div>

            <div class="set-body">

                {{-- ============================================================ SHOP PROFILE ============================================================ --}}
                <div class="set-pane" id="pane-shop">
                    <div class="set-section-head">
                        <span class="set-section-icon"><i class="fas fa-store"></i></span>
                        <div>
                            <h5>Shop Profile</h5>
                            <p>Update your shop information. These details will appear on your printed receipts.</p>
                        </div>
                    </div>

                    <div class="set-grid">
                        <div class="set-field">
                            <label>Shop Name</label>
                            <input type="text" class="form-control" name="shop_name" value="{{ old('shop_name', $settings['shop_name']) }}">
                        </div>
                        <div class="set-field">
                            <label>Address</label>
                            <input type="text" class="form-control" name="shop_address" value="{{ old('shop_address', $settings['shop_address']) }}">
                        </div>
                        <div class="set-field">
                            <label>Phone</label>
                            <input type="text" class="form-control" name="shop_phone" value="{{ old('shop_phone', $settings['shop_phone']) }}">
                        </div>
                        <div class="set-field">
                            <label>Email (Optional)</label>
                            <input type="email" class="form-control" name="shop_email" value="{{ old('shop_email', $settings['shop_email']) }}">
                        </div>
                        <div class="set-field">
                            <label>Tax / VAT Number (Optional)</label>
                            <input type="text" class="form-control" name="shop_tax_vat" value="{{ old('shop_tax_vat', $settings['shop_tax_vat']) }}">
                        </div>
                        <div class="set-field">
                            <label>Currency</label>
                            <input type="text" class="form-control" name="shop_currency" maxlength="10" placeholder="NPR"
                                   value="{{ old('shop_currency', $settings['shop_currency'] ?? 'NPR') }}">
                            <small class="set-hint">Label only — amounts across the app are shown in Rs. regardless of this value.</small>
                        </div>
                    </div>

                    <div class="set-field" style="margin-top:22px;">
                        <label>Shop Logo (Optional)</label>
                        <div class="set-upload-row">
                            <div class="set-upload-preview" id="shopLogoPreviewBox">
                                @if ($settings['shop_logo_path'])
                                    <img src="{{ asset('storage/' . $settings['shop_logo_path']) }}" id="shopLogoPreviewImg" alt="Shop logo">
                                @else
                                    <i class="fas fa-store" id="shopLogoPreviewIcon"></i>
                                    <img src="" id="shopLogoPreviewImg" alt="Shop logo" style="display:none;">
                                @endif
                            </div>
                            <div>
                                <label for="shopLogoInput" class="set-file-btn"><i class="fas fa-upload"></i> Choose File</label>
                                <input type="file" id="shopLogoInput" name="shop_logo" accept="image/*" style="display:none;" onchange="setPreviewFile(this, 'shopLogoPreviewImg', 'shopLogoPreviewIcon', 'shopLogoFileName')">
                                <span class="set-file-name" id="shopLogoFileName">No file chosen</span>
                                <small class="set-hint">Recommended size: 200x200px (PNG/JPG). This logo will appear on your printed receipts.</small>
                            </div>
                        </div>
                    </div>

                    <div class="set-note">
                        <i class="fas fa-info-circle"></i>
                        <span><strong>Note:</strong> This is a general reference profile. Your printed receipts use whichever details are set in each individual <a href="{{ route('admin.bill-templates.index') }}">Bill Design</a>.</span>
                    </div>
                </div>

                {{-- ============================================================ BILLING PREFERENCES ============================================================ --}}
                <div class="set-pane" id="pane-billing" style="display:none;">
                    <div class="set-section-head">
                        <span class="set-section-icon"><i class="fas fa-file-invoice"></i></span>
                        <div>
                            <h5>Billing Preferences</h5>
                            <p>Configure default billing values and inventory alerts for your business.</p>
                        </div>
                    </div>

                    <div class="set-grid cols-2">
                        <div class="set-field">
                            <label>Default Discount (Rs.)</label>
                            <div class="set-suffix">
                                <input type="number" step="0.01" min="0" class="form-control" name="default_discount" value="{{ old('default_discount', $settings['default_discount'] ?? 0) }}">
                                <span>Rs.</span>
                            </div>
                            <small class="set-hint">Pre-fills the discount field on the billing screen. Cashiers can still change it per bill.</small>
                        </div>
                        <div class="set-field">
                            <label>Low Stock Alert Threshold</label>
                            <div class="set-input-icon">
                                <input type="number" step="0.001" min="0" class="form-control" name="low_stock_threshold" value="{{ old('low_stock_threshold', $settings['low_stock_threshold'] ?? 5) }}">
                                <i class="fas fa-box"></i>
                            </div>
                            <small class="set-hint">Suggested minimum stock level when adding a new product. You can still set a different value per product.</small>
                        </div>
                    </div>

                    <div class="set-note">
                        <i class="fas fa-info-circle"></i>
                        <span><strong>Note:</strong> These preferences are used on the billing screen and inventory module.</span>
                    </div>
                </div>

                {{-- ============================================================ DIGITAL WALLET QR ============================================================ --}}
                <div class="set-pane" id="pane-qr" style="display:none;">
                    <div class="set-section-head">
                        <span class="set-section-icon"><i class="fas fa-qrcode"></i></span>
                        <div>
                            <h5>Digital Wallet QR</h5>
                            <p>This QR replaces the demo QR shown on all printed receipts.</p>
                        </div>
                    </div>

                    <div class="set-upload-row">
                        @if ($settings['payment_qr_path'])
                            <img src="{{ asset('storage/' . $settings['payment_qr_path']) }}" class="set-qr-preview" id="qrPreviewImg">
                        @else
                            <div class="set-upload-preview" style="width:130px;height:130px;">
                                <img src="" class="set-qr-preview" id="qrPreviewImg" style="display:none;">
                                <i class="fas fa-qrcode" id="qrPreviewIcon" style="font-size:30px;"></i>
                            </div>
                        @endif
                        <div>
                            <label for="qrInput" class="set-file-btn"><i class="fas fa-upload"></i> Upload QR Image</label>
                            <input type="file" id="qrInput" class="form-control-file" name="payment_qr" accept="image/*" style="display:none;" onchange="setPreviewFile(this, 'qrPreviewImg', 'qrPreviewIcon', 'qrFileName')">
                            <span class="set-file-name" id="qrFileName">No file chosen</span>
                            <small class="set-hint">PNG or JPG. This is your real eSewa / Khalti / bank QR image.</small>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ PRINTER SETTINGS ============================================================ --}}
                <div class="set-pane" id="pane-printer" style="display:none;">
                    <div class="set-section-head">
                        <span class="set-section-icon"><i class="fas fa-print"></i></span>
                        <div>
                            <h5>Printer Settings</h5>
                            <p>Controls physical printer output only (paper/label size, font scaling, copies). Receipt content stays in Bill Designer.</p>
                        </div>
                    </div>

                    <div class="set-subtabs" id="printerSubtabs">
                        <button type="button" class="set-subtab active" data-target="sub-receipt"><i class="fas fa-receipt"></i> Receipt Printer</button>
                        <button type="button" class="set-subtab" data-target="sub-label"><i class="fas fa-tag"></i> Label Printer</button>
                        <button type="button" class="set-subtab" data-target="sub-profiles"><i class="fas fa-sliders-h"></i> Printer Profiles</button>
                    </div>

                    {{-- ---------------- RECEIPT PRINTER ---------------- --}}
                    <div class="set-subpane" id="sub-receipt">
                        <div class="set-printer-grid">
                            <div class="set-subcard">
                                <h6>Printer Configuration</h6>
                                <div class="set-subcard-desc">Configure paper size and print behavior.</div>

                                <div class="set-field" style="margin-bottom:16px;">
                                    <label>Paper Width</label>
                                    <div class="set-suffix">
                                        <input type="number" step="0.1" min="20" max="200" class="form-control"
                                               name="printer_paper_width_mm" id="printerPaperWidthInput"
                                               value="{{ old('printer_paper_width_mm', $settings['printer_paper_width_mm'] ?? 72) }}">
                                        <span>mm</span>
                                    </div>
                                    <small class="set-hint">Common: 48mm, 58mm, 72mm, 80mm. Decimals are fine (e.g. 73.5mm).</small>
                                </div>

                                <div class="set-field" style="margin-bottom:16px;">
                                    <label>Text Alignment</label>
                                    <select class="form-control" name="printer_alignment">
                                        <option value="left" {{ old('printer_alignment', $settings['printer_alignment'] ?? 'left') == 'left' ? 'selected' : '' }}>Left</option>
                                        <option value="center" {{ old('printer_alignment', $settings['printer_alignment'] ?? 'left') == 'center' ? 'selected' : '' }}>Center</option>
                                        <option value="right" {{ old('printer_alignment', $settings['printer_alignment'] ?? 'left') == 'right' ? 'selected' : '' }}>Right</option>
                                    </select>
                                </div>

                                <div class="set-field" style="margin-bottom:16px;">
                                    <label>Margins</label>
                                    <div class="set-margins-grid" style="grid-template-columns:1fr 1fr;">
                                        <div class="set-suffix">
                                            <input type="number" step="0.5" min="0" max="30" class="form-control" name="printer_margin_left_mm" placeholder="Left"
                                                   value="{{ old('printer_margin_left_mm', $settings['printer_margin_left_mm'] ?? 0) }}">
                                            <span>mm</span>
                                        </div>
                                        <div class="set-suffix">
                                            <input type="number" step="0.5" min="0" max="30" class="form-control" name="printer_margin_right_mm" placeholder="Right"
                                                   value="{{ old('printer_margin_right_mm', $settings['printer_margin_right_mm'] ?? 0) }}">
                                            <span>mm</span>
                                        </div>
                                    </div>
                                    <small class="set-hint">Left/Right unprintable margins. Reduces the printable width, which also scales the font size down slightly.</small>
                                </div>

                                <div class="set-field" style="margin-bottom:16px;">
                                    <label>Paper Length</label>
                                    @php $lengthMode = old('printer_page_length_mode', $settings['printer_page_length_mode'] ?? 'auto'); @endphp
                                    <div class="set-radio-row">
                                        <input type="radio" id="lengthAuto" name="printer_page_length_mode" value="auto" {{ $lengthMode === 'auto' ? 'checked' : '' }}
                                               onchange="document.getElementById('customLengthInput').disabled = true;">
                                        <label for="lengthAuto" style="margin:0;font-weight:500;">Auto / Continuous</label>
                                    </div>
                                    <div class="set-radio-row">
                                        <input type="radio" id="lengthCustom" name="printer_page_length_mode" value="custom" {{ $lengthMode === 'custom' ? 'checked' : '' }}
                                               onchange="document.getElementById('customLengthInput').disabled = false;">
                                        <label for="lengthCustom" style="margin:0;font-weight:500;">Custom Length</label>
                                    </div>
                                    <div class="set-suffix" style="margin-top:8px;">
                                        <input type="number" step="1" min="20" max="500" class="form-control" id="customLengthInput"
                                               name="printer_page_length_mm"
                                               value="{{ old('printer_page_length_mm', $settings['printer_page_length_mm'] ?? 200) }}"
                                               {{ $lengthMode !== 'custom' ? 'disabled' : '' }}>
                                        <span>mm</span>
                                    </div>
                                    <small class="set-hint">Auto lets the receipt cut after the content ends. Range: 20–500mm.</small>
                                </div>

                                <div class="set-field" style="margin-bottom:16px;">
                                    <label>Print Size (Font Scaling)</label>
                                    <select class="form-control" name="printer_size_preset">
                                        <option value="small" {{ old('printer_size_preset', $settings['printer_size_preset'] ?? 'medium') == 'small' ? 'selected' : '' }}>Small</option>
                                        <option value="medium" {{ old('printer_size_preset', $settings['printer_size_preset'] ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium (recommended)</option>
                                        <option value="large" {{ old('printer_size_preset', $settings['printer_size_preset'] ?? 'medium') == 'large' ? 'selected' : '' }}>Large</option>
                                    </select>
                                    <small class="set-hint">Adjusts the content size on the real printed receipt.</small>
                                </div>

                                <div class="set-field" style="margin-bottom:16px;">
    <label>Receipt Text Size</label>
    <div class="set-suffix">
        <input type="number" step="1" min="10" max="40" class="form-control"
               name="printer_font_size_px" id="printerFontSizeInput"
               value="{{ old('printer_font_size_px', $settings['printer_font_size_px'] ?? (int) $printerVars['font_size']) }}">
        <span>px</span>
    </div>
    <small class="set-hint">The single text size used everywhere a receipt prints — Test Print, Billing, and Admin Bill History always match this exact value.</small>
</div>

                                <div class="set-field">
                                    <label>Receipt Copies</label>
                                    <input type="number" class="form-control" name="printer_copies" min="1" max="5" style="max-width:120px;"
                                           value="{{ old('printer_copies', $settings['printer_copies'] ?? 1) }}">
                                    <small class="set-hint">Same copies behavior in Billing, Admin reprint, Test Print, and Bill Designs.</small>
                                </div>


                                <a href="{{ route('admin.settings.testPrint') }}" target="_blank" class="set-btn-primary-outline">
                                    <i class="fas fa-print"></i> Test Print Receipt
                                </a>
                                <div class="set-inline-hint">Print a sample to check your printer output.</div>
                            </div>

                            <div class="set-subcard">
                                <div class="set-preview-toolbar">
                                    <h6>Receipt Preview</h6>
                                    <button type="button" class="set-btn-ghost" onclick="reloadPreview('receiptPreviewFrame')"><i class="fas fa-sync-alt"></i> Refresh</button>
                                </div>
                                <div class="set-preview-note">This is how your receipt will look. Reflects your last <strong>saved</strong> settings.</div>
                                <div class="set-preview-frame-wrap">
                                    <iframe id="receiptPreviewFrame" class="set-preview-frame" src="{{ route('admin.settings.testPrint') }}"></iframe>
                                </div>
                                <div class="set-preview-actions">
                                    <button type="button" class="set-btn-ghost" onclick="printPreview('receiptPreviewFrame')"><i class="fas fa-download"></i> Download Preview</button>
                                </div>
                            </div>

                            <div class="set-subcard">
                                <h6>Printer Information</h6>
                                <div class="set-subcard-desc">Current saved values, computed the same way your real receipts are.</div>
                                <div class="set-info-row"><span>Paper Width</span><span>{{ $printerVars['width'] }}</span></div>
                                <div class="set-info-row"><span>Printable Width</span><span>{{ $printerVars['printable_width'] }}</span></div>
                                <div class="set-info-row"><span>Alignment</span><span>{{ ucfirst($printerVars['alignment']) }}</span></div>
                                <div class="set-info-row"><span>Margins (L/R)</span><span>{{ $printerVars['margin_left'] }} / {{ $printerVars['margin_right'] }}</span></div>
                                <div class="set-info-row"><span>Paper Length</span><span>{{ $printerVars['length'] }}</span></div>
                                <div class="set-info-row"><span>Font Size</span><span>{{ $printerVars['font_size'] }}</span></div>
                                <div class="set-info-row"><span>Copies</span><span>{{ $printerVars['copies'] }}</span></div>
                                <div class="set-info-row"><span>Applies to</span><span>All Bill Designs</span></div>
                            </div>
                        </div>
                    </div>

                    {{-- ---------------- LABEL PRINTER ---------------- --}}
                    <div class="set-subpane" id="sub-label" style="display:none;">
                        <div class="set-printer-grid">
                            <div class="set-subcard">
                                <h6>Printer Configuration</h6>
                                <div class="set-subcard-desc">Configure label size, margins, and gap.</div>

                                <div class="set-grid cols-2" style="margin-bottom:16px;">
                                    <div class="set-field">
                                        <label>Label Width</label>
                                        <div class="set-suffix">
                                            <input type="number" step="1" min="10" max="200" class="form-control" name="label_width_mm"
                                                   value="{{ old('label_width_mm', $settings['label_width_mm'] ?? 50) }}">
                                            <span>mm</span>
                                        </div>
                                    </div>
                                    <div class="set-field">
                                        <label>Label Height</label>
                                        <div class="set-suffix">
                                            <input type="number" step="1" min="10" max="200" class="form-control" name="label_height_mm"
                                                   value="{{ old('label_height_mm', $settings['label_height_mm'] ?? 25) }}">
                                            <span>mm</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="set-field" style="margin-bottom:16px;">
                                    <label>Margins</label>
                                    <div class="set-margins-grid">
                                        <div class="set-suffix">
                                            <input type="number" step="1" min="0" max="50" class="form-control" name="label_margin_top_mm" placeholder="Top"
                                                   value="{{ old('label_margin_top_mm', $settings['label_margin_top_mm'] ?? 0) }}">
                                            <span>mm</span>
                                        </div>
                                        <div class="set-suffix">
                                            <input type="number" step="1" min="0" max="50" class="form-control" name="label_margin_right_mm" placeholder="Right"
                                                   value="{{ old('label_margin_right_mm', $settings['label_margin_right_mm'] ?? 0) }}">
                                            <span>mm</span>
                                        </div>
                                        <div class="set-suffix">
                                            <input type="number" step="1" min="0" max="50" class="form-control" name="label_margin_bottom_mm" placeholder="Bottom"
                                                   value="{{ old('label_margin_bottom_mm', $settings['label_margin_bottom_mm'] ?? 0) }}">
                                            <span>mm</span>
                                        </div>
                                        <div class="set-suffix">
                                            <input type="number" step="1" min="0" max="50" class="form-control" name="label_margin_left_mm" placeholder="Left"
                                                   value="{{ old('label_margin_left_mm', $settings['label_margin_left_mm'] ?? 0) }}">
                                            <span>mm</span>
                                        </div>
                                    </div>
                                    <small class="set-hint">Top / Right / Bottom / Left inner spacing. Range: 0–50mm each.</small>
                                </div>

                                <div class="set-field" style="margin-bottom:16px;">
                                    <label>Label Gap</label>
                                    <div class="set-suffix" style="max-width:160px;">
                                        <input type="number" step="1" min="0" max="50" class="form-control" name="label_gap_mm"
                                               value="{{ old('label_gap_mm', $settings['label_gap_mm'] ?? 2) }}">
                                        <span>mm</span>
                                    </div>
                                </div>

                                <div class="set-grid cols-2" style="margin-bottom:6px;">
                                    <div class="set-field">
                                        <label>Print Size</label>
                                        <select class="form-control" name="label_size_preset">
                                            <option value="small" {{ old('label_size_preset', $settings['label_size_preset'] ?? 'medium') == 'small' ? 'selected' : '' }}>Small</option>
                                            <option value="medium" {{ old('label_size_preset', $settings['label_size_preset'] ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium (recommended)</option>
                                            <option value="large" {{ old('label_size_preset', $settings['label_size_preset'] ?? 'medium') == 'large' ? 'selected' : '' }}>Large</option>
                                        </select>
                                    </div>
                                    <div class="set-field">
                                        <label>Label Copies</label>
                                        <input type="number" class="form-control" name="label_copies" min="1" max="100"
                                               value="{{ old('label_copies', $settings['label_copies'] ?? 1) }}">
                                    </div>
                                </div>

                                <a href="{{ route('admin.settings.testLabel') }}" target="_blank" class="set-btn-primary-outline">
                                    <i class="fas fa-tag"></i> Print Test Label
                                </a>
                                <div class="set-inline-hint">Print a sample to check your printer output.</div>
                            </div>

                            <div class="set-subcard">
                                <div class="set-preview-toolbar">
                                    <h6>Label Preview</h6>
                                    <button type="button" class="set-btn-ghost" onclick="reloadPreview('labelPreviewFrame')"><i class="fas fa-sync-alt"></i> Refresh</button>
                                </div>
                                <div class="set-preview-note">This is how your label will look. Reflects your last <strong>saved</strong> settings.</div>
                                <div class="set-preview-frame-wrap">
                                    <iframe id="labelPreviewFrame" class="set-preview-frame" src="{{ route('admin.settings.testLabel') }}"></iframe>
                                </div>
                                <div class="set-preview-actions">
                                    <button type="button" class="set-btn-ghost" onclick="printPreview('labelPreviewFrame')"><i class="fas fa-download"></i> Download Preview</button>
                                </div>
                            </div>

                            <div class="set-subcard">
                                <h6>Label Information</h6>
                                <div class="set-subcard-desc">Current saved values, computed the same way your real labels are.</div>
                                <div class="set-info-row"><span>Label Size</span><span>{{ $labelVars['width'] }} × {{ $labelVars['height'] }}</span></div>
                                <div class="set-info-row"><span>Gap</span><span>{{ $labelVars['gap'] }}</span></div>
                                <div class="set-info-row"><span>Copies</span><span>{{ $labelVars['copies'] }}</span></div>
                            </div>
                        </div>
                    </div>

                    {{-- ---------------- PRINTER PROFILES (placeholder, unchanged from before) ---------------- --}}
                    <div class="set-subpane" id="sub-profiles" style="display:none;">
                        <div class="set-placeholder">
                            <i class="fas fa-sliders-h"></i>
                            <strong>Printer Profiles — Coming soon</strong>
                            <p>Save and switch between multiple printer configurations (e.g. a receipt printer and a label printer) without re-entering settings each time. For now, the Receipt and Label settings above apply directly.</p>
                        </div>
                    </div>
                </div>

                {{-- ============================================================ OTHER SETTINGS ============================================================ --}}
                <div class="set-pane" id="pane-other" style="display:none;">
                    <div class="set-section-head">
                        <span class="set-section-icon"><i class="fas fa-cog"></i></span>
                        <div>
                            <h5>Other Settings</h5>
                            <p>General system, receipt display, backup, and security preferences.</p>
                        </div>
                    </div>

                    <div class="set-other-grid">

                        {{-- ---------------- GENERAL SETTINGS ---------------- --}}
                        <div class="set-subcard">
                            <h6><i class="fas fa-cog" style="color:#6777ef; margin-right:6px;"></i>General Settings</h6>
                            <div class="set-subcard-desc">Configure general system preferences.</div>

                            <div class="set-field" style="margin-bottom:16px;">
                                <label>Business Start Date</label>
                                <input type="date" class="form-control" name="business_start_date"
                                       value="{{ old('business_start_date', $settings['business_start_date']) }}">
                            </div>

                            <div class="set-field" style="margin-bottom:16px;">
                                <label>Default Language</label>
                                <select class="form-control" name="default_language">
                                    <option value="en" {{ old('default_language', $settings['default_language'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                                    <option value="ne" {{ old('default_language', $settings['default_language'] ?? 'en') == 'ne' ? 'selected' : '' }}>नेपाली (Nepali)</option>
                                </select>
                                <small class="set-hint">Changes validation & system messages app-wide. UI labels are translated progressively.</small>
                            </div>

                            <div class="set-field" style="margin-bottom:18px;">
                                <label>Time Zone</label>
                                <select class="form-control" name="app_timezone">
                                    @php $currentTz = old('app_timezone', $settings['app_timezone'] ?? 'Asia/Kathmandu'); @endphp
                                    @foreach ($timezones as $tz)
                                        <option value="{{ $tz }}" {{ $currentTz === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                                <small class="set-hint">Changes how dates/times render across Dashboard, Reports, and Bills.</small>
                            </div>

                            <div class="set-toggle-row">
                                <div>
                                    <label>Enable Stock Management</label>
                                    <small class="set-hint">Show low-stock alerts and the Low Stock indicator on Inventory.</small>
                                </div>
                                <label class="set-switch">
                                    <input type="checkbox" name="enable_stock_management" value="1" {{ old('enable_stock_management', $settings['enable_stock_management']) ? 'checked' : '' }}>
                                    <span></span>
                                </label>
                            </div>

                            <div class="set-toggle-row">
                                <div>
                                    <label>Show Product SKU</label>
                                    <small class="set-hint">Display SKU under each product on the Products page.</small>
                                </div>
                                <label class="set-switch">
                                    <input type="checkbox" name="show_product_sku" value="1" {{ old('show_product_sku', $settings['show_product_sku'] ?? true) ? 'checked' : '' }}>
                                    <span></span>
                                </label>
                            </div>

                            <div class="set-toggle-row">
                                <div>
                                    <label>Enable Sales Return <span class="set-soon-badge">Coming soon</span></label>
                                    <small class="set-hint">Allow return and refund for sales. Saved now, not yet enforced anywhere.</small>
                                </div>
                                <label class="set-switch">
                                    <input type="checkbox" name="enable_sales_return" value="1" {{ old('enable_sales_return', $settings['enable_sales_return']) ? 'checked' : '' }}>
                                    <span></span>
                                </label>
                            </div>

                            <div class="set-toggle-row" style="border-bottom:none;">
                                <div>
                                    <label>Enable Notifications <span class="set-soon-badge">Coming soon</span></label>
                                    <small class="set-hint">Receive system notifications and alerts. Saved now, not yet built.</small>
                                </div>
                                <label class="set-switch">
                                    <input type="checkbox" name="enable_notifications" value="1" {{ old('enable_notifications', $settings['enable_notifications']) ? 'checked' : '' }}>
                                    <span></span>
                                </label>
                            </div>
                        </div>

                        {{-- ---------------- RECEIPT & DISPLAY ---------------- --}}
                        <div class="set-subcard">
                            <h6><i class="fas fa-receipt" style="color:#1ca54a; margin-right:6px;"></i>Receipt &amp; Display</h6>
                            <div class="set-subcard-desc">Configure receipt content and display preferences.</div>

                            @if (! $defaultTemplate)
                                <div class="set-note" style="margin-top:0;">
                                    <i class="fas fa-info-circle"></i>
                                    <span>No default <a href="{{ route('admin.bill-templates.index') }}">Bill Design</a> is set yet, so the toggles below have nothing to apply to. Set one as default first.</span>
                                </div>
                            @else
                                <div class="set-toggle-row">
                                    <div><label>Show Logo on Receipt</label></div>
                                    <label class="set-switch">
                                        <input type="checkbox" name="show_logo" value="1" {{ old('show_logo', $defaultTemplate->show_logo) ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </div>
                                <div class="set-toggle-row">
                                    <div><label>Show Barcode on Receipt</label></div>
                                    <label class="set-switch">
                                        <input type="checkbox" name="show_barcode" value="1" {{ old('show_barcode', $defaultTemplate->show_barcode) ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </div>
                                <div class="set-toggle-row">
                                    <div><label>Show QR on Receipt</label></div>
                                    <label class="set-switch">
                                        <input type="checkbox" name="show_qr" value="1" {{ old('show_qr', $defaultTemplate->show_qr) ? 'checked' : '' }}>
                                        <span></span>
                                    </label>
                                </div>
                                <small class="set-hint" style="display:block; margin:-6px 0 16px;">These apply to your default Bill Design — the exact same fields as Bill Designer.</small>

                                <div class="set-field" style="margin-bottom:16px;">
                                    <label>Footer Message on Receipt</label>
                                    <input type="text" class="form-control" name="footer_text" maxlength="255"
                                           value="{{ old('footer_text', $defaultTemplate->footer_text) }}">
                                </div>
                            @endif

                            <div class="set-field">
                                <label>Items Per Page (In Tables)</label>
                                <select class="form-control" name="items_per_page" style="max-width:160px;">
                                    @foreach ([10, 20, 50, 100] as $n)
                                        <option value="{{ $n }}" {{ old('items_per_page', $settings['items_per_page'] ?? 20) == $n ? 'selected' : '' }}>{{ $n }}</option>
                                    @endforeach
                                </select>
                                <small class="set-hint">Applies to Customers and Bill History. Products/Categories are unpaginated and unaffected.</small>
                            </div>
                        </div>

                        {{-- ---------------- BACKUP & DATA ---------------- --}}
                        <div class="set-subcard">
                            <h6><i class="fas fa-cloud-upload-alt" style="color:#6777ef; margin-right:6px;"></i>Backup &amp; Data</h6>
                            <div class="set-subcard-desc">Manage system backup and data maintenance.</div>

                            <div class="set-toggle-row">
                                <div>
                                    <label>Auto Backup</label>
                                    <small class="set-hint">Automatically back up data daily at the time below.</small>
                                </div>
                                <label class="set-switch">
                                    <input type="checkbox" name="auto_backup" value="1" {{ old('auto_backup', $settings['auto_backup']) ? 'checked' : '' }}>
                                    <span></span>
                                </label>
                            </div>

                            <div class="set-grid cols-2" style="margin-top:16px; margin-bottom:16px;">
                                <div class="set-field">
                                    <label>Backup Time</label>
                                    <input type="time" class="form-control" name="backup_time" value="{{ old('backup_time', $settings['backup_time'] ?? '02:00') }}">
                                </div>
                                <div class="set-field">
                                    <label>Keep Backup For (Days)</label>
                                    <input type="number" class="form-control" name="keep_backup_for_days" min="1" max="365"
                                           value="{{ old('keep_backup_for_days', $settings['keep_backup_for_days'] ?? 30) }}">
                                    <small class="set-hint">Old backups will be deleted automatically.</small>
                                </div>
                            </div>

                            <small class="set-hint" style="display:block; margin-bottom:10px;">
                                Requires a system cron calling <code>php artisan schedule:run</code> every minute — see the setup notes I gave you.
                            </small>
                        </div>

                        {{-- ---------------- SECURITY SETTINGS ---------------- --}}
                        <div class="set-subcard">
                            <h6><i class="fas fa-lock" style="color:#c9790a; margin-right:6px;"></i>Security Settings</h6>
                            <div class="set-subcard-desc">Configure security and access preferences.</div>

                            <div class="set-toggle-row">
                                <div>
                                    <label>Require Strong Password</label>
                                    <small class="set-hint">Enforce 8+ chars, upper/lowercase & a number for all users.</small>
                                </div>
                                <label class="set-switch">
                                    <input type="checkbox" name="require_strong_password" value="1" {{ old('require_strong_password', $settings['require_strong_password']) ? 'checked' : '' }}>
                                    <span></span>
                                </label>
                            </div>

                            <div class="set-toggle-row">
                                <div>
                                    <label>Two Factor Authentication <span class="set-soon-badge">Coming soon</span></label>
                                    <small class="set-hint">Needs a small package install — see notes below.</small>
                                </div>
                                <label class="set-switch">
                                    <input type="checkbox" disabled>
                                    <span></span>
                                </label>
                            </div>

                            <div class="set-grid cols-2" style="margin-top:16px;">
                                <div class="set-field">
                                    <label>Auto Logout (Minutes)</label>
                                    <select class="form-control" name="auto_logout_minutes">
                                        @foreach ([15, 30, 60, 120, 240] as $n)
                                            <option value="{{ $n }}" {{ old('auto_logout_minutes', $settings['auto_logout_minutes'] ?? 120) == $n ? 'selected' : '' }}>{{ $n }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="set-field">
                                    <label>Login Session Limit</label>
                                    <input type="number" class="form-control" name="login_session_limit" min="0" max="20"
                                           value="{{ old('login_session_limit', $settings['login_session_limit'] ?? 0) }}">
                                    <small class="set-hint">Number of allowed sessions per user. 0 = unlimited.</small>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Backup Now needs its own POST submission (the main form uses
                         PUT) — submitted via a dynamically-created form so it's never
                         nested inside the settings <form>, which browsers won't handle
                         reliably. --}}
                    <button type="button" class="set-btn-primary-outline" style="margin-top:22px;" onclick="submitBackupNow()">
                        <i class="fas fa-cloud-upload-alt"></i> Backup Now
                    </button>

                    @if ($backups->count())
                        <div class="set-subcard" style="margin-top:18px;">
                            <h6>Recent Backups</h6>
                            @foreach ($backups->take(5) as $backup)
                                <div class="set-info-row">
                                    <span>{{ $backup['name'] }} ({{ number_format($backup['size'] / 1024, 0) }} KB)</span>
                                    <span>{{ $backup['date']->format('M d, Y g:i A') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            <div class="set-footer">
                <button type="submit" class="set-save-btn"><i class="fas fa-save"></i> Save All Settings</button>
                <span class="set-footer-hint">All changes will be saved to your system settings.</span>
            </div>

        </div>
    </form>

</div>
@endsection

@section('scripts')
<script>
    (function () {
        // ---- Top-level tabs ----
        document.querySelectorAll('#setTabbar .set-tab').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('#setTabbar .set-tab').forEach(function (b) { b.classList.remove('active'); });
                document.querySelectorAll('.set-pane').forEach(function (p) { p.style.display = 'none'; });
                btn.classList.add('active');
                document.getElementById(btn.dataset.target).style.display = '';
            });
        });

        // ---- Printer sub-tabs ----
        document.querySelectorAll('#printerSubtabs .set-subtab').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('#printerSubtabs .set-subtab').forEach(function (b) { b.classList.remove('active'); });
                document.querySelectorAll('.set-subpane').forEach(function (p) { p.style.display = 'none'; });
                btn.classList.add('active');
                document.getElementById(btn.dataset.target).style.display = '';
            });
        });
    })();

    // ---- File input preview (shop logo / QR) ----
    function setPreviewFile(input, imgId, iconId, nameId) {
        var nameEl = document.getElementById(nameId);
        if (nameEl) { nameEl.textContent = input.files && input.files[0] ? input.files[0].name : 'No file chosen'; }

        if (!input.files || !input.files[0]) { return; }

        var reader = new FileReader();
        reader.onload = function (e) {
            var img = document.getElementById(imgId);
            var icon = document.getElementById(iconId);
            if (img) { img.src = e.target.result; img.style.display = ''; }
            if (icon) { icon.style.display = 'none'; }
        };
        reader.readAsDataURL(input.files[0]);
    }

    // ---- Live preview iframe controls ----
    function reloadPreview(frameId) {
        var frame = document.getElementById(frameId);
        if (!frame) { return; }
        var base = frame.src.split('?')[0];
        frame.src = base + '?_=' + Date.now();
    }

    function printPreview(frameId) {
        var frame = document.getElementById(frameId);
        if (!frame || !frame.contentWindow) { return; }
        frame.contentWindow.focus();
        frame.contentWindow.print();
    }

    // Builds and submits a standalone POST form for "Backup Now" — kept
    // outside the main settings <form> (which uses PUT) so nothing is
    // ever nested in the DOM.
    function submitBackupNow() {
        var token = document.querySelector('#settingsForm input[name="_token"]').value;

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route('admin.settings.backupNow') }}';
        form.style.display = 'none';

        var tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = token;
        form.appendChild(tokenInput);

        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection