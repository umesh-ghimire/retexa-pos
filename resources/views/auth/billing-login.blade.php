<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Login - RETEXA POS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="login-body">

@php
    $selectedCashier = $cashiers->firstWhere('id', (int) old('user_id'));
    $showPasswordForm = $errors->has('email');
@endphp

<div class="shell">

    <section class="brand">
        <div class="logo">
            <div class="mark"><b>R</b></div>
            <div>
                <strong>RETEXA</strong>
                <span>POS</span>
            </div>
        </div>

        <div class="brand-main">
            <div class="kicker">POINT OF SALE</div>
            <h1>Ready to <span>sell?</span></h1>
            <p>
                Sign in quickly and get straight to the checkout screen.
                RETEXA keeps your daily billing simple, fast and organized.
            </p>
        </div>

        <div class="status">
            <span class="status-dot"></span>
            POS SYSTEM READY
        </div>
    </section>

    <section class="login">
        <div class="card">

            <div class="heading">
                <div class="label">CASHIER ACCESS</div>
                <h2>Welcome back</h2>
                <p>Select your account and enter your PIN to continue.</p>
            </div>

            {{-- ============ PIN LOGIN ============ --}}
            <form method="POST" action="{{ url('/billing/login-pin') }}" id="pinLoginForm" class="login-form-mode {{ $showPasswordForm ? '' : 'is-active' }}">
                @csrf
                <input type="hidden" name="user_id" id="pinUserIdInput" value="{{ $selectedCashier->id ?? '' }}">
                <input type="hidden" name="pin" id="pinHiddenInput" value="">

                <p class="cashier-label">CASHIER</p>

                <button type="button" class="cashier-select" id="cashierSelectBtn">
                    @php
                        $initials = 'U';
                        if ($selectedCashier) {
                            $words = preg_split('/\s+/', trim($selectedCashier->name));
                            $initials = strtoupper(($words[0][0] ?? 'U') . ($words[1][0] ?? ''));
                        }
                    @endphp
                    <div class="avatar" id="selectedAvatar">{{ $initials }}</div>
                    <div class="cashier-info">
                        <strong id="selectedName">{{ $selectedCashier->name ?? 'Select your account' }}</strong>
                        <small id="selectedRole">{{ $selectedCashier ? ucfirst($selectedCashier->role) : 'Tap to choose' }}</small>
                    </div>
                    <div class="chevron">&#8964;</div>
                </button>

                <div class="cashier-dropdown" id="cashierDropdown" style="display:none;">
                    @forelse ($cashiers as $cashier)
                        @php
                            $words = preg_split('/\s+/', trim($cashier->name));
                            $cashierInitials = strtoupper(($words[0][0] ?? 'U') . ($words[1][0] ?? ''));
                        @endphp
                        <button type="button" class="cashier-option"
                                data-id="{{ $cashier->id }}"
                                data-name="{{ $cashier->name }}"
                                data-role="{{ ucfirst($cashier->role) }}"
                                data-initials="{{ $cashierInitials }}">
                            <div class="avatar avatar-sm">{{ $cashierInitials }}</div>
                            <div class="cashier-info">
                                <strong>{{ $cashier->name }}</strong>
                                <small>{{ ucfirst($cashier->role) }}</small>
                            </div>
                        </button>
                    @empty
                        <p class="cashier-dropdown-empty">No active accounts found.</p>
                    @endforelse
                </div>

                <div class="pin-title">
                    <span>ENTER PIN</span>
                    <button type="button" class="pin-toggle" id="togglePinVisibility" aria-label="Show PIN" aria-pressed="false">
                        <span id="togglePinIcon">Show</span>
                    </button>
                </div>

                <div class="pin" id="pinDisplay" aria-label="PIN">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>

                <div class="message" id="pinMessage">
                    @error('pin')
                        {{ $message }}
                    @enderror
                </div>

                <div class="keypad" id="keypad">
                    <button type="button" class="key" data-key="1">1</button>
                    <button type="button" class="key" data-key="2">2</button>
                    <button type="button" class="key" data-key="3">3</button>
                    <button type="button" class="key" data-key="4">4</button>
                    <button type="button" class="key" data-key="5">5</button>
                    <button type="button" class="key" data-key="6">6</button>
                    <button type="button" class="key" data-key="7">7</button>
                    <button type="button" class="key" data-key="8">8</button>
                    <button type="button" class="key" data-key="9">9</button>
                    <button type="button" class="key clear" data-key="clear">CLEAR</button>
                    <button type="button" class="key" data-key="0">0</button>
                    <button type="button" class="key enter" data-key="enter">ENTER</button>
                </div>

                <div class="forgot-note" id="forgotMessage" style="display:none;">
                    Ask your admin to reset your PIN from Admin &rarr; Users.
                </div>

                <div class="bottom-row">
                    <button class="forgot" type="button" id="forgotPinBtn">Forgot PIN?</button>
                    <button class="forgot" type="button" id="usePasswordBtn">Use email &amp; password</button>
                </div>
            </form>

            {{-- ============ EMAIL/PASSWORD FALLBACK ============ --}}
            <form method="POST" action="{{ url('/billing/login') }}" id="passwordLoginForm" class="login-form-mode {{ $showPasswordForm ? 'is-active' : '' }}">
                @csrf

                <div class="field">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                </div>

                <div class="field">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" placeholder="Enter your password" required>
                </div>

                <label class="remember-row">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me on this device</span>
                </label>

                <div class="message">
                    @error('email')
                        {{ $message }}
                    @enderror
                </div>

                <button type="submit" class="key enter submit-btn">Sign In</button>

                <div class="bottom-row bottom-row--center">
                    <button class="forgot" type="button" id="usePinBtn">&#8592; Use PIN instead</button>
                </div>
            </form>

            <div class="version">RETEXA POS v1.0</div>
            <div class="footer">&copy; {{ date('Y') }} RETEXA POS &bull; Secure cashier access</div>
        </div>
    </section>
