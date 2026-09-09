@extends('admin.layouts.master')

@section('content')
    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-plans.css') }}?v=2" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-plans-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">PLAN ID {{request('id')}}</h3>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{ route('admin.packages.index') }}" class="btn btn-dark">
                                <i class="la la-arrow-alt-circle-left"></i>
                                Back
                            </a>
                            @if (Gate::allows('plans_log_excel'))
                                <a href="{{ route('admin.packages.log', [ request('id'), 'excel']) }}" class="btn btn-primary">
                                    <i class="la la-file-export"></i>
                                    Excel
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('datatable-js')
        <script>
            var plane_id = "{{request('id')}}";
        </script>
        <script src="{{asset('assets/js/pages/admin_settings/plan-log.js')}}"></script>
    @endpush

@endsection
