@extends('admin.layouts.master')
@section('title', 'Patient History')
@section('content')
    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-plans.css') }}?v=3" rel="stylesheet" type="text/css" />
        <style>
            .form-control:disabled,
            .form-control[readonly] {
                background-color: #f5f5f9 !important;
                opacity: 1;
            }
        </style>
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-plans-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Patient History</h3>
                        </div>
                        <div class="card-toolbar sneat-plans-actions">
                            @if (Gate::allows('plans_create'))
                                <a href="javascript:void(0);" onclick="createPlan('{{ route('admin.packages.create') }}');" class="btn btn-primary" data-toggle="modal"
                                    data-target="#modal_add_plan">
                                    <i class="la la-plus"></i>
                                    Sell Services
                                </a>
                                <a href="javascript:void(0);" onclick="createBundle('{{ route('admin.packages.create') }}');" class="btn btn-success" data-toggle="modal"
                                    data-target="#modal_add_bundle">
                                    <i class="la la-plus"></i>
                                    Sell Package
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        @include('admin.packages.filters', ['custom_reset' => 'custom_reset'])
                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.packages.modals')

    @push('js')
        <script src="{{ asset('assets/js/pages/appointments/referred-by-patient-search.js') }}"></script>
        <script src="{{ asset('assets/js/pages/admin_settings/create-plan.js') }}?v=3"></script>
        <script src="{{ asset('assets/js/pages/admin_settings/create-bundle.js') }}"></script>
        <script src="{{ asset('assets/js/pages/admin_settings/create-membership.js') }}"></script>
        <script src="{{ asset('assets/js/pages/admin_settings/edit-bundle.js') }}"></script>
        <script src="{{ asset('assets/js/pages/crud/forms/validation/admin_settings/refunds.js') }}"></script>

        <script>
            function getUserCentre() {
                $.ajax({
                    url: '{{ route('admin.users.get_centers') }}',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status && response.data && response.data.centers) {
                            // Populate the search_location_id dropdown with centers
                            var locationOptions = '<option value="">All</option>';
                            Object.entries(response.data.centers).forEach(function([id, name]) {
                                locationOptions += '<option value="' + id + '">' + name + '</option>';
                            });
                            $("#search_location_id").html(locationOptions);

                            // Auto-select if only one center
                            var centerKeys = Object.keys(response.data.centers);
                            if (centerKeys.length === 1) {
                                $("#search_location_id").val(centerKeys[0]).change();
                                $("#add_plan_location_id").val(centerKeys[0]).change();
                                $("#add_bundle_location_id").val(centerKeys[0]).change();
                                $("#add_membership_location_id").val(centerKeys[0]).change();
                            }
                        }
                    },
                    error: function() {
                        console.error('Failed to load user centers');
                    }
                });
            }
        </script>
    @endpush

@endsection
