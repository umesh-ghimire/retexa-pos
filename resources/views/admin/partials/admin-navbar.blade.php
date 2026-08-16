<style>
    .admin-account-chip{
        display:flex;align-items:center;gap:10px;
        padding:6px 14px 6px 8px;
        border:1px solid #eceef3;border-radius:10px;
        background:#fff;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    .admin-account-chip:hover,
    .admin-account-chip:focus{
        border-color:#c9cffa;
        box-shadow:0 0.25rem 0.75rem rgba(90,97,105,0.08);
        text-decoration:none;
    }
    .admin-account-chip .chip-icon{
        flex-shrink:0;
        width:34px;height:34px;border-radius:8px;
        background:#eef0fb;color:#6777ef;
        display:flex;align-items:center;justify-content:center;
        font-size:15px;
    }
    .admin-account-chip .chip-text{
        display:flex;flex-direction:column;line-height:1.2;
        min-width:0;
    }
    .admin-account-chip .chip-title{
        font-size:13.5px;font-weight:700;color:#191d21;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;
    }
    .admin-account-chip .chip-subtitle{
        font-size:11.5px;color:#98a6ad;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:150px;
    }
    .admin-account-chip .chip-status-dot{
        flex-shrink:0;width:8px;height:8px;border-radius:50%;background:#1ca54a;
        box-shadow:0 0 0 2px #e5f9ea;
    }
    .admin-account-chip .chip-caret{
        flex-shrink:0;color:#98a6ad;font-size:11px;
    }
    @media (max-width: 575.98px){
        .admin-account-chip .chip-text{display:none;}
        .admin-account-chip{padding:6px;gap:6px;}
    }
</style>

<nav class="navbar navbar-expand-lg main-navbar sticky">
    <div class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li>
                <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                    <i data-feather="align-justify"></i>
                </a>
            </li>
        </ul>
    </div>
    <ul class="navbar-nav navbar-right">
        <li class="dropdown">
            @php
                $navUser = auth()->user();
                $navRole = $navUser->role ?? null;
                $navTitle = $navRole === 'owner' ? 'Administrator' : ($navRole ? ucfirst($navRole) : 'Admin');
                $navSubtitle = $navUser->name ?? 'System Admin';
            @endphp
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user admin-account-chip">
                <span class="chip-icon"><i class="fas fa-shield-alt"></i></span>
                <span class="chip-text">
                    <span class="chip-title">{{ $navTitle }}</span>
                    <span class="chip-subtitle">{{ $navSubtitle }}</span>
                </span>
                <span class="chip-status-dot" title="Online"></span>
                <i class="fas fa-chevron-down chip-caret"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-title">{{ $navSubtitle }}</div>
                <a href="{{ url('/admin/settings') }}" class="dropdown-item has-icon"><i class="fas fa-cog"></i> Settings</a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ url('/admin/logout') }}">
                   @csrf
                   <button type="submit" class="dropdown-item has-icon text-danger" style="background:none; border:none; width:100%; text-align:left;">
                       <i class="fas fa-sign-out-alt"></i> Logout
                   </button>
                </form>
            </div>
        </li>
    </ul>
</nav>