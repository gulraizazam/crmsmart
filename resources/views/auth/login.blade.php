@extends('layouts.auth_login')
@section('title', 'Login')
@section('content')

<div class="login-container">

    <!-- ========== LEFT PANEL ========== -->
    <div class="login-left">
        <!-- Floating botanical elements -->
        <div class="floating-elements">
            <!-- Animated leaves -->
            <div class="leaf leaf-1">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                    <path d="M20 2C20 2 35 10 35 25C35 32 28 38 20 38C12 38 5 32 5 25C5 10 20 2 20 2Z" fill="rgba(242,232,213,0.3)" />
                    <path d="M20 8V32M14 16C14 16 17 20 20 20C23 20 26 16 26 16" stroke="rgba(242,232,213,0.2)" stroke-width="0.8" />
                </svg>
            </div>
            <div class="leaf leaf-2">
                <svg width="35" height="35" viewBox="0 0 40 40" fill="none">
                    <path d="M5 35C5 35 5 5 35 5C35 5 35 35 5 35Z" fill="rgba(242,232,213,0.25)" />
                    <path d="M5 35C15 25 25 15 35 5" stroke="rgba(242,232,213,0.15)" stroke-width="0.8" />
                </svg>
            </div>
            <div class="leaf leaf-3">
                <svg width="30" height="45" viewBox="0 0 30 45" fill="none">
                    <path d="M15 0C15 0 30 15 25 30C22 38 8 38 5 30C0 15 15 0 15 0Z" fill="rgba(242,232,213,0.2)" />
                    <path d="M15 5V40" stroke="rgba(242,232,213,0.15)" stroke-width="0.6" />
                </svg>
            </div>
            <div class="leaf leaf-4">
                <svg width="28" height="28" viewBox="0 0 40 40" fill="none">
                    <path d="M20 2C20 2 35 10 35 25C35 32 28 38 20 38C12 38 5 32 5 25C5 10 20 2 20 2Z" fill="rgba(242,232,213,0.2)" />
                </svg>
            </div>
            <div class="leaf leaf-5">
                <svg width="32" height="32" viewBox="0 0 40 40" fill="none">
                    <path d="M5 35C5 35 5 5 35 5C35 5 35 35 5 35Z" fill="rgba(242,232,213,0.18)" />
                </svg>
            </div>
            <div class="leaf leaf-6">
                <svg width="24" height="36" viewBox="0 0 30 45" fill="none">
                    <path d="M15 0C15 0 30 15 25 30C22 38 8 38 5 30C0 15 15 0 15 0Z" fill="rgba(242,232,213,0.15)" />
                </svg>
            </div>

            <!-- Sparkle particles -->
            <div class="sparkle sparkle-1"></div>
            <div class="sparkle sparkle-2"></div>
            <div class="sparkle sparkle-3"></div>
            <div class="sparkle sparkle-4"></div>
            <div class="sparkle sparkle-5"></div>
            <div class="sparkle sparkle-6"></div>
            <div class="sparkle sparkle-7"></div>
            <div class="sparkle sparkle-8"></div>

            <!-- Decorative rings -->
            <div class="deco-ring ring-1"></div>
            <div class="deco-ring ring-2"></div>
            <div class="deco-ring ring-3"></div>
        </div>

        <!-- Left panel content -->
        <div class="left-content">
            <!-- Botanical brand icon -->
            <div class="brand-icon">
                <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="40" cy="40" r="38" stroke="rgba(242,232,213,0.3)" stroke-width="0.5"/>
                    <circle cx="40" cy="40" r="30" stroke="rgba(242,232,213,0.2)" stroke-width="0.5"/>
                    <!-- Leaf motif -->
                    <path d="M40 15C40 15 55 28 52 42C50 52 30 52 28 42C25 28 40 15 40 15Z" fill="rgba(242,232,213,0.15)" stroke="rgba(242,232,213,0.4)" stroke-width="0.8"/>
                    <path d="M40 20V55" stroke="rgba(242,232,213,0.3)" stroke-width="0.6"/>
                    <path d="M34 30C34 30 37 34 40 34C43 34 46 30 46 30" stroke="rgba(242,232,213,0.25)" stroke-width="0.5"/>
                    <path d="M32 38C32 38 36 42 40 42C44 42 48 38 48 38" stroke="rgba(242,232,213,0.2)" stroke-width="0.5"/>
                    <!-- Small decorative dots -->
                    <circle cx="40" cy="60" r="1.5" fill="rgba(242,232,213,0.3)"/>
                    <circle cx="36" cy="63" r="1" fill="rgba(242,232,213,0.2)"/>
                    <circle cx="44" cy="63" r="1" fill="rgba(242,232,213,0.2)"/>
                </svg>
            </div>

            <!-- <h1 class="left-title">
                SKIN
                <span>& CO.</span>
            </h1> -->
            <img src="{{ asset('sko.jpeg') }}" alt="Skin & Co. Logo" class="left-title" style="height:200px">
            <div class="left-divider"></div>
            <!-- <p class="left-subtitle">Aesthetic Clinic Management</p> -->
            <p class="left-tagline">"Where beauty meets precision"</p>
        </div>
    </div>

    <!-- ========== RIGHT PANEL - Login Form ========== -->
    <div class="login-right">
        <div class="login-form-wrapper">

            <!-- Logo -->
            <div class="form-logo">
                <a href="/" style="text-decoration: none;">
                    <div class="text-logo">
                        <span class="text-logo-main">SKIN <em>&</em> CO.</span>
                    </div>
                </a>
            </div>

            <!-- Header -->
            <div class="form-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your account</p>
            </div>

            <!-- Alert Messages -->
            <div class="login-alerts">
                @include('admin.partials.messages', ['message' => true])
            </div>

            <!-- Login Form -->
            <form class="aesthetic-form" novalidate="novalidate" id="kt_sign_in_form" method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="form-group fv-row">
                    <label class="form-label">Email Address</label>
                    <input class="form-input form-control" value="{{old('email')}}" type="text" name="email" autocomplete="off" placeholder="Enter your email" />
                </div>

                <!-- Password -->
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

                <!-- Submit -->
                <button type="button" id="kt_sign_in_submit" class="btn-login">
                    <span class="btn-text">Sign In</span>
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                <p>&copy; {{ date('Y') }} Skin & Co. All rights reserved.</p>
            </div>

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
