@extends('admin.layouts.admin')

@section('title', 'Bill Designs')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-12">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Bill Designs</h4>
                <button type="button" class="btn btn-primary" onclick="openAddTemplateModal()">
                    + New Design
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Paper Width</th>
                                <th>Font Size</th>
                                <th>Alignment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($templates as $template)
                                <tr>
                                    <td>{{ $template->name }}</td>
                                    <td>{{ $template->paper_width }}</td>
                                    <td>{{ ucfirst($template->font_size) }}</td>
                                    <td>{{ ucfirst($template->alignment) }}</td>
                                    <td>
                                        @if ($template->is_default)
                                            <span class="badge badge-primary">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light"
                                                onclick='openEditTemplateModal(@json($template))'>
                                            Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary"
                                                onclick='openPreviewModal(@json($template))'>
                                            Preview
                                        </button>

                                        <form action="{{ route('admin.bill-templates.duplicate', $template) }}"
                                              method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-info">Duplicate</button>
                                        </form>

                                        @unless ($template->is_default)
                                            <form action="{{ route('admin.bill-templates.setDefault', $template) }}"
                                                  method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary">Set Default</button>
                                            </form>

                                            <form action="{{ route('admin.bill-templates.destroy', $template) }}"
                                                  method="POST" style="display:inline;"
                                                  onsubmit="return confirm('Delete this bill design? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No bill designs yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ADD / EDIT MODAL (full designer) --}}
<div class="modal fade" id="templateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-designer-dialog" role="document">
        <div class="modal-content designer-content">
            <form id="templateForm" method="POST" enctype="multipart/form-data" data-store-url="{{ route('admin.bill-templates.store') }}">
                @csrf
                <div id="templateMethodField"></div>
                <input type="hidden" id="tplSectionOrderInput" name="section_order">

                <div class="designer-topbar">
                    <input type="text" class="designer-name-input" id="tplNameInput" name="name" placeholder="Design Name" required>
                    <div class="designer-topbar-actions">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Design</button>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
                </div>

                <div class="designer-body">

                    {{-- LEFT: Section order (drag-to-reorder added in the next step) --}}
                    <div class="designer-col designer-col-left">
                        <h6 class="designer-col-title">Sections</h6>
                        <p class="designer-col-hint">Order shown on the printed receipt. Drag-to-reorder is coming next.</p>
                        <ul class="designer-section-list" id="designerSectionList"></ul>
                    </div>

                    {{-- CENTER: live preview --}}
                    <div class="designer-col designer-col-center">
                        <div class="designer-preview-toolbar">
                            <div class="designer-toolbar-group">
                                <label>Paper Width</label>
                                <select class="form-control form-control-sm" id="tplPaperWidthInput" name="paper_width">
                                    <option value="58mm">58mm</option>
                                    <option value="80mm" selected>80mm</option>
                                </select>
                            </div>
                            <div class="designer-toolbar-group">
                                <label>Font Size</label>
                                <select class="form-control form-control-sm" id="tplFontSizeInput" name="font_size">
                                    <option value="small">Small</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="large">Large</option>
                                </select>
                            </div>
                            <div class="designer-toolbar-group">
                                <label>Alignment</label>
                                <select class="form-control form-control-sm" id="tplAlignmentInput" name="alignment">
                                    <option value="left" selected>Left</option>
                                    <option value="center">Center</option>
                                    <option value="right">Right</option>
                                </select>
                            </div>
                        </div>

                        <div class="designer-preview-frame">
                            <div id="designerPreviewContent" class="receipt-content"></div>
                        </div>
                    </div>

                    {{-- RIGHT: settings --}}
                    <div class="designer-col designer-col-right">

                        <h6 class="designer-col-title">Shop Details</h6>
                        <div class="form-group">
                            <label>Shop Name</label>
                            <input type="text" class="form-control" id="tplShopNameInput" name="shop_name">
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" class="form-control" id="tplPhoneInput" name="phone">
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" class="form-control" id="tplAddressInput" name="address">
                        </div>
                        <div class="form-group">
                            <label>Header Text (optional)</label>
                            <input type="text" class="form-control" id="tplHeaderTextInput" name="header_text">
                        </div>
                        <div class="form-group">
                            <label>Footer Text</label>
                            <input type="text" class="form-control" id="tplFooterTextInput" name="footer_text" value="THANK YOU / VISIT AGAIN">
                        </div>

                        <h6 class="designer-col-title mt-3">Logo</h6>
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="tplShowLogoInput" name="show_logo" value="1">
                            <label class="custom-control-label" for="tplShowLogoInput">Show shop logo</label>
                        </div>
                        <div class="form-group">
                            <input type="file" class="form-control-file" id="tplLogoInput" name="logo" accept="image/*">
                            <div id="tplCurrentLogoWrapper" style="margin-top:8px; display:none;">
                                <small class="text-muted">Current logo:</small><br>
                                <img id="tplCurrentLogo" src="" style="width:50px; height:50px; object-fit:contain; border:1px solid var(--color-border); border-radius:4px; padding:4px;">
                            </div>
                        </div>

                        <h6 class="designer-col-title mt-3">Fields to Show</h6>
                        <div class="designer-toggle-grid">
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowCustomerInput" name="show_customer" value="1" checked>
                                <label class="custom-control-label" for="tplShowCustomerInput">Customer Info</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowBillNumberInput" name="show_bill_number" value="1" checked>
                                <label class="custom-control-label" for="tplShowBillNumberInput">Bill Number</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowDateInput" name="show_date" value="1" checked>
                                <label class="custom-control-label" for="tplShowDateInput">Date / Time</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowSkuInput" name="show_sku" value="1">
                                <label class="custom-control-label" for="tplShowSkuInput">SKU</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowQuantityInput" name="show_quantity" value="1" checked>
                                <label class="custom-control-label" for="tplShowQuantityInput">Quantity</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowUnitInput" name="show_unit" value="1" checked>
                                <label class="custom-control-label" for="tplShowUnitInput">Unit</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowPriceInput" name="show_price" value="1" checked>
                                <label class="custom-control-label" for="tplShowPriceInput">Item Price</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowSubtotalInput" name="show_subtotal" value="1" checked>
                                <label class="custom-control-label" for="tplShowSubtotalInput">Subtotal</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowDiscountInput" name="show_discount" value="1" checked>
                                <label class="custom-control-label" for="tplShowDiscountInput">Discount</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowCashReceivedInput" name="show_cash_received" value="1" checked>
                                <label class="custom-control-label" for="tplShowCashReceivedInput">Cash Received</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowChangeInput" name="show_change" value="1" checked>
                                <label class="custom-control-label" for="tplShowChangeInput">Change</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="tplShowQrInput" name="show_qr" value="1" checked>
                                <label class="custom-control-label" for="tplShowQrInput">QR Code</label>
                            </div>
                        </div>

                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

{{-- PREVIEW MODAL --}}
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview — <span id="previewTemplateName"></span></h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="background:#e9ecef; display:flex; justify-content:center; padding:24px;">
                <div id="previewReceiptContent" class="receipt-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script>
        const latestSaleForPreview = @json($latestSale);
    </script>
    <script src="{{ asset('js/receipt-renderer.js') }}"></script>
    <script src="{{ asset('admin-assets/js/admin-bill-templates.js') }}"></script>
@endsection