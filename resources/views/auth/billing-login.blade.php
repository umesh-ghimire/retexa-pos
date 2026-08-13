<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Login - Smart Retail POS</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
</head>
<body>
    <main class="app-main" style="display:flex; align-items:center; justify-content:center; min-height:90vh;">
        <div class="card" style="width:100%; max-width:360px; padding:32px;">
            <h2 style="text-align:center; margin-bottom:4px;">Cashier Login</h2>
            <p style="text-align:center; color:var(--color-text-muted); font-size:0.9rem; margin-bottom:24px;">
                Sign in to start billing
            </p>

            @if ($errors->any())
                <div style="background:#fde2e2; color:var(--color-danger); padding:10px 14px; border-radius:var(--radius); margin-bottom:16px; font-size:0.9rem;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/billing/login') }}">
                @csrf

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:4px;">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="display:block; font-size:0.85rem; font-weight:600; margin-bottom:4px;">Password</label>
                    <input type="password" name="password" required>
                </div>

                <button type="submit" class="btn-show-bill" style="width:100%;">Login</button>
            </form>
        </div>
    </main>
</body>
</html>