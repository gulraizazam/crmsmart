@extends('admin.layouts.master')
@section('title', 'Scheduling Shifts')
@section('content')

    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-schedule.css') }}?v=2" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-schedule-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Scheduling Shifts</h3>
                        </div>
                        <div class="card-toolbar">
                            @if(Gate::allows('resourcerotas_create'))
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button" id="addDropdownBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Add
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="addDropdownBtn">
                                        <a class="dropdown-item" href="javascript:void(0);" id="btn_add_time_off_global">
                                            <i class="la la-clock mr-2"></i>Time off
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0);" id="btn_add_business_closed">
                                            <i class="la la-ban mr-2"></i>Business closed period
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger" href="javascript:void(0);" id="btn_bulk_delete_shifts">
                                            <i class="la la-trash mr-2"></i>Delete Shifts
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="sneat-schedule-toolbar">
                            <div class="filterouterdiv mb-0">
                                <label>Location <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="filter_location_id">
                                </select>
                            </div>
                            {{-- Resource Type field commented out
                            <div class="filterouterdiv mb-0">
                                <label>Resource Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="filter_resource_type">
                                    <option value="2" selected>Doctor</option>
                                    <option value="1">Machine</option>
                                </select>
                            </div>
                            --}}
                            <input type="hidden" id="filter_resource_type" value="2">
                            <div class="sneat-week-nav" role="group" aria-label="Week">
                                <button type="button" class="btn btn-icon btn-light" id="btn_prev_week" aria-label="Previous week">
                                    <i class="la la-angle-left"></i>
                                </button>
                                <span id="week_range_display">This week</span>
                                <button type="button" class="btn btn-icon btn-light" id="btn_next_week" aria-label="Next week">
                                    <i class="la la-angle-right"></i>
                                </button>
                            </div>
                            <button type="button" class="btn btn-primary sneat-schedule-today" id="btn_today">Today</button>
                        </div>

                        <!--begin::Schedule Calendar-->
                        <div class="schedule-calendar-wrapper">
                            <div class="table-responsive">
                                <table class="table table-bordered schedule-calendar" id="schedule_calendar">
                                    <thead>
                                        <tr class="bg-light">
                                            <th class="team-member-header" style="min-width: 180px;">
                                                <div class="font-weight-bold">Team member</div>
                                            </th>
                                            <th class="day-header" data-day="0"></th>
                                            <th class="day-header" data-day="1"></th>
                                            <th class="day-header" data-day="2"></th>
                                            <th class="day-header" data-day="3"></th>
                                            <th class="day-header" data-day="4"></th>
                                            <th class="day-header" data-day="5"></th>
                                            <th class="day-header" data-day="6"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="schedule_body">
                                        <tr>
                                            <td colspan="8" class="text-center py-10">
                                                <div class="spinner spinner-primary spinner-lg"></div>
                                                <div class="mt-3">Loading schedule...</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--end::Schedule Calendar-->
                    </div>
                </div>
                <!--end::Card-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Entry-->
    </div>
    <!--end::Content-->

    <!-- Bulk Delete Shifts Modal -->
    <div class="modal fade" id="modal_bulk_delete_shifts" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content sch-modal">
                <div class="modal-header sch-modal-header">
                    <h4 class="modal-title font-weight-bold">Delete Shifts</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body sch-modal-body">
                    <p class="text-muted mb-4">Select a date range and resource to delete all shifts within that period.</p>
                    
                    <form id="form_bulk_delete_shifts">
                        <div class="form-group">
                            <label>Resource <span class="text-danger">*</span></label>
                            <select class="form-control" id="bulk_delete_resource_id" name="resource_id" required>
                                <option value="">Select Resource</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Start Date <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bulk_delete_start_date" name="start_date" placeholder="Select start date" required readonly>
                        </div>
                        <div class="form-group">
                            <label>End Date <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bulk_delete_end_date" name="end_date" placeholder="Select end date" required readonly>
                        </div>
                        <div class="alert alert-warning mb-0" id="bulk_delete_warning" style="display: none;">
                            <i class="la la-exclamation-triangle mr-2"></i>
                            <span id="bulk_delete_warning_text"></span>
                        </div>
                    </form>
                </div>
                <div class="sch-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="btn_confirm_bulk_delete">
                        <i class="la la-trash"></i> Delete Shifts
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Shift Modal -->
    <div class="modal fade" id="modal_add_shift" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content sch-modal">
                <div class="modal-header sch-modal-header">
                    <h4 class="modal-title font-weight-bold" id="add_shift_title">Add Shift</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body sch-modal-body">
                    <p class="text-muted mb-4">You are editing this day's shifts only. To set repeating shifts, go to <a href="javascript:void(0);" class="text-primary" onclick="$('#modal_add_shift').modal('hide'); handleShiftAction('repeating-shifts');">scheduled shifts</a>.</p>
                    
                    <form id="form_add_shift">
                        <input type="hidden" id="shift_resource_id" name="resource_id">
                        <input type="hidden" id="shift_date" name="date">
                        <input type="hidden" id="shift_location_id" name="location_id">
                        
                        <div id="shift_rows_container">
                            <div class="shift-row mb-3" data-row="0">
                                <div class="row align-items-end">
                                    <div class="col-md-5">
                                        <label class="mb-2">Start time</label>
                                        <select class="form-control shift-start-time" name="shifts[0][start_time]">
                                            <!-- Time options will be populated by JS -->
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="mb-2">End time</label>
                                        <select class="form-control shift-end-time" name="shifts[0][end_time]">
                                            <!-- Time options will be populated by JS -->
                                        </select>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <button type="button" class="btn btn-icon btn-light-danger btn-sm remove-shift-row" style="display: none;">
                                            <i class="la la-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn_add_shift_row">
                                <i class="la la-plus-circle mr-1"></i> Add shift
                            </button>
                            <span class="text-muted" id="total_shift_duration">Total shift duration: 0h</span>
                        </div>
                    </form>
                </div>
                <div class="sch-footer">
                    <div class="sch-footer-split">
                        <button type="button" class="btn btn-icon btn-outline-danger rounded-circle" id="btn_delete_all_shifts" title="Delete all shifts" style="display: none;" onclick="deleteAllShifts()">
                            <i class="la la-trash"></i>
                        </button>
                        <div class="ml-auto">
                            <button type="button" class="btn btn-light mr-2" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="btn_save_shift">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Time Off Modal -->
    <div class="modal fade" id="modal_add_time_off" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content sch-modal">
                <div class="modal-header sch-modal-header d-flex justify-content-between align-items-start">
                    <h4 class="modal-title font-weight-bold">Add time off</h4>
                    <button type="button" class="btn btn-icon btn-sm btn-active-icon-primary" data-dismiss="modal" aria-label="Close">
                        <i class="la la-times"></i>
                    </button>
                </div>
                <div class="modal-body sch-modal-body">
                    <form id="form_add_time_off">
                        <input type="hidden" id="time_off_location_id" name="location_id">
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="mb-2 font-weight-bold">Team member</label>
                                <select class="form-control" id="time_off_resource_id" name="resource_id">
                                    <!-- Options populated by JS -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-5">
                                <label class="mb-2 font-weight-bold">Start date</label>
                                <input type="text" class="form-control datepicker" id="time_off_start_date" name="start_date" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="mb-2 font-weight-bold">Start time</label>
                                <select class="form-control" id="time_off_start_time" name="start_time">
                                    <!-- Options populated by JS -->
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="mb-2 font-weight-bold">End time</label>
                                <select class="form-control" id="time_off_end_time" name="end_time">
                                    <!-- Options populated by JS -->
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="d-flex align-items-center cursor-pointer">
                                <span class="time-off-checkbox mr-3">
                                    <input type="checkbox" id="time_off_repeat" name="repeat">
                                    <span class="checkbox-box"></span>
                                </span>
                                <span class="font-weight-bold" style="font-size: 15px;">Repeat</span>
                            </label>
                        </div>
                        
                        <div class="row mb-4" id="repeat_until_row" style="display: none;">
                            <div class="col-md-5">
                                <label class="mb-2 font-weight-bold">Repeat until</label>
                                <input type="text" class="form-control datepicker" id="time_off_repeat_until" name="repeat_until" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="font-weight-bold mb-0">Description</label>
                                <span class="text-muted" id="description_counter">0/100</span>
                            </div>
                            <textarea class="form-control" id="time_off_description" name="description" rows="3" maxlength="100" placeholder="Add description or note (optional)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="sch-footer">
                    <button type="button" class="btn btn-light px-6" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-6" id="btn_save_time_off">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!--begin::Edit Business Closure Modal-->
    <div class="modal fade" id="modal_edit_closure" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content sch-modal">
                <div class="modal-header sch-modal-header">
                    <h5 class="modal-title font-weight-bold" id="edit_closure_modal_title">Edit closed period</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body sch-modal-body">
                    <input type="hidden" id="edit_closure_id" value="">
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="mb-2 font-weight-bold">Start date</label>
                            <input type="text" class="form-control" id="edit_closure_start_date" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="mb-2 font-weight-bold">End date</label>
                            <input type="text" class="form-control" id="edit_closure_end_date" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="mb-2 font-weight-bold">Title</label>
                            <input type="text" class="form-control" id="edit_closure_title" placeholder="Enter title">
                        </div>
                    </div>
                    
                    
                </div>
                <div class="sch-footer">
                    <div class="sch-footer-split">
                        <span class="text-muted" id="closure_duration"></span>
                        <div>
                            <button type="button" class="btn btn-icon btn-light-danger mr-2" onclick="deleteClosure()" title="Delete">
                                <i class="la la-trash" style="font-size: 25px;"></i>
                            </button>
                            <button type="button" class="btn btn-primary px-6" onclick="saveClosureEdit()">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Edit Business Closure Modal-->

    <!--begin::Delete Confirmation Modal-->
    <div class="modal fade" id="modal_delete_closure_confirm" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content sch-modal">
                <div class="modal-header sch-modal-header">
                    <h5 class="modal-title font-weight-bold">Delete Closed Period</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i class="ki ki-close"></i>
                    </button>
                </div>
                <div class="modal-body sch-modal-body">
                    <p class="mb-3">Are you sure you want to delete this closed period?</p>
                    <div class="bg-light-danger rounded p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="la la-calendar text-danger mr-2" style="font-size: 18px;"></i>
                            <span class="font-weight-bold" id="delete_closure_dates"></span>
                        </div>
                        <div class="text-muted" style="font-size: 13px;" id="delete_closure_duration"></div>
                    </div>
                </div>
                <div class="sch-footer">
                    <button type="button" class="btn btn-light px-6" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-6" id="btn_confirm_delete_closure">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Delete Confirmation Modal-->


    @push('js')
        <script src="{{asset('assets/js/pages/admin_settings/resourcerotas.js')}}"></script>
        <script src="{{asset('assets/js/pages/crud/forms/validation/admin_settings/resourcerotas.js')}}"></script>
        <script src="{{asset('assets/js/pages/admin_settings/schedule-calendar.js')}}?v={{ time() }}"></script>
    @endpush

@endsection
