<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand">
        <a href="{{ route('admin.home') }}" class="app-brand-link">
            <span class="app-brand-logo">
                <svg width="25" viewBox="0 0 25 42" xmlns="http://www.w3.org/2000/svg">
                    <path fill="currentColor" d="M13.79.36L3.4 7.44C.57 9.69-.38 12.48.56 15.8c.13.43.54 1.99 2.57 3.43  .69.49 2.2 1.15 4.53 1.99l-4.96 3.3C.45 26.3.09 28.51 1.56 31.17c1.27 1.64 3.65 2.09 5.53 1.37 1.26-.48 4.36-2.54 9.33-6.16 1.62-1.88 2.28-3.92 1.99-6.14-.44-2.7-2.23-4.66-5.36-5.86l-2.13-.9 7.7-5.49L13.79.36z"/>
                </svg>
            </span>
            <span class="app-brand-text">Smart</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-close d-xl-none" id="layout-menu-close" aria-label="Close menu">&times;</a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
                <li class="menu-header">
                    <span class="menu-header-text">Dashboards</span>
                </li>
                <li class="menu-item {{ activeMenu('admin.home') }}" aria-haspopup="true">
                    <a href="{{ route('admin.home') }}" class="menu-link">
                        <span class="svg-icon menu-icon">

                            {{-- <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <polygon points="0 0 24 0 24 24 0 24" />
                                                <path d="M12.9336061,16.072447 L19.36,10.9564761 L19.5181585,10.8312381 C20.1676248,10.3169571 20.2772143,9.3735535 19.7629333,8.72408713 C19.6917232,8.63415859 19.6104327,8.55269514 19.5206557,8.48129411 L12.9336854,3.24257445 C12.3871201,2.80788259 11.6128799,2.80788259 11.0663146,3.24257445 L4.47482784,8.48488609 C3.82645598,9.00054628 3.71887192,9.94418071 4.23453211,10.5925526 C4.30500305,10.6811601 4.38527899,10.7615046 4.47382636,10.8320511 L4.63,10.9564761 L11.0659024,16.0730648 C11.6126744,16.5077525 12.3871218,16.5074963 12.9336061,16.072447 Z" fill="#000000" fill-rule="nonzero" />
                                                <path d="M11.0563554,18.6706981 L5.33593024,14.122919 C4.94553994,13.8125559 4.37746707,13.8774308 4.06710397,14.2678211 C4.06471678,14.2708238 4.06234874,14.2738418 4.06,14.2768747 L4.06,14.2768747 C3.75257288,14.6738539 3.82516916,15.244888 4.22214834,15.5523151 C4.22358765,15.5534297 4.2250303,15.55454 4.22647627,15.555646 L11.0872776,20.8031356 C11.6250734,21.2144692 12.371757,21.2145375 12.909628,20.8033023 L19.7677785,15.559828 C20.1693192,15.2528257 20.2459576,14.6784381 19.9389553,14.2768974 C19.9376429,14.2751809 19.9363245,14.2734691 19.935,14.2717619 L19.935,14.2717619 C19.6266937,13.8743807 19.0546209,13.8021712 18.6572397,14.1104775 C18.654352,14.112718 18.6514778,14.1149757 18.6486172,14.1172508 L12.9235044,18.6705218 C12.377022,19.1051477 11.6029199,19.1052208 11.0563554,18.6706981 Z" fill="#000000" opacity="0.3" />
                                            </g>
                                        </svg> --}}
                            <i class="font-icon la la-home"></i>
                        </span>
                        <span class="menu-text">Dashboard</span>
                    </a>
                </li>

                <li class="menu-header">
                    <span class="menu-header-text">Users</span>
                </li>
                @if (Gate::allows('permissions_manage') ||
                Gate::allows('roles_manage') ||
                Gate::allows('users_manage') ||
                Gate::allows('doctors_manage'))
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.permissions.index', 'admin.users.index', 'admin.roles.index', 'admin.roles.edit', 'admin.users.index', 'admin.doctors.index']) }}" aria-haspopup="true" data-menu-toggle="hover">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <i class="font-icon la la-user"></i>
                        </span>
                        <span class="menu-text">User Management</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">

                            @can('permissions_manage')
                            <li class="menu-item {{ activeMenu('admin.permissions.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.permissions.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Permissions</span>
                                </a>
                            </li>
                            @endcan

                            @can('roles_manage')
                            <li class="menu-item {{ activeMenu('admin.roles.index') }} {{ activeMenu('admin.roles.edit') }}" aria-haspopup="true">
                                <a href="{{ route('admin.roles.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Roles</span>
                                </a>
                            </li>
                            @endcan

                            @can('users_manage')
                            <li class="menu-item {{ activeMenu('admin.users.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.users.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Application Users</span>
                                </a>
                            </li>
                            @endcan
                            @can('doctors_manage')
                            <li class="menu-item {{ activeMenu('admin.doctors.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.doctors.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Doctors</span>
                                </a>
                            </li>
                            @endcan
                            {{-- User Types menu hidden --}}
                            {{-- @can('user_types_manage')
                            <li class="menu-item {{ activeMenu('admin.user_types.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.user_types.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">User Types</span>
                                </a>
                            </li>
                            @endcan --}}

                        </ul>
                    </div>
                </li>
                @endif


                <li class="menu-header">
                    <span class="menu-header-text">Clinic</span>
                </li>
                <!--Patient menu-->

                @if (Gate::allows('patients_manage'))
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.patients.index', 'admin.patients.preview']) }}" aria-haspopup="true" data-menu-toggle="hover">

                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <i class="font-icon la la-users"></i>
                        </span>
                        <span class="menu-text">Patients Management</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            @can('patients_manage')
                            <li class="menu-item {{ activeMenu('admin.patients.index') }} {{ activeMenu('admin.patients.preview') }}" aria-haspopup="true">
                                <a href="{{ route('admin.patients.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Patients</span>
                                </a>
                            </li>
                            @endcan

                        </ul>
                    </div>

                </li>
                @endif

                <!-- Leads menu -->

                @if (Gate::allows('leads_manage'))
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.leads.index']) }}" aria-haspopup="true" data-menu-toggle="hover">

                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <i class="font-icon la la-briefcase"></i>
                        </span>
                        <span class="menu-text">Leads</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">

                            {{-- @can('leads_create')
                            <li class="menu-item {{ isActive(url('admin/leads?create=create'), 'create') }}" aria-haspopup="true">
                                <a href="{{ route('admin.leads.index', ['create' => 'create']) }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Create Lead </span>
                                </a>
                            </li>
                            @endcan --}}

                            @can('leads_manage')
                            <li class="menu-item {{ isActive(url('admin/leads'), 'other') }}" aria-haspopup="true">
                                <a href="{{ route('admin.leads.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Leads </span>
                                </a>
                            </li>
                            @endcan

                            <!-- @can('leads_junk')
                            <li class="menu-item {{ isActive(url('admin/leads?type=junk'), 'junk') }}" aria-haspopup="true">
                                <a href="{{ route('admin.leads.index', ['type' => 'junk']) }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Junk Leads</span>
                                </a>
                            </li>
                            @endcan -->


                        </ul>
                    </div>

                </li>
                @endif

                <!-- End leads menu -->

                <!-- Appointment menu -->

                @if (Gate::allows('appointments_manage'))
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.consultancy.index']) }} {{ openMenu(['admin.treatment.index']) }} {{ openMenu(['admin.appointments.index']) }}" aria-haspopup="true" data-menu-toggle="hover">

                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon fa_icon">
                            <i class="font-icon la la-clock-o"></i>
                        </span>
                        <span class="menu-text">Appointments</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>

                        <ul class="menu-subnav">

                            @can('appointments_consultancy')
                            <li class="menu-item manage-consultancy {{ activeMenu('admin.consultancy.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.consultancy.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Consultancies</span>
                                </a>
                            </li>
                            @endcan

                            @can('treatments_manage')
                            <li class="menu-item manage-treatment {{ activeMenu('admin.treatment.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.treatment.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Treatments</span>
                                </a>
                            </li>
                            @endcan

                        </ul>
                    </div>

                </li>
                @endif

                <!-- End Appointment menu -->

                @if (Gate::allows('plans_manage'))
                <li class="menu-item {{ activeMenu('admin.packages.index') }}" aria-haspopup="true">
                    <a href="{{ route('admin.packages.index') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="font-icon la la-cog"></i></span>
                        <span class="menu-text">@lang('global.packages.title')</span>
                    </a>
                </li>
                @endif
                {{-- Refunds menu hidden --}}
                {{-- @if (Gate::allows('refunds_manage'))
                <li class="menu-item {{ activeMenu('admin.refunds.index') }}" aria-haspopup="true">
                    <a href="{{ route('admin.refunds.index') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="font-icon la la-refresh"></i></span>
                        <span class="menu-text"> Refunds </span>
                    </a>
                </li>
                @endif --}}
                @if (Gate::allows('services_manage') || Gate::allows('packages_manage') || Gate::allows('discounts_manage'))
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.services.index']) }} {{ openMenu(['admin.bundles.index']) }} {{ openMenu(['admin.discounts.index']) }}" aria-haspopup="true" data-menu-toggle="hover">

                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon fa_icon">
                            <i class="font-icon la la-clock-o"></i>
                        </span>
                        <span class="menu-text">Services</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>

                        <ul class="menu-subnav">

                            @can('services_manage')
                            <li class="menu-item manage-consultancy {{ activeMenu('admin.services.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.services.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Services</span>
                                </a>
                            </li>
                            @endcan

                            @can('packages_manage')
                            <li class="menu-item manage-treatment {{ activeMenu('admin.bundles.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.bundles.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Bundles</span>
                                </a>
                            </li>
                            @endcan

                            @can('discounts_manage')
                            <li class="menu-item manage-treatment {{ activeMenu('admin.discounts.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.discounts.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Discounts</span>
                                </a>
                            </li>
                            @endcan
                            
                        </ul>
                    </div>

                </li>
                @endif
                <!-- @if (Gate::allows('vouchers_manage') || Gate::allows('voucher_types_manage'))
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.voucherTypes.index', 'admin.vouchers.index']) }} " aria-haspopup="true" data-menu-toggle="hover">

                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <i class="font-icon la la-ticket"></i>
                        </span>
                        <span class="menu-text">Vouchers</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>

                        <ul class="menu-subnav">

                            @can('voucher_types_manage')
                            <li class="menu-item {{ activeMenu('admin.voucherTypes.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.voucherTypes.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Voucher Types</span>
                                </a>
                            </li>
                            @endcan

                            @can('vouchers_manage')
                            <li class="menu-item {{ activeMenu('admin.vouchers.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.vouchers.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Vouchers</span>
                                </a>
                            </li>
                            @endcan



                        </ul>
                    </div>

                </li>
                @endif -->

                {{-- Memberships menu hidden --}}
                {{--
                @if (Gate::allows('memberships_manage') || Gate::allows('membershiptypes_manage'))
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.membershiptypes.index', 'admin.memberships.index']) }} " aria-haspopup="true" data-menu-toggle="hover">

                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <i class="font-icon la la-id-card"></i>
                        </span>
                        <span class="menu-text">Memberships</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>

                        <ul class="menu-subnav">

                            @can('membershiptypes_manage')
                            <li class="menu-item {{ activeMenu('admin.membershiptypes.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.membershiptypes.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Membership Types</span>
                                </a>
                            </li>
                            @endcan

                            @can('memberships_manage')
                            <li class="menu-item {{ activeMenu('admin.memberships.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.memberships.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Memberships</span>
                                </a>
                            </li>
                            @endcan

                        </ul>
                    </div>

                </li>
                @endif
                --}}
                {{-- Doctor Ratings menu hidden --}}
                {{-- @if (Gate::allows('feedbacks_manage'))
                <li class="menu-item {{ activeMenu('admin.feedbacks.index') }} " aria-haspopup="true">
                    <a href="{{ route('admin.feedbacks.index') }}" class="menu-link">
                        <span class="svg-icon menu-icon"><i class="font-icon la la-file"></i></span>
                        <span class="menu-text">Doctor Ratings</span>
                    </a>
                </li>
                @endif --}}
                @if (Gate::allows('resourcerotas_manage') || Gate::allows('business_closures_manage'))
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.resourcerotas.schedule', 'admin.business-closures.index']) }}" aria-haspopup="true" data-menu-toggle="hover">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon"><i class="font-icon la la-calendar-alt"></i></span>
                        <span class="menu-text">Schedule</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            @if (Gate::allows('resourcerotas_manage'))
                            <li class="menu-item {{ activeMenu('admin.resourcerotas.schedule') }} {{ activeMenu('admin.resourcerotas.repeating-shifts') }}" aria-haspopup="true">
                                <a href="{{ route('admin.resourcerotas.schedule') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Scheduling Shifts</span>
                                </a>
                            </li>
                            @endif
                            @if (Gate::allows('business_closures_manage'))
                            <li class="menu-item {{ activeMenu('admin.business-closures.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.business-closures.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Business Closed Periods</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                @endif

                {{-- Admin Settings menu hidden --}}
                @if(false)
                <li class="menu-header">
                    <span class="menu-header-text">Settings</span>
                </li>
                @if (Gate::allows('cities_manage') ||
                Gate::allows('payment_modes_manage') ||
                Gate::allows('custom_forms_manage') ||
                Gate::allows('custom_form_feedbacks_manage') ||
                Gate::allows('locations_manage') ||
                Gate::allows('staff_targets_manage') ||
                Gate::allows('centre_targets_manage') ||
                Gate::allows('lead_sources_manage') ||
                Gate::allows('lead_statuses_manage') ||
                Gate::allows('appointment_statuses_manage') ||
                Gate::allows('cancellation_reasons_manage') ||
                Gate::allows('resources_manage') ||
                Gate::allows('logs_manage') ||
                Gate::allows('finances_manage') ||
                Gate::allows('invoices_manage') ||
                Gate::allows('pabao_records_manage') ||
                Gate::allows('machineType_manage'))

                <li class="menu-item menu-item-submenu {{ openMenu([
                        'admin.payment_modes.index',
                        'admin.payment_modes.sort',
                        'admin.cities.index',
                        'admin.cities.sort',
                        'admin.lead_sources.index',
                        'admin.lead_sources.sort',
                        'admin.lead_statuses.index',
                        'admin.lead_statuses.sort',
                        'admin.appointment_statuses.index',
                        'admin.locations.index',
                        'admin.machine_types.index',
                        'admin.resources.index',
                        'admin.logs.index',

                        'admin.centre_targets.index',

                        'admin.packagesadvances.index',
                        'admin.resourcerotas.calender-view',
                        'admin.invoices.index',
                        'admin.packages.log',
                        'admin.custom_form_feedbacks.index',
                        'admin.custom_form_feedbacks.edit',
                        'admin.custom_form_feedbacks.filled_preview',
                        'admin.custom_forms.index',
                        'admin.custom_forms.edit',
                        'admin.custom_form_feedbacks.preview_form',
                        'admin.custom_form_feedbacks.fill_form',
                    ]) }}" aria-haspopup="true" data-menu-toggle="hover">

                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <i class="font-icon la la-cog"></i>
                        </span>
                        <span class="menu-text">Admin Settings</span>
                        <i class="menu-arrow"></i>
                    </a>

                    {{-- @can('settings_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.settings.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.settings.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Global Settings</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan --}}
                    {{-- @can('user_operator_settings_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.user_operator_settings.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.user_operator_settings.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Operator Settings</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan --}}

                    @can('payment_modes_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ openMenu(['admin.payment_modes.index', 'admin.payment_modes.sort'], 'menu-item-active') }}" aria-haspopup="true">
                                <a href="{{ route('admin.payment_modes.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Payment Modes</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan

                    {{-- @can('regions_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ openMenu(['admin.regions.index', 'admin.regions.sort'], 'menu-item-active') }}" aria-haspopup="true">
                                <a href="{{ route('admin.regions.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Regions</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan --}}

                    @can('cities_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ openMenu(['admin.cities.index', 'admin.cities.sort'], 'menu-item-active') }}" aria-haspopup="true">
                                <a href="{{ route('admin.cities.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Cities</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan

                    {{-- @can('towns_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.towns.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.towns.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Towns</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan --}}

                    @can('lead_sources_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ openMenu(['admin.lead_sources.index', 'admin.lead_sources.sort'], 'menu-item-active') }}" aria-haspopup="true">
                                <a href="{{ route('admin.lead_sources.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Lead Sources</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan

                    @can('lead_statuses_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ openMenu(['admin.lead_statuses.index', 'admin.lead_statuses.sort'], 'menu-item-active') }}" aria-haspopup="true">
                                <a href="{{ route('admin.lead_statuses.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Lead Statuses</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan

                    @can('locations_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.locations.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.locations.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Centres</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan

                    @can('appointment_statuses_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.appointment_statuses.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.appointment_statuses.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Appointment Statuses</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan

                    @can('machineType_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.machine_types.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.machine_types.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Machine Type</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan

                    @can('resources_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.resources.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.resources.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Resource</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan

                    <!-- @can('logs_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.logs.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.logs.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Logs</span>

                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan -->

                    {{-- @can('sms_templates_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.sms_templates.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.sms_templates.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">SMS Templates</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan --}}

                    


                   

                    @can('invoices_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.invoices.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.invoices.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Invoices</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan


                    

                    
                </li>
                @endif
                @endif
                {{-- Inventory menu hidden --}}
                {{--
                <!-- Inventory menu -->
                @if (Gate::allows('inventory_manage'))
                @can('inventory_manage')
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.warehouse.index']) }} {{ openMenu(['admin.brands.index']) }} {{ openMenu(['admin.products.index']) }} {{ openMenu(['admin.products.logs']) }} {{ openMenu(['admin.products.stock']) }} {{ openMenu(['admin.transfer_product.index']) }} {{ openMenu(['admin.orders.index']) }} {{ openMenu(['admin.order.refunds.index']) }}" aria-haspopup="true" data-menu-toggle="hover">

                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon fa_icon">
                            <i class="la la-warehouse"></i>
                        </span>
                        <span class="menu-text">Inventory</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <!-- @can('warehouse_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.warehouse.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.warehouse.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Warehouse</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan -->
                    @can('brand_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.brands.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.brands.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Brand</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan
                    @can('product_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.products.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.products.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Product</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan
                    @can('product_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.transfer_product.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.transfer_product.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Transfer</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan
                    @can('order_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.orders.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.orders.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Order</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan
                    <!-- @can('inventory_refund_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.order.refunds.index') }}" aria-haspopup="true">
                                <a href="{{ route('admin.order.refunds.index') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Refund</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan
                </li>
                @endcan -->
                @endif
                --}}
                <!-- End Inventory menu -->
                <li class="menu-header">
                    <span class="menu-header-text">Reports</span>
                </li>
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.reports.finance_reports', 'admin.reports.operations_report', 'admin.reports.inventory_report']) }}" aria-haspopup="true" data-menu-toggle="hover">

                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <span class="svg-icon menu-icon">
                            <i class="font-icon la la-file-text-o"></i>
                        </span>
                        <span class="menu-text">Reports Management</span>
                        <i class="menu-arrow"></i>
                    </a>

                    @can('finance_general_revenue_reports_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.finance_reports') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.finance_reports') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">General Sales Report</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan

                    <!-- @can('operations_reports_operations_tax_calculation_report')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.tax_calculation_report') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.tax_calculation_report') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Tax Calculation Report</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan -->

                    <!-- @can('operations_reports_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.operations_report') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.operations_report') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Operation Reports</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan -->
                    <!-- @can('finance_general_revenue_reports_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.inventory_report') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.inventory_report') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Inventory Reports</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan -->
                    <!-- @can('operations_reports_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.membership-reports') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.membership-reports') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Membership Reports</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan -->
                    <!-- @can('operations_reports_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.doctorWiseConversion') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.doctorWiseConversion') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Doctor incentive Report</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan -->
                    <!-- @can('appointment_reports_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.appointmentsReport') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.appointmentsReport') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Appointments Report</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan -->
                    <!-- @can('csr_dashboard_report')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.csr_dashboard') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.csr_dashboard') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">CSR Dashboard</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan -->
                    <!-- @can('non_converted_customers_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.arrived_not_converted') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.arrived_not_converted') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Non-Converted Customer Report </span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan -->
                    @can('conversion_report_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.conversion') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.conversion') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Conversion Report</span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan
                    @can('appointment_reports_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.activity_logs') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.activity_logs') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Activity Logs </span>
                                </a>
                            </li>

                        </ul>
                    </div>
                    @endcan
                    <!-- @can('staff_wise_arrival_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.staff_wise_arrival') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.staff_wise_arrival') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Staff Wise Arrival Report </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan -->
                    @can('follow_up_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.follow_up') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.follow_up') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Follow Up Report </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan
                    {{-- Inventory Report hidden --}}
                    {{--
                    @can('inventory_report_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.inventory_report') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.inventory_report') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Inventory Report </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan
                    --}}
                    @can('feedbacks_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.feedback_report') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.feedback_report') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Doctor Ratings Report </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan
                   <!-- @can('followuppatient_manage')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.future_treatments') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.future_treatments') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Future Treatments Report</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan -->
                    @can('upselling_report')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.upselling') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.upselling') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Doctors Upselling Report </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan
                    <!-- @can('consultant_revenue_report')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.consultant_revenue') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.consultant_revenue') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Consultants Revenue Report </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan -->
                    <!-- @can('consultant_revenue_report')
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item {{ activeMenu('admin.reports.doctor_revenue') }}" aria-haspopup="true">
                                <a href="{{ route('admin.reports.doctor_revenue') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot">
                                        <span></span>
                                    </i>
                                    <span class="menu-text">Doctor Revenue Report</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    @endcan -->
                </li>

                {{-- Cash Flow menu hidden --}}
                @if(false)
                <li class="menu-header">
                    <span class="menu-header-text">Finance</span>
                </li>
                {{-- Cash Flow Module --}}
                @can('cashflow_manage')
                <li class="menu-item menu-item-submenu {{ openMenu(['admin.cashflow.dashboard','admin.cashflow.expenses','admin.cashflow.transfers','admin.cashflow.vendors','admin.cashflow.staff','admin.cashflow.reports','admin.cashflow.settings']) }}" aria-haspopup="true" data-menu-toggle="hover">
                    <a href="javascript:;" class="menu-link menu-toggle">
                        <i class="menu-icon la la-money-bill-wave"></i>
                        <span class="menu-text">Cash Flow</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="menu-submenu">
                        <i class="menu-arrow"></i>
                        <ul class="menu-subnav">
                            <li class="menu-item menu-item-parent" aria-haspopup="true">
                                <span class="menu-link"><span class="menu-text">Cash Flow</span></span>
                            </li>
                            @can('cashflow_dashboard')
                            <li class="menu-item {{ activeMenu('admin.cashflow.dashboard') }}" aria-haspopup="true">
                                <a href="{{ route('admin.cashflow.dashboard') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                    <span class="menu-text">Dashboard</span>
                                </a>
                            </li>
                            @endcan
                            @can('cashflow_expense_create')
                            <li class="menu-item {{ activeMenu('admin.cashflow.expenses') }}" aria-haspopup="true">
                                <a href="{{ route('admin.cashflow.expenses') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                    <span class="menu-text">Expenses</span>
                                </a>
                            </li>
                            @endcan
                            @can('cashflow_transfer_create')
                            <li class="menu-item {{ activeMenu('admin.cashflow.transfers') }}" aria-haspopup="true">
                                <a href="{{ route('admin.cashflow.transfers') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                    <span class="menu-text">Transfers</span>
                                </a>
                            </li>
                            @endcan
                            @can('cashflow_vendor_manage')
                            <li class="menu-item {{ activeMenu('admin.cashflow.vendors') }}" aria-haspopup="true">
                                <a href="{{ route('admin.cashflow.vendors') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                    <span class="menu-text">Vendors</span>
                                </a>
                            </li>
                            @endcan
                            @can('cashflow_staff_advance')
                            <li class="menu-item {{ activeMenu('admin.cashflow.staff') }}" aria-haspopup="true">
                                <a href="{{ route('admin.cashflow.staff') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                    <span class="menu-text">Staff Advances</span>
                                </a>
                            </li>
                            @endcan
                            {{-- @can('cashflow_fdm_view')
                            <li class="menu-item {{ activeMenu('admin.cashflow.fdm') }}" aria-haspopup="true">
                                <a href="{{ route('admin.cashflow.fdm') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                    <span class="menu-text">FDM View</span>
                                </a>
                            </li>
                            @endcan --}}
                            @can('cashflow_reports')
                            <li class="menu-item {{ activeMenu('admin.cashflow.reports') }}" aria-haspopup="true">
                                <a href="{{ route('admin.cashflow.reports') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                    <span class="menu-text">Reports</span>
                                </a>
                            </li>
                            @endcan
                            @can('cashflow_settings')
                            <li class="menu-item {{ activeMenu('admin.cashflow.settings') }}" aria-haspopup="true">
                                <a href="{{ route('admin.cashflow.settings') }}" class="menu-link">
                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                    <span class="menu-text">Settings</span>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcan
                @endif

            </ul>
</aside>
