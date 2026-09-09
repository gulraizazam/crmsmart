@extends('admin.layouts.master')
@section('title', 'Feedback Report')
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
                            <h3 class="card-label">Doctor Ratings Report</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mt-2 mb-7">
                            <div class="sneat-filter-row">
                                    <div class="form-group col-md-3 sn-select @if($errors->has('centre_id')) has-error @endif"
                                             id="locations">
                                            {!! Form::label('location_id', 'Centre:', ['class' => 'control-label']) !!}
                                            <select class="form-control select2" id="centre_id" name="centre_id" onchange="getCentreDoctors(this.value);">
                                                <option value="">Select Centre</option>
                                                @foreach($locations as $location)
                                                <option value="{{$location->id}}">{{$location->name}}</option>
                                                @endforeach
                                            </select>

                                            <span id="centre_id_handler"></span>
                                        </div>
                                        <div class="form-group col-md-2 sn-select @if($errors->has('doctor_id')) has-error @endif"
                                             id="doc_dropdown" >
                                            {!! Form::label('doctor_id', 'Doctor:', ['class' => 'control-label']) !!}
                                            <select class="form-control select2" id="feedback_doctor_id_filter" name="doctor_id">
                                            <option value="">Select Doctor</option>
                                                @foreach($Users as $user)
                                                <option value="{{$user->id}}">{{$user->name}}</option>
                                                @endforeach
                                            </select>
                                            <span id="centre_id_handler"></span>
                                        </div>
                                        <div class="form-group col-md-2 sn-select @if($errors->has('service_id')) has-error @endif"
                                             id="service_dropdown" >
                                            {!! Form::label('service_id', 'Service:', ['class' => 'control-label']) !!}
                                            <select class="form-control select2" id="service_id_filter" name="service_id">
                                            <option value="">Select Service</option>
                                                @foreach($services as $service)
                                                <option value="{{$service->id}}">{{$service->name}}</option>
                                                @endforeach
                                            </select>
                                            <span id="centre_id_handler"></span>
                                        </div>
                                        <div class="col-md-3 form-group sn-select @if($errors->has('date_range')) has-error @endif">
                                            {!! Form::label('date_range', 'Date*', ['class' => 'control-label']) !!}
                                            <div class="input-group">
                                                {!! Form::text('date_range', null, ['id' => 'date_range_ratings', 'class' => 'form-control']) !!}
                                            </div>
                                        </div>



                                        <div class="sneat-filter-actions">
                                            <a href="javascript:void(0);" onclick="loadFeedbackReport($(this));" id="load_feedback_report"
                                               class="btn btn-primary spinner-button">Load Report</a>
                                        </div>
                            </div>
                                        <div class="sneat-report-result" id="feedback_content">
                                        <div id="ratings_report">


                                        <table id="ratings_table" class="display">
                                            <thead>
                                                <tr>

                                                <th>Doctor</th>
                                                <th>Average Rating (out of 10)</th>
                                                <th>Total Feedbacks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($feedbacks as $row)
                                                    <tr>
                                                        @if(isset($row->doctor))
                                                            <td><a href="{{ route('admin.feedback.view', $row->doctor->id) }}" style="color: black";> {{$row->doctor->name ?? 'N/A' }}</a></td>
                                                        @endif



                                                        <td>{{ number_format($row->avg_rating, 2) }}</td>
                                                        <td>{{ $row->total_feedbacks ?? 0 }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                        </div>
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
        <script>
            $("#ratings_table").DataTable({
                searching: false,     // Disable search box
                paging: false,        // Disable pagination
                info: false           // Disable "Showing X of Y entries" info text (optional)
            });
</script>
    @endpush
@endsection
