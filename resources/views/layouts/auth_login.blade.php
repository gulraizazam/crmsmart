<!DOCTYPE html>
<html lang="en">
<head>
    <title>Clarity Aesthetic | @yield('title')</title>
    <meta charset="utf-8" />
    <meta name="description" content="Clarity Aesthetic Management System" />
    <meta name="keywords" content="Aesthetic Clinic" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" type="image/svg+xml" href="{{asset('favicon.svg')}}?v=2" />
    <link rel="icon" type="image/x-icon" href="{{asset('favicon.ico')}}?v=2" />
    <link rel="shortcut icon" href="{{asset('favicon.ico')}}?v=2" />
    <link rel="apple-touch-icon" href="{{asset('apple-touch-icon.png')}}?v=2" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link href="{{asset('assets/css/auth/plugins.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/auth/style.bundle.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/login-aesthetic.css')}}" rel="stylesheet" type="text/css" />
</head>
<body class="login-page">

@yield('content')

<script src="{{asset('assets/js/auth/plugins.bundle.js')}}"></script>
<script src="{{asset('assets/js/auth/scripts.bundle.js')}}"></script>
<script src="{{asset('assets/js/auth/general.js')}}"></script>
<script src="{{asset('assets/js/auth/password-reset.js')}}"></script>

@include('admin.partials.messages', ['toaster' => true])

</body>
</html>
