<div class="sneat-tabs-wrap">
    <ul class="sneat-page-tabs appointment-menu list-unstyled mb-0">
        @can('appointments_manage')
            <li>
                <a href="javascript:void(0)" onclick="toggleSection($(this), 'appointment');" class="change-tab appointment-tab nav-bar-active">
                    <i class="la la-list"></i>
                    <span class="navi-text">Treatments</span>
                </a>
            </li>
        @endcan

        @can("treatments_services")
            <li>
                <a href="javascript:void(0)" onclick="toggleSection($(this), 'treatment');" class="change-tab treatment-tab">
                    <i class="la la-calendar"></i>
                    <span class="navi-text">Manage Treatment</span>
                </a>
            </li>
        @endcan
    </ul>
</div>
