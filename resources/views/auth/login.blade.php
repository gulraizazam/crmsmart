@extends('layouts.auth_login')
@section('title', 'Login')
@section('content')

<div class="sneat-auth">
    <aside class="sneat-auth-visual" aria-hidden="true">
        <div class="sneat-auth-visual-inner">
            <div class="sneat-auth-visual-copy">
                <h2>Clarity Aesthetic</h2>
                <p>Appointments, patients, and clinic operations in one place.</p>
            </div>
            <div class="sneat-auth-art">
                <svg viewBox="0 0 560 360" xmlns="http://www.w3.org/2000/svg" role="presentation">
                    <rect x="48" y="36" width="464" height="288" rx="28" fill="#fff" opacity="0.72"/>
                    <rect x="72" y="60" width="200" height="240" rx="18" fill="#fff"/>
                    <rect x="92" y="84" width="96" height="14" rx="7" fill="#696cff"/>
                    <rect x="92" y="112" width="160" height="10" rx="5" fill="#d9dee3"/>
                    <rect x="92" y="132" width="128" height="10" rx="5" fill="#eceef1"/>
                    <rect x="92" y="168" width="160" height="52" rx="12" fill="#e7e7ff"/>
                    <rect x="92" y="236" width="72" height="36" rx="10" fill="#696cff"/>
                    <rect x="176" y="236" width="76" height="36" rx="10" fill="#f0f0ff"/>
                    <rect x="292" y="60" width="196" height="112" rx="18" fill="#fff"/>
                    <circle cx="336" cy="116" r="28" fill="#e7e7ff"/>
                    <rect x="380" y="96" width="84" height="12" rx="6" fill="#566a7f"/>
                    <rect x="380" y="118" width="64" height="10" rx="5" fill="#d9dee3"/>
                    <rect x="292" y="188" width="196" height="112" rx="18" fill="#fff"/>
                    <rect x="316" y="214" width="28" height="64" rx="8" fill="#cfd1ff"/>
                    <rect x="356" y="198" width="28" height="80" rx="8" fill="#696cff"/>
                    <rect x="396" y="226" width="28" height="52" rx="8" fill="#b6b8ff"/>
                    <rect x="436" y="210" width="28" height="68" rx="8" fill="#e7e7ff"/>
                </svg>
            </div>
        </div>
    </aside>

    <div class="sneat-auth-form-col">
        <div class="sneat-auth-card">
            <div class="sneat-auth-brand">
                <span class="sneat-auth-brand-logo">
                    <svg width="25" viewBox="0 0 25 42" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill="currentColor" d="M13.79.36L3.4 7.44C.57 9.69-.38 12.48.56 15.8c.13.43.54 1.99 2.57 3.43  .69.49 2.2 1.15 4.53 1.99l-4.96 3.3C.45 26.3.09 28.51 1.56 31.17c1.27 1.64 3.65 2.09 5.53 1.37 1.26-.48 4.36-2.54 9.33-6.16 1.62-1.88 2.28-3.92 1.99-6.14-.44-2.7-2.23-4.66-5.36-5.86l-2.13-.9 7.7-5.49L13.79.36z"/>
                    </svg>
                </span>
                <span class="sneat-auth-brand-text">Clarity</span>
            </div>

            <div class="sneat-auth-header">
                <h1>Welcome to Clarity!</h1>
                <p>Sign in to your account to continue</p>
            </div>

            <div class="login-alerts">
                @include('admin.partials.messages', ['message' => true])
            </div>

            <form class="sneat-login-form" novalidate="novalidate" id="kt_sign_in_form" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group fv-row">
                    <label class="form-label" for="login-email">Email Address</label>
                    <input class="form-input form-control" id="login-email" value="{{old('email')}}" type="text" name="email" autocomplete="off" placeholder="Enter your email" />
                </div>

                <div class="form-group fv-row">
                    <div class="password-header">
                        <label class="form-label" for="login-password">Password</label>
                        <a href="{{route('auth.password.reset')}}" class="forgot-link toggle-form">Forgot Password?</a>
                    </div>
                    <div class="password-toggle">
                        <input class="form-input form-control" id="login-password" value="{{old('password')}}" type="password" name="password" autocomplete="off" placeholder="Enter your password" />
                        <button type="button" class="toggle-eye" onclick="togglePassword()" aria-label="Show password" aria-controls="login-password">
                            <svg id="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="button" id="kt_sign_in_submit" class="btn-login">
                    <span class="btn-text">Sign In</span>
                </button>
            </form>

            <div class="login-footer">
                <p>&copy; {{ date('Y') }} Clarity Aesthetic. All rights reserved.</p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var input = document.getElementById('login-password');
    var eyeOpen = document.getElementById('eye-open');
    var eyeClosed = document.getElementById('eye-closed');
    var toggle = document.querySelector('.toggle-eye');
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
        if (toggle) {
            toggle.setAttribute('aria-label', 'Hide password');
        }
    } else {
        input.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
        if (toggle) {
            toggle.setAttribute('aria-label', 'Show password');
        }
    }
}
</script>

@endsection
