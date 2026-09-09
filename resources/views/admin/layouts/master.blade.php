<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->
<head>
    <meta charset="utf-8" />
    <title>Smart Aesthetics | @yield('title')
    </title>
    <meta
        content="Smart Aesthetics Management System"
        name="description" />
    <meta content="Smart Aesthetics" name="author" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <!--begin::Fonts-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" />
    <!--end::Fonts-->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v=2" />
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" />
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2" />
    <!--begin::Global Theme Styles(used by all pages)-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Theme Styles-->
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/dark-overrides.css') }}?v=2" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/sneat/css/sneat-datatables.css') }}?v=2" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/sneat/css/sneat-layout.css') }}?v=7" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/sneat-appointment-modals.css') }}?v=7" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/sneat-toastr.css') }}?v=1" rel="stylesheet" type="text/css" />
    @stack('css')
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="sneat-shell">
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-overlay"></div>
        <div class="layout-container">
            @include('admin.partials.sidebar')
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
    <!--begin::Global Config(global config for global JS scripts)-->
    <script>
        var KTAppSettings = {
            "breakpoints": {
                "sm": 576,
                "md": 768,
                "lg": 992,
                "xl": 1200,
                "xxl": 1400
            },
            "colors": {
                "theme": {
                    "base": {
                        "white": "#ffffff",
                        "primary": "#7A8B6A",
                        "secondary": "#E5EAEE",
                        "success": "#7A8B6A",
                        "info": "#8950FC",
                        "warning": "#FFA800",
                        "danger": "#F64E60",
                        "light": "#E4E6EF",
                        "dark": "#181C32"
                    },
                    "light": {
                        "white": "#ffffff",
                        "primary": "#E8EDE5",
                        "secondary": "#EBEDF3",
                        "success": "#E8EDE5",
                        "info": "#EEE5FF",
                        "warning": "#FFF4DE",
                        "danger": "#FFE2E5",
                        "light": "#F3F6F9",
                        "dark": "#D6D6E0"
                    },
                    "inverse": {
                        "white": "#ffffff",
                        "primary": "#ffffff",
                        "secondary": "#3F4254",
                        "success": "#ffffff",
                        "info": "#ffffff",
                        "warning": "#ffffff",
                        "danger": "#ffffff",
                        "light": "#464E5F",
                        "dark": "#ffffff"
                    }
                },
                "gray": {
                    "gray-100": "#F3F6F9",
                    "gray-200": "#EBEDF3",
                    "gray-300": "#E4E6EF",
                    "gray-400": "#D1D3E0",
                    "gray-500": "#B5B5C3",
                    "gray-600": "#7E8299",
                    "gray-700": "#5E6278",
                    "gray-800": "#3F4254",
                    "gray-900": "#181C32"
                }
            },
            "font-family": "Poppins"
        };
    </script>
    <!--end::Global Config-->
    <!--begin::Global Theme Bundle(used by all pages)-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/sneat-toastr.js') }}?v=1"></script>
    <script src="{{ asset('assets/plugins/custom/prismjs/prismjs.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('assets/sneat/js/sneat-ktdatatable-adapter.js') }}?v=2"></script>
    <script src="{{ asset('assets/sneat/js/sneat-layout.js') }}?v=2"></script>
    <!--end::Global Theme Bundle-->
    <!--begin::ApexCharts Latest (overrides bundled v3.25.0)-->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <!--end::ApexCharts Latest-->
    <script src="{{ asset('assets/js/pages/features/custom/spinners.js') }}"></script>
    <script src="{{ asset('assets/js/pages/widgets.js') }}"></script>
    <script>
        const debug = "{{ config('app.debug') }}";
    </script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>
    @stack('datatable-js')
    <script src="{{ asset('assets/js/pages/crud/ktdatatable/advanced/row-details.js') }}"></script>
    <script src="{{ asset('assets/js/pages/crud/forms/widgets/select2.js') }}"></script>
    <!--end::Page Scripts-->
    @include('admin.partials.messages', ['toastr' => true])
    @stack('js')
    <script>
        $(function() {
            var $bell = $('#cashflow_notification_toggle');
            var $dropdown = $('#cashflow-notif-dropdown');
            var $count = $('#cashflow-notif-count');
            if (!$bell.length) return;

            // Toggle dropdown
            $bell.on('click', function(e) {
                e.stopPropagation();
                var visible = $dropdown.is(':visible');
                $dropdown.toggle(!visible);
                if (!visible) loadNotifications();
            });
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#cashflow-notification-bell').length) $dropdown.hide();
            });

            // Poll unread count
            function pollCount() {
                $.ajax({
                    url: '/api/cashflow/notifications',
                    type: 'GET',
                    data: { per_page: 1 },
                    success: function(res) {
                        if (res.success && res.unread_count > 0) {
                            $count.text(res.unread_count).show();
                        } else {
                            $count.hide();
                        }
                    }
                });
            }
            pollCount();
            setInterval(pollCount, 60000);

            // Load notifications
            function loadNotifications() {
                $.ajax({
                    url: '/api/cashflow/notifications',
                    type: 'GET',
                    data: { per_page: 15 },
                    success: function(res) {
                        if (!res.success) return;
                        var $list = $('#cashflow-notif-list').empty();
                        if (!res.data || res.data.length === 0) {
                            $list.html('<div class="text-center text-muted py-4 font-size-sm">No notifications</div>');
                            return;
                        }
                        $.each(res.data, function(i, n) {
                            var bg = n.read_at ? '' : 'bg-light-primary';
                            $list.append(
                                '<div class="d-flex align-items-start px-3 py-2 border-bottom ' + bg + '">' +
                                '<div class="flex-grow-1">' +
                                '<div class="font-weight-bold font-size-sm">' + (n.title || '') + '</div>' +
                                '<div class="text-muted font-size-xs">' + (n.message || '') + '</div>' +
                                '<div class="text-muted font-size-xs mt-1">' + timeAgo(n.created_at) + '</div>' +
                                '</div></div>'
                            );
                        });
                    }
                });
            }

            // Mark all read
            $('#cashflow-mark-all-read').on('click', function() {
                $.ajax({
                    url: '/api/cashflow/notifications/mark-read',
                    type: 'POST',
                    data: { all: true },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function() { $count.hide(); loadNotifications(); }
                });
            });

            function timeAgo(dt) {
                if (!dt) return '';
                var diff = Math.floor((Date.now() - new Date(dt).getTime()) / 1000);
                if (diff < 60) return 'just now';
                if (diff < 3600) return Math.floor(diff/60) + 'm ago';
                if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
                return Math.floor(diff/86400) + 'd ago';
            }
        });
    </script>

    @if(request()->is('*/cashflow*'))
    <script src="{{ asset('assets/js/pages/cashflow/keyboard-shortcuts.js') }}"></script>
    @endif

</body>
<!--end::Body-->

</html>
