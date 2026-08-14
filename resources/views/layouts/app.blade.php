<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart Retail POS')</title>

    {{-- Base styles shared across all pages --}}
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">

    {{-- Page-specific styles get added here by child pages --}}
    @yield('styles')
</head>
<body>

    <nav class="app-nav">
    <div class="app-nav__left">
        <div class="app-nav__brand">Smart Retail POS</div>
        <div class="app-nav__links">
            <a href="{{ url('/billing') }}" class="{{ request()->is('billing') ? 'active' : '' }}">Billing</a>
            @auth
                @if (auth()->user()->isOwner())
                    <a href="{{ url('/admin/dashboard') }}">Admin Panel</a>
                @endif
            @endauth
        </div>
    </div>
    <div class="app-nav__user">
        @auth
            <span class="app-nav__username">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ url('/billing/logout') }}">
                @csrf
                <button type="submit" class="app-nav__logout-btn">Logout</button>
            </form>
        @endauth
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