@extends('admin.layouts.admin')

@section('title', 'Users')

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
                <h4>Add User</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.store') }}" class="form-row">
                    @csrf
                    <div class="col-md-3 form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-2 form-group">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="cashier">Cashier</option>
                            <option value="owner">Owner</option>
                        </select>
                    </div>
                    <div class="col-md-1 form-group d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>All Users</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->role === 'owner')
                                        <span class="badge badge-primary">Owner</span>
                                    @else
                                        <span class="badge badge-secondary">Cashier</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                              onsubmit="return confirm('Delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @else
                                        <span class="text-muted" style="font-size:0.85rem;">You</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection