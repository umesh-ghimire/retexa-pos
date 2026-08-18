<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>@yield('title', 'Admin') - Smart Retail POS</title>

    <link rel="stylesheet" href="{{ asset('admin-assets/css/app.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('admin-assets/css/custom.css') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('admin-assets/img/favicon.ico') }}">

    @yield('styles')
</head>
<body>
    <div class="loader"></div>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <div class="navbar-bg"></div>

            @include('admin.partials.admin-navbar')
            @include('admin.partials.admin-sidebar')

            <div class="main-content">
                <section class="section">
                    @yield('content')
                </section>
            </div>

            @include('admin.partials.admin-footer')
        </div>
    </div>

    <script src="{{ asset('admin-assets/js/app.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/vendor/JsBarcode.all.min.js') }}"></script>
    <script src="{{ asset('admin-assets/js/scripts.js') }}"></script>
    <script src="{{ asset('admin-assets/js/custom.js') }}"></script>

@yield('scripts')
</body>
</html>