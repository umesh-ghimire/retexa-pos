@extends('admin.layouts.admin')

@section('title', 'Categories')

@section('styles')
<style>
    /* ===== Categories (card grid) ===== */
    .categories-page{max-width:100%;}

    .stats-row{
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
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
        font-size:18px;
    }
    .stat-card .stat-icon.bg-total{background:#eef0fb;color:#6777ef;}
    .stat-card .stat-icon.bg-active{background:#e5f9ea;color:#1ca54a;}
    .stat-card .stat-icon.bg-products{background:#fff3e0;color:#c9790a;}
    .stat-card .stat-icon.bg-value{background:#e5f4ff;color:#2f8fe0;}
    .stat-card .stat-info{min-width:0;}
    .stat-card .stat-value{font-size:22px;font-weight:700;color:#191d21;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .stat-card .stat-label{font-size:12.5px;color:#98a6ad;font-weight:600;letter-spacing:.2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

    .filters-card{
        background:#fff;border-radius:12px;padding:16px 18px;margin-bottom:22px;
        display:flex;flex-wrap:wrap;gap:12px;align-items:center;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06);
    }
    .filters-card .search-wrap{position:relative;flex:1 1 260px;min-width:200px;}
    .filters-card .search-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#98a6ad;font-size:13px;}
    .filters-card .search-wrap input{padding-left:36px;border-radius:8px;}
    .filters-card select{border-radius:8px;min-width:150px;flex:0 1 170px;}
    .filters-card .btn-filter-clear{border-radius:8px;flex:0 0 auto;}

    .category-grid{
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(230px, 1fr));
        gap:18px;
        margin-bottom:18px;
    }

    .category-card{
        background:#fff;
        border-radius:12px;
        padding:20px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        display:flex;
        flex-direction:column;
        transition:transform .15s ease, box-shadow .15s ease;
        min-width:0;
    }
    .category-card:hover{
        transform:translateY(-3px);
        box-shadow:0 1rem 2.5rem rgba(90,97,105,0.16);
    }

    .category-icon{
        width:56px;height:56px;border-radius:14px;
        display:flex;align-items:center;justify-content:center;
        font-size:24px;margin-bottom:16px;
        overflow:hidden;
    }
    .category-icon img{
        width:100%;height:100%;object-fit:cover;display:block;border-radius:14px;
    }

    .category-name{
        font-size:16px;font-weight:700;color:#191d21;margin-bottom:2px;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }
    .category-meta{font-size:12.5px;color:#98a6ad;margin-bottom:12px;}

    .category-status{
        display:inline-block;
        font-size:11px;font-weight:700;padding:3px 11px;border-radius:20px;
        letter-spacing:.3px;margin-bottom:18px;width:fit-content;
    }
    .category-status.status-active{background:#e5f9ea;color:#1ca54a;}
    .category-status.status-inactive{background:#eceef3;color:#7c8698;}

    .category-card-actions{
        margin-top:auto;
        display:flex;gap:8px;
    }
    .category-card-actions form{flex:1 1 0;margin:0;min-width:0;}
    .category-card-actions > button{flex:1 1 0;min-width:0;}
    .category-card-actions .card-action-btn{
        width:100%;height:36px;border:1px solid transparent;border-radius:8px;font-size:13px;font-weight:600;
        padding:0 10px;box-sizing:border-box;background-color:#fff;
        display:flex;align-items:center;justify-content:center;gap:6px;
        cursor:pointer;transition:background-color .15s ease, color .15s ease, border-color .15s ease;
        appearance:none;-webkit-appearance:none;outline:none;
    }
    .category-card-actions .card-action-btn:focus{box-shadow:0 0 0 2px rgba(103,119,239,0.25);}
    .card-action-btn.action-edit{background:#fff;color:#6777ef;border-color:#c9cffa;}
    .card-action-btn.action-edit:hover{background:#6777ef;color:#fff;border-color:#6777ef;}

    .card-action-btn.action-delete{background:#fff;color:#fc544b;border-color:#fbc7c4;}
    .card-action-btn.action-delete:hover{background:#fc544b;color:#fff;border-color:#fc544b;}

    .empty-state-categories{
        grid-column:1/-1;
        text-align:center;padding:60px 20px;color:#98a6ad;
        background:#fff;border-radius:12px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06);
    }
    .empty-state-categories i{font-size:40px;margin-bottom:14px;display:block;color:#c7cbe0;}

    .categories-footer{
        display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;
        color:#98a6ad;font-size:13px;
    }

    @media (max-width: 575.98px){
        .category-grid{grid-template-columns:repeat(auto-fill, minmax(160px, 1fr));gap:12px;}
        .filters-card select{flex:1 1 100%;}
    }
</style>
@endsection

@section('content')
<div class="categories-page">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:12px; margin-bottom:6px;">
        <div>
            <nav aria-label="breadcrumb" style="margin-bottom:2px;">
                <a href="{{ url('/admin/dashboard') }}" style="color:#6777ef; font-weight:600; font-size:13px;">Dashboard</a>
                <span style="color:#98a6ad; font-size:13px;"> &gt; </span>
                <span style="color:#191d21; font-weight:600; font-size:13px;">Categories</span>
            </nav>
            <h4 style="margin-bottom:2px; margin-top:6px;">Categories</h4>
            <p class="text-muted" style="margin-bottom:0; font-size:13px;">Organize your products into categories for better management.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openAddCategoryModal()">
            <i class="fas fa-plus" style="font-size:12px; margin-right:4px;"></i> Add Category
        </button>
    </div>

    @php
        $totalCategories = $categories->count();
        $activeCategories = $categories->where('status', 'active')->count();
        $totalProducts = $categories->sum('products_count');
        $totalInventoryValue = $categories->sum(function ($category) {
            return $category->products->sum(function ($product) {
                return (float) $product->price * (float) $product->stock;
            });
        });

        // Deterministic icon + color per category so the grid feels intentional
        // even though the schema doesn't store an icon/color per category.
        $iconMap = [
            'electronic' => ['icon' => 'fa-laptop', 'bg' => '#efe9fe', 'color' => '#8a5bf2'],
            'general' => ['icon' => 'fa-shopping-bag', 'bg' => '#e5f9ea', 'color' => '#1ca54a'],
            'beverage' => ['icon' => 'fa-coffee', 'bg' => '#ffece5', 'color' => '#ef7c4d'],
            'grocer' => ['icon' => 'fa-shopping-cart', 'bg' => '#e5f0ff', 'color' => '#3b82f6'],
            'cloth' => ['icon' => 'fa-tshirt', 'bg' => '#ffe6f0', 'color' => '#e0447b'],
            'health' => ['icon' => 'fa-heartbeat', 'bg' => '#e1f8f5', 'color' => '#0fb2a3'],
            'beauty' => ['icon' => 'fa-heartbeat', 'bg' => '#e1f8f5', 'color' => '#0fb2a3'],
            'mobile' => ['icon' => 'fa-mobile-alt', 'bg' => '#efe9fe', 'color' => '#8a5bf2'],
            'accessor' => ['icon' => 'fa-mobile-alt', 'bg' => '#efe9fe', 'color' => '#8a5bf2'],
            'book' => ['icon' => 'fa-book', 'bg' => '#fff3d9', 'color' => '#d8a326'],
            'stationery' => ['icon' => 'fa-book', 'bg' => '#fff3d9', 'color' => '#d8a326'],
        ];
        $fallbackPalette = [
            ['icon' => 'fa-tag', 'bg' => '#eef0fb', 'color' => '#6777ef'],
            ['icon' => 'fa-cube', 'bg' => '#fdeceb', 'color' => '#e1362c'],
            ['icon' => 'fa-star', 'bg' => '#fff3e0', 'color' => '#c9790a'],
            ['icon' => 'fa-gift', 'bg' => '#e5f4ff', 'color' => '#2f8fe0'],
        ];
    @endphp

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon bg-total"><i class="fas fa-th-large"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalCategories }}</div>
                <div class="stat-label">Total Categories</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-active"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $activeCategories }}</div>
                <div class="stat-label">Active Categories</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-products"><i class="fas fa-box"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalProducts }}</div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-value"><i class="fas fa-tag"></i></div>
            <div class="stat-info">
                <div class="stat-value">Rs. {{ number_format($totalInventoryValue, 2) }}</div>
                <div class="stat-label">Total Inventory Value</div>
            </div>
        </div>
    </div>

    <div class="filters-card">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="categorySearchInput" class="form-control" placeholder="Search categories by name...">
        </div>
        <select id="statusFilter" class="form-control">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
        <button type="button" class="btn btn-light btn-filter-clear" id="clearFiltersBtn">
            <i class="fas fa-filter" style="margin-right:5px;"></i>Clear
        </button>
    </div>

    <div class="category-grid" id="categoryGrid">
        @forelse ($categories as $index => $category)
            @php
                $key = strtolower($category->name);
                $style = null;
                foreach ($iconMap as $needle => $conf) {
                    if (str_contains($key, $needle)) {
                        $style = $conf;
                        break;
                    }
                }
                if (! $style) {
                    $style = $fallbackPalette[$index % count($fallbackPalette)];
                }
            @endphp
            <div class="category-card"
                 data-name="{{ $key }}"
                 data-status="{{ $category->status }}">

                <div class="category-icon" style="background:{{ $style['bg'] }}; color:{{ $style['color'] }};">
                    @if ($category->image)
                        <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}">
                    @else
                        <i class="fas {{ $style['icon'] }}"></i>
                    @endif
                </div>

                <div class="category-name" title="{{ $category->name }}">{{ $category->name }}</div>
                <div class="category-meta">{{ $category->products_count }} {{ Str::plural('Product', $category->products_count) }}</div>

                <span class="category-status {{ $category->status === 'active' ? 'status-active' : 'status-inactive' }}">
                    {{ ucfirst($category->status) }}
                </span>

                <div class="category-card-actions">
                    <button type="button" class="card-action-btn action-edit"
                            onclick="openEditCategoryModal(
                                '{{ route('admin.categories.update', $category) }}',
                                '{{ $category->name }}',
                                '{{ $category->status }}',
                                '{{ $category->image ? asset('storage/' . $category->image) : '' }}'
                            )">
                        <i class="fas fa-pen"></i> Edit
                    </button>

                    <form action="{{ route('admin.categories.destroy', $category) }}"
                          method="POST"
                          onsubmit="return confirm('Delete this category? Products using it will become uncategorized.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="card-action-btn action-delete">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state-categories">
                <i class="fas fa-layer-group"></i>
                No categories yet.
            </div>
        @endforelse
    </div>

    <div class="empty-state-categories" id="noResultsState" style="display:none;">
        <i class="fas fa-search"></i>
        No categories match your search.
    </div>

    @if ($categories->count())
        <div class="categories-footer">
            <span id="categoriesCountText">Showing {{ $categories->count() }} of {{ $categories->count() }} categories</span>
        </div>
    @endif

</div>

{{-- ADD / EDIT MODAL (shared by both actions) --}}
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="categoryForm" method="POST" enctype="multipart/form-data" data-store-url="{{ route('admin.categories.store') }}">
                @csrf
                <div id="categoryMethodField"></div>

                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="categoryNameInput">Category Name</label>
                        <input type="text" class="form-control" id="categoryNameInput" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="categoryStatusInput">Status</label>
                        <select class="form-control" id="categoryStatusInput" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="categoryImageInput">Category Image / Icon (optional)</label>
                        <input type="file" class="form-control-file" id="categoryImageInput" name="image" accept="image/*">
                        <small class="text-muted">PNG or JPG, up to 2MB. Leave blank to keep the auto icon.</small>
                        <div id="categoryCurrentImageWrapper" style="margin-top:8px; display:none;">
                            <small class="text-muted">Current image:</small><br>
                            <img id="categoryCurrentImage" src="" style="width:56px; height:56px; object-fit:cover; border-radius:14px; margin-top:4px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/js/admin-categories.js') }}"></script>
    <script>
        (function () {
            var searchInput = document.getElementById('categorySearchInput');
            var statusFilter = document.getElementById('statusFilter');
            var clearBtn = document.getElementById('clearFiltersBtn');
            var grid = document.getElementById('categoryGrid');
            var noResults = document.getElementById('noResultsState');
            var countText = document.getElementById('categoriesCountText');
            var totalCount = grid ? grid.querySelectorAll('.category-card').length : 0;

            function applyFilters() {
                if (!grid) return;

                var search = (searchInput.value || '').toLowerCase().trim();
                var status = statusFilter.value;
                var visibleCount = 0;

                var cards = grid.querySelectorAll('.category-card');
                cards.forEach(function (card) {
                    var matchesSearch = !search || card.dataset.name.indexOf(search) !== -1;
                    var matchesStatus = !status || card.dataset.status === status;

                    var visible = matchesSearch && matchesStatus;
                    card.style.display = visible ? '' : 'none';
                    if (visible) visibleCount++;
                });

                if (noResults) {
                    noResults.style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
                }
                if (countText) {
                    countText.textContent = 'Showing ' + visibleCount + ' of ' + totalCount + ' categories';
                }
            }

            if (searchInput) searchInput.addEventListener('input', applyFilters);
            if (statusFilter) statusFilter.addEventListener('change', applyFilters);
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    statusFilter.value = '';
                    applyFilters();
                });
            }
        })();
    </script>
@endsection