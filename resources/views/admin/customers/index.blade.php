@extends('admin.layouts.admin')

@section('title', 'Customers')

@section('content')
<div class="row">
    <div class="col-12">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h4>Customers</h4>
            </div>
            <div class="card-body">

                <form method="GET" class="form-row mb-3">
                    <div class="col-md-4 form-group">
                        <label>Search (name or phone)</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">Search</button>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-light">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Total Bills</th>
                                <th>Total Spent</th>
                                <th>Customer Since</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $customer)
                                <tr>
                                    <td>{{ $customer->name ?? '—' }}</td>
                                    <td>{{ $customer->phone ?? '—' }}</td>
                                    <td>{{ $customer->sales_count }}</td>
                                    <td>Rs. {{ number_format($customer->sales_sum_total ?? 0) }}</td>
                                    <td>{{ $customer->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light"
                                                onclick="openEditCustomerModal(
                                                    '{{ route('admin.customers.update', $customer) }}',
                                                    '{{ addslashes($customer->name) }}',
                                                    '{{ addslashes($customer->phone) }}'
                                                )">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.customers.destroy', $customer) }}"
                                              method="POST" style="display:inline;"
                                              onsubmit="return confirm('Delete this customer? Their past bills will remain, shown as Walk-in.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No customers yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $customers->links('pagination::bootstrap-4') }}

            </div>
        </div>

    </div>
</div>

{{-- EDIT CUSTOMER MODAL --}}
<div class="modal fade" id="customerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="customerForm" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Customer</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" id="customerNameInput" name="name">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" id="customerPhoneInput" name="phone">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/js/admin-customers.js') }}"></script>
@endsection