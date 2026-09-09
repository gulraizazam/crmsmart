@extends('admin.layouts.master')
@section('title', 'Patients')
@section('content')

    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-patients.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-patients-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Patients</h3>
                        </div>
                        <div class="card-toolbar">
                            {{-- @if(Gate::allows('patients_create'))
                                <a href="javascript:void(0);" onclick="createPatient('{{ route('admin.patients.create') }}');" class="btn btn-primary" data-toggle="modal" data-target="#modal_add_patients">
                                    <i class="la la-plus"></i>
                                    Add New
                                </a>
                            @endif --}}
                        </div>
                    </div>

                    <div class="card-body">
                        @include('admin.patients.filters')
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_add_patients" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="patients_add">
            @include('admin.patients.create')
        </div>
    </div>

    <div class="modal fade" id="modal_edit_patients" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="edit_patients">
            @include('admin.patients.edit')
        </div>
    </div>

    <div class="modal fade" id="modal_edit_memberships" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="edit_memberships">
            @include('admin.patients.assignmembership')
        </div>
    </div>

    <div class="modal fade" id="modal_edit_vouchers" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="edit_vouchers">
            @include('admin.patients.assignVoucher')
        </div>
    </div>

    <div class="modal fade" id="modal_add_referral" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="add_referral">
            @include('admin.patients.addreferral')
        </div>
    </div>

    <div class="modal fade" id="modal_import_leads" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="import_leads">
            @include('admin.leads.import')
        </div>
    </div>

    @push('js')
        <script src="{{asset('assets/js/pages/crud/forms/validation/patients/patient.js')}}"></script>
    @endpush

    @push('datatable-js')
        <script src="{{asset('assets/js/pages/patients/patient.js')}}?v=1"></script>
    @endpush

@endsection
