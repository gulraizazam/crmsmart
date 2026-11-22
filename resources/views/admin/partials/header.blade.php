<nav class="layout-navbar" id="layout-navbar">
    <div class="navbar-left">
        <a href="javascript:void(0);" class="layout-menu-toggle" id="layout-menu-toggle" aria-label="Open menu">
            <i class="la la-bars"></i>
        </a>
        <h4 class="navbar-page-title">@yield('title', 'Smart Aesthetics')</h4>
    </div>

    <div class="navbar-right">
        @auth
        <div class="dropdown" id="hr-notification-bell">
            <button class="btn btn-icon btn-clean position-relative" type="button" id="hr_notification_toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="HR Notifications">
                <i class="la la-bell-o icon-lg"></i>
                <span class="badge badge-warning badge-pill position-absolute" style="top:2px;right:0;font-size:10px;display:none;" id="hr-notif-count">0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-right p-0" aria-labelledby="hr_notification_toggle" style="width:350px;max-height:400px;overflow-y:auto;">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                    <h6 class="mb-0">HR Notifications</h6>
                    <a href="javascript:;" id="hr-mark-all-read" class="text-primary font-size-sm">Mark all read</a>
                </div>
                <div id="hr-notif-list">
                    <div class="text-center text-muted py-4 font-size-sm">No notifications</div>
                </div>
            </div>
        </div>
        @push('js')
        <script>
            (function () {
                var listUrl = '{{ route('admin.hr.notifications.index') }}';
                var readUrl = '{{ route('admin.hr.notifications.read', ':id') }}';
                var markAllUrl = '{{ route('admin.hr.notifications.mark-all-read') }}';
                var csrf = { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
                var $count = $('#hr-notif-count');

                function esc(str) {
                    return $('<span>').text(str == null ? '' : str).html();
                }

                function pollHrCount() {
                    $.get(listUrl, function (res) {
                        if (res.status && res.unread_count > 0) {
                            $count.text(res.unread_count).show();
                        } else {
                            $count.hide();
                        }
                    });
                }

                function loadHrNotifications() {
                    $.get(listUrl, function (res) {
                        if (!res.status) return;
                        var $list = $('#hr-notif-list').empty();
                        if (!res.data || res.data.length === 0) {
                            $list.html('<div class="text-center text-muted py-4 font-size-sm">No notifications</div>');
                            return;
                        }
                        $.each(res.data, function (i, n) {
                            var $item = $('<a class="d-block px-3 py-2 border-bottom text-dark hr-notif-item"></a>')
                                .attr('href', n.url || 'javascript:;')
                                .attr('data-id', n.id)
                                .toggleClass('bg-light-warning', !n.is_read)
                                .html(
                                    '<div class="' + (n.is_read ? '' : 'font-weight-bold') + ' font-size-sm">' + esc(n.title) + '</div>' +
                                    '<div class="text-muted font-size-xs">' + esc(n.message) + '</div>' +
                                    '<div class="text-muted font-size-xs mt-1">' + esc(n.time_ago) + '</div>'
                                );
                            $list.append($item);
                        });
                    });
                }

                $('#hr-notification-bell').on('show.bs.dropdown', loadHrNotifications);

                $(document).on('click', '.hr-notif-item', function () {
                    $.ajax({ url: readUrl.replace(':id', $(this).data('id')), type: 'POST', headers: csrf });
                });

                $('#hr-mark-all-read').on('click', function (e) {
                    e.stopPropagation();
                    $.ajax({
                        url: markAllUrl, type: 'POST', headers: csrf,
                        success: function () { $count.hide(); loadHrNotifications(); }
                    });
                });

                pollHrCount();
                setInterval(pollHrCount, 60000);
            })();
        </script>
        @endpush
        @endauth
        <div class="dropdown">
            <button class="navbar-user-btn" type="button" id="navbarUserDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img src="{{ asset('assets/media/logos/avatar.jpg') }}" alt="">
                <span class="navbar-user-meta text-left">
                    <span class="navbar-user-name d-block">{{ auth()->check() ? auth()->user()->name : '' }}</span>
                    <span class="navbar-user-email d-block">{{ auth()->check() ? auth()->user()->email : '' }}</span>
                </span>
                <i class="la la-angle-down"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarUserDropdown">
                <a class="dropdown-item" href="{{ route('admin.change_password') }}">My Profile</a>
                <a class="dropdown-item" href="javascript:void(0);" onclick="document.getElementById('logout-form').submit();">Sign Out</a>
                <form id="logout-form" action="{{ route('logout') }}" method="post" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</nav>
