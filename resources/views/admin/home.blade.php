@extends('admin.layouts.master')
@section('title', 'Dashboard')
@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/sneat-dashboard.css') }}?v=2">

<div class="content d-flex flex-column flex-column-fluid sneat-dashboard" id="kt_content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid dash-container">
            @include('admin.partials.dashboard-overview')
            <div id="kt_mixed_widget_1_chart" style="height: 0; overflow: hidden;"></div>
            <div class="row">
                <div class="modal fade" id="modal_change_appointment_status" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered form-popup" id="appointment_status_change">
                        @include('admin.appointments.appointment-forms.change-status')
                    </div>
                </div>
                <div class="modal fade" id="modal_change_appointment_schedule" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered form-popup" id="appointment_schedule_change">
                        @include('admin.appointments.appointment-forms.schedule')
                    </div>
                </div>

                <div class="col-12" id="activitydiv">
                    <div class="card card-custom dash-widget" id="activities-container">
                        <div class="card-header align-items-center border-0 mt-4">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="font-weight-bolder text-dark">Today's Activities</span>
                                <span class="text-muted mt-3 font-weight-bold font-size-sm" id="totalactivities">0 activities</span>
                            </h3>
                        </div>
                        <div class="card-body pt-4" id="activities-body">
                            <div class="text-center" id="activities-loader">
                                <img src="{{ asset('assets/media/loader.gif') }}" alt="Loading" style="width: 50px;">
                            </div>
                            <div class="timeline timeline-6 mt-3" id="activities-timeline" style="display: none;"></div>
                            <div class="text-center" id="activities-empty" style="display: none;">
                                <span style="color: #000;text-align:center;font-size: 12px;padding: 80px 0px 0px;font-family: Arial; display:block;">No Activity Found</span>
                            </div>
                            <div class="text-center" id="activities-unauthorized" style="display: none;">
                                <span>You are not authorized</span>
                            </div>
                            <div class="text-center py-3" id="load-more-container" style="display: none;">
                                <img src="{{ asset('assets/media/loader.gif') }}" alt="Loading more" id="load-more-spinner" style="width: 30px; display: none;">
                            </div>
                        </div>
                    </div>
                </div>

                @if (\Illuminate\Support\Facades\Gate::allows('dashboard_staff_wise_arrival'))
                <div class="col-lg-12 col-xxl-12 custom_tabs_style" id="staff_wise_arrival">
                    <div class="card card-custom dash-widget" >
                        <div class="card-body p-0">
                            <div class="d-flex align-items-center justify-content-between card-spacer2 flex-grow-1">
                                <span class="dashboard-counter text-uppercase">Centre Wise Arrival</span>
                                <ul class="nav nav-tabs d-flex align-items-center wise_arrival_ul">
                                    <li style="border-bottom: none;">
                                        <div class="actions action-style p-3 mr-3">
                                            @if ($isAdmin)
                                            <div class="btn-group">
                                                <select class="dropdown-menu dropdown-menu-right centre_name_ul" id="centervise_center">
                                                    <option data-period="thismonth" value="All">All Centres</option>
                                                    @foreach ($centres as $centre)
                                                    <option data-period="thismonth" value="{{ $centre->id }}">{{ $centre->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @elseif($isCSRRole)
                                            <div class="btn-group">
                                                <select class="dropdown-menu dropdown-menu-right" id="userwise_arrival">
                                                    <option onclick="initUserWiseArrival('thismonth', 'All')" value="">All</option>
                                                    @foreach ($csrUsers as $user)
                                                    <option onclick="initUserWiseArrival('thismonth', {{ $user->id }})" value="{{ $user->id }}" data-period="thismonth">{{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @else
                                            <div class="btn-group">
                                                <select name="" id="centervise_center">
                                                    <option value="" class="btn form-control btndropdown btn_Report centre_name arrivalbtn">
                                                        {{ $firstCentre ? $firstCentre->name : 'No Centre Assigned' }}
                                                    </option>
                                                </select>
                                            </div>
                                            @endif
                                        </div>
                                    </li>
                                    @if ($isCSRRole)
                                    <li style="border-bottom: none;">
                                        <div class="actions date_action_dropdown action-style py-3 mr-0">
                                            <select id="center_wise_arrival" class="form-control" name="type">


                                                <option value="yesterday" {{ request('type')=='yesterday' ? 'selected' : '' }}>Yesterday</option>
                                                <option value="last7days" {{ request('type')=='last7days' ? 'selected' : '' }}>Last 7 Days</option>
                                                <option value="week" {{ request('type')=='week' ? 'selected' : '' }}>This
                                                    Week</option>
                                                <option value="thismonth" {{ request('type')=='thismonth' ? 'selected' : '' }}>This
                                                    Month</option>
                                                <option value="lastmonth" {{ request('type')=='lastmonth' ? 'selected' : '' }}>Last Month</option>
                                            </select>
                                        </div>
                                    </li>
                                    @else
                                    <li style="border-bottom: none;">
                                        <div class="actions date_action_dropdown action-style py-3 mr-0">
                                            <select id="initCentreWiseArrival" class="form-control" name="type">


                                                <option value="today" {{ request('type')=='today' ? 'selected' : '' }}>Today</option>
                                                <option value="yesterday" {{ request('type')=='yesterday' ? 'selected' : '' }}>Yesterday</option>
                                                <option value="last7days" {{ request('type')=='last7days' ? 'selected' : '' }}>Last 7 Days</option>
                                                <option value="week" {{ request('type')=='week' ? 'selected' : '' }}>This
                                                    Week</option>
                                                <option value="thismonth" {{ request('type')=='thismonth' ? 'selected' : '' }}>This
                                                    Month</option>
                                                <option value="lastmonth" {{ request('type')=='lastmonth' ? 'selected' : '' }}>Last Month</option>
                                            </select>
                                        </div>
                                    </li>
                                    @endif
                                </ul>
                                <div class="d-none flex-column text-right">
                                    <span class="text-dark-75 font-weight-bolder font-size-h3 total-appointment-by-status"></span>
                                    <span class="text-muted font-weight-bold mt-2 appointment-by-status-title"></span>
                                </div>
                            </div>
                            <div class="row pt-7">
                                <div class="col-7">
                                    <div id="centre_wise_arrival"></div>
                                </div>
                                <div class="col-5 centre_wise_arrival_wrap">
                                    <div class="row" id="centre_wise_arrival_02">
                                        <div class='table-responsive' style="overflow-y: scroll; height: 475px;">
                                            <table class='table'>
                                                <thead>
                                                    @if ($isCSRRole)
                                                    <tr>
                                                        <th class='table-cols'>CSR Name</th>
                                                        <th class='table-cols'>Arrived</th>
                                                        <th class='table-cols'>Percentage</th>
                                                    </tr>
                                                    @else
                                                    <tr>
                                                        <th class='table-cols'></th>
                                                        <th class='table-cols'>Arrived</th>
                                                        <th class='table-cols'>WalkIn</th>
                                                        <th class='table-cols'>Percentage</th>
                                                    </tr>
                                                    @endif
                                                </thead>
                                                <tbody id="table-body"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <img src="{{ asset('assets/media/loader.gif') }}" alt="Loading" class="custom_loader loader-img-attended">
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if (\Illuminate\Support\Facades\Gate::allows('dashboard_doctor_wise_conversion'))
                <div class="col-lg-12 col-xxl-12 custom_tabs_style" id="doctor_wise_conversion_section">
                    <div class="card card-custom dash-widget" >
                        <div class="card-body p-0">
                            <div class="d-flex align-items-center justify-content-between card-spacer2 flex-grow-1">
                                <span class="dashboard-counter text-uppercase">Doctor Wise Conversion</span>
                                <ul class="nav nav-tabs d-flex align-items-center  doc_wise_arrival_ul">
                                    <li style="border-bottom: none;">
                                        <div class="actions action-style p-3 mr-3">
                                            <div class="btn-group">
                                                <select class="form-control btndropdown btn_Report doctorwiseconversion selectcenter"
                                                    data-placeholder="Select Centre" data-dropdown-css-class="select2-dropdown">
                                                    @if ($hasMultipleCentres)
                                                    <option value="all" data-period="thismonth">All Centres</option>
                                                    @endif
                                                    @foreach ($centres as $centre)
                                                    <option value="{{ $centre->id }}" data-period="thismonth">{{ $centre->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </li>
                                    <li style="border-bottom: none;">
                                        <div class="actions action-style p-3 mr-3">
                                            <div class="btn-group">
                                                {{-- <a data-id="all-docs" class="btn form-control btndropdown btn_Report doctorname" href="javascript:;"
                                                    data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="false" id="all_docs">
                                                    All Doctors
                                                    <i class="fa fa-angle-down"></i>
                                                </a> --}}
                                                <select class="form-control btndropdown btn_Report doctorname" data-dropdown-css-class="select2-dropdown"
                                                    id="doc_nav">
                                                </select>
                                            </div>
                                        </div>
                                    </li>
                                    <li style="border-bottom: none;">
                                        <div class="actions date_action_dropdown action-style py-3 mr-0">
                                            <select id="dr_wise_con" class="form-control" name="type">
                                                <option value="today" {{ request('type')=='today' ? 'selected' : '' }}>Today
                                                </option>
                                                <option value="yesterday" {{ request('type')=='yesterday' ? 'selected' : '' }}>Yesterday</option>
                                                <option value="last7days" {{ request('type')=='last7days' ? 'selected' : '' }}>Last 7 Days</option>
                                                <option value="week" {{ request('type')=='week' ? 'selected' : '' }}>This
                                                    Week</option>
                                                <option value="thismonth" {{ (request('type')=='thismonth' || !request('type')) ? 'selected' : '' }}>This
                                                    Month</option>
                                                {{-- <option value="lastmonth" {{ request('type')=='lastmonth' ? 'selected' : '' }}>Last Month</option> --}}
                                            </select>
                                        </div>
                                    </li>
                                </ul>

                                <div class="d-none flex-column text-right">
                                    <span class="text-dark-75 font-weight-bolder font-size-h3 total-appointment-by-status"></span>
                                    <span class="text-muted font-weight-bold mt-2 appointment-by-status-title"></span>
                                </div>
                            </div>

                            <div class="row pt-7">
                                <div class="col-7" style="overflow-x: auto;">
                                    <div id="doc_wise_conversion"></div>
                                </div>
                                <div class="col-5 appenddoctorlist" id="centre_wise_arrival_02">
                                    <div class='table-responsive' style="overflow-y: scroll; height: 475px;">
                                        <table class='table'>
                                            <thead>
                                                <tr>
                                                    <th class='table-cols'></th>
                                                    <th class='table-cols'>Con. Ratio</th>
                                                    <th class='table-cols'>% avg</th>
                                                    <th class='table-cols'>Avg Value</th>
                                                </tr>
                                            </thead>
                                            <tbody id="categories-table-body"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <img src="{{ asset('assets/media/loader.gif') }}" alt="Loading" class="custom_loader loader-img-attended">
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('datatable-js')
<script src="{{ asset('assets/js/pages/crud/forms/validation/appointment/validation.js') }}"></script>
<script src="{{ asset('assets/js/pages/dashboard/datatable.js') }}"></script>
<script src="{{ asset('assets/js/jsapi.js') }}"></script>
<script src="{{ asset('assets/js/pie.js') }}"></script>
<script src="{{ asset('assets/js/home.js') }}?v={{ @filemtime(public_path('assets/js/home.js')) }}"></script>
<script>
window.dashboardOverviewCharts = @json($charts ?? []);
window.dashboardConfig = {
    requestType: '{{ $period->value ?? $requestType ?? 'last90days' }}',
    locationIds: {!! json_encode($location_id) !!},
    startDate: '{{ $today ?? '' }}',
    endDate: '{{ $today ?? '' }}',
    isCSR: {{ auth()->user()->hasRole('CSR') ? 'true' : 'false' }},
    isCSRSupervisor: {{ auth()->user()->hasRole('CSR Supervisor') ? 'true' : 'false' }},
    isSocialLead: {{ auth()->user()->hasRole('Social Lead') ? 'true' : 'false' }},
    routes: {
        doctorUpsellingData: '/api/dashboard/doctor-upselling-data'
    }
};
</script>
<script src="{{ asset('assets/js/overview-dashboard.js') }}?v=2"></script>
<script src="{{ asset('assets/js/dashboard.js') }}?v={{ @filemtime(public_path('assets/js/dashboard.js')) }}"></script>
<script src="{{ asset('assets/js/dashboard-charts.js') }}?v={{ @filemtime(public_path('assets/js/dashboard-charts.js')) }}"></script>
@endpush
@endsection