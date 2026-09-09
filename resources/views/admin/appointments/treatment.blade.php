@extends('admin.layouts.master')
@section('title', 'Treatments')
@section('content')

    @push('css')
        <link href="{{asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
    @endpush
    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-treatments" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                @include('admin.appointments.partials.treatment-menu')
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label change-label">Treatments</h3>

                            @php
                                $userCentres = $userCentres ?? [];
                                $showDropdown = count($userCentres) > 1;
                            @endphp

                            @if($showDropdown)
                            <div class="treatment-location-header-dropdown d-none">
                                <select class="form-control" id="treatment_location_filter"></select>
                            </div>
                            @else
                            <div style="display: none;">
                                <select class="form-control" id="treatment_location_filter"></select>
                            </div>
                            @endif
                        </div>
                        <div class="card-toolbar">
                            @if(Gate::allows('treatments_today'))
                                <div class="export-appointments">
                                    <a id="today_consultancies" onclick="loadTodayAppointments('{{date('Y-m-d')}}', 'treatment');" href="javascript:void(0);" class="btn btn-info">
                                        Today Treatments
                                    </a>
                                </div>
                            @endif
                            @if(Gate::allows('treatments_export'))
                                    <form method="POST" action="download-filter-data" id="filtersform">
                                        @csrf
                                        <input type="hidden" id="filter_patient_id" name="filter_patient_id">
                                        <input type="hidden" id="filter_date_from" name="filter_date_from">
                                        <input type="hidden" name="appointmenttype" value="2">
                                        <input type="hidden" name="filter_phone" id="filter_phone">
                                        <input type="hidden" id="filter_date_to" name="filter_date_to">
                                        <input type="hidden" id="filter_doctor_id" name="filter_doctor_id">
                                        <input type="hidden" id="filter_center_id" name="filter_center_id">
                                        <input type="hidden" id="filter_status_id" name="filter_status_id">
                                        <input type="hidden" id="filter_created_by_id" name="filter_created_by_id">
                                        <input type="hidden" id="filter_city_id" name="filter_city_id">
                                        <input type="hidden" id="filter_region_id" name="filter_region_id">
                                        <input type="hidden" id="filter_service_id" name="filter_service_id">
                                        <input type="hidden" id="filter_updated_by_id" name="filter_updated_by_id">
                                        <input type="hidden" id="filter_created_from_id" name="filter_created_from_id">
                                        <input type="hidden" id="filter_created_to_id" name="filter_created_to_id">
                                        <input type="hidden" id="filter_rescheduled_by_id" name="filter_rescheduled_by_id">
                                        <a id="appointment_exports_submit" class="btn btn-primary">
                                            <i class="la la-file-export"></i> Export
                                        </a>
                                    </form>
                            @endif
                        </div>
                    </div>

                    <div class="card-body appointment appointment-section">
                        @include('admin.appointments.treatment-filters', ['custom_reset' => 'custom_reset'])
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>

                    <div class="card-body appointment consultancy-section d-none">
                        @include('admin.appointments.consultancy.filters')
                        <div id="consultancy_calendar" style="position: relative">
                            <div class="appointment-loader-base" style="display: none;">
                                <div class="blockui"> <span>Please wait...</span>
                                    <span>
                                        <div class="spinner spinner-primary"></div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body appointment treatment-section d-none">
                        @include('admin.appointments.services.filters')
                        <div id="custom_treatment_resource_calendar" style="display: none; position: relative;">
                            <div class="appointment-loader-base" style="display: none;">
                                <div class="blockui"> <span>Please wait...</span>
                                    <span>
                                        <div class="spinner spinner-primary"></div>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div id="treatment_calendar" style="position: relative">
                            <div class="appointment-loader-base" style="display: none;">
                                <div class="blockui"> <span>Please wait...</span>
                                    <span>
                                        <div class="spinner spinner-primary"></div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--All forms popups--}}
    @include('admin.appointments.appointment-forms.modals')
    @push('js')
        <script>
            // Pass user role to JavaScript
            window.userRole = '{{ Auth::user()->getRoleNames()->first() ?? '' }}';
            window.canSendWhatsApp = {{ Auth::user()->hasRole('FDM') || Auth::user()->hasRole('Super-Admin') ? 'true' : 'false' }};
        </script>
        <script defer>
            $(document).ready(function () {
                var result = get_query();
            
                if (typeof result.tab !== 'undefined') {
                    $("." + result.tab+ '-tab').click();
                    // Show location dropdown if tab is treatment
                    if (result.tab === 'treatment') {
                        $(".treatment-location-header-dropdown").removeClass("d-none");
                        // Initialize select2 and load locations for treatment dropdown
                        setTimeout(function() {
                            $('#treatment_location_filter').select2({ width: '100%' });
                            if ($('#treatment_location_filter option').length <= 1) {
                                loadLocations('', 'treatment');
                            }
                        }, 300);
                    }
                } else {
                    $(".appointment-tab").addClass("nav-bar-active")
                }
                if (typeof result.city_id !== "undefined"
                    && typeof result.location_id !== "undefined"
                    && typeof result.doctor_id !== "undefined"
                    && typeof result.machine_id !== "undefined"
                    && typeof result.tab !== 'undefined') {
                    loadDoctors(result.location_id, result.tab);
                    setTimeout( function () {
                  
                        $("#treatment_city_filter option[value='"+result.city_id+"']").attr('selected','selected');
                        $("#treatment_city_filter").val(result.city_id).change();
                        setDashboardFilters();
                    }, 1300);
                } else {
                    setTimeout( function () {
                        setDashboardFilters();
                    }, 1300);
                }
            });
            $(document).on('click', '#appointment_exports_submit', function(e){
                e.preventDefault();
                $("#filtersform").submit();
            });
            let appointment_limit = '{{config('constants.export-appointment-limit')}}';
            var limit = '{{config('constants.export-appointment-limit')}}';
            var offset = 0;
            $(document).ready(function () {
                $("#appointment_exports").attr('href', route('admin.appointments.export', [limit, offset]));
            })
            function changeLimitOffset($this) {
                limit = parseInt(limit) + parseInt(appointment_limit);
                offset = parseInt(offset) + parseInt(appointment_limit);
                setTimeout( function () {
                    $this.attr('href', route('admin.appointments.export', [limit, offset]));
                },1000);
            }
        </script>
        <script>
            function SetFromdate(){
                $("#filter_date_from").val($("#treatment_search_start").val());
            }
            function SetTodate(){
                $("#filter_date_to").val($("#treatment_appoint_end").val());
            }
            function SetPhone()
             {
                $("#filter_phone").val($("#appoint_search_phone").val());
             }
            function SetDocId()
            {
                $("#filter_doctor_id").val($("#treatment_search_doctor").val());
            }
            function SetStatus()
            {
                $("#filter_status_id").val($("#treatment_search_status").val());
            }
            function SetCreated()
            {
                $("#filter_created_by_id").val($("#treatment_search_created_by").val());
            }
            function SetCenter()
            {
                $("#filter_center_id").val($("#treatment_search_centre").val());
            }
            function SetPatient()
            {
                $("#filter_patient_id").val($("#treatment_patient_id").val());
            }
            function SetCity(){
                $("#filter_city_id").val($("#treatment_search_city").val());
            }
            function SetRegion(){
                $("#filter_region_id").val($("#treatment_search_region").val());
            }
            function SetUpdatedBy(){
                $("#filter_updated_by_id").val($("#treatment_search_updated_by").val());
            }
            function SetRescheduledBy(){
                $("#filter_rescheduled_by_id").val($("#treatment_search_rescheduled_by").val());
            }
            function SetAdvanceFromdate(){
                $("#filter_created_from_id").val($("#treatment_search_created_from").val());
            }
            function SetAdvanceTodate(){
                $("#filter_created_to_id").val($("#treatment_search_created_to").val());
            }
            function SetService()
             {
                let service_value = $("#treatment_search_service").val();
                if (service_value.indexOf("bold-") !== -1) {
                    var service = service_value.split("bold-")[1];
                } else {
                    var service = service_value;
                }
                $("#filter_service_id").val(service);
             }

            function setDashboardFilters() {
                let result = get_query();
                if(result?.type != null ) {
                    $("#appoint_search_type").val('{{request('type')}}').change();
                    $("#treatment_search_start").val('{{request('from')}}');
                    $("#treatment_appoint_end").val('{{request('to')}}');
                    @php
                        $ids = explode(',', request('center_id'));
                    @endphp
                        @if (count($ids) == 1)
                            $("#treatment_search_centre").val('{{request('center_id')}}').change();
                        @endif

                    $("#treatment_search_status").val('{{request('appoint_status')}}').change();

                    datatable.search({
                        location_id: '{{request('center_id')}}',
                        appointment_type_id: '{{request('type')}}',
                        date_from: '{{request('from')}}',
                        date_to: '{{request('to')}}',
                        appointment_status_id: '{{request('appoint_status')}}',
                        filter: 'filter',
                    }, 'search');
                }
            }

            function getUserCity() {
                @if (auth()->id() != 1)
                    $.ajax({
                        url: '{{route('admin.users.get_cities')}}',
                        type: 'GET',
                        dataType: 'json',
                        success: function (response) {
                            if (response.status) {
                                $("#consultancy_city_filter").val(response.data.city).change();
                                $("#treatment_city_filter").val(response.data.city).change();
                                $("#appoint_search_city").val(response.data.city).change();
                               setTimeout( function () {
                                   getUserCentre();
                               }, 400);
                            }
                        },
                        error: function () {

                        }
                    });
                @endif
            }

            function getUserCentre() {
                $.ajax({
                    url: '{{route('admin.users.get_centers')}}',
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status) {
                            $("#consultancy_location_filter").val(response.data.center).change();
                            $("#treatment_location_filter").val(response.data.center).change();
                            $("#appoint_search_centre").val(response.data.center).change();
                        }
                    },
                    error: function () {
                    }
                });
            }

            // Auto-trigger calendar for single centre users (Treatment)
            function autoTriggerTreatmentCalendarForSingleCentre() {
                // Check if userCentres is defined and has exactly one centre
                if (typeof window.userCentres !== 'undefined' && window.userCentres.length === 1) {
                    var singleCentreId = window.userCentres[0];

                    // Check if treatment section is visible
                    var result = get_query();
                    var isTreatmentVisible = $('.treatment-section').is(':visible') && !$('.treatment-section').hasClass('d-none');

                    if (isTreatmentVisible || (typeof result.tab !== 'undefined' && result.tab === 'treatment')) {
                        // Only trigger if calendar is not already loaded
                        if ($('#treatment_location_filter').val() === '' || $('#treatment_location_filter').val() === null) {
                            // First, ensure the location dropdown is populated
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                url: route('admin.appointments.load_locations'),
                                type: 'POST',
                                data: {
                                    city_id: ''
                                },
                                cache: false,
                                success: function(response) {
                                    if (response.status && response.data.dropdown) {
                                        var dropdown_options = '';
                                        Object.entries(response.data.dropdown).forEach(function (dropdown) {
                                            dropdown_options += '<option value="'+dropdown[0]+'">'+dropdown[1]+'</option>';
                                        });
                                        $('#treatment_location_filter').html(dropdown_options);

                                        // Now set the value and trigger calendar
                                        setTimeout(function() {
                                            $("#treatment_location_filter").val(singleCentreId);
                                 
                                            loadDoctors(singleCentreId, 'treatment');
                                        }, 300);
                                    }
                                },
                                error: function(xhr, ajaxOptions, thrownError) {
                                    console.error('Failed to load locations for treatment auto-trigger');
                                }
                            });
                        }
                    }
                }
            }

            // Call auto-trigger on page load and when treatment tab is clicked
            $(document).ready(function() {
                // Trigger after a delay to ensure page is fully loaded
                setTimeout(function() {
                    autoTriggerTreatmentCalendarForSingleCentre();
                }, 800);
            });

            // Also trigger when treatment tab is clicked
            $(document).on('click', '.treatment-tab', function() {
                setTimeout(function() {
                    autoTriggerTreatmentCalendarForSingleCentre();
                }, 800);
            });

        </script>
        <script src="{{asset('assets/js/pages/appointment/invoice.js?v=1')}}"></script>
        <script src="{{asset('assets/js/pages/appointment/treatment-calendar.js?v=' . time())}}"></script>
        <script src="{{asset('assets/js/pages/appointments/referred-by-patient-search.js')}}"></script>

        <script src="{{asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js')}}"></script>
        <script src="{{asset('assets/js/pages/appointment/treatment-data.js?v=7')}}"></script>
        <script src="{{asset('assets/js/pages/crud/forms/validation/feedbacks/feedbacks.js')}}"></script>
        <script src="{{asset('assets/js/pages/crud/forms/validation/appointment/validation.js?v=2')}}"></script>
        <script src="{{asset('assets/js/pages/appointment/plan/create.js')}}"></script>
        <script src="{{asset('assets/js/pages/appointment/common.js?v=8')}}"></script>

    @endpush

    @push('datatable-js')
        <script src="{{asset('assets/js/pages/appointment/treatment-columns.js')}}?v=3"></script>
        <script src="{{asset('assets/js/pages/appointment/treatmentDatatable.js?v=2')}}"></script>
    @endpush

@endsection
