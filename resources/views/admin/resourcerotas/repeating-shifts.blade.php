@extends('admin.layouts.master')
@section('title', 'Set Repeating Shifts')
@section('content')

    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-schedule.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-schedule-page" id="kt_content">

        <div class="d-flex flex-column-fluid">
            <div class="container-fluid py-6 sneat-page">
                
                <!--begin::Header-->
                <div class="mb-8">
                    <h1 class="page-header-title" id="page_title">Set Repeating Shifts</h1>
                    <p class="page-header-subtitle">Set weekly, biweekly or custom shifts. Changes saved will apply to all upcoming shifts for the selected period. <a href="javascript:void(0);">Learn more</a></p>
                </div>
                <!--end::Header-->

                <div class="row">
                    <!--begin::Left Column - Settings-->
                    <div class="col-lg-4 col-md-5">
                        <!--begin::Location Card-->
                        <div class="card location-card mb-5">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="location-icon mr-4">
                                        <i class="la la-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <div class="location-name" id="location_name">Location</div>
                                        <div class="location-address" id="location_address">Address</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Location Card-->

                        <!--begin::Settings Card-->
                        <div class="card settings-card mb-5">
                            <div class="card-body">
                                <div class="form-group mb-5">
                                    <label class="form-label-custom">Schedule type</label>
                                    <select class="form-control form-control-custom" id="schedule_type">
                                        <option value="every_week">Every week</option>
                                        <option value="every_2_weeks">Every 2 weeks</option>
                                        <option value="every_3_weeks">Every 3 weeks</option>
                                        <option value="every_4_weeks">Every 4 weeks</option>
                                    </select>
                                </div>

                                <div class="form-group mb-5">
                                    <label class="form-label-custom">Start date</label>
                                    <input type="text" class="form-control form-control-custom" id="schedule_start_date" name="start_date" readonly>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="form-label-custom">End date</label>
                                    <input type="text" class="form-control form-control-custom" id="schedule_end_date" name="end_date" readonly>
                                </div>
                            </div>
                        </div>
                        <!--end::Settings Card-->

                        <!--begin::Info Card-->
                        <div class="card info-card">
                            <div class="card-body py-4">
                                <div class="d-flex align-items-start">
                                    <i class="la la-info-circle info-icon mr-3" style="margin-top: 2px;"></i>
                                    <span class="info-text">Team members will not be scheduled on business closed periods.</span>
                                </div>
                            </div>
                        </div>
                        <!--end::Info Card-->
                    </div>
                    <!--end::Left Column-->

                    <!--begin::Right Column - Weekly Schedule-->
                    <div class="col-lg-8 col-md-7">
                        <div class="card weekly-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-5">
                                    <div>
                                        <h5 class="weekly-title mb-1">Weekly</h5>
                                        <span class="weekly-hours" id="total_hours_display">0 hours total</span>
                                    </div>
                                </div>

                                <!--begin::Days Schedule-->
                                <div id="days_schedule_container">
                                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $index => $day)
                                    <div class="day-schedule-row {{ $index < 6 ? 'border-bottom' : '' }}" data-day="{{ strtolower($day) }}">
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <label class="d-flex align-items-center cursor-pointer mb-0">
                                                    <span class="day-checkbox mr-3">
                                                        <input type="checkbox" class="day-enabled" {{ $index < 6 ? 'checked' : '' }}>
                                                        <span class="checkbox-box"></span>
                                                    </span>
                                                    <span class="day-name">{{ $day }}</span>
                                                </label>
                                                <div class="day-hours-label day-hours">{{ $index < 6 ? '9h' : '' }}</div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="day-shifts-container {{ $index >= 6 ? 'd-none' : '' }}">
                                                    <div class="shift-time-row d-flex align-items-center mb-2">
                                                        <select class="form-control time-select shift-start mr-2">
                                                            <!-- Options populated by JS -->
                                                        </select>
                                                        <span class="time-separator mx-2">to</span>
                                                        <select class="form-control time-select shift-end mr-3">
                                                            <!-- Options populated by JS -->
                                                        </select>
                                                        <button type="button" class="btn-add-time add-shift-time mr-2" title="Add shift">
                                                            <i class="la la-plus"></i>
                                                        </button>
                                                        <button type="button" class="btn-remove-time remove-shift-time" title="Remove" style="display: none;">
                                                            <i class="la la-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="not-working-label {{ $index < 6 ? 'd-none' : '' }}">Not working</div>
                                            </div>
                                            <div class="col-md-1 d-flex align-items-start pt-1">
                                                <button type="button" class="btn-copy-all copy-to-all-days {{ $index >= 6 ? 'd-none' : '' }}" title="Copy to all days">
                                                    <i class="la la-copy"></i> Copy to all
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <!--end::Days Schedule-->
                            </div>
                        </div>
                    </div>
                    <!--end::Right Column-->
                </div>

                <!--begin::Action Buttons-->
                <div class="d-flex justify-content-end mt-8 mb-4">
                    <a href="{{ route('admin.resourcerotas.schedule') }}" class="btn btn-light btn-lg mr-3 px-6">Cancel</a>
                    <button type="button" class="btn btn-primary btn-lg px-6" id="btn_save_repeating_shifts">Save</button>
                </div>
                <!--end::Action Buttons-->

            </div>
            <!--end::Container-->
        </div>
        <!--end::Entry-->
    </div>
    <!--end::Content-->


    @push('js')
    <script>
        var resourceId = {{ request()->get('resource_id', 0) }};
        var resourceName = '{{ request()->get('resource_name', 'Resource') }}';
        var locationId = {{ request()->get('location_id', 0) }};
        var locationName = '{{ request()->get('location_name', 'Location') }}';
        var selectedDate = '{{ request()->get('date', date('Y-m-d')) }}';
        var defaultStartTime = '{{ request()->get('start_time', '10:00am') }}';
        var defaultEndTime = '{{ request()->get('end_time', '7:00pm') }}';
    </script>
    <script src="{{asset('assets/js/pages/admin_settings/repeating-shifts.js')}}?v={{ time() }}"></script>
    @endpush

@endsection
