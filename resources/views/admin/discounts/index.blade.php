@extends('admin.layouts.master')
@section('title', 'Discounts')
@section('content')

    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-services.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-discounts-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Discounts</h3>
                        </div>
                        <div class="card-toolbar">
                            @if(Gate::allows('discounts_create'))
                                <a href="javascript:void(0);" onclick="createDiscount('{{ route('admin.discounts.create') }}');" class="btn btn-primary" data-toggle="modal" data-target="#modal_add_discounts">
                                    <i class="la la-plus"></i>
                                    Add Discount
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        @include('admin.discounts.filters')
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_add_discounts" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup medium_modal" id="discounts_add">
            @include('admin.discounts.create')
        </div>
    </div>

    <div class="modal fade" id="modal_edit_discounts" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup medium_modal" id="discounts_edit">
            @include('admin.discounts.edit')
        </div>
    </div>

    <div class="modal fade" id="modal_allocate_discounts" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" id="discounts_allocate">
            @include('admin.discounts.allocate')
        </div>
    </div>

    @push('datatable-js')
        <script src="{{asset('assets/js/pages/admin_settings/discounts.js')}}?v={{time()}}"></script>
    @endpush

    @push('js')
        <script src="{{asset('assets/js/pages/crud/forms/validation/admin_settings/discounts.js')}}"></script>
    @endpush

@endsection
