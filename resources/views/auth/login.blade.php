@extends('layouts.auth_login')
@section('title', 'Login')
@section('content')

<div class="login-page-container">
    <div class="login-card">

        <div class="brand-mark">
            <span class="brand-demo">DEMO</span>
        </div>

        <div class="form-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your account</p>
        </div>

        <div class="login-alerts">
            @include('admin.partials.messages', ['message' => true])
        </div>

        <form class="aesthetic-form" novalidate="novalidate" id="kt_sign_in_form" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group fv-row">
                <label class="form-label">Email Address</label>
                <input class="form-input form-control" value="{{old('email')}}" type="text" name="email" autocomplete="off" placeholder="Enter your email" />
            </div>

            <div class="form-group fv-row">
                <div class="password-header">
                    <label class="form-label" style="margin-bottom: 0;">Password</label>
                    <a href="{{route('auth.password.reset')}}" class="forgot-link toggle-form">Forgot Password?</a>
                </div>
                <div class="password-toggle">
                    <input class="form-input form-control" id="login-password" value="{{old('password')}}" type="password" name="password" autocomplete="off" placeholder="Enter your password" />
                    <button type="button" class="toggle-eye" onclick="togglePassword()">
                        <svg id="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg id="eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
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
            <p>&copy; {{ date('Y') }} DEMO. All rights reserved.</p>
        </div>

    </div>
</div>

<script>
function togglePassword() {
    var input = document.getElementById('login-password');
    var eyeOpen = document.getElementById('eye-open');
    var eyeClosed = document.getElementById('eye-closed');
    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.style.display = 'none';
        eyeClosed.style.display = 'block';
    } else {
        input.type = 'password';
        eyeOpen.style.display = 'block';
        eyeClosed.style.display = 'none';
    }
}
</script>

@endsection
