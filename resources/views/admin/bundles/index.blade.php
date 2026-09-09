@extends('admin.layouts.master')
@section('title', 'Bundles List')
@section('content')

    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-services.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-bundles-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Bundles List</h3>
                        </div>
                        <div class="card-toolbar">
                            @if(Gate::allows('packages_create'))
                                <a href="javascript:void(0);" class="btn btn-primary" id="create-btn" data-toggle="modal" data-target="#modal_bundles">
                                    <i class="la la-plus"></i>
                                    Create Bundle
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        @include('admin.bundles.filters')
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_bundles" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="region-create">
            @include('admin.bundles.create')
        </div>
    </div>
    <div class="modal fade" id="modal_details_bundles" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="region-edit">
            @include('admin.bundles.detail')
        </div>
    </div>

    @push('datatable-js')
        <script src="{{asset('assets/js/pages/admin_settings/bundles.js')}}"></script>
    @endpush

    @push('js')
        <script src="{{asset('assets/js/pages/crud/forms/validation/admin_settings/bundles.js')}}"></script>
    @endpush

@endsection
