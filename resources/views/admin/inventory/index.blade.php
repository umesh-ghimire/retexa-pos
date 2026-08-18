@extends('admin.layouts.admin')

@section('title', 'Inventory')

@section('styles')
<style>
    .inv-page{max-width:100%;}

    /* ---- Stat cards ---- */
    .inv-stats-row{
        display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:18px;
        margin-bottom:24px;
    }
    .inv-stat-card{
        position:relative;background:#fff;border-radius:12px;padding:18px 20px 46px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        overflow:hidden;min-width:0;
    }
    .inv-stat-card .stat-icon{
        width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;
        font-size:17px;margin-bottom:12px;position:relative;z-index:1;
    }
    .inv-stat-card .stat-label{font-size:12.5px;color:#98a6ad;font-weight:600;position:relative;z-index:1;}
    .inv-stat-card .stat-value{font-size:24px;font-weight:700;color:#191d21;line-height:1.3;position:relative;z-index:1;}
    .inv-stat-card .stat-sub{font-size:11.5px;color:#98a6ad;position:relative;z-index:1;}
    .inv-stat-card .stat-wave{position:absolute;left:0;right:0;bottom:0;height:40px;z-index:0;opacity:.55;}

    .bg-total .stat-icon{background:#eef0fb;color:#6777ef;}
    .bg-stock .stat-icon{background:#e5f9ea;color:#1ca54a;}
    .bg-low .stat-icon{background:#fff3e0;color:#c9790a;}
    .bg-out .stat-icon{background:#feeceb;color:#e1362c;}

    /* ---- Generic section card ---- */
    .inv-card{
        background:#fff;border-radius:12px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        margin-bottom:24px;overflow:hidden;
    }
    .inv-card-head{
        display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;
        padding:20px 22px 0;
    }
    .inv-card-head h6{margin:0;font-size:15px;font-weight:700;color:#191d21;display:flex;align-items:center;gap:9px;}
    .inv-card-head h6 .head-icon{
        width:30px;height:30px;border-radius:8px;background:#eef0fb;color:#6777ef;
        display:flex;align-items:center;justify-content:center;font-size:13px;
    }

    .inv-filters{display:flex;flex-wrap:wrap;gap:10px;align-items:center;padding:16px 22px 4px;}
    .inv-filters .search-wrap{position:relative;flex:1 1 220px;min-width:180px;}
    .inv-filters .search-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#98a6ad;font-size:12.5px;}
    .inv-filters .search-wrap input{padding-left:34px;border-radius:8px;font-size:13.5px;}
    .inv-filters select{border-radius:8px;font-size:13.5px;min-width:140px;flex:0 1 160px;}

    /* ---- Stock levels table ---- */
    .inv-table{width:100%;border-collapse:collapse;}
    .inv-table thead th{
        text-align:left;font-size:11px;font-weight:700;letter-spacing:.4px;color:#98a6ad;text-transform:uppercase;
        padding:14px 22px;border-bottom:1px solid #eceef3;background:#fafbfc;white-space:nowrap;
    }
    .inv-table tbody td{padding:14px 22px;border-bottom:1px solid #f1f2f6;vertical-align:middle;}
    .inv-table tbody tr:last-child td{border-bottom:none;}
    .inv-table tbody tr:hover{background:#fafbff;}

    .prod-cell{display:flex;align-items:center;gap:12px;min-width:0;}
    .prod-thumb{
        flex-shrink:0;width:38px;height:38px;border-radius:9px;object-fit:cover;
        background:#f1f2f6;display:flex;align-items:center;justify-content:center;color:#c2c9d1;font-size:13px;
    }
    .prod-name{font-size:13.5px;font-weight:700;color:#191d21;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;}

    .pill{display:inline-block;font-size:11.5px;font-weight:700;padding:4px 12px;border-radius:20px;white-space:nowrap;}
    .pill-category{background:#eef0fb;color:#6777ef;}
    .pill-category.none{background:#f1f2f6;color:#98a6ad;}
    .pill-stock{background:#e5f9ea;color:#1ca54a;}
    .pill-stock.low{background:#fff3e0;color:#c9790a;}
    .pill-stock.out{background:#feeceb;color:#e1362c;}

    .status-pill{display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:700;padding:4px 12px;border-radius:20px;white-space:nowrap;}
    .status-pill i{font-size:6px;}
    .status-pill.in{background:#e5f9ea;color:#1ca54a;}
    .status-pill.low{background:#fff3e0;color:#c9790a;}
    .status-pill.out{background:#feeceb;color:#e1362c;}

    .btn-adjust-row{
        border:1px solid #c9cffa;background:#fff;color:#6777ef;font-size:12.5px;font-weight:600;
        border-radius:8px;padding:7px 13px;display:inline-flex;align-items:center;gap:6px;cursor:pointer;
        transition:background-color .15s ease, color .15s ease;
    }
    .btn-adjust-row:hover{background:#6777ef;color:#fff;}

    /* ---- Movement table ---- */
    .move-type-badge{display:inline-block;font-size:11px;font-weight:700;padding:4px 12px;border-radius:20px;white-space:nowrap;}
    .move-type-badge.in{background:#e5f9ea;color:#1ca54a;}
    .move-type-badge.out{background:#feeceb;color:#e1362c;}
    .move-type-badge.set{background:#e5f0ff;color:#3b82f6;}

    .before-after{font-size:13px;color:#4a5568;white-space:nowrap;}
    .before-after .after-in{color:#1ca54a;font-weight:700;}
    .before-after .after-out{color:#e1362c;font-weight:700;}
    .before-after .after-set{color:#3b82f6;font-weight:700;}

    .inv-empty{text-align:center;padding:50px 20px;color:#98a6ad;}
    .inv-empty i{font-size:34px;display:block;margin-bottom:10px;color:#c7cbe0;}

    .inv-footer{
        display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;
        padding:16px 22px;color:#98a6ad;font-size:13px;
    }

    .pg-controls{display:flex;gap:6px;align-items:center;flex-wrap:wrap;}
    .pg-btn{
        min-width:34px;height:34px;padding:0 8px;border-radius:8px;border:1px solid #eceef3;background:#fff;
        display:inline-flex;align-items:center;justify-content:center;font-size:12.5px;font-weight:600;color:#4a5568;
        text-decoration:none;transition:background-color .15s ease,color .15s ease,border-color .15s ease;
    }
    .pg-btn:hover{background:#f1f2f6;text-decoration:none;color:#191d21;}
    .pg-btn.active{background:#6777ef;color:#fff;border-color:#6777ef;}
    .pg-btn.disabled{opacity:.4;pointer-events:none;}
    .pg-dots{color:#c2c9d1;font-size:12.5px;padding:0 2px;}

    @media (max-width: 767.98px){
        .inv-table thead{display:none;}
        .inv-table, .inv-table tbody, .inv-table tbody tr, .inv-table tbody td{display:block;width:100%;}
        .inv-table tbody tr{padding:14px 18px;border-bottom:1px solid #f1f2f6;}
        .inv-table tbody td{padding:5px 0;border-bottom:none;}
        .inv-filters select{flex:1 1 47%;}
    }
</style>
@endsection

@section('content')
<div class="inv-page">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:12px; margin-bottom:22px;">
        <div>
            <h4 style="margin-bottom:2px;">Inventory</h4>
            <p class="text-muted" style="margin-bottom:0; font-size:13px;">Manage stock levels and monitor recent stock movements in your store.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openAddAdjustModal()">
            <i class="fas fa-sliders-h" style="font-size:12px; margin-right:5px;"></i> Adjust Stock
        </button>
    </div>

    @php
        $totalItems = $products->count();
        $outOfStockProducts = $products->filter(fn ($p) => $p->stock <= 0);
        $lowStockProductsOnly = $products->filter(fn ($p) => $p->stock > 0 && $p->isLowStock());
        $inStockProducts = $products->filter(fn ($p) => $p->stock > 0 && ! $p->isLowStock());

        $waveGreen = 'M0,30 Q40,10 80,25 T160,20 T240,28 T320,15 T400,25 L400,40 L0,40 Z';
        $wavePurple = 'M0,25 Q40,35 80,18 T160,30 T240,15 T320,28 T400,20 L400,40 L0,40 Z';
        $waveAmber = 'M0,20 Q40,32 80,15 T160,28 T240,18 T320,30 T400,22 L400,40 L0,40 Z';
        $waveRed = 'M0,28 Q40,15 80,30 T160,18 T240,32 T320,20 T400,28 L400,40 L0,40 Z';
    @endphp

    {{-- STAT CARDS --}}
    <div class="inv-stats-row">
        <div class="inv-stat-card bg-total">
            <span class="stat-icon"><i class="fas fa-cube"></i></span>
            <div class="stat-label">Total Items</div>
            <div class="stat-value">{{ $totalItems }}</div>
            <div class="stat-sub">Products in inventory</div>
            <svg class="stat-wave" viewBox="0 0 400 40" preserveAspectRatio="none"><path d="{{ $wavePurple }}" fill="#6777ef"></path></svg>
        </div>
        <div class="inv-stat-card bg-stock">
            <span class="stat-icon"><i class="fas fa-layer-group"></i></span>
            <div class="stat-label">In Stock</div>
            <div class="stat-value">{{ $inStockProducts->count() }}</div>
            <div class="stat-sub">All items available</div>
            <svg class="stat-wave" viewBox="0 0 400 40" preserveAspectRatio="none"><path d="{{ $waveGreen }}" fill="#1ca54a"></path></svg>
        </div>
        @if (\App\Models\Setting::get('enable_stock_management', true))
        <div class="inv-stat-card bg-low">
            <span class="stat-icon"><i class="fas fa-chart-line"></i></span>
            <div class="stat-label">Low Stock</div>
            <div class="stat-value">{{ $lowStockProductsOnly->count() }}</div>
            <div class="stat-sub">Below minimum level</div>
            <svg class="stat-wave" viewBox="0 0 400 40" preserveAspectRatio="none"><path d="{{ $waveAmber }}" fill="#c9790a"></path></svg>
        </div>
        @endif
        <div class="inv-stat-card bg-out">
            <span class="stat-icon"><i class="fas fa-box-open"></i></span>
            <div class="stat-label">Out of Stock</div>
            <div class="stat-value">{{ $outOfStockProducts->count() }}</div>
            <div class="stat-sub">Unavailable items</div>
            <svg class="stat-wave" viewBox="0 0 400 40" preserveAspectRatio="none"><path d="{{ $waveRed }}" fill="#e1362c"></path></svg>
        </div>
    </div>

    {{-- STOCK LEVELS --}}
    <div class="inv-card">
        <div class="inv-card-head">
            <h6><span class="head-icon"><i class="fas fa-boxes"></i></span> Stock Levels</h6>
        </div>

        <div class="inv-filters">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="stockSearchInput" class="form-control" placeholder="Search products...">
            </div>
            <select id="stockCategoryFilter" class="form-control">
                <option value="">All Categories</option>
                @foreach ($products->pluck('category')->filter()->unique('id')->sortBy('name') as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select id="stockStatusFilter" class="form-control">
                <option value="">All Status</option>
                <option value="in">In Stock</option>
                <option value="low" @if(!\App\Models\Setting::get('enable_stock_management', true)) style="display:none;" @endif>Low Stock</option>
                <option value="out">Out of Stock</option>
            </select>
        </div>

        <div style="overflow-x:auto; margin-top:12px;">
            @if ($products->count())
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Unit</th>
                            <th>Min Level</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="stockRowsBody">
                        @foreach ($products as $product)
                            @php
                                $stockState = $product->stock <= 0 ? 'out' : ($product->isLowStock() ? 'low' : 'in');
                                $stockDisplay = rtrim(rtrim((string) $product->stock, '0'), '.');
                                $minDisplay = rtrim(rtrim((string) $product->min_stock_level, '0'), '.');
                                $statusLabel = ['in' => 'In Stock', 'low' => 'Low Stock', 'out' => 'Out of Stock'][$stockState];
                            @endphp
                            <tr class="stock-row"
                                data-name="{{ strtolower($product->name) }}"
                                data-category="{{ $product->category_id ?? '' }}"
                                data-status="{{ $stockState }}">
                                <td>
                                    <div class="prod-cell">
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="prod-thumb">
                                        @else
                                            <span class="prod-thumb"><i class="fas fa-box"></i></span>
                                        @endif
                                        <span class="prod-name" title="{{ $product->name }}">{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if ($product->category)
                                        <span class="pill pill-category">{{ $product->category->name }}</span>
                                    @else
                                        <span class="pill pill-category none">Uncategorized</span>
                                    @endif
                                </td>
                                <td><span class="pill pill-stock {{ $stockState !== 'in' ? $stockState : '' }}">{{ $stockDisplay }}</span></td>
                                <td>{{ $product->unit->short_code ?? '—' }}</td>
                                <td>{{ $minDisplay }}</td>
                                <td>
                                    <span class="status-pill {{ $stockState }}"><i class="fas fa-circle"></i>{{ $statusLabel }}</span>
                                </td>
                                <td>
                                    <button type="button" class="btn-adjust-row"
                                            onclick="openAdjustModal(
                                                {{ $product->id }},
                                                '{{ $product->name }}',
                                                '{{ $product->unit->short_code ?? '' }}'
                                            )">
                                        <i class="fas fa-sliders-h" style="font-size:11px;"></i> Adjust Stock
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="inv-empty" id="noStockResultsState" style="display:none;">
                    <i class="fas fa-search"></i>
                    No products match your search.
                </div>
            @else
                <div class="inv-empty">
                    <i class="fas fa-box-open"></i>
                    No products yet.
                </div>
            @endif
        </div>
    </div>

    {{-- RECENT STOCK MOVEMENTS --}}
    <div class="inv-card">
        <div class="inv-card-head">
            <h6><span class="head-icon" style="background:#e5f0ff; color:#3b82f6;"><i class="fas fa-history"></i></span> Recent Stock Movements</h6>
            <select id="movementTypeFilter" class="form-control" style="max-width:200px;" onchange="filterMovements(this.value)">
                <option value="" {{ ! $movementType ? 'selected' : '' }}>All Types</option>
                <option value="in" {{ $movementType === 'in' ? 'selected' : '' }}>Stock In</option>
                <option value="out" {{ $movementType === 'out' ? 'selected' : '' }}>Stock Out</option>
                <option value="set" {{ $movementType === 'set' ? 'selected' : '' }}>Correction</option>
            </select>
        </div>

        <div style="overflow-x:auto; margin-top:16px;">
            @if ($recentMovements->count())
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Before &rarr; After</th>
                            <th>Note</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentMovements as $movement)
                            @php
                                $qty = rtrim(rtrim((string) $movement->quantity, '0'), '.');
                                $before = rtrim(rtrim((string) $movement->stock_before, '0'), '.');
                                $after = rtrim(rtrim((string) $movement->stock_after, '0'), '.');
                                $typeLabel = ['in' => 'Stock In', 'out' => 'Stock Out', 'set' => 'Correction'][$movement->type] ?? ucfirst($movement->type);
                            @endphp
                            <tr>
                                <td style="white-space:nowrap; color:#4a5568; font-size:13px;">{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div class="prod-cell">
                                        @if ($movement->product && $movement->product->image)
                                            <img src="{{ asset('storage/' . $movement->product->image) }}" alt="" class="prod-thumb">
                                        @else
                                            <span class="prod-thumb"><i class="fas fa-box"></i></span>
                                        @endif
                                        <span class="prod-name">{{ $movement->product->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td><span class="move-type-badge {{ $movement->type }}">{{ $typeLabel }}</span></td>
                                <td>{{ $qty }}</td>
                                <td class="before-after">{{ $before }} &rarr; <span class="after-{{ $movement->type }}">{{ $after }}</span></td>
                                <td style="color:#98a6ad; font-size:13px;">{{ $movement->note ?? '—' }}</td>
                                <td style="font-size:13px;">{{ $movement->createdBy->name ?? 'System' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="inv-empty">
                    <i class="fas fa-history"></i>
                    No stock movements yet.
                </div>
            @endif
        </div>

        @if ($recentMovements->total())
            <div class="inv-footer">
                <span>Showing {{ $recentMovements->firstItem() }} to {{ $recentMovements->lastItem() }} of {{ $recentMovements->total() }} movements</span>

                @if ($recentMovements->lastPage() > 1)
                    @php
                        $current = $recentMovements->currentPage();
                        $last = $recentMovements->lastPage();
                        $delta = 1;
                        $range = [];
                        for ($i = max(2, $current - $delta); $i <= min($last - 1, $current + $delta); $i++) {
                            $range[] = $i;
                        }
                        $pages = collect([1])->merge($range)->push($last)->unique()->sort()->values();
                        $withDots = [];
                        $prev = null;
                        foreach ($pages as $p) {
                            if ($prev !== null && $p - $prev > 1) {
                                $withDots[] = '...';
                            }
                            $withDots[] = $p;
                            $prev = $p;
                        }
                    @endphp
                    <div class="pg-controls">
                        <a href="{{ $recentMovements->previousPageUrl() ?? '#' }}" class="pg-btn {{ $recentMovements->onFirstPage() ? 'disabled' : '' }}"><i class="fas fa-chevron-left"></i></a>
                        @foreach ($withDots as $item)
                            @if ($item === '...')
                                <span class="pg-dots">&hellip;</span>
                            @else
                                <a href="{{ $recentMovements->url($item) }}" class="pg-btn {{ $item === $current ? 'active' : '' }}">{{ $item }}</a>
                            @endif
                        @endforeach
                        <a href="{{ $recentMovements->nextPageUrl() ?? '#' }}" class="pg-btn {{ $recentMovements->hasMorePages() ? '' : 'disabled' }}"><i class="fas fa-chevron-right"></i></a>
                    </div>
                @endif
            </div>
        @endif
    </div>

</div>

{{-- ADJUST STOCK MODAL --}}
<div class="modal fade" id="adjustStockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="adjustStockForm" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock <span id="adjustProductNameWrap">— <span id="adjustProductName"></span></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group" id="adjustProductSelectWrap">
                        <label>Product</label>
                        <select class="form-control" id="adjustProductSelect" required>
                            <option value="">Select a product…</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-unit="{{ $product->unit->short_code ?? '' }}"
                                        data-url="{{ route('admin.inventory.adjust', $product) }}">
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Adjustment Type</label>
                        <select class="form-control" name="type" required>
                            <option value="in">Stock In (add / restock)</option>
                            <option value="out">Stock Out (remove)</option>
                            <option value="set">Correction (set exact amount)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity <span id="adjustUnitLabel"></span></label>
                        <input type="number" step="0.001" min="0" class="form-control" name="quantity" required>
                    </div>
                    <div class="form-group">
                        <label>Note (optional)</label>
                        <input type="text" class="form-control" name="note" placeholder="e.g. Supplier delivery, damaged goods, stock count correction">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/js/admin-inventory.js') }}"></script>
@endsection