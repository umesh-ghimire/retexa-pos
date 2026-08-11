@extends('admin.layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="row">
    <div class="col-12">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Categories</h4>
                <button type="button" class="btn btn-primary" onclick="openAddCategoryModal()">
                    + Add Category
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->products_count }}</td>
                                    <td>
                                        @if ($category->status === 'active')
                                            <span class="badge badge-primary">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light"
                                                onclick="openEditCategoryModal(
                                                    '{{ route('admin.categories.update', $category) }}',
                                                    '{{ $category->name }}',
                                                    '{{ $category->status }}'
                                                )">
                                            Edit
                                        </button>

                                        <form action="{{ route('admin.categories.destroy', $category) }}"
                                              method="POST" style="display:inline;"
                                              onsubmit="return confirm('Delete this category? Products using it will become uncategorized.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No categories yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ADD / EDIT MODAL (shared by both actions) --}}
<div class="modal fade" id="categoryModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="categoryForm" method="POST" data-store-url="{{ route('admin.categories.store') }}">
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
@endsection