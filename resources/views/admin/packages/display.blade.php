<!--begin::Modal content-->
<div class="modal-content">
    <div class="modal-header" id="kt_modal_password_header">
        <h2 class="fw-bolder rota-title">View Plan</h2>
        <div class="btn btn-icon btn-sm btn-active-icon-primary popup-close" data-kt-users-modal-action="close">
            <span class="svg-icon svg-icon-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                </svg>
            </span>
        </div>
    </div>
    <div class="modal-body scroll-y">

        <div class="d-flex flex-column scroll-y" id="kt_modal_plans_scroll">

            <div class="form-group sneat-plan-meta">
                <div class="sneat-plan-meta-item">
                    <label>Patient</label>
                    <strong id="user_name"></strong>
                </div>
                <div class="sneat-plan-meta-item">
                    <label>Membership</label>
                    <strong id="membership_name"></strong>
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
                                <th>Discount</th>
                                <th>Subtotal</th>
                                <th>Tax</th>
                                <th>Total</th>
                                <th>Consumed</th>
                                <th>Consumed At</th>
                                <th>Sold By</th>
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
                    <h4>History</h4>
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
                    <a id="package_pdf" class="btn btn-primary hidden-print" target="_blank" href="">Print
                        <i class="fa fa-print"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Modal content-->
