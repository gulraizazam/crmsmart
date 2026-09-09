@extends('admin.layouts.master')
@section('title', 'General Revenue Reports')
@section('content')
    @push('css')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-reports.css') }}?v=5" rel="stylesheet" type="text/css" />
        <style>
            .daterangepicker .drp-calendar th.month .monthselect,
            .daterangepicker .drp-calendar th.month .yearselect {
                background: #fff !important;
                border: 1px solid #ccc !important;
                border-radius: 4px;
                padding: 2px 4px;
                cursor: pointer;
                font-weight: 600;
                font-size: 13px;
                appearance: auto !important;
                -webkit-appearance: menulist !important;
                -moz-appearance: menulist !important;
            }
            .daterangepicker .drp-calendar th.month .yearselect:hover,
            .daterangepicker .drp-calendar th.month .monthselect:hover {
                border-color: #696cff !important;
                background: rgba(105, 108, 255, 0.08) !important;
            }
        </style>
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-reports-page" id="kt_content">

        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">General Sales Reports</h3>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="mt-2 mb-7">
                            <div class="sneat-filter-row">
                                            @if(Auth::user()->hasRole('FDM'))
                                            <div class="form-group col-md-3 sn-select @if($errors->has('date_range')) has-error @endif">
                                                {!! Form::label('date_range_fdm', 'Date Range*', ['class' => 'control-label']) !!}
                                                <div class="input-group">

                                                    {!! Form::text('date_range', null, ['id' => 'date_range_fdm', 'class' => 'form-control','disabled']) !!}
                                                </div>
                                            </div>
                                            @else
                                            <div class="form-group col-md-3 sn-select @if($errors->has('date_range')) has-error @endif">
                                                {!! Form::label('date_range', 'Date Range*', ['class' => 'control-label']) !!}
                                                <div class="input-group">

                                                    {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            @endif
                                            <div class="form-group col-md-3 sn-select @if($errors->has('report_type')) has-error @endif">
                                                {!! Form::label('report_type', 'Report Type*', ['class' => 'control-label']) !!}
                                                <select name="report_type" id="report_type" style="width:100%" class="form-control select2">
                                                    @if(Gate::allows('finance_general_revenue_reports_center_performance_stats_by_revenue_finance'))
                                                        <option value="default">Select a Report Type</option>
                                                    @endif
                                                    
                                                   
                                                   

                                                    

                                                  
                                                   
                                                   
                                                    
                                                    @if(Gate::allows('finance_general_revenue_reports_general_revenue__detail_report'))
                                                        <option value="general_revenue_report_detail">Sales Detail Report</option>
                                                    @endif
                                                    @if(Gate::allows('finance_general_revenue_reports_general_revenue__summary_report'))
                                                        <option value="general_revenue_report_summary">Sales Summary Report</option>
                                                    @endif
                                                   
                                                         @if(Gate::allows('finance_general_revenue_reports_conversion_report'))
                                                            <option value="gender_wise_revenue">Gender Wise Revenue Report
                                                            </option>
                                                        @endif
                                                </select>

                                                <span id="report_type_handler"></span>
                                            </div>
                                            {{--<div class="d-none form-group col-md-3 sn-select @if($errors->has('patient_id')) has-error @endif"
                                                 id="patient_id_E">
                                                {!! Form::label('patient_id', 'Patient', ['class' => 'control-label']) !!}
                                                <select name="patient_id" id="patient_id" class="form-control patient_id"></select>
                                                <span id="patient_id_handler"></span>
                                            </div>--}}
                                            <div class="form-group col-md-3 sn-select @if($errors->has('appointment_type_id')) has-error @endif"
                                                 id="appointment_type_id_E">
                                                {!! Form::label('appointment_type_id', 'Appointment Type', ['class' => 'control-label']) !!}
                                                {!! Form::select('appointment_type_id', $appointment_types, null, ['onchange' => 'getDiscounts()','id' => 'appointment_type_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                                                <span id="appointment_type_id_handler"></span>
                                            </div>
                                            <div class="form-group col-md-3 @if($errors->has('discount_id')) has-error @endif" id="discount"
                                                 style="display: none;">
                                            </div>
                                            <div class="form-group col-md-3 sn-select @if($errors->has('city_id')) has-error @endif"
                                                 id="city_id_E">
                                                {!! Form::label('city_id', 'City', ['class' => 'control-label']) !!}
                                                {!! Form::select('city_id', $cities, null, ['onchange' => 'getCenters($(this));', 'id' => 'city_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                                                <span id="city_id_handler"></span>
                                            </div>
                                            <div class="form-group col-md-3 sn-select @if($errors->has('location_id')) has-error @endif"
                                                 id="location_id_E">
                                                {!! Form::label('location_id', 'Centres', ['class' => 'control-label']) !!}
                                                {!! Form::select('location_id', $locations, (Auth::user()->hasRole('FDM')) ? array_keys($locations->toArray()) : null, [ 'id' => 'location_id', 'style' => 'width: 100%;', 'class' => 'form-control select2 sn-select', 'multiple']) !!}
                                                <span id="location_id_handler"></span>
                                            </div>
                                            <div class="form-group col-md-3 sn-select @if($errors->has('machine')) has-error @endif"
                                                 id="machine"
                                                 style="display: none;">
                                            </div>

                                            <div class="form-group col-md-3 sn-select @if($errors->has('location_id')) has-error @endif"
                                                 style="display: none;" id="location_id_D" onchange="SetLocation()">
                                                {!! Form::label('location_id_com', 'Centres', ['class' => 'control-label']) !!}

                                                {!! Form::select('location_id_com[]', $locations_com, (Auth::user()->hasRole('FDM')) ? array_keys($locations_com->toArray()) : null, ['id' => 'location_id_com','class' => 'form-control select2', 'multiple' => 'multiple']) !!}
                                                <span id="location_id_handler"></span>
                                            </div>
                                                <div class="form-group col-md-3 sn-select @if($errors->has('location_id')) has-error @endif"
                                                 style="display: none;" id="gender_id" >
                                                {!! Form::label('gender_id', 'Gender', ['class' => 'control-label']) !!}

                                                <select class="form-control select2" id="gender_id_form" name="gender_id">
                                                    <option value="all">All</option>
                                                    <option value="1">Male</option>
                                                    <option value="2">Female</option>
                                                </select>
                                                <span id="location_id_handler"></span>
                                            </div>
                                            {!! Form::hidden('medium_type', 'web', ['id' => 'medium_type']) !!}
                                            <div class="form-group col-md-3 sn-select @if($errors->has('service_id')) has-error @endif"
                                                 id="service_id_E">
                                                {!! Form::label('service_id', 'Services', ['class' => 'control-label']) !!}
                                                <select class="form-control select2" id="service_id" name="service_id">
                                                    <option value="">All</option>
                                                    @foreach($services as $id => $service)
                                                        @if ($id == 0) @continue; @endif
                                                        @if($id < 0)
                                                            @php($tmp_id = ($id * -1))
                                                        @else
                                                            @php($tmp_id = ($id * 1))
                                                        @endif
                                                        <option value="@if($id < 0){{ ($id * -1) }}@else{{ $id }}@endif">@if($id < 0)
                                                                <b>{!! $service['name'] !!}</b>@else{!! $service['name'] !!}@endif</option>
                                                    @endforeach
                                                </select>
                                                <span id="service_id_handler"></span>
                                            </div>

                                            <div class="form-group col-md-3 sn-select @if($errors->has('appointment_type_id')) has-error @endif"
                                                 id="user_id_E">
                                                {!! Form::label('user_id', 'Employee', ['class' => 'control-label']) !!}
                                                {!! Form::select('user_id', $users, null, ['id' => 'user_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                                                <span id="user_id_handler"></span>
                                            </div>
                                            <div class="form-group col-md-3 sn-select @if($errors->has('doctor_id')) has-error @endif"
                                                 style="display: none" id="doctors_id">
                                                {!! Form::label('doctor_id', 'Doctor', ['class' => 'control-label']) !!}
                                                {!! Form::select('doctor_id', $operators, null, ['id' => 'doctor_id', 'style' => 'width: 100%;', 'class' => 'form-control select2']) !!}
                                                <span id="doctor_id_handler"></span>
                                            </div>
                                            <div class="sneat-filter-actions">
                                                <a href="javascript:void(0);" onclick="loadReport($(this));" id="load_report"
                                                   class="btn btn-primary spinner-button">Load Report</a>
                                            </div>
                            </div>

                                        <div class="sneat-report-result" id="content"></div>

                                            {!! Form::open(['method' => 'POST', 'target' => '_blank', 'route' => ['admin.reports.account_sales_report_load'], 'id' => 'report-form']) !!}
                                            {!! Form::hidden('date_range', null, ['id' => 'date_range-report']) !!}
                                            {!! Form::hidden('patient_id', null, ['id' => 'patient_id-report']) !!}
                                            {!! Form::hidden('appointment_type_id', null, ['id' => 'appointment_type_id-report']) !!}
                                            {!! Form::hidden('city_id', null, ['id' => 'city_id-report']) !!}
                                            <span id="location_id_report_container"></span>
                                            {!! Form::hidden('location_id_com', null, ['id' => 'location_id_com-report']) !!}
                                            {!! Form::hidden('region_id', null, ['id' => 'region_id-report']) !!}
                                            {!! Form::hidden('service_id', null, ['id' => 'service_id-report']) !!}
                                            {!! Form::hidden('user_id', null, ['id' => 'user_id-report']) !!}
                                            {!! Form::hidden('doctor_id', null, ['id' => 'doctor_id-report']) !!}
                                            {!! Form::hidden('machine_id', null, ['id' => 'machine_id-report']) !!}
                                            {!! Form::hidden('medium_type', null, ['id' => 'medium_type-report']) !!}
                                            {!! Form::hidden('report_type', null, ['id' => 'report_type-report']) !!}
                                            {!! Form::hidden('converted', null, ['id' => 'converted_type-report']) !!}
                                            {!! Form::hidden('discount_id', '', ['id' => 'discount_id-report']) !!}
                                            {!! Form::hidden('gender_id', null, ['id' => 'gender_id-report']) !!}
                                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.settings.edit')

    @push('js')
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>

   <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
   <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

        <script>
            $("#location_id_com").on('change',function(){
                $("#location_id_com-report").val($("#location_id_com").val());
            });
            $(document).ready(function() {
                if ($('#date_range').data('daterangepicker')) {
                    $('#date_range').data('daterangepicker').remove();
                }
                var minYearVal = parseInt(moment().format('YYYY')) - 10;
                var maxYearVal = parseInt(moment().format('YYYY'));

                $('#date_range').daterangepicker({
                    showDropdowns: true,
                    linkedCalendars: false,
                    minDate: moment().subtract(10, 'years').startOf('year'),
                    minYear: minYearVal,
                    maxYear: maxYearVal,
                    locale: {
                    },
                    ranges   : {
                        'Today'       : [moment(), moment()],
                        'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month'  : [moment().startOf('month'), moment().endOf('month')],
                        'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                        'This Year'  : [moment().startOf('year'), moment().endOf('year')],
                        'Last Year'  : [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                    },
                    startDate: moment().startOf('month'),
                    endDate  :  moment().endOf('month')
                });

                function fixYearDropdowns() {
                    var picker = $('#date_range').data('daterangepicker');
                    if (!picker) return;
                    picker.container.find('.yearselect').each(function() {
                        var $sel = $(this);
                        var currentVal = parseInt($sel.val());
                        var existingYears = [];
                        $sel.find('option').each(function() { existingYears.push(parseInt($(this).val())); });
                        if (existingYears.length >= (maxYearVal - minYearVal + 1)) return;
                        $sel.empty();
                        for (var y = minYearVal; y <= maxYearVal; y++) {
                            $sel.append('<option value="' + y + '"' + (y === currentVal ? ' selected' : '') + '>' + y + '</option>');
                        }
                    });
                }

                var pickerContainer = $('#date_range').data('daterangepicker').container[0];
                var observer = new MutationObserver(function() { fixYearDropdowns(); });
                observer.observe(pickerContainer, { childList: true, subtree: true });

                $('#date_range').on('show.daterangepicker', function() { fixYearDropdowns(); });
            });
            $('#date_range_fdm').daterangepicker({
                locale: {
                },
                ranges   : {
                    'Today' : [moment(), moment()],

                },
                startDate: moment(),
                endDate  :  moment()
            });

            var loadReport = function (that) {

                if (typeof that.prop("disabled") !== 'undefined' && that.prop("disabled") === true) {
                    return false;
                }
                var date_ranges;
                if($("#date_range_fdm").val()!=undefined){

                    date_ranges = $("#date_range_fdm").val();
                }else{

                    date_ranges = $("#date_range").val();
                }

                showSpinner();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: route('admin.reports.account_sales_report_load'),
                    type: "POST",
                    data: {

                        date_range: date_ranges,
                        patient_id: $('#patient_id').val(),
                        appointment_type_id: $('#appointment_type_id').val(),
                        location_id: $('#location_id').val(),
                        location_id_com: $('#location_id_com').val(),
                        region_id: $('#region_id').val(),
                        service_id: $('#service_id').val(),
                        user_id: $('#user_id').val(),
                        doctor_id: $('#doctor_id').val(),
                        medium_type: $('#medium_type').val(),
                        report_type: $('#report_type').val(),
                        city_id: $('#city_id').val(),
                        machine_id: $('#machine_id').val(),
                        discount_id:$('#discount_id').val(),
                        gender_id:$('#gender_id_form').val(),
                    },
                    success: function(response){
                        $('#content').html('');

                        if($('#medium_type').val() == 'web') {
                            $('#content').html(response);
                        } else {
                            return false;
                            // loadChart(response.start_date, response.end_date, response.SaleData);
                        }

                        hideSpinner();
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        hideSpinner();
                        return false;
                    }
                });
            }

            var printReport = function (medium_type) {

                $('#date_range-report').val($('#date_range').val());

                $('#date_range_by-report').val($('#date_range_by').val());
                $('#date_range_by_first-report').val($('#date_range_by_first').val());
                $('#patient_id-report').val($('#patient_id').val());
                $('#scheduled_date-report').val($('#scheduled_date').val());
                $('#doctor_id-report').val($('#doctor_id').val());
                $('#city_id-report').val($('#city_id').val());
                $('#region_id-report').val($('#region_id').val());
                // Dynamically add multiple hidden fields for location_id array
                $('#location_id_report_container').empty();
                var selectedLocations = $('#location_id').val();
                if (selectedLocations && selectedLocations.length) {
                    $.each(selectedLocations, function(i, val) {
                        $('#location_id_report_container').append('<input type="hidden" name="location_id[]" value="' + val + '">');
                    });
                }
                $('#service_id-report').val($('#service_id').val());
                $('#appointment_status_id-report').val($('#appointment_status_id').val());
                $('#appointment_type_id-report').val($('#appointment_type_id').val());
                $('#consultancy_type-report').val($('#consultancy_type').val());
                $('#user_id-report').val($('#user_id').val());
                $('#re_user_id-report').val($('#re_user_id').val());
                $('#up_user_id-report').val($('#up_user_id').val());
                $('#referred_by-report').val($('#referred_by').val());
                $('#medium_type-report').val(medium_type);
                $('#report_type-report').val($('#report_type').val());
                $('#gender_id-report').val($('#gender_id_form').val());
                $('#report-form').submit();
              
            }


            $(document).on('change', '#report_type', function () {
                var type_p = $("#report_type").val();
                $('#city_id_E').hide();
                if (type_p == 'general_revenue_report_detail') {
                    $("#patient_id_E").hide();
                    $("#appointment_type_id_E").hide();
                    $("#location_id_D").show();
                    $("#location_id_E").hide();
                    $("#user_id_E").hide();
                    $("#service_id_E").hide();
                    $("#region_id_E").hide();
                    $("#doctors_id").hide();
                    $("#machine").hide();
                    $('#discount').hide();
                     $('#gender_id').show();
                } else if (type_p == 'daily_employee_stats_summary') {
                    $("#machine").hide();
                    $('#discount').hide();
                    $("#doctors_id").hide();
                    $("#user_id_E").hide();
                    $("#appointment_type_id_E").show();
                    $("#patient_id_E").show();
                    $("#location_id_E").show();
                    $("#location_id_D").hide();
                    $("#service_id_E").show();
                     $('#gender_id').hide();
                } else if (type_p == 'general_revenue_report_summary') {
                    $("#patient_id_E").hide();
                    $("#appointment_type_id_E").hide();
                    $("#location_id_D").hide();
                    $("#location_id_E").show();
                    $("#user_id_E").hide();
                    $("#service_id_E").hide();
                    $("#region_id_E").show();
                    $('#gender_id').hide();
                    $("#doctors_id").hide();
                    $("#machine").hide();
                    $('#discount').hide();
                } else if (type_p == 'conversion_report') {
                    $("#location_id_E").show();
                    $("#location_id_D").hide();
                    $("#patient_id_E").show();
                    $("#user_id_E").hide();
                    $("#doctors_id").show();
                    $("#region_id_E").show();
                    $("#city_id_E").show();
                    $("#service_id_E").show();
                    $("#appointment_type_id_E").hide();
                    $("#machine").hide();
                    $('#discount').hide();
                    $('#gender_id').hide();
                } else if (type_p == 'services_sold') {
                    $("#location_id_E").show();
                    $("#location_id_D").hide();

                    $("#service_id_E").show();
                    $('#gender_id').hide();
                } else if (type_p == "collection_by_service") {
                    $("#location_id_E").show();
                    $("#location_id_D").hide();
                    $("#patient_id_E").hide();
                    $("#user_id_E").hide();
                    $("#doctors_id").hide();
                    $('#gender_id').hide();
                    $("#region_id_E").show();
                    $("#city_id_E").hide();
                    $("#service_id_E").hide();
                    $("#appointment_type_id_E").hide();
                    $("#machine").hide();
                    $('#discount').hide();
                } else if (type_p == 'daily_employee_stats') {
                    $("#patient_id_E").show();
                    $("#appointment_type_id_E").show();
                    $("#location_id_D").hide();
                    $("#location_id_E").show();
                    $("#user_id_E").hide();
                    $("#service_id_E").show();
                    $("#region_id_E").hide();
                    $("#doctors_id").show();
                    $('#gender_id').hide();
                    $("#machine").hide();
                    $('#discount').hide();
                } else {
                    $("#location_id_E").show();
                    $("#location_id_D").hide();
                    $("#patient_id_E").hide();
                    $('#gender_id').hide();
                    $("#user_id_E").hide();
                    $("#doctors_id").hide();
                    $("#region_id_E").show();
                    $("#city_id_E").hide();
                    $("#service_id_E").hide();
                    $("#appointment_type_id_E").hide();
                    $("#machine").hide();
                    $('#discount').hide();
                }
            });
            $('#report_type').change();


            function getCenters(that) {

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: route('admin.appointments.load_locations'),
                    type: "POST",
                    data: {
                        city_id: that.val(),
                    },
                    success: function(response){
                        if (response.status) {
                            let dropdown_options = '<option value="">All</option>';
                            let dropdowns = response.data.dropdown;

                            Object.entries(dropdowns).forEach(function (dropdown) {
                                dropdown_options += '<option value="'+dropdown[0]+'">'+dropdown[1]+'</option>';
                            });

                            $("#location_id").html(dropdown_options);
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        return false;
                    }
                });

            }

        </script>


    @endpush

@endsection
