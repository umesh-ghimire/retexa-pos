@extends('admin.layouts.admin')

@section('title', 'Users & Staff')

@section('styles')
<style>
    .users-page{max-width:100%;}

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
    .stat-card .stat-icon.bg-total{background:#e5f0ff;color:#3b82f6;}
    .stat-card .stat-icon.bg-active{background:#e5f9ea;color:#1ca54a;}
    .stat-card .stat-icon.bg-disabled{background:#fff3e0;color:#c9790a;}
    .stat-card .stat-icon.bg-admins{background:#efe9fe;color:#8a5bf2;}
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
    .filters-card select{border-radius:8px;min-width:140px;flex:0 1 160px;}
    .filters-card .btn-filter-clear{border-radius:8px;flex:0 0 auto;}

    .users-table-card{
        background:#fff;border-radius:12px;
        box-shadow:0 0.46875rem 2.1875rem rgba(90,97,105,0.06),0 0.125rem 0.1875rem rgba(90,97,105,0.08);
        overflow:hidden;
    }
    .users-table{width:100%;border-collapse:collapse;}
    .users-table thead th{
        text-align:left;font-size:11.5px;font-weight:700;letter-spacing:.4px;
        color:#98a6ad;text-transform:uppercase;
        padding:16px 20px;border-bottom:1px solid #eceef3;background:#fafbfc;
        white-space:nowrap;
    }
    .users-table tbody td{
        padding:16px 20px;border-bottom:1px solid #f1f2f6;vertical-align:middle;
    }
    .users-table tbody tr:last-child td{border-bottom:none;}
    .users-table tbody tr:hover{background:#fafbff;}

    .user-cell{display:flex;align-items:center;gap:12px;min-width:0;}
    .user-avatar{
        flex-shrink:0;width:42px;height:42px;border-radius:50%;
        display:flex;align-items:center;justify-content:center;
        font-size:13.5px;font-weight:700;
    }
    .user-cell-text{min-width:0;}
    .user-cell-name{font-size:14px;font-weight:700;color:#191d21;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .user-cell-sub{font-size:12px;color:#98a6ad;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

    .user-email{font-size:13.5px;color:#4a5568;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:240px;}

    .role-badge, .status-badge{
        display:inline-block;font-size:11.5px;font-weight:700;padding:4px 13px;border-radius:20px;letter-spacing:.2px;white-space:nowrap;
    }
    .role-badge.role-administrator{background:#191d21;color:#fff;}
    .role-badge.role-owner{background:#6777ef;color:#fff;}
    .role-badge.role-cashier{background:#eef0fb;color:#6777ef;}

    .status-badge.status-active{background:#e5f9ea;color:#1ca54a;}
    .status-badge.status-active i{color:#1ca54a;}
    .status-badge.status-disabled{background:#feeceb;color:#e1362c;}
    .status-badge.status-disabled i{color:#e1362c;}
    .status-badge i{font-size:6px;margin-right:6px;vertical-align:middle;}

    .row-actions{display:flex;align-items:center;gap:8px;}
    .icon-action-btn{
        width:34px;height:34px;border-radius:8px;border:1px solid #eceef3;background:#fff;
        display:inline-flex;align-items:center;justify-content:center;
        cursor:pointer;transition:background-color .15s ease, color .15s ease, border-color .15s ease;
        color:#4a5568;font-size:13px;padding:0;
    }
    .icon-action-btn.action-edit{color:#6777ef;border-color:#c9cffa;}
    .icon-action-btn.action-edit:hover{background:#6777ef;color:#fff;border-color:#6777ef;}
    .icon-action-btn.action-enable{color:#1ca54a;border-color:#bfead0;}
    .icon-action-btn.action-enable:hover{background:#1ca54a;color:#fff;border-color:#1ca54a;}
    .icon-action-btn.action-disable{color:#c9790a;border-color:#f6dcae;}
    .icon-action-btn.action-disable:hover{background:#c9790a;color:#fff;border-color:#c9790a;}
    .icon-action-btn.action-delete{color:#fc544b;border-color:#fbc7c4;}
    .icon-action-btn.action-delete:hover{background:#fc544b;color:#fff;border-color:#fc544b;}
    .icon-action-btn:disabled{opacity:.4;cursor:not-allowed;}
    .icon-action-btn:focus{outline:none;box-shadow:0 0 0 2px rgba(103,119,239,0.25);}

    .self-pill{
        display:inline-block;background:#e5f0ff;color:#3b82f6;font-size:11.5px;font-weight:700;
        padding:5px 14px;border-radius:20px;
    }

    .empty-state-users{text-align:center;padding:60px 20px;color:#98a6ad;}
    .empty-state-users i{font-size:40px;margin-bottom:14px;display:block;color:#c7cbe0;}

    .users-footer{
        display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;
        color:#98a6ad;font-size:13px;padding:16px 20px;
    }

    /* Card layout for narrow / mobile screens */
    @media (max-width: 767.98px){
        .users-table thead{display:none;}
        .users-table, .users-table tbody, .users-table tbody tr, .users-table tbody td{display:block;width:100%;}
        .users-table tbody tr{padding:16px 18px;border-bottom:1px solid #f1f2f6;}
        .users-table tbody td{padding:6px 0;border-bottom:none;}
        .user-email::before{content:"Email: ";color:#98a6ad;font-weight:600;}
        .row-actions{margin-top:10px;}
        .filters-card select{flex:1 1 47%;}
    }
</style>
@endsection

@section('content')
<div class="users-page">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin-bottom:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:12px; margin-bottom:6px;">
        <div>
            <nav aria-label="breadcrumb" style="margin-bottom:2px;">
                <a href="{{ url('/admin/dashboard') }}" style="color:#6777ef; font-weight:600; font-size:13px;">Dashboard</a>
                <span style="color:#98a6ad; font-size:13px;"> &gt; </span>
                <span style="color:#191d21; font-weight:600; font-size:13px;">Users &amp; Staff</span>
            </nav>
            <h4 style="margin-bottom:2px; margin-top:6px;">Users &amp; Staff</h4>
            <p class="text-muted" style="margin-bottom:0; font-size:13px;">Manage administrator and cashier accounts. Add, edit or disable users.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
            <i class="fas fa-user-plus" style="font-size:12px; margin-right:4px;"></i> Add User
        </button>
    </div>

    @php
        $totalUsers = $users->count();
        $activeUsers = $users->where('status', 'active')->count();
        $disabledUsers = $users->where('status', 'disabled')->count();
        $administrators = $users->where('role', 'owner')->count();

        $avatarPalette = [
            ['bg' => '#e5f0ff', 'color' => '#3b82f6'],
            ['bg' => '#efe9fe', 'color' => '#8a5bf2'],
            ['bg' => '#fff3d9', 'color' => '#d8a326'],
            ['bg' => '#e5f9ea', 'color' => '#1ca54a'],
            ['bg' => '#ffe6f0', 'color' => '#e0447b'],
        ];
    @endphp

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon bg-total"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $totalUsers }}</div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-active"><i class="fas fa-user-check"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $activeUsers }}</div>
                <div class="stat-label">Active Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-disabled"><i class="fas fa-user-slash"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $disabledUsers }}</div>
                <div class="stat-label">Disabled Users</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-admins"><i class="fas fa-shield-alt"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $administrators }}</div>
                <div class="stat-label">Administrators</div>
            </div>
        </div>
    </div>

    <div class="filters-card">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="userSearchInput" class="form-control" placeholder="Search users by name or email...">
        </div>
        <select id="roleFilter" class="form-control">
            <option value="">All Roles</option>
            <option value="owner">Owner</option>
            <option value="cashier">Cashier</option>
        </select>
        <select id="statusFilter" class="form-control">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="disabled">Disabled</option>
        </select>
        <button type="button" class="btn btn-light btn-filter-clear" id="clearUserFiltersBtn">
            <i class="fas fa-filter" style="margin-right:5px;"></i>Clear
        </button>
    </div>

    <div class="users-table-card">
        @if ($users->count())
            <div style="overflow-x:auto;">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userRowsBody">
                        @foreach ($users as $index => $user)
                            @php
                                $isSelf = $user->id === auth()->id();
                                $displayRole = $isSelf ? 'Administrator' : ucfirst($user->role);
                                $roleBadgeClass = $isSelf ? 'role-administrator' : 'role-' . $user->role;
                                $palette = $avatarPalette[$index % count($avatarPalette)];
                                $words = preg_split('/\s+/', trim($user->name));
                                $initials = strtoupper(($words[0][0] ?? 'U') . ($words[1][0] ?? ''));
                            @endphp
                            <tr class="user-row"
                                data-name="{{ strtolower($user->name) }}"
                                data-email="{{ strtolower($user->email) }}"
                                data-role="{{ $user->role }}"
                                data-status="{{ $user->status }}">
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar" style="background:{{ $palette['bg'] }}; color:{{ $palette['color'] }};">
                                            {{ $initials }}
                                        </div>
                                        <div class="user-cell-text">
                                            <div class="user-cell-name">{{ $user->name }}</div>
                                            <div class="user-cell-sub">{{ $displayRole }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="user-email">{{ $user->email }}</span></td>
                                <td>
                                    <span class="role-badge {{ $roleBadgeClass }}">{{ $displayRole }}</span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $user->status }}">
                                        <i class="fas fa-circle"></i>{{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($isSelf)
                                        <span class="self-pill">You</span>
                                    @else
                                        <div class="row-actions">
                                            <button type="button" class="icon-action-btn action-edit" title="Edit user"
                                                    onclick="openEditUserModal(
                                                        '{{ route('admin.users.update', $user) }}',
                                                        '{{ $user->name }}',
                                                        '{{ $user->email }}',
                                                        '{{ $user->role }}'
                                                    )">
                                                <i class="fas fa-pen"></i>
                                            </button>

                                            <form action="{{ route('admin.users.updateStatus', $user) }}" method="POST"
                                                  onsubmit="return confirm('{{ $user->status === 'active' ? 'Disable' : 'Enable' }} {{ $user->name }}\'s account?');">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $user->status === 'active' ? 'disabled' : 'active' }}">
                                                <button type="submit"
                                                        class="icon-action-btn {{ $user->status === 'active' ? 'action-disable' : 'action-enable' }}"
                                                        title="{{ $user->status === 'active' ? 'Disable user' : 'Enable user' }}">
                                                    <i class="fas {{ $user->status === 'active' ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                  onsubmit="return confirm('Delete {{ $user->name }}\'s account? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="icon-action-btn action-delete" title="Delete user">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="empty-state-users" id="noUserResultsState" style="display:none;">
                <i class="fas fa-search"></i>
                No users match your search.
            </div>

            <div class="users-footer">
                <span id="usersCountText">Showing {{ $users->count() }} of {{ $users->count() }} users</span>
            </div>
        @else
            <div class="empty-state-users">
                <i class="fas fa-users"></i>
                No users yet. Add your first team member to get started.
            </div>
        @endif
    </div>
</div>

{{-- ADD / EDIT USER MODAL --}}
<div class="modal fade" id="userModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="userForm" method="POST" data-store-url="{{ route('admin.users.store') }}">
                @csrf
                <div id="userMethodField"></div>

                <div class="modal-header">
                    <h5 class="modal-title" id="userModalTitle">Add User</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label for="userNameInput">Full Name</label>
                        <input type="text" class="form-control" id="userNameInput" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="userEmailInput">Email</label>
                        <input type="email" class="form-control" id="userEmailInput" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="userRoleInput">Role</label>
                        <select class="form-control" id="userRoleInput" name="role">
                            <option value="owner">Owner</option>
                            <option value="cashier">Cashier</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="userPasswordInput" id="userPasswordLabel">Password</label>
                        <input type="password" class="form-control" id="userPasswordInput" name="password" minlength="6" required>
                        <small class="text-muted" id="userPasswordHint" style="display:none;">Leave blank to keep the current password.</small>
                    </div>
                    <div class="form-group">
                        <label for="userPinInput">Billing PIN (optional)</label>
                        <input type="text" class="form-control" id="userPinInput" name="pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" placeholder="4 digits">
                        <small class="text-muted">Used for quick PIN login on the billing screen. Leave blank to keep the current PIN.</small>
                    </div>
                    <div class="form-group">
                        <label for="userPinInput">Billing PIN (optional)</label>
                        <input type="text" class="form-control" id="userPinInput" name="pin" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" placeholder="4 digits">
                        <small class="text-muted">Used for quick PIN login on the billing screen. Leave blank to keep the current PIN.</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/js/admin-users.js') }}"></script>
@endsection