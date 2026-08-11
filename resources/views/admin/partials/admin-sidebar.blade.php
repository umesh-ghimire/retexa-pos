<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">
        <div class="sidebar-brand">
            <a href="{{ url('/admin/dashboard') }}">
                <img alt="logo" src="{{ asset('admin-assets/img/logo.png') }}" class="header-logo">
                <span class="logo-name">RETEXA</span>
            </a>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-header">Main</li>

            <li class="dropdown {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                <a href="{{ url('/admin/dashboard') }}" class="nav-link">
                    <i data-feather="monitor"></i><span>Dashboard</span>
                </a>
            </li>

            <li class="menu-header">Catalog</li>

            <li class="dropdown {{ request()->is('admin/products*') ? 'active' : '' }}">
                <a href="{{ url('/admin/products') }}" class="nav-link">
                    <i data-feather="box"></i><span>Products</span>
                </a>
            </li>

            <li class="dropdown {{ request()->is('admin/categories*') ? 'active' : '' }}">
                <a href="{{ url('/admin/categories') }}" class="nav-link">
                    <i data-feather="tag"></i><span>Categories</span>
                </a>
            </li>

            <li class="dropdown {{ request()->is('admin/inventory*') ? 'active' : '' }}">
                <a href="{{ url('/admin/inventory') }}" class="nav-link">
                    <i data-feather="package"></i><span>Inventory</span>
                </a>
            </li>

            <li class="menu-header">Sales</li>

            <li class="dropdown {{ request()->is('admin/bills*') ? 'active' : '' }}">
                <a href="{{ url('/admin/bills') }}" class="nav-link">
                    <i data-feather="file-text"></i><span>Bills</span>
                </a>
            </li>

            <li class="dropdown {{ request()->is('admin/customers*') ? 'active' : '' }}">
                <a href="{{ url('/admin/customers') }}" class="nav-link">
                    <i data-feather="users"></i><span>Customers</span>
                </a>
            </li>

            <li class="dropdown {{ request()->is('admin/reports*') ? 'active' : '' }}">
                <a href="{{ url('/admin/reports') }}" class="nav-link">
                    <i data-feather="bar-chart-2"></i><span>Reports</span>
                </a>
            </li>

            <li class="menu-header">System</li>

            <li class="dropdown {{ request()->is('admin/users*') ? 'active' : '' }}">
                <a href="{{ url('/admin/users') }}" class="nav-link">
                    <i data-feather="user-check"></i><span>Users</span>
                </a>
            </li>

            <li class="dropdown {{ request()->is('admin/settings*') ? 'active' : '' }}">
                <a href="{{ url('/admin/settings') }}" class="nav-link">
                    <i data-feather="settings"></i><span>Settings</span>
                </a>
            </li>
        </ul>
    </aside>
</div>