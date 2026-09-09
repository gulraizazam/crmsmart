@extends('admin.layouts.master')
@section('title', 'Doctors')
@section('content')

    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-doctors.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-doctors-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Doctors</h3>
                        </div>
                        <div class="card-toolbar">
                            @if(Gate::allows('doctors_create'))
                                <a href="javascript:void(0);" class="btn btn-primary" onclick="createUsers('{{ route('admin.doctors.create') }}');" data-toggle="modal" data-target="#modal_add_user">
                                    <i class="la la-plus"></i>
                                    Add New
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        @include('admin.doctors.filters')
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_add_user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="user-create">
            @include('admin.doctors.create')
        </div>
    </div>

    <div class="modal fade" id="modal_edit_user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="user-edit">
            @include('admin.doctors.edit')
        </div>
    </div>

    <div class="modal fade" id="change_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="change_password">
            @include('admin.doctors.change_password')
        </div>
    </div>

    <div class="modal fade" id="modal_allocate_discounts" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="discounts_allocate">
            @include('admin.doctors.allocate')
        </div>
    </div>

    @push('datatable-js')
        <script src="{{asset('assets/js/pages/admin_settings/doctors.js')}}?v=1"></script>
    @endpush

    @push('js')
        <script src="{{asset('assets/js/pages/crud/forms/validation/admin_settings/doctors.js')}}"></script>
    @endpush

@endsection
