@extends('admin.layouts.admin')

@section('title', 'Products')

@section('content')
<div class="row">
    <div class="col-12">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Products</h4>
                <button type="button" class="btn btn-primary" onclick="openAddProductModal()">
                    + Add Product
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>
                                        @if ($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}"
                                                 alt="{{ $product->name }}"
                                                 style="width:40px; height:40px; object-fit:cover; border-radius:4px;">
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->sku ?? '—' }}</td>
                                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                    <td>{{ $product->unit->short_code ?? '—' }}</td>
                                    <td>Rs. {{ number_format($product->price, 2) }}</td>
                                    <td>
                                        @if ($product->isLowStock())
                                            <span class="badge badge-warning">{{ rtrim(rtrim($product->stock, '0'), '.') }} (low)</span>
                                        @else
                                            <span class="badge badge-success">{{ rtrim(rtrim($product->stock, '0'), '.') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($product->status === 'active')
                                            <span class="badge badge-primary">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light"
                                                onclick='openEditProductModal(@json($product))'>
                                            Edit
                                        </button>

                                        <form action="{{ route('admin.products.destroy', $product) }}"
                                              method="POST" style="display:inline;"
                                              onsubmit="return confirm('Delete this product? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No products yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

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
                            <input type="number" step="0.001" min="0" class="form-control" id="productMinStockInput" name="min_stock_level" value="0">
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
@endsection