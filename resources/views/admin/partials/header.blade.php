<nav class="layout-navbar" id="layout-navbar">
    <div class="navbar-left">
        <a href="javascript:void(0);" class="layout-menu-toggle" id="layout-menu-toggle" aria-label="Open menu">
            <i class="la la-bars"></i>
        </a>
        <h4 class="navbar-page-title">@yield('title', 'Clarity Aesthetic')</h4>
    </div>

    <div class="navbar-right">
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
