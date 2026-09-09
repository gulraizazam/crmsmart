@extends('admin.layouts.master')
@section('title', 'Consultations')
@section('content')
    @push('css')
        <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
    @endpush
    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                @include('admin.appointments.partials.consultancy-menu')
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label change-label">Consultancies</h3>

                            @php
                                $userCentres = $userCentres ?? [];
                                $showDropdown = count($userCentres) > 1;
                            @endphp

                            @if($showDropdown)
                            <div class="consultancy-location-header-dropdown d-none">
                                <select onchange="loadConsultantDoctors($(this).val(), 'consultancy');" class="form-control" id="consultancy_location_filter"></select>
                            </div>
                            @else
                            <div style="display: none;">
                                <select onchange="loadConsultantDoctors($(this).val(), 'consultancy');" class="form-control" id="consultancy_location_filter"></select>
                            </div>
                            @endif
                        </div>
                        <div class="card-toolbar">
                            @if (Gate::allows('appointments_export_today'))
                                <div class="export-appointments">
                                    <a id="today_consultancies"
                                        onclick="loadTodayAppointments('{{ date('Y-m-d') }}', 'consultancy');"
                                        href="javascript:void(0);" class="btn btn-info">
                                        Today Consultancies
                                    </a>
                                </div>
                            @endif
                            @if (Gate::allows('appointments_export'))
                                <div class="delete-records export-appointments">
                                    <form method="POST" action="download-filter-data" id="filtersform">
                                        @csrf
                                        <input type="hidden" id="filter_patient_id" name="filter_patient_id">
                                        <input type="hidden" id="filter_lead_id" name="filter_lead_id">
                                        <input type="hidden" id="filter_date_from" name="filter_date_from">
                                        <input type="hidden" name="appointmenttype" value="1">
                                        <input type="hidden" name="filter_phone" id="filter_phone">
                                        <input type="hidden" id="filter_date_to" name="filter_date_to">
                                        <input type="hidden" id="filter_doctor_id" name="filter_doctor_id">
                                        <input type="hidden" id="filter_center_id" name="filter_center_id">
                                        <input type="hidden" id="filter_status_id" name="filter_status_id">
                                        <input type="hidden" id="filter_city_id" name="filter_city_id">
                                        <input type="hidden" id="filter_service_id" name="filter_service_id">
                                        <input type="hidden" id="filter_region_id" name="filter_region_id">
                                        <input type="hidden" id="filter_consultancytype_id"
                                            name="filter_consultancytype_id">
                                        <input type="hidden" id="filter_updated_by_id" name="filter_updated_by_id">
                                        <input type="hidden" id="filter_created_from_id" name="filter_created_from_id">
                                        <input type="hidden" id="filter_created_to_id" name="filter_created_to_id">
                                        <input type="hidden" id="filter_rescheduled_by_id"
                                            name="filter_rescheduled_by_id">
                                        <a id="appointment_exports_submit" class="btn btn-primary">
                                            <i class="la la-file-export"></i> Export
                                        </a>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-body appointment appointment-section">
                        @include('admin.appointments.filters', ['custom_reset' => 'custom_reset'])
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>

                    <div class="card-body appointment consultancy-section d-none">
                        @include('admin.appointments.consultancy.filters')
                        <div id="custom_resource_calendar" style="display: none; position: relative;">
                            <div class="appointment-loader-base" style="display: none;">
                                <div class="blockui"> <span>Please wait...</span>
                                    <span>
                                        <div class="spinner spinner-primary"></div>
                                    </span>
                                </div>
                            </div>
                        </div>
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
                </div>
            </div>
        </div>
    </div>

    {{-- All forms popups --}}
    @include('admin.appointments.appointment-forms.modals')

    @push('js')
        <script>
            // Pass user role to JavaScript
            window.userRole = '{{ Auth::user()->getRoleNames()->first() ?? '' }}';
            window.canSendWhatsApp = {{ Auth::user()->hasRole('FDM') || Auth::user()->hasRole('Super-Admin') ? 'true' : 'false' }};

            let appointment_limit = '{{ config('constants.export-appointment-limit') }}';
            var limit = '{{ config('constants.export-appointment-limit') }}';
            var offset = 0;
            $(document).ready(function() {
                $("#appointment_exports").attr('href', route('admin.appointments.export', [limit, offset]));

            });
            $(document).on('click', '#appointment_exports_submit', function(e) {
                e.preventDefault();
                $("#filtersform").submit();

            });

            function changeLimitOffset($this) {
                limit = parseInt(limit) + parseInt(appointment_limit);
                offset = parseInt(offset) + parseInt(appointment_limit);
                setTimeout(function() {
                    $this.attr('href', route('admin.appointments.export', [limit, offset]));
                }, 1000);
            }

            function SetFromdate() {
                $("#filter_date_from").val($("#appoint_search_start").val());
            }

            function SetTodate() {
                $("#filter_date_to").val($("#appoint_appoint_end").val());
            }

            function SetDocId() {
                $("#filter_doctor_id").val($("#appoint_search_doctor").val());

            }

            function SetStatus() {
                $("#filter_status_id").val($("#appoint_search_status").val());

            }

            function SetCreated() {
                $("#filter_created_by_id").val($("#appoint_search_created_by").val());

            }

            function SetCenter() {
                $("#filter_center_id").val($("#appoint_search_centre").val());

            }

            function SetPatient() {
                $("#filter_patient_id").val($("#appoint_search_patient").val());
            }

            function SetLead() {
                $("#filter_lead_id").val($("#appoint_search_lead").val());

            }
            ///////advance filters////////
            function SetCity() {
                $("#filter_city_id").val($("#appoint_search_city").val());
            }

            function SetRegion() {
                $("#filter_region_id").val($("#appoint_search_region").val());
            }

            function SetConsultancyType() {
                $("#filter_consultancytype_id").val($("#appoint_search_consultancy_type").val());
            }

            function SetUpdatedBy() {
                $("#filter_updated_by_id").val($("#appoint_search_updated_by").val());
            }

            function SetRescheduledBy() {
                $("#filter_rescheduled_by_id").val($("#appoint_search_rescheduled_by").val());
            }

            function SetAdvanceFromdate() {
                $("#filter_created_from_id").val($("#appoint_search_created_from").val());
            }

            function SetAdvanceTodate() {
                $("#filter_created_to_id").val($("#appoint_search_created_to").val());
            }

            function SetService() {
                $("#filter_service_id").val($("#appoint_search_service").val());
            }

            function SetPhone() {
                $("#filter_phone").val($("#appoint_search_phone").val());
            }

            function changeAppointmentStatus() {
                var appointment_id = $("#appointment_id").val();
                var appointment_status_not_show = $("#appointment_status_not_show").val();
                var cancellation_reason_other_reason = $("#cancellation_reason_other_reason").val();
                $.ajax({
                    // headers: {
                    //     'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    // },
                    url: route('admin.appointments.storeappointmentstatus'),
                    type: "post",
                    data: {
                        id: appointment_id,
                        appointment_status_not_show: appointment_status_not_show,
                        cancellation_reason_other_reason: cancellation_reason_other_reason
                    },
                    cache: false,
                    success: function(response) {
                    
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        errorMessage(xhr);
                    }
                });

            }
        </script>
        <script defer>
            $(document).ready(function() {
                var result = get_query();
                if (typeof result.tab !== 'undefined') {
                    $("." + result.tab + '-tab').click();
                    // Show location dropdown if tab is consultancy
                    if (result.tab === 'consultancy') {
                        $(".consultancy-location-header-dropdown").removeClass("d-none");
                        // Initialize select2 and load locations for consultancy dropdown
                        setTimeout(function() {
                            $('#consultancy_location_filter').select2({ width: '100%' });
                            if ($('#consultancy_location_filter option').length <= 1) {
                                loadLocations('', 'consultancy');
                            }
                        }, 300);
                    }
                } else {
                    $(".appointment-tab").addClass("nav-bar-active")
                }
                if (typeof result.city_id !== "undefined" &&
                    typeof result.location_id !== "undefined" &&
                    typeof result.doctor_id !== "undefined" &&
                    typeof result.tab !== 'undefined') {
                    loadDoctors(result.location_id, result.tab);
                    setTimeout(function() {
                        $("#consultancy_city_filter option[value='" + result.city_id + "']").attr('selected',
                            'selected');
                        $("#consultancy_city_filter").val(result.city_id).change();
                        setDashboardFilters();
                    }, 1300);
                } else {
                    setTimeout(function() {
                        setDashboardFilters();
                    }, 1300);
                }

                // Auto-trigger calendar for users with single centre
                setTimeout(function() {
                    autoTriggerCalendarForSingleCentre();
                }, 800);

            });
        </script>
        <script>
            function setDashboardFilters() {
                let result = get_query();
                if (result?.type != null) {
                    $("#appoint_search_type").val('{{ request('type') }}').change();
                    $("#appoint_search_start").val('{{ request('from') }}');
                    $("#appoint_appoint_end").val('{{ request('to') }}');
                    @php
                        $ids = explode(',', request('center_id'));
                    @endphp
                    @if (count($ids) == 1)
                        $("#appoint_search_centre").val('{{ request('center_id') }}').change();
                    @endif

                    $("#appoint_search_status").val('{{ request('appoint_status') }}').change();

                    datatable.search({
                        location_id: '{{ request('center_id') }}',
                        appointment_type_id: '{{ request('type') }}',
                        date_from: '{{ request('from') }}',
                        date_to: '{{ request('to') }}',
                        appointment_status_id: '{{ request('appoint_status') }}',
                        filter: 'filter',
                    }, 'search');
                }
            }

            function getUserCity() {

                setTimeout(function() {

                    let city_value = $("#consultancy_city_filter").val();

                    if (city_value == null || city_value == '') {

                        @if (auth()->id() != 1)

                            $.ajax({
                                url: '{{ route('admin.users.get_cities') }}',
                                type: 'GET',
                                dataType: 'json',
                                success: function(response) {
                                    if (response.status) {
                                        $("#appoint_search_city").val(response.data.city).change();
                                        setTimeout(function() {
                                            getUserCentre();
                                        }, 400);
                                    }
                                },
                                error: function() {

                                }
                            });
                        @endif

                    }

                }, 500);

            }

            function getUserCentre() {
                $.ajax({
                    url: '{{ route('admin.users.get_centers') }}',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {

                        if (response.status) {
                            $("#consultancy_location_filter").val(response.data.center).change();
                            $("#treatment_location_filter").val(response.data.center).change();
                            $("#appoint_search_centre").val(response.data.center).change();
                        }
                    },
                    error: function() {

                    }
                });
            }

            // Auto-trigger calendar for single centre users
            function autoTriggerCalendarForSingleCentre() {
                // Check if userCentres is defined and has exactly one centre
                if (typeof window.userCentres !== 'undefined' && window.userCentres.length === 1) {
                    var singleCentreId = window.userCentres[0];

                    // Check if consultancy section is visible (either no tab param or consultancy tab)
                    var result = get_query();
                    var isConsultancyVisible = $('.consultancy-section').is(':visible') && !$('.consultancy-section').hasClass('d-none');

                    if (isConsultancyVisible || (typeof result.tab === 'undefined' || result.tab === 'consultancy')) {
                        // Only trigger if calendar is not already loaded
                        if ($('#consultancy_location_filter').val() === '' || $('#consultancy_location_filter').val() === null) {
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
                                        $('#consultancy_location_filter').html(dropdown_options);

                                        // Now set the value and trigger calendar
                                        setTimeout(function() {
                                            $("#consultancy_location_filter").val(singleCentreId);
                                      
                                            loadConsultantDoctors(singleCentreId, 'consultancy');
                                        }, 300);
                                    }
                                },
                                error: function(xhr, ajaxOptions, thrownError) {
                                    console.error('Failed to load locations for auto-trigger');
                                }
                            });
                        }
                    }
                }
            }

        </script>
        <script src="{{ asset('assets/js/pages/appointment/invoice.js?v=1') }}"></script>
        <script src="{{ asset('assets/js/pages/appointment/consultancy-calendar.js') }}?v={{ @filemtime(public_path('assets/js/pages/appointment/consultancy-calendar.js')) }}"></script>
        <script src="{{ asset('assets/js/pages/appointments/referred-by-patient-search.js') }}"></script>

        <script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
        <script src="{{ asset('assets/js/pages/appointment/consultancy-data.js') }}"></script>
        {{-- <script src="{{asset('assets/js/pages/appointment/treatment-data.js')}}"></script> --}}

        <script src="{{ asset('assets/js/pages/crud/forms/validation/appointment/validation.js') }}"></script>
        <script src="{{ asset('assets/js/pages/appointment/plan/create.js') }}"></script>
        <script src="{{ asset('assets/js/pages/appointment/common.js?v=8') }}"></script>
    @endpush

    @push('datatable-js')
        <script src="{{ asset('assets/js/pages/appointment/consultation-columns.js') }}?v=4"></script>
        <script src="{{ asset('assets/js/pages/appointment/consultation-common.js') }}"></script>
        <script src="{{ asset('assets/js/pages/appointment/datatable.js') }}"></script>
    @endpush

@endsection
