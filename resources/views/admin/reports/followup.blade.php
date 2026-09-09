@extends('admin.layouts.master')
@section('title', 'Patients Follow Up Report')
@section('content')
    @push('css')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-reports.css') }}?v=4" rel="stylesheet" type="text/css" />
    @endpush
    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-reports-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Patients Follow Up Report</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mt-2 mb-7">
                            <div class="sneat-filter-row">
                                        <input type="hidden" id="patient_id_url" value="{{ Request::get('patient_id') }}">
                                        <input type="hidden" id="report_type_url" value="{{ Request::get('report_type') }}">

                                        <div class="form-group col-md-3 "
                                             id="report_type_div">
                                            {!! Form::label('report_type', 'Report Type:', ['class' => 'control-label']) !!}
                                            <select class="form-control " id="report_types" name="report_type">
                                                <option value="">Select Report</option>
                                                <option value="weekly">Unattended Payments</option>
                                                <option value="monthly">Overdue Treatments</option>
                                            </select>

                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Patient Search:</label>
                                            <input class="form-control filter-field appointment_patient_id">
                                            <input type="hidden" class="filter-field search_field" id="appointment_patient_id" value="">
                                            <input type="hidden" class="filter-field search_field" id="patient_id" value="">

                                            <div class="suggesstion-box" style="display: none;">
                                                <ul class="suggestion-list w-100">
                                                    <li onclick="selectLead(`Gulraiz azam`, `116197`, `lead_search_id`, `1`);">Gulraiz azam - 116197</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-3 sn-select @if($errors->has('location_id')) has-error @endif"
                                             id="locations">
                                            {!! Form::label('location_id', 'Centre:', ['class' => 'control-label']) !!}
                                            <select class="form-control select2" id="location_id" name="service_id">
                                                @if(Auth::user()->hasRole('FDM'))
                                                    @foreach($locations as $location)
                                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                                    @endforeach
                                                @else
                                                    <option value="">Select</option>
                                                    @foreach($locations as $location)
                                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <span id="location_id_handler"></span>
                                        </div>
                                        <div class="col-md-3 form-group sn-select @if($errors->has('date_range')) has-error @endif">
                                            {!! Form::label('date_range', 'Date:', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control']) !!}
                                            </div>
                                        </div>

                                        <div class="sneat-filter-actions">
                                            <a href="javascript:void(0);" onclick="loadPatientFollowUpReport($(this));" id="load_patient_foolow_up_report"
                                               class="btn btn-primary spinner-button">Load Report</a>
                                        </div>
                            </div>
                                        <div class="sneat-report-result" id="followup_content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.settings.edit')
    @push('datatable-js')
        <script src="{{asset('assets/js/pages/admin_settings/settings.js')}}"></script>
        <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
        <script src="{{asset('assets/js/dailyarrival.js')}}"></script>
    @endpush
@endsection
