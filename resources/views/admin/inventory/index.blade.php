@extends('admin.layouts.admin')

@section('title', 'Inventory')

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
            <div class="card-header">
                <h4>Stock Levels</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Unit</th>
                                <th>Min Level</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                    <td>
                                        @if ($product->isLowStock())
                                            <span class="badge badge-warning">{{ rtrim(rtrim($product->stock, '0'), '.') }} (low)</span>
                                        @else
                                            <span class="badge badge-success">{{ rtrim(rtrim($product->stock, '0'), '.') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->unit->short_code ?? '—' }}</td>
                                    <td>{{ rtrim(rtrim($product->min_stock_level, '0'), '.') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary"
                                                onclick="openAdjustModal({{ $product->id }}, '{{ $product->name }}', '{{ $product->unit->short_code ?? '' }}')">
                                            Adjust Stock
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No products yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Recent Stock Movements</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Before → After</th>
                                <th>Note</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentMovements as $movement)
                                <tr>
                                    <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ $movement->product->name ?? '—' }}</td>
                                    <td>
                                        @if ($movement->type === 'in')
                                            <span class="badge badge-success">Stock In</span>
                                        @elseif ($movement->type === 'out')
                                            <span class="badge badge-danger">Stock Out</span>
                                        @else
                                            <span class="badge badge-info">Correction</span>
                                        @endif
                                    </td>
                                    <td>{{ rtrim(rtrim($movement->quantity, '0'), '.') }}</td>
                                    <td>{{ rtrim(rtrim($movement->stock_before, '0'), '.') }} → {{ rtrim(rtrim($movement->stock_after, '0'), '.') }}</td>
                                    <td>{{ $movement->note ?? '—' }}</td>
                                    <td>{{ $movement->createdBy->name ?? 'System' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No stock movements yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ADJUST STOCK MODAL --}}
<div class="modal fade" id="adjustStockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="adjustStockForm" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Adjust Stock — <span id="adjustProductName"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
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