@extends('admin.layouts.master')
@section('title', 'Users')
@section('content')

    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-users.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-users-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Users</h3>
                        </div>
                        <div class="card-toolbar">
                            @if(Gate::allows('users_create'))
                                <a href="javascript:void(0);" onclick="createUsers('{{ route('admin.users.create') }}');" class="btn btn-primary" data-toggle="modal" data-target="#modal_add_user">
                                    <i class="la la-plus"></i>
                                    Add New
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        @include('admin.users.filters')
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_add_user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="user-create">
            @include('admin.users.create')
        </div>
    </div>

    <div class="modal fade" id="modal_edit_user" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="user-edit">
            @include('admin.users.edit')
        </div>
    </div>

    <div class="modal fade" id="change_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="change_password">
        </div>
    </div>

    @push('datatable-js')
        <script src="{{asset('assets/js/pages/users/user.js')}}?v=1"></script>
    @endpush

    @push('js')
        <script src="{{asset('assets/js/pages/crud/forms/validation/users/validate.js')}}"></script>
        <script src="{{asset('assets/js/pages/crud/forms/validation/users/change-validate.js')}}"></script>

        <script>
            function getUserCentre() {
                $.ajax({
                    url: '{{route('admin.users.get_centers')}}',
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status) {
                            $("#search_center").val(response.data.center).change();
                        }
                    },
                    error: function () {

                    }
                });
            }
        </script>
    @endpush

@endsection
