@extends('admin.layouts.admin')

@section('title', 'Products')

@section('styles')
<style>
    /* ===== Products catalog (card grid) ===== */
    .products-page{max-width:100%;}

    .stats-row{
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));
        gap:18px;
        margin-bottom:22px;
    }
    .stat-card{
        background:#fff;
        border-radius:12px;
        padding:18px 20px;
        display:flex;
        align-items:center;
        gap:14px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        min-width:0;
    }
    .stat-card .stat-icon{
        flex-shrink:0;
        width:46px;height:46px;border-radius:12px;
        display:flex;align-items:center;justify-content:center;
        font-size:18px;color:#fff;
    }
    .stat-card .stat-icon.bg-total{background:#6777ef;}
    .stat-card .stat-icon.bg-active{background:#54ca68;}
    .stat-card .stat-icon.bg-low{background:#ffa426;}
    .stat-card .stat-icon.bg-out{background:#fc544b;}
    .stat-card .stat-icon.bg-cat{background:#9a7bee;}
    .stat-card .stat-info{min-width:0;}
    .stat-card .stat-value{font-size:22px;font-weight:700;color:#191d21;line-height:1.1;}
    .stat-card .stat-label{font-size:12px;color:#98a6ad;font-weight:600;letter-spacing:.2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

    .filters-card{
        background:#fff;border-radius:12px;padding:16px 18px;margin-bottom:22px;
        display:flex;flex-wrap:wrap;gap:12px;align-items:center;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06);
    }
    .filters-card .search-wrap{position:relative;flex:1 1 240px;min-width:200px;}
    .filters-card .search-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#98a6ad;font-size:13px;}
    .filters-card .search-wrap input{padding-left:36px;border-radius:8px;}
    .filters-card select{border-radius:8px;min-width:140px;flex:0 1 160px;}

    .product-grid{
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));
        gap:16px;
    }

    .product-card{
        background:#fff;
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.08),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        display:flex;
        flex-direction:column;
        transition:transform .15s ease, box-shadow .15s ease;
        min-width:0;
        aspect-ratio:1/1;
    }
    .product-card:hover{
        transform:translateY(-3px);
        box-shadow:0 1rem 2.5rem rgba(90,97,105,0.16);
    }

    .product-card-media{
        position:relative;
        width:100%;
        flex:0 0 42%;
        min-height:0;
        background:#f5f6fb;
    }
    .product-card-media img{
        width:100%;height:100%;object-fit:cover;display:block;
    }
    .product-card-media .no-image{
        width:100%;height:100%;display:flex;align-items:center;justify-content:center;
        color:#c7cbe0;font-size:32px;
    }

    .product-badge{
        position:absolute;top:8px;left:8px;
        font-size:10px;font-weight:700;padding:4px 9px;border-radius:20px;
        letter-spacing:.3px;
    }
    .product-badge.status-active{background:#e5f9ea;color:#1ca54a;}
    .product-badge.status-inactive{background:#eceef3;color:#7c8698;}

    .product-card-body{
        padding:9px 11px 2px 11px;
        flex:1 1 auto;
        display:flex;
        flex-direction:column;
        min-width:0;
        min-height:0;
        overflow:hidden;
    }
    .product-name{
        font-size:12.5px;font-weight:700;color:#191d21;margin-bottom:1px;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }
    .product-meta{font-size:10px;color:#98a6ad;margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

    .product-price-row{
        margin-top:auto;
        display:flex;align-items:center;justify-content:space-between;gap:5px;
        min-width:0;
    }
    .product-price{font-size:13px;font-weight:700;color:#6777ef;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

    .stock-badge{
        font-size:9px;font-weight:700;padding:3px 8px;border-radius:11px;
        white-space:nowrap;text-align:center;line-height:1.2;flex-shrink:0;
    }
    .stock-badge .stock-qty{display:block;font-size:10px;}
    .stock-badge.in-stock{background:#e5f9ea;color:#1ca54a;}
    .stock-badge.low-stock{background:#fff3e0;color:#c9790a;}
    .stock-badge.out-stock{background:#fdeceb;color:#e1362c;}

    .product-card-actions{
        flex:0 0 auto;
        display:flex;gap:5px;padding:8px 10px 10px 10px;
    }
    .product-card-actions form{flex:1;margin:0;min-width:0;}
    .product-card-actions > button,
    .product-card-actions > a{
        flex:1;min-width:0;
    }
    .product-card-actions .card-action-btn{
        width:100%;height:30px;border:1px solid transparent;border-radius:7px;font-size:12px;
        display:flex;align-items:center;justify-content:center;
        cursor:pointer;transition:background-color .15s ease, color .15s ease, border-color .15s ease;
    }
    .card-action-btn.action-edit{
        background:#eef0fb;color:#4b5bd6;
    }
    .card-action-btn.action-edit:hover{background:#6777ef;color:#fff;}

    .card-action-btn.action-print,
    .card-action-btn.action-barcode{
        background:#fff;color:#6777ef;border-color:#c9cffa;
    }
    .card-action-btn.action-print:hover,
    .card-action-btn.action-barcode:hover{background:#6777ef;color:#fff;border-color:#6777ef;}

    .card-action-btn.action-delete{
        background:#fff;color:#fc544b;border-color:#fbc7c4;
    }
    .card-action-btn.action-delete:hover{background:#fc544b;color:#fff;border-color:#fc544b;}

    .empty-state-products{
        grid-column:1/-1;
        text-align:center;padding:60px 20px;color:#98a6ad;
    }
    .empty-state-products i{font-size:40px;margin-bottom:14px;display:block;color:#c7cbe0;}

    @media (max-width: 575.98px){
        .product-grid{grid-template-columns:repeat(auto-fill, minmax(140px, 1fr));gap:12px;}
        .filters-card select{flex:1 1 100%;}
    }
</style>
@endsection

@section('content')
<div class="products-page">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:12px; margin-bottom:22px;">
        <div>
            <h4 style="margin-bottom:2px;">Products</h4>
            <p class="text-muted" style="margin-bottom:0; font-size:13px;">Manage your store products, stock, pricing and availability.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openAddProductModal()">
            + Add Product
        </button>
    </div>

    @php
        $totalProducts = $products->count();
        $activeProducts = $products->where('status', 'active')->count();
        $lowStockCount = $products->filter(fn ($p) => $p->isLowStock() && (float) $p->stock > 0)->count();
        $outOfStockCount = $products->filter(fn ($p) => (float) $p->stock <= 0)->count();
    @endphp

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon bg-total"><i class="fas fa-shopping-bag"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalProducts }}</div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-active"><i class="fas fa-check"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $activeProducts }}</div>
                <div class="stat-label">Active Products</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-low"><i class="fas fa-box"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $lowStockCount }}</div>
                <div class="stat-label">Low Stock</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-out"><i class="fas fa-ban"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $outOfStockCount }}</div>
                <div class="stat-label">Out of Stock</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-cat"><i class="fas fa-tags"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $categories->count() }}</div>
                <div class="stat-label">Categories</div>
            </div>
        </div>
    </div>

    <div class="filters-card">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="productSearchInput" class="form-control" placeholder="Search by name or SKU...">
        </div>
        <select id="categoryFilter" class="form-control">
            <option value="">All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        <select id="statusFilter" class="form-control">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <select id="stockFilter" class="form-control">
            <option value="">All Stock</option>
            <option value="in">In Stock</option>
            <option value="low">Low Stock</option>
            <option value="out">Out of Stock</option>
        </select>
    </div>

    <div class="product-grid" id="productGrid">
        @forelse ($products as $product)
            @php
                $stockValue = (float) $product->stock;
                $stockLabel = rtrim(rtrim($product->stock, '0'), '.');
                if ($stockValue <= 0) {
                    $stockClass = 'out-stock';
                    $stockText = 'Out of Stock';
                } elseif ($product->isLowStock()) {
                    $stockClass = 'low-stock';
                    $stockText = 'Low Stock';
                } else {
                    $stockClass = 'in-stock';
                    $stockText = 'In Stock';
                }
            @endphp
            <div class="product-card"
                 data-name="{{ strtolower($product->name) }}"
                 data-sku="{{ strtolower($product->sku ?? '') }}"
                 data-category="{{ $product->category_id }}"
                 data-status="{{ $product->status }}"
                 data-stock-state="{{ $stockValue <= 0 ? 'out' : ($product->isLowStock() ? 'low' : 'in') }}">

                <div class="product-card-media">
                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                    @else
                        <div class="no-image"><i class="fas fa-image"></i></div>
                    @endif

                    <span class="product-badge {{ $product->status === 'active' ? 'status-active' : 'status-inactive' }}">
                        {{ ucfirst($product->status) }}
                    </span>
                </div>

                <div class="product-card-body">
                    <div class="product-name" title="{{ $product->name }}">{{ $product->name }}</div>
                    @if (\App\Models\Setting::get('show_product_sku', true))
                        <div class="product-meta">SKU: {{ $product->sku ?? '—' }}</div>
                    @endif

                    <div class="product-price-row">
                        <span class="product-price">Rs. {{ number_format($product->price, 2) }}</span>
                        <span class="stock-badge {{ $stockClass }}">
                            <span class="stock-qty">{{ $stockLabel }}</span>{{ $stockText }}
                        </span>
                    </div>
                </div>

                <div class="product-card-actions">
                    <button type="button" class="card-action-btn action-edit" title="Edit" onclick='openEditProductModal(@json($product))'>
                        <i class="fas fa-pen"></i>
                    </button>

                    @if ($product->barcode)
                        <a class="card-action-btn action-print" title="Print Label" href="{{ route('admin.products.label', $product) }}" target="_blank">
                            <i class="fas fa-print"></i>
                        </a>
                    @else
                        <form action="{{ route('admin.products.generateBarcode', $product) }}" method="POST">
                            @csrf
                            <button type="submit" class="card-action-btn action-barcode" title="Generate Barcode">
                                <i class="fas fa-barcode"></i>
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                          onsubmit="return confirm('Delete this product? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="card-action-btn action-delete" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state-products">
                <i class="fas fa-box-open"></i>
                No products yet.
            </div>
        @endforelse
    </div>

    <div class="empty-state-products" id="noResultsState" style="display:none;">
        <i class="fas fa-search"></i>
        No products match your filters.
    </div>

</div>

{{-- ADD / EDIT MODAL --}}
<div class="modal fade" id="productModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="productForm" method="POST" enctype="multipart/form-data"
                  data-store-url="{{ route('admin.products.store') }}">
                @csrf
                <div id="productMethodField"></div>

                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle">Add Product</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Product Name</label>
                            <input type="text" class="form-control" id="productNameInput" name="name" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>SKU (optional)</label>
                            <input type="text" class="form-control" id="productSkuInput" name="sku">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Category</label>
                            <select class="form-control" id="productCategoryInput" name="category_id">
                                <option value="">-- Uncategorized --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Unit</label>
                            <select class="form-control" id="productUnitInput" name="unit_id">
                                <option value="">-- No unit --</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->short_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Selling Price (Rs.)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="productPriceInput" name="price" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Cost Price (Rs., optional)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="productCostPriceInput" name="cost_price">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Barcode (optional)</label>
                            <input type="text" class="form-control" id="productBarcodeInput" name="barcode">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Stock Quantity</label>
                            <input type="number" step="0.001" min="0" class="form-control" id="productStockInput" name="stock" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Minimum Stock Level</label>
                            <input type="number" step="0.001" min="0" class="form-control" id="productMinStockInput" name="min_stock_level" value="{{ $defaultLowStock }}">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Status</label>
                            <select class="form-control" id="productStatusInput" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Product Image (optional)</label>
                        <input type="file" class="form-control-file" id="productImageInput" name="image" accept="image/*">
                        <div id="productCurrentImageWrapper" style="margin-top:8px; display:none;">
                            <small class="text-muted">Current image:</small><br>
                            <img id="productCurrentImage" src="" style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/js/admin-products.js') }}"></script>
    <script>
        (function () {
            var searchInput = document.getElementById('productSearchInput');
            var categoryFilter = document.getElementById('categoryFilter');
            var statusFilter = document.getElementById('statusFilter');
            var stockFilter = document.getElementById('stockFilter');
            var grid = document.getElementById('productGrid');
            var noResults = document.getElementById('noResultsState');

            function applyFilters() {
                if (!grid) return;

                var search = (searchInput.value || '').toLowerCase().trim();
                var category = categoryFilter.value;
                var status = statusFilter.value;
                var stock = stockFilter.value;
                var visibleCount = 0;

                var cards = grid.querySelectorAll('.product-card');
                cards.forEach(function (card) {
                    var matchesSearch = !search ||
                        card.dataset.name.indexOf(search) !== -1 ||
                        card.dataset.sku.indexOf(search) !== -1;
                    var matchesCategory = !category || card.dataset.category === category;
                    var matchesStatus = !status || card.dataset.status === status;
                    var matchesStock = !stock || card.dataset.stockState === stock;

                    var visible = matchesSearch && matchesCategory && matchesStatus && matchesStock;
                    card.style.display = visible ? '' : 'none';
                    if (visible) visibleCount++;
                });

                noResults.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
            }

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (stockFilter) stockFilter.addEventListener('change', applyFilters);
        })();
    </script>
@endsection