@extends('admin.layouts.admin')

@section('title', 'Bill Designer')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/billing.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/bill-designer.css') }}">
@endsection

@section('content')
<div class="bd-app">

    <div class="bd-topbar">
        <div class="bd-topbar-left">
            <div class="bd-breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <i data-feather="chevron-right"></i>
                <a href="{{ route('admin.bill-templates.index') }}">Bill Designer</a>
                <i data-feather="chevron-right"></i>
                <strong>{{ $template->name }}</strong>
            </div>
            @if($template->is_default)
                <span class="bd-badge">Default Template</span>
            @endif
        </div>

        <div class="bd-topbar-actions">
            <div class="bd-btn-group">
                <button type="button" class="bd-icon-btn" id="bdUndoBtn" title="Undo" disabled>
                    <i data-feather="corner-up-left"></i><span>Undo</span>
                </button>
                <button type="button" class="bd-icon-btn" id="bdRedoBtn" title="Redo" disabled>
                    <i data-feather="corner-up-right"></i><span>Redo</span>
                </button>
            </div>

            <select id="bdPaperWidthSelect" class="bd-select" title="Paper width">
                <option value="58">58mm</option>
                <option value="72">72mm</option>
                <option value="80">80mm</option>
            </select>

            <button type="button" class="bd-btn" id="bdPreviewBtn">
                <i data-feather="eye"></i> Preview
            </button>
            <button type="button" class="bd-btn bd-btn-primary" id="bdSaveBtn">
                <i data-feather="save"></i> Save Template
            </button>
            <a href="{{ route('admin.bill-templates.index') }}" class="bd-btn">Close</a>
        </div>
    </div>

    <div class="bd-body">

        <div class="bd-left">
            <div class="bd-tabs">
                <button type="button" class="bd-tab active" data-tab="elements">Elements</button>
                <button type="button" class="bd-tab" data-tab="sections">Sections</button>
            </div>

            <div class="bd-tab-panel" id="bdPanelElements">
                <p class="bd-hint">Drag an element to canvas</p>
                <div class="bd-elements-grid">
                    <button type="button" class="bd-element-btn" data-add="text" draggable="true">
                        <i data-feather="type"></i><small>Text</small>
                    </button>
                    <button type="button" class="bd-element-btn" data-add="logo" draggable="true">
                        <i data-feather="image"></i><small>Logo</small>
                    </button>
                    <button type="button" class="bd-element-btn" data-add="qr" draggable="true">
                        <i data-feather="grid"></i><small>QR Code</small>
                    </button>
                    <button type="button" class="bd-element-btn" data-add="barcode" draggable="true">
                        <i data-feather="align-justify"></i><small>Barcode</small>
                    </button>
                    <button type="button" class="bd-element-btn" data-add="line" draggable="true">
                        <i data-feather="minus"></i><small>Line</small>
                    </button>
                    <button type="button" class="bd-element-btn" data-add="rectangle" draggable="true">
                        <i data-feather="square"></i><small>Rectangle</small>
                    </button>
                    <button type="button" class="bd-element-btn" data-add="table_items" draggable="true">
                        <i data-feather="list"></i><small>Table (Items)</small>
                    </button>
                    <button type="button" class="bd-element-btn" data-add="dynamic_field" draggable="true">
                        <i data-feather="code"></i><small>Dynamic Field</small>
                    </button>
                    <button type="button" class="bd-element-btn" data-add="spacer" draggable="true">
                        <i data-feather="square"></i><small>Spacer</small>
                    </button>
                </div>
            </div>

            <div class="bd-tab-panel" id="bdPanelSections" style="display:none;">
                <p class="bd-hint">Drag to reorder (stacking order)</p>
                <ul class="bd-layers-list" id="bdLayersList"></ul>
            </div>
        </div>

        <div class="bd-center">
            <div class="bd-canvas-toolbar">
                <div class="bd-tool-group">
                    <button type="button" class="bd-tool-btn active" id="bdToolSelect" data-tool="select" title="Select">
                        <i data-feather="mouse-pointer"></i>
                    </button>
                    <button type="button" class="bd-tool-btn" id="bdToolPan" data-tool="pan" title="Pan canvas">
                        <i data-feather="move"></i>
                    </button>
                    <button type="button" class="bd-tool-btn" id="bdToolMarquee" data-tool="marquee" title="Select area">
                        <i data-feather="crop"></i>
                    </button>
                </div>
                <div class="bd-tool-sep"></div>
                <div class="bd-tool-group">
                    <button type="button" class="bd-tool-btn" id="bdAlignLeftBtn" title="Align left">
                        <i data-feather="align-left"></i>
                    </button>
                    <button type="button" class="bd-tool-btn" id="bdAlignCenterBtn" title="Center horizontally">
                        <i data-feather="align-center"></i>
                    </button>
                </div>
                <div class="bd-tool-sep"></div>
                <div class="bd-tool-group">
                    <button type="button" class="bd-tool-btn" id="bdDuplicateBtn" title="Duplicate">
                        <i data-feather="copy"></i>
                    </button>
                    <button type="button" class="bd-tool-btn" id="bdDeleteBtn" title="Delete">
                        <i data-feather="trash-2"></i>
                    </button>
                    <button type="button" class="bd-tool-btn" id="bdLockBtn" title="Lock / unlock">
                        <i data-feather="lock"></i>
                    </button>
                </div>
                <div class="bd-tool-spacer"></div>
                <button type="button" class="bd-tool-btn" id="bdLayersBtn" title="Sections / layers">
                    <i data-feather="layers"></i>
                </button>
            </div>

            <div class="bd-ruler-row">
                <div class="bd-ruler-corner"></div>
                <div class="bd-ruler-h" id="bdRulerH"></div>
            </div>
            <div class="bd-canvas-row">
                <div class="bd-ruler-v" id="bdRulerV"></div>
                <div class="bd-canvas-frame" id="bdCanvasFrame">
                    <div class="bd-canvas" id="bdCanvas"></div>
                </div>
            </div>

            <div class="bd-zoom-bar">
                <button type="button" class="bd-zoom-btn" id="bdZoomOutBtn" title="Zoom out">
                    <i data-feather="minus"></i>
                </button>
                <span class="bd-zoom-value" id="bdZoomValue">100%</span>
                <button type="button" class="bd-zoom-btn" id="bdZoomInBtn" title="Zoom in">
                    <i data-feather="plus"></i>
                </button>
            </div>
        </div>

        <div class="bd-right">
            <div class="bd-tabs">
                <button type="button" class="bd-right-tab active" data-right-tab="properties">Properties</button>
                <button type="button" class="bd-right-tab" data-right-tab="settings">Settings</button>
            </div>

            <div class="bd-tab-panel" id="bdPanelProperties">
                <div id="bdPropertiesPanel">
                    <p class="bd-hint">Select an element to edit its properties</p>
                </div>
            </div>

            <div class="bd-tab-panel" id="bdPanelSettings" style="display:none;">
                <label>Template Name</label>
                <input type="text" value="{{ $template->name }}" disabled>

                <label>Printer Paper Width</label>
                <input type="text" value="{{ $printerPaperWidthMm }}mm (from Printer Settings)" disabled>

                <label>Elements on Canvas</label>
                <input type="text" id="bdElementCount" value="0" disabled>

                <div class="bd-checkbox-row" style="margin-top:16px;">
                    <input type="checkbox" id="bdShowGridToggle" checked>
                    <label style="margin:0;">Show grid dots</label>
                </div>

                <p class="bd-hint" style="margin-top:16px;">
                    Full template settings (shop info, toggles, logo upload) are managed from the
                    <a href="{{ route('admin.bill-templates.index') }}">Bill Designs</a> list.
                </p>
            </div>
        </div>

    </div>
</div>

{{-- Reuse the existing Preview modal styling/pattern --}}
<div class="modal fade" id="bdPreviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" style="background:#e9ecef; display:flex; justify-content:center; padding:24px;">
                <div id="bdPreviewContent" class="receipt-content"></div>
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
        const bdTemplate = @json($template);
        const bdLatestSale = @json($latestSale);
        const paymentQrImageUrl = @json($paymentQrUrl);
        const printerPaperWidthMm = {{ $printerPaperWidthMm }};
        const bdSaveLayoutUrl = @json(route('admin.bill-templates.saveLayout', $template));
        const csrfToken = @json(csrf_token());
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/JsBarcode/3.11.5/JsBarcode.all.min.js"></script>
    <script src="{{ asset('js/receipt-renderer.js') }}"></script>
    <script src="{{ asset('admin-assets/js/admin-bill-designer.js') }}"></script>
@endsection
