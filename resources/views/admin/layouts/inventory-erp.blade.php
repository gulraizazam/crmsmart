<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Inventory ERP | @yield('title')</title>
    <meta content="Inventory ERP Module" name="description" />
    <meta content="Smart Aesthetics" name="author" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2" />
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" />
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/dark-overrides.css') }}?v=2" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/sneat/css/sneat-datatables.css') }}?v=2" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/sneat/css/sneat-layout.css') }}?v=8" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/sneat-appointment-modals.css') }}?v=7" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/sneat-toastr.css') }}?v=2" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/sneat-inventory-erp.css') }}?v=2" rel="stylesheet" type="text/css" />
    @stack('css')
</head>
<body id="kt_body" class="sneat-shell sneat-inv-erp-shell @yield('body_class')">
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-overlay"></div>
        <div class="layout-container">
            @include('admin.inventory-erp.partials.sidebar')
            <div class="layout-page">
                @include('admin.partials.header')
                <div class="content-wrapper">
                    @yield('content')
                    @include('admin.partials.footer')
                </div>
            </div>
        </div>
    </div>
    @routes
    <script>
        var HOST_URL = "/metronic/theme/html/tools/preview";
        var base_route = "{{ url('/') }}";
        var asset_url = "{{ asset('/') }}";
    </script>
    <script>
        var KTAppSettings = {
            "breakpoints": { "sm": 576, "md": 768, "lg": 992, "xl": 1200, "xxl": 1400 },
            "colors": {
                "theme": {
                    "base": {
                        "white": "#ffffff", "primary": "#7A8B6A", "secondary": "#E5EAEE",
                        "success": "#7A8B6A", "info": "#8950FC", "warning": "#FFA800",
                        "danger": "#F64E60", "light": "#E4E6EF", "dark": "#181C32"
                    },
                    "light": {
                        "white": "#ffffff", "primary": "#E8EDE5", "secondary": "#EBEDF3",
                        "success": "#E8EDE5", "info": "#EEE5FF", "warning": "#FFF4DE",
                        "danger": "#FFE2E5", "light": "#F3F6F9", "dark": "#D6D6E0"
                    },
                    "inverse": {
                        "white": "#ffffff", "primary": "#ffffff", "secondary": "#3F4254",
                        "success": "#ffffff", "info": "#ffffff", "warning": "#ffffff",
                        "danger": "#ffffff", "light": "#464E5F", "dark": "#ffffff"
                    }
                },
                "gray": {
                    "gray-100": "#F3F6F9", "gray-200": "#EBEDF3", "gray-300": "#E4E6EF",
                    "gray-400": "#D1D3E0", "gray-500": "#B5B5C3", "gray-600": "#7E8299",
                    "gray-700": "#5E6278", "gray-800": "#3F4254", "gray-900": "#181C32"
                }
            },
            "font-family": "Poppins"
        };
    </script>
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/sneat-toastr.js') }}?v=1"></script>
    <script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/sneat/js/sneat-ktdatatable-adapter.js') }}?v=2"></script>
    <script src="{{ asset('assets/sneat/js/sneat-layout.js') }}?v=3"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script src="{{ asset('assets/js/pages/features/custom/spinners.js') }}"></script>
    <script src="{{ asset('assets/js/pages/widgets.js') }}"></script>
    <script>const debug = "{{ config('app.debug') }}";</script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    @stack('datatable-js')
    <script src="{{ asset('assets/js/pages/crud/ktdatatable/advanced/row-details.js') }}"></script>
    <script src="{{ asset('assets/js/pages/crud/forms/widgets/select2.js') }}"></script>
    @include('admin.partials.messages', ['toastr' => true])
    @stack('js')
</body>
</html>
