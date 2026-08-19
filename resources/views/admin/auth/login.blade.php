<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | RETEXA POS</title>

    <meta name="theme-color" content="#0b74ff">

    <style>
/* =========================================================
   RETEXA POS — Admin Login
   No Tailwind / no Bootstrap required
   ========================================================= */

:root {
    --retexa-blue: #087cff;
    --retexa-blue-dark: #0759c9;
    --retexa-cyan: #16bfff;
    --navy: #071a35;
    --text: #13233b;
    --muted: #718096;
    --border: #dce5ef;
    --surface: #ffffff;
    --background: #f4f8fc;
    --danger: #dc3545;
    --success: #159a67;
    --radius: 14px;
}

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    min-height: 100%;
}

body {
    min-height: 100vh;
    color: var(--text);
    background: var(--background);
    font-family:
        Inter,
        ui-sans-serif,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        Roboto,
        Arial,
        sans-serif;
    -webkit-font-smoothing: antialiased;
}

button,
input {
    font: inherit;
}

button {
    cursor: pointer;
}

.login-page {
    min-height: 100vh;
    display: grid;
    grid-template-columns: minmax(420px, 47%) 1fr;
}


/* =========================================================
   BRAND PANEL
   ========================================================= */

.brand-panel {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    isolation: isolate;
    display: flex;
    align-items: center;
    background:
        radial-gradient(circle at 85% 15%, rgba(22, 191, 255, .24), transparent 30%),
        radial-gradient(circle at 20% 85%, rgba(8, 124, 255, .28), transparent 34%),
        linear-gradient(145deg, #06172e 0%, #082b59 52%, #076bdc 100%);
}

.brand-content {
    position: relative;
    z-index: 3;
    width: min(560px, 82%);
    margin: 0 auto;
    padding: 70px 0;
}

.brand-logo {
    display: inline-flex;
    align-items: center;
    gap: 13px;
    color: #fff;
    text-decoration: none;
}

.brand-mark {
    position: relative;
    width: 55px;
    height: 55px;
    display: grid;
    place-items: center;
    border-radius: 16px;
    background: linear-gradient(145deg, #159cff, #0759d8);
    box-shadow:
        0 12px 35px rgba(0, 116, 255, .35),
        inset 0 1px 0 rgba(255,255,255,.35);
    overflow: hidden;
}

.brand-r {
    position: relative;
    z-index: 2;
    font-size: 32px;
    font-weight: 900;
    font-style: italic;
    line-height: 1;
}

.brand-orbit {
    position: absolute;
    width: 78px;
    height: 23px;
    border: 2px solid rgba(255,255,255,.8);
    border-left-color: transparent;
    border-right-color: transparent;
    border-radius: 50%;
    transform: rotate(-25deg);
    animation: logoOrbit 4s linear infinite;
}

.brand-name {
    display: flex;
    align-items: baseline;
    gap: 8px;
}

.brand-name strong {
    font-size: 27px;
    font-weight: 850;
    letter-spacing: 1.8px;
}

.brand-name span {
    padding: 4px 9px;
    border-radius: 7px;
    background: rgba(255,255,255,.15);
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 2px;
}

.brand-copy {
    margin-top: 105px;
}

.eyebrow,
.welcome-label {
    display: block;
    color: #62c9ff;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2.6px;
}

.brand-copy h1 {
    max-width: 500px;
    margin: 17px 0 20px;
    color: #fff;
    font-size: clamp(42px, 4.3vw, 65px);
    font-weight: 800;
    line-height: .98;
    letter-spacing: -2.5px;
}

.brand-copy h1 span {
    display: block;
    background: linear-gradient(90deg, #fff, #72d7ff);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.brand-copy p {
    max-width: 510px;
    margin: 0;
    color: rgba(235, 247, 255, .72);
    font-size: 16px;
    line-height: 1.75;
}

.feature-list {
    display: grid;
    gap: 18px;
    margin-top: 45px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 13px;
}

.feature-icon {
    width: 30px;
    height: 30px;
    flex: 0 0 30px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 9px;
    background: rgba(255,255,255,.09);
    color: #69d2ff;
    font-weight: 900;
}

.feature-item strong {
    display: block;
    color: #fff;
    font-size: 14px;
}

.feature-item small {
    display: block;
    margin-top: 3px;
    color: rgba(235,247,255,.52);
    font-size: 12px;
}

.brand-footer {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 62px;
    color: rgba(255,255,255,.48);
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 3px;
}

.brand-footer i {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: #28bfff;
}

.brand-decoration {
    position: absolute;
    z-index: -1;
    border-radius: 50%;
    pointer-events: none;
}

.decoration-one {
    width: 550px;
    height: 550px;
    right: -320px;
    bottom: -270px;
    border: 1px solid rgba(255,255,255,.12);
    box-shadow:
        0 0 0 55px rgba(255,255,255,.025),
        0 0 0 110px rgba(255,255,255,.018);
}

.decoration-two {
    width: 240px;
    height: 240px;
    right: -120px;
    top: 100px;
    border: 1px solid rgba(100,215,255,.20);
    animation: slowRotate 16s linear infinite;
}

.brand-grid {
    position: absolute;
    inset: 0;
    z-index: -2;
    opacity: .08;
    background-image:
        linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px);
    background-size: 38px 38px;
    mask-image: linear-gradient(to right, transparent, #000 35%, #000);
}


/* =========================================================
   LOGIN PANEL
   ========================================================= */

.login-panel {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 50px;
    background:
        radial-gradient(circle at 90% 10%, rgba(8,124,255,.05), transparent 25%),
        #f7faff;
}

.login-card {
    width: min(440px, 100%);
    animation: cardIn .65s cubic-bezier(.2,.8,.2,1) both;
}

.mobile-logo {
    display: none;
}

.login-heading {
    margin-bottom: 31px;
}

.login-heading h2 {
    margin: 10px 0 9px;
    color: var(--navy);
    font-size: 34px;
    font-weight: 800;
    letter-spacing: -1.1px;
}

.login-heading p {
    margin: 0;
    color: var(--muted);
    font-size: 14px;
    line-height: 1.6;
}

.login-alert {
    display: flex;
    gap: 11px;
    margin-bottom: 20px;
    padding: 13px 14px;
    border-radius: 11px;
    font-size: 13px;
}

.login-alert .alert-icon {
    width: 24px;
    height: 24px;
    flex: 0 0 24px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    font-size: 12px;
    font-weight: 900;
}

.login-alert strong,
.login-alert p {
    display: block;
}

.login-alert strong {
    font-size: 12px;
}

.login-alert p {
    margin: 3px 0 0;
    line-height: 1.4;
}

.error-alert {
    border: 1px solid #f3c5cb;
    background: #fff5f6;
    color: #9d2533;
}

.error-alert .alert-icon {
    background: #ffe0e4;
}

.success-alert {
    border: 1px solid #bce8d5;
    background: #f1fcf7;
    color: #14734f;
}

.success-alert .alert-icon {
    background: #d8f5e7;
}

.login-form {
    display: grid;
    gap: 22px;
}

.field-group {
    display: grid;
    gap: 8px;
}

.field-label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.field-group label,
.field-label-row label {
    color: #263952;
    font-size: 13px;
    font-weight: 700;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    width: 19px;
    height: 19px;
    transform: translateY(-50%);
    color: #8a9bb0;
    pointer-events: none;
}

.input-icon svg,
.password-toggle svg {
    width: 100%;
    height: 100%;
    stroke: currentColor;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.input-wrapper input {
    width: 100%;
    height: 54px;
    padding: 0 48px;
    border: 1px solid var(--border);
    border-radius: 11px;
    outline: none;
    background: #fff;
    color: var(--text);
    font-size: 14px;
    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        background .2s ease;
}

.input-wrapper input::placeholder {
    color: #a9b5c3;
}

.input-wrapper input:hover {
    border-color: #bdcad8;
}

.input-wrapper input:focus {
    border-color: var(--retexa-blue);
    box-shadow: 0 0 0 4px rgba(8,124,255,.10);
}

.input-wrapper:focus-within .input-icon {
    color: var(--retexa-blue);
}

.forgot-link {
    color: var(--retexa-blue-dark);
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
}

.forgot-link:hover {
    text-decoration: underline;
}

.password-toggle {
    position: absolute;
    top: 50%;
    right: 14px;
    width: 24px;
    height: 24px;
    padding: 3px;
    transform: translateY(-50%);
    border: 0;
    background: transparent;
    color: #8191a5;
}

.password-toggle:hover {
    color: var(--retexa-blue);
}

.password-toggle .eye-closed {
    display: none;
}

.password-toggle.is-visible .eye-open {
    display: none;
}

.password-toggle.is-visible .eye-closed {
    display: block;
}

.remember-row {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    width: fit-content;
    color: #607086;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.check-wrap {
    position: relative;
    width: 18px;
    height: 18px;
}

.check-wrap input {
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
}

.custom-check {
    position: absolute;
    inset: 0;
    border: 1px solid #c9d5e1;
    border-radius: 5px;
    background: #fff;
    transition: .18s ease;
}

.check-wrap input:checked + .custom-check {
    border-color: var(--retexa-blue);
    background: var(--retexa-blue);
}

.check-wrap input:checked + .custom-check::after {
    content: "";
    position: absolute;
    width: 4px;
    height: 8px;
    left: 6px;
    top: 3px;
    border: solid #fff;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.login-button {
    position: relative;
    width: 100%;
    height: 55px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 11px;
    overflow: hidden;
    margin-top: 2px;
    border: 0;
    border-radius: 11px;
    background: linear-gradient(100deg, #086ee9, #0a8dff);
    color: #fff;
    box-shadow: 0 10px 25px rgba(8,124,255,.20);
    font-size: 14px;
    font-weight: 800;
    transition:
        transform .18s ease,
        box-shadow .18s ease,
        filter .18s ease;
}

.login-button::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        110deg,
        transparent 20%,
        rgba(255,255,255,.18) 50%,
        transparent 80%
    );
    transform: translateX(-100%);
    transition: transform .6s ease;
}

.login-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 13px 28px rgba(8,124,255,.27);
}

.login-button:hover::before {
    transform: translateX(100%);
}

.login-button:active {
    transform: translateY(0);
}

.login-button:disabled {
    cursor: not-allowed;
    filter: saturate(.75);
    opacity: .85;
}

.button-arrow {
    position: relative;
    font-size: 19px;
    line-height: 1;
    transition: transform .2s ease;
}

.login-button:hover .button-arrow {
    transform: translateX(3px);
}

.button-loader {
    display: none;
    width: 17px;
    height: 17px;
    border: 2px solid rgba(255,255,255,.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin .7s linear infinite;
}

.login-button.loading .button-loader {
    display: block;
}

.login-button.loading .button-arrow {
    display: none;
}

.login-button.loading .button-text {
    opacity: .9;
}

.login-divider {
    position: relative;
    margin: 34px 0 19px;
    text-align: center;
}

.login-divider::before {
    content: "";
    position: absolute;
    top: 50%;
    right: 0;
    left: 0;
    height: 1px;
    background: #e3eaf2;
}

.login-divider span {
    position: relative;
    z-index: 1;
    padding: 0 12px;
    background: #f7faff;
    color: #9aa8b8;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.8px;
}

.security-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    margin: 0;
    color: #8290a0;
    font-size: 11px;
}

.shield-icon {
    width: 17px;
    height: 17px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: #e8f7f1;
    color: #159a67;
    font-size: 9px;
    font-weight: 900;
}

.copyright {
    margin: 45px 0 0;
    color: #a2adba;
    text-align: center;
    font-size: 10px;
}


/* =========================================================
   ANIMATIONS
   ========================================================= */

@keyframes cardIn {
    from {
        opacity: 0;
        transform: translateY(18px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes logoOrbit {
    to {
        transform: rotate(335deg);
    }
}

@keyframes slowRotate {
    to {
        transform: rotate(360deg);
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1050px) {
    .login-page {
        grid-template-columns: 42% 58%;
    }

    .brand-content {
        width: 78%;
    }

    .brand-copy {
        margin-top: 75px;
    }

    .brand-copy h1 {
        font-size: 45px;
    }

    .brand-copy p {
        font-size: 14px;
    }
}

@media (max-width: 800px) {
    .login-page {
        display: block;
    }

    .brand-panel {
        display: none;
    }

    .login-panel {
        min-height: 100vh;
        padding: 35px 22px;
    }

    .login-card {
        width: min(430px, 100%);
    }

    .mobile-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 55px;
    }

    .mobile-mark {
        width: 43px;
        height: 43px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: linear-gradient(145deg, #159cff, #0759d8);
        color: #fff;
        font-size: 25px;
        font-weight: 900;
        font-style: italic;
        box-shadow: 0 8px 20px rgba(8,124,255,.22);
    }

    .mobile-logo strong {
        color: #092343;
        font-size: 22px;
        letter-spacing: 1.4px;
    }

    .mobile-logo span {
        margin-left: 5px;
        padding: 3px 6px;
        border-radius: 5px;
        background: #e8f2ff;
        color: #086ee9;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 1px;
    }
}

@media (max-width: 430px) {
    .login-panel {
        padding: 28px 18px;
    }

    .mobile-logo {
        margin-bottom: 42px;
    }

    .login-heading h2 {
        font-size: 30px;
    }

    .login-heading {
        margin-bottom: 26px;
    }

    .input-wrapper input {
        height: 52px;
    }

    .login-button {
        height: 53px;
    }

    .copyright {
        margin-top: 35px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .01ms !important;
    }
}

</style>
</head>
<body>

    <main class="login-page">

        <!-- LEFT BRAND PANEL -->
        <section class="brand-panel">
            <div class="brand-content">

                <a href="#" class="brand-logo" aria-label="RETEXA POS">
                    <div class="brand-mark">
                        <span class="brand-r">R</span>
                        <span class="brand-orbit"></span>
                    </div>

                    <div class="brand-name">
                        <strong>RETEXA</strong>
                        <span>POS</span>
                    </div>
                </a>

                <div class="brand-copy">
                    <span class="eyebrow">RETAIL MANAGEMENT SYSTEM</span>

                    <h1>
                        Run your store.
                        <span>Smarter.</span>
                    </h1>

                    <p>
                        A simple and powerful point-of-sale system designed
                        to help you manage sales, inventory and your business
                        from one place.
                    </p>
                </div>

                <div class="feature-list">
                    <div class="feature-item">
                        <span class="feature-icon">&check;</span>
                        <div>
                            <strong>Fast billing</strong>
                            <small>Simple and efficient checkout</small>
                        </div>
                    </div>

                    <div class="feature-item">
                        <span class="feature-icon">&check;</span>
                        <div>
                            <strong>Inventory control</strong>
                            <small>Keep your products organized</small>
                        </div>
                    </div>

                    <div class="feature-item">
                        <span class="feature-icon">&check;</span>
                        <div>
                            <strong>Business insights</strong>
                            <small>Understand your store better</small>
                        </div>
                    </div>
                </div>

                <div class="brand-footer">
                    <span>SMART</span>
                    <i></i>
                    <span>SIMPLE</span>
                    <i></i>
                    <span>POWERFUL</span>
                </div>
            </div>

            <div class="brand-decoration decoration-one"></div>
            <div class="brand-decoration decoration-two"></div>
            <div class="brand-grid"></div>
        </section>


        <!-- RIGHT LOGIN PANEL -->
        <section class="login-panel">
            <div class="login-card">

                <div class="mobile-logo">
                    <div class="mobile-mark">R</div>
                    <div>
                        <strong>RETEXA</strong>
                        <span>POS</span>
                    </div>
                </div>

                <div class="login-heading">
                    <span class="welcome-label">ADMIN PORTAL</span>
                    <h2>Welcome back</h2>
                    <p>Sign in to continue to your RETEXA POS dashboard.</p>
                </div>

                @if ($errors->any())
                    <div class="login-alert error-alert" role="alert">
                        <span class="alert-icon">!</span>
                        <div>
                            <strong>Unable to sign in</strong>
                            <p>{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                @if (session('status'))
                    <div class="login-alert success-alert" role="status">
                        <span class="alert-icon">&check;</span>
                        <div>
                            <strong>Success</strong>
                            <p>{{ session('status') }}</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ url('/admin/login') }}" class="login-form" id="loginForm">
                    @csrf

                    <div class="field-group">
                        <label for="email">Email address</label>

                        <div class="input-wrapper">
                            <span class="input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M4 6.5h16v11H4z" />
                                    <path d="m4.5 7 7.5 6 7.5-6" />
                                </svg>
                            </span>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="admin@example.com"
                                autocomplete="email"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="field-group">
                        <div class="field-label-row">
                            <label for="password">Password</label>
                        </div>

                        <div class="input-wrapper">
                            <span class="input-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <rect x="5" y="10" width="14" height="10" rx="2" />
                                    <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                                </svg>
                            </span>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                id="passwordToggle"
                                aria-label="Show password"
                                aria-pressed="false"
                            >
                                <svg class="eye-open" viewBox="0 0 24 24" fill="none">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/>
                                    <circle cx="12" cy="12" r="2.5"/>
                                </svg>

                                <svg class="eye-closed" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18"/>
                                    <path d="M10.6 6.2A9.8 9.8 0 0 1 12 6c6 0 9.5 6 9.5 6a17.7 17.7 0 0 1-3.1 3.8"/>
                                    <path d="M6.2 6.2C3.8 8 2.5 12 2.5 12s3.5 6 9.5 6c1.5 0 2.8-.3 4-.9"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <label class="remember-row">
                        <span class="check-wrap">
                            <input
                                type="checkbox"
                                name="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}
                            >
                            <span class="custom-check"></span>
                        </span>

                        <span>Remember me</span>
                    </label>

                    <button type="submit" class="login-button" id="loginButton">
                        <span class="button-text">Sign in to dashboard</span>
                        <span class="button-arrow">&rarr;</span>
                        <span class="button-loader" aria-hidden="true"></span>
                    </button>
                </form>

                <div class="login-divider">
                    <span>SECURE ADMIN ACCESS</span>
                </div>

                <p class="security-note">
                    <span class="shield-icon">&check;</span>
                    Your account credentials are protected.
                </p>

                <p class="copyright">
                    &copy; {{ date('Y') }} RETEXA POS. All rights reserved.
                </p>

            </div>
        </section>

    </main>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    const password = document.getElementById("password");
    const passwordToggle = document.getElementById("passwordToggle");
    const loginForm = document.getElementById("loginForm");
    const loginButton = document.getElementById("loginButton");

    /*
     * Password visibility. UI-only — toggles the input's type
     * attribute between "password" and "text"; it does not touch
     * how the value is submitted, validated, or stored.
     */
    if (password && passwordToggle) {
        passwordToggle.addEventListener("click", function () {
            const isPassword = password.type === "password";

            password.type = isPassword ? "text" : "password";

            passwordToggle.classList.toggle("is-visible", isPassword);
            passwordToggle.setAttribute("aria-pressed", isPassword ? "true" : "false");
            passwordToggle.setAttribute(
                "aria-label",
                isPassword ? "Hide password" : "Show password"
            );

            password.focus();
        });
    }

    /*
     * Login button loading state.
     *
     * This does not replace Laravel authentication.
     * It simply gives the user immediate visual feedback
     * while the normal form request is being submitted.
     */
    if (loginForm && loginButton) {
        loginForm.addEventListener("submit", function (event) {
            if (!loginForm.checkValidity()) {
                return;
            }

            loginButton.classList.add("loading");
            loginButton.disabled = true;
        });
    }

    /*
     * Remove loading state when browser restores the page
     * from its back/forward cache.
     */
    window.addEventListener("pageshow", function () {
        if (loginButton) {
            loginButton.classList.remove("loading");
            loginButton.disabled = false;
        }
    });
});

</script>
</body>
</html>