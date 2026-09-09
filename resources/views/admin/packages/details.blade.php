@extends('admin.layouts.master')
@section('title', 'Plan Details')
@section('content')
    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-plans.css') }}?v=2" rel="stylesheet" type="text/css" />
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
                            <h3 class="card-label">Plan Details</h3>
                        </div>
                        <div class="card-toolbar">
                            @if (Gate::allows('plans_edit'))
                                <a href="javascript:void(0);" onclick="editRow('{{ $url }}');" class="btn btn-primary" data-toggle="modal" data-target="#modal_add_plan">
                                    <i class="la la-pencil"></i>
                                    Edit
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column scroll-y" id="kt_modal_plans_scroll">
                            <div class="form-group sneat-plan-meta">
                                <div class="sneat-plan-meta-item">
                                    <label>Patient</label>
                                    <strong id="user_name"></strong>
                                </div>
                                <div class="sneat-plan-meta-item">
                                    <label>Centre</label>
                                    <strong id="location_name"></strong>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="table-responsive">
                                    <table id="plans_service" class="table table-bordered table-advance">
                                        <thead>
                                            <tr>
                                                <th>Service Name</th>
                                                <th>Regular Price</th>
                                                <th>Discount Name</th>
                                                <th>Type</th>
                                                <th>Discount Value</th>
                                                <th>Subtotal</th>
                                                <th>Tax</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="display_plans"></tbody>
                                    </table>
                                </div>
                                <div class="sneat-plan-total invoice-block">
                                    <ul class="list-unstyled amounts">
                                        <li>
                                            <strong>Total:</strong> <span class="package_total_price"></span>/-
                                        </li>
                                    </ul>
                                </div>
                                <div class="table-responsive">
                                    <h4 class="sneat-plan-history-title">History</h4>
                                    <table id="plan_history" class="table table-bordered table-advance">
                                        <thead>
                                            <tr>
                                                <th>Payment Mode</th>
                                                <th>Cash Flow</th>
                                                <th>Cash Amount</th>
                                                <th>Created At</th>
                                            </tr>
                                        </thead>
                                        <tbody class="plan_history">
                                            <tr>
                                                <td id="payment_mode"></td>
                                                <td id="cash_flow"></td>
                                                <td id="cash_amount"></td>
                                                <td id="Created At"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="sneat-plan-actions">
                                    <a class="btn btn-outline-dark" href="/admin/packages">
                                        <i class="fa fa-arrow-left" aria-hidden="true"></i> Back To Plans
                                    </a>
                                    <a id="package_pdf" class="btn btn-primary hidden-print" target="_blank" href="">Print
                                        <i class="fa fa-print"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal_edit_plan" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered very-big-modal" id="packages_edit">
                @include('admin.packages.edit')
            </div>
        </div>
    </div>

    @push('js')
        <script src="{{ asset('assets/js/pages/admin_settings/create-plan.js') }}?v=2"></script>
        <script src="{{ asset('assets/js/pages/crud/forms/validation/admin_settings/refunds.js') }}"></script>

        <script>
            $(document).ready(function() {
                id = '{{ $id }}';
                url = route('admin.packages.display', {
                    id: id
                });
                viewPlan(url);
            });

            function getUserCentre() {
                $.ajax({
                    url: '{{ route('admin.users.get_centers') }}',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            $("#search_location_id").val(response.data.center).change();
                            $("#add_plan_location_id").val(response.data.center).change();
                        }
                    },
                    error: function() {

                    }
                });
            }
        </script>
    @endpush

@endsection