</div>

<script>
(function () {
    const pinForm = document.getElementById('pinLoginForm');
    const passwordForm = document.getElementById('passwordLoginForm');
    const usePasswordBtn = document.getElementById('usePasswordBtn');
    const usePinBtn = document.getElementById('usePinBtn');

    const cashierSelectBtn = document.getElementById('cashierSelectBtn');
    const cashierDropdown = document.getElementById('cashierDropdown');
    const selectedAvatar = document.getElementById('selectedAvatar');
    const selectedName = document.getElementById('selectedName');
    const selectedRole = document.getElementById('selectedRole');
    const pinUserIdInput = document.getElementById('pinUserIdInput');
    const pinHiddenInput = document.getElementById('pinHiddenInput');

    const dots = Array.prototype.slice.call(document.querySelectorAll('.dot'));
    const keypad = document.getElementById('keypad');
    const pinMessage = document.getElementById('pinMessage');
    const forgotBtn = document.getElementById('forgotPinBtn');
    const forgotMessage = document.getElementById('forgotMessage');
    const togglePinBtn = document.getElementById('togglePinVisibility');
    const togglePinIcon = document.getElementById('togglePinIcon');

    let pin = '';
    let pinVisible = false;

    // Guards against the PIN form being submitted twice (e.g. the
    // auto-submit timer firing *and* the user pressing Enter/tapping
    // ENTER before it does). A duplicate second request would carry a
    // now-stale CSRF token once the first request's successful login
    // has already regenerated the session, which is what was causing
    // the "419 Page Expired" on an otherwise-correct PIN.
    let isSubmitting = false;
    let autoSubmitTimer = null;

    function renderDots() {
        dots.forEach(function (dot, index) {
            const filled = index < pin.length;
            dot.classList.toggle('active', filled);
            dot.classList.toggle('digit-visible', pinVisible);
            dot.textContent = (pinVisible && filled) ? pin[index] : '';
        });
    }

    if (togglePinBtn) {
        togglePinBtn.addEventListener('click', function () {
            pinVisible = !pinVisible;
            togglePinBtn.setAttribute('aria-pressed', String(pinVisible));
            togglePinBtn.setAttribute('aria-label', pinVisible ? 'Hide PIN' : 'Show PIN');
            togglePinIcon.textContent = pinVisible ? 'Hide' : 'Show';
            renderDots();
        });
    }

    function selectCashier(button) {
        pinUserIdInput.value = button.dataset.id;
        selectedAvatar.textContent = button.dataset.initials;
        selectedName.textContent = button.dataset.name;
        selectedRole.textContent = button.dataset.role;
        cashierDropdown.style.display = 'none';
        pinMessage.textContent = '';
    }

    if (cashierSelectBtn) {
        cashierSelectBtn.addEventListener('click', function () {
            cashierDropdown.style.display = (cashierDropdown.style.display === 'none') ? 'block' : 'none';
        });
    }

    document.querySelectorAll('.cashier-option').forEach(function (button) {
        button.addEventListener('click', function () { selectCashier(button); });
    });

    document.addEventListener('click', function (event) {
        if (!cashierDropdown) return;
        if (!cashierDropdown.contains(event.target) && event.target !== cashierSelectBtn && !cashierSelectBtn.contains(event.target)) {
            cashierDropdown.style.display = 'none';
        }
    });

    function submitPin() {
        if (isSubmitting) {
            return;
        }
        if (!pinUserIdInput.value) {
            pinMessage.textContent = 'Please select your cashier account first.';
            return;
        }
        if (pin.length !== 4) {
            pinMessage.textContent = 'Please enter your 4-digit PIN.';
            return;
        }

        if (autoSubmitTimer) {
            clearTimeout(autoSubmitTimer);
            autoSubmitTimer = null;
        }

        isSubmitting = true;
        pinHiddenInput.value = pin;
        pinForm.submit();
    }

    if (keypad) {
        keypad.addEventListener('click', function (event) {
            if (isSubmitting) return;

            const button = event.target.closest('[data-key]');
            if (!button) return;
            const key = button.dataset.key;

            if (key === 'clear') {
                pin = '';
                pinMessage.textContent = '';
            } else if (key === 'enter') {
                submitPin();
                return;
            } else if (pin.length < 4) {
                pin += key;
            }

            renderDots();

            if (pin.length === 4) {
                autoSubmitTimer = setTimeout(submitPin, 150);
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (!pinForm.classList.contains('is-active') || isSubmitting) return;

        if (event.key >= '0' && event.key <= '9' && pin.length < 4) {
            pin += event.key;
            renderDots();
            if (pin.length === 4) autoSubmitTimer = setTimeout(submitPin, 150);
        }
        if (event.key === 'Backspace') {
            pin = pin.slice(0, -1);
            pinMessage.textContent = '';
            renderDots();
        }
        if (event.key === 'Enter') submitPin();
    });

    if (usePasswordBtn) {
        usePasswordBtn.addEventListener('click', function () {
            pinForm.classList.remove('is-active');
            passwordForm.classList.add('is-active');
        });
    }
    if (usePinBtn) {
        usePinBtn.addEventListener('click', function () {
            passwordForm.classList.remove('is-active');
            pinForm.classList.add('is-active');
        });
    }

    if (forgotBtn) {
        forgotBtn.addEventListener('click', function () {
            forgotMessage.style.display = (forgotMessage.style.display === 'none') ? 'block' : 'none';
        });
    }
})();
</script>

</body>
</html>