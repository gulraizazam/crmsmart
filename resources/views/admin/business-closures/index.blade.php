@extends('admin.layouts.master')
@section('title', 'Business Closed Periods')
@section('content')

    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-schedule.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-schedule-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Business Closed Periods</h3>
                        </div>
                        <div class="card-toolbar">
                            @if(Gate::allows('business_closures_create'))
                                <a href="javascript:void(0);" onclick="openAddModal();" class="btn btn-primary" data-toggle="modal" data-target="#modal_add_business_closure">
                                    <i class="la la-plus"></i>
                                    Add New
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        @include('admin.business-closures.filters')
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_add_business_closure" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            @include('admin.business-closures.create')
        </div>
    </div>

    <div class="modal fade" id="modal_edit_business_closure" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            @include('admin.business-closures.edit')
        </div>
    </div>

    @push('datatable-js')
        <script>
            var changePages = 100;
        </script>
        <script src="{{asset('assets/js/pages/admin_settings/business-closures.js')}}?v={{ time() }}"></script>
    @endpush

@endsection
