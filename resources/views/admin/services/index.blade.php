@extends('admin.layouts.master')
@section('title', 'Services')
@section('content')
    @push('css')
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-services.css') }}?v=2" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-services-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Services</h3>
                        </div>
                        <div class="card-toolbar">
                            @if(Gate::allows('services_sort'))
                                <a id="sort-action" href="{{route('admin.services.sort_get')}}" class="btn btn-info">
                                    <i class="fa fa-sort-amount-up"></i>Sort
                                </a>
                            @endif
                            @if(Gate::allows('services_create'))
                                <a href="javascript:void(0);" onclick="createService('{{ route('admin.services.create') }}');" class="btn btn-primary" data-toggle="modal" data-target="#modal_add_services">
                                    <i class="la la-plus"></i>
                                    Add New
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        @include('admin.services.filters')
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_add_services" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="services_add">
            @include('admin.services.create')
        </div>
    </div>

    <div class="modal fade" id="modal_edit_services" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="services_edit">
            @include('admin.services.edit')
        </div>
    </div>

    <div class="modal fade" id="modal_service_instructions" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content svc-modal">
                <div class="modal-header svc-modal-header">
                    <h2 class="fw-bolder" style="font-size: 1.25rem; word-break: break-word;">Service Instructions</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-body svc-modal-body">
                    <div id="service_instructions_content">
                    </div>
                </div>
                <div class="svc-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @push('datatable-js')
        <script src="{{asset('assets/js/pages/admin_settings/services.js')}}?v=2"></script>
    @endpush

    @push('js')
        <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
        <script src="{{asset('assets/js/pages/crud/forms/validation/admin_settings/services.js')}}"></script>
        <script>
            function SetName()
            {
                $("#filter_service_name").val($("#search_name").val());

            }
            function SetStatus()
            {
                $("#filter_active").val($("#search_status").val());
            }
            function getColor()
            {
                var service = $('#add_parent_service').val();
                if(service > 0){
                    $.ajax({
                        type:'GET',
                        url:"{{route('admin.dashboard.getcolor')}}",
                        data:{'service':service},
                        success:function(data) {
                            $("#service_color").val(typeof normalizeServiceColor === 'function' ? normalizeServiceColor(data.color) : data.color);
                        }
                    });
                    $('.servicefield').show();
                    $('#endnode').attr('checked','checked');
                }else{
                    $('.servicefield').hide();
                }
            }
            function getEditColor()
            {
                var service = $('#edit_parent_service').val();
                if(service > 0){
                    $.ajax({
                        type:'GET',
                        url:"{{route('admin.dashboard.getcolor')}}",
                        data:{'service':service},
                        success:function(data) {
                            $("#edit_color").val(typeof normalizeServiceColor === 'function' ? normalizeServiceColor(data.color) : data.color);
                        }
                    });
                    $('.servicefield').show();
                }else{
                    $('.servicefield').hide();
                }
            }
        </script>
    @endpush
@endsection
