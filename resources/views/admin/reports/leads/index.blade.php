@extends('admin.layouts.master')
@section('title', 'Lead Reports')
@section('content')
    @push('css')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-reports.css') }}?v=5" rel="stylesheet" type="text/css" />
    @endpush
    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-reports-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Lead Reports</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mt-2 mb-7">
                            <div class="sneat-filter-row">
                                <div class="form-group col-md-3 sn-select">
                                    <label class="control-label" for="report_type">Report</label>
                                    <select id="report_type" name="report_type" class="form-control select2" style="width: 100%;">
                                        @foreach ($reportTypes as $value => $label)
                                            <option value="{{ $value }}" {{ $value === 'source' ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 sn-select">
                                    <label class="control-label" for="date_range">Date range</label>
                                    <input type="text" id="date_range" name="date_range" class="form-control" autocomplete="off">
                                </div>
                                <div class="form-group col-md-3 sn-select">
                                    <label class="control-label" for="city_id">City</label>
                                    <select id="city_id" name="city_id[]" class="form-control select2" multiple style="width: 100%;">
                                        @foreach ($cities as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 sn-select">
                                    <label class="control-label" for="location_id">Centre</label>
                                    <select id="location_id" name="location_id[]" class="form-control select2" multiple style="width: 100%;">
                                        @foreach ($locations as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 sn-select">
                                    <label class="control-label" for="lead_source_id">Lead source</label>
                                    <select id="lead_source_id" name="lead_source_id" class="form-control select2" style="width: 100%;">
                                        <option value="">All</option>
                                        @foreach ($sources as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 sn-select">
                                    <label class="control-label" for="lead_status_id">Status</label>
                                    <select id="lead_status_id" name="lead_status_id" class="form-control select2" style="width: 100%;">
                                        <option value="">All</option>
                                        @foreach ($statuses as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 sn-select">
                                    <label class="control-label" for="assigned_to">Agent</label>
                                    <select id="assigned_to" name="assigned_to" class="form-control select2" style="width: 100%;">
                                        <option value="">All</option>
                                        @foreach ($agents as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 sn-select">
                                    <label class="control-label" for="created_by">Created by</label>
                                    <select id="created_by" name="created_by" class="form-control select2" style="width: 100%;">
                                        <option value="">All</option>
                                        @foreach ($creators as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 sn-select">
                                    <label class="control-label" for="department_id">Department</label>
                                    <select id="department_id" name="department_id" class="form-control select2" style="width: 100%;">
                                        <option value="">All</option>
                                        @foreach ($departments as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3 sn-select">
                                    <label class="control-label" for="gender">Gender</label>
                                    <select id="gender" name="gender" class="form-control select2" style="width: 100%;">
                                        <option value="">All</option>
                                        <option value="1">Male</option>
                                        <option value="2">Female</option>
                                    </select>
                                </div>
                                <div class="sneat-filter-actions">
                                    <a href="javascript:void(0);" id="load_lead_report" class="btn btn-primary spinner-button">Load Report</a>
                                </div>
                            </div>
                            <div class="sneat-report-result" id="content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('js')
        <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
        <script src="{{ asset('assets/js/pages/leads/reports.js') }}?v={{ @filemtime(public_path('assets/js/pages/leads/reports.js')) }}"></script>
    @endpush
@endsection
