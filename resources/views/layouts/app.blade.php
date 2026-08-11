<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Retail POS')</title>

    {{-- Base styles shared across all pages --}}
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">

    {{-- Page-specific styles get added here by child pages --}}
    @yield('styles')
</head>
<body>

    <nav class="app-nav">
        <div class="app-nav__brand">Smart Retail POS</div>
        <div class="app-nav__links">
            <a href="{{  url('/dashboard') }}" class="{{  request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ url('/billing') }}" class="{{ request ()->is('billing') ? 'active' : '' }}">Billing</a>
            <a href="{{ url('/inventory') }}" class="{{ request ()->is('inventory') ? 'active' : '' }}">Inventory</a>
            <a href="{{ url('/bill-history') }}" class="{{ request ()->is('bill-history') ? 'active' : '' }}">Bill History</a>
            <a href="{{ url('/settings') }}" class="{{ request ()->is('settings') ? 'active' : '' }}">Settings</a>
        </div>
    </nav>

    <main class="app-main">
        @yield('content')
    </main>

    {{-- Base scripts --}}
    <script src="{{ asset('js/utils.js') }}"></script>

    {{-- Page-specific scripts get added here by child pages --}}
    @yield('scripts')
</body>
</html>