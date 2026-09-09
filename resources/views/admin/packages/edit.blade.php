<!--begin::Modal content-->
<style>
    .ep-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .ep-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .ep-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .ep-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .ep-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .ep-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .ep-modal-close:hover { background: rgba(255,255,255,0.25); }
    .ep-modal-body { padding: 24px 28px; background: #fff; }
    .ep-modal-body .alert { border-radius: 8px; font-size: 13px; padding: 10px 16px; border: none; }
    .ep-modal-body .alert-danger { background: #FEF2F2; color: #991B1B; }
    .ep-modal-body .alert-success { background: #F0FDF4; color: #166534; }
    .ep-section { background: #F8F6F1; border: 1px solid #E8EDE5; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .ep-section-title { font-size: 13px; font-weight: 700; color: #3D4A35; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .ep-section-title::before { content: ''; width: 4px; height: 16px; background: linear-gradient(180deg, #007bff, #C4A265); border-radius: 2px; }
    .ep-modal-body label { font-size: 12px; font-weight: 600; color: #007bff; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .ep-modal-body .form-control, .ep-modal-body .form-control-solid { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 9px 14px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff; }
    .ep-modal-body .form-control:focus, .ep-modal-body .form-control-solid:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .ep-modal-body .select2-container--default .select2-selection--single { border: 1px solid #D5DDD0; border-radius: 8px; height: 38px; }
    .ep-modal-body .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; color: #3D4A35; padding-left: 14px; }
    .ep-modal-body .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .ep-btn-add { background: linear-gradient(135deg, #C4A265, #D4B87A); border: none; border-radius: 8px; padding: 9px 24px; font-weight: 600; font-size: 13px; color: #3D4A35; transition: all 0.3s; cursor: pointer; }
    .ep-btn-add:hover { background: linear-gradient(135deg, #B8954F, #C4A265); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(196,162,101,0.3); color: #3D4A35; }
    .ep-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #E8EDE5; border-radius: 10px; overflow: hidden; }
    .ep-table thead th { background: #F4F7F2; color: #007bff; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 14px; border-bottom: 2px solid #E8EDE5; white-space: nowrap; }
    .ep-table tbody td { padding: 10px 14px; font-size: 13px; color: #3D4A35; border-bottom: 1px solid #E8EDE5; }
    .ep-table tbody tr:last-child td { border-bottom: none; }
    .ep-table tbody tr:hover { background: #FAFDF8; }
    .ep-summary { background: linear-gradient(135deg, #F8F6F1 0%, #F4F7F2 100%); border: 1px solid #E8EDE5; border-radius: 12px; padding: 20px; margin-top: 20px; }
    .ep-history { margin-top: 24px; }
    .ep-history-title { font-size: 14px; font-weight: 700; color: #3D4A35; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .ep-history-title::before { content: ''; width: 4px; height: 16px; background: linear-gradient(180deg, #C4A265, #007bff); border-radius: 2px; }
    .ep-footer { display: flex; align-items: center; justify-content: center; gap: 12px; padding-top: 20px; margin-top: 8px; border-top: 2px solid #E8EDE5; }
    .ep-btn-cancel { background: #fff; border: 1px solid #D5DDD0; border-radius: 8px; padding: 10px 28px; font-weight: 600; font-size: 13px; color: #007bff; transition: all 0.2s; cursor: pointer; }
    .ep-btn-cancel:hover { background: #F8F6F1; border-color: #7A8B6A; }
    .ep-btn-save { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 8px; padding: 10px 32px; font-weight: 600; font-size: 13px; color: #fff; transition: all 0.3s; cursor: pointer; }
    .ep-btn-save:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
<div class="modal-content ep-modal">
    <div class="ep-modal-header" id="kt_modal_password_header">
        <div class="ep-modal-header-top">
            <h2 class="ep-modal-title">Edit Plan</h2>
            <div class="ep-modal-close popup-close"
                 data-kt-users-modal-action="close"
                 onclick="resetVoucherEdit(event); return false;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="ep-modal-body scroll-y">
        <div id="edit_duplicateErr" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Duplicate record found, please select another one.
        </div>
        <div id="edit_successMessage" class="alert alert-success display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Plan successfully created
        </div>
        <div id="edit_inputfieldMessage" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Kindly enter required fields or you enter wrong value.
        </div>
        <div id="edit_wrongMessage" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Something went wrong!
        </div>
        <div id="edit_consume" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            This plan includes a service that is consumed and cannot be deleted.
        </div>
        <div id="edit_percentageMessage" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Your discount limit exceeded.
        </div>
        <div id="edit_AlreadyExitMessage" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Unable to enter same service with different price.
        </div>
        <div id="casesetteled" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            This plan is settled out and cannot accommodate any additional treatments.
        </div>
        <div id="casesetteledamount" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            This plan is settled out and cannot receive any further payments.
        </div>
        <div id="edit_datanotexist" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            That center not have any service.
        </div>
        <div id="edit_DiscountRange" class="alert alert-danger" style="display: none;">
            <button class="close" data-close="alert"></button>
            Your discount limit exceeded.
        </div>

        <form id="update_plane_form">
            <input type="hidden" name="random_id_1" id="edit_random_id_1" class="form-control">
            <input type="hidden" name="random_id" id="edit_random_id" class="form-control">
            <input type="hidden" name="slug" id="slug" class="form-control">
            <input type="hidden" id="edit_parent_id" name="parent_id">
            <input type="hidden" id="edit_location_id" name="location_id">
            <input type="hidden" name="slug_1" id="edit_slug_1" class="form-control">
            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_discounts_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                <div class="ep-section">
                    <div class="ep-section-title">Patient & Appointment</div>
                    <div class="form-group">
                        <div class="row">
                            <div class="fv-row col-md-2 mt-2 select2-search">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Patient Name</label>
                                <h3 id="edit-patient-name" style="font-size: 15px; font-weight: 700; color: #3D4A35; margin: 0;"></h3>
                            </div>
                            <div class="fv-row col-md-2 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Membership </label>
                                <h4 id="edit-membership-name" style="font-size:14px; color: #3D4A35; margin: 0;"></h4>
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Location</label>
                                <h3 id="edit-location-name" style="font-size: 15px; font-weight: 700; color: #3D4A35; margin: 0;"></h3>
                            </div>

                            <div class="fv-row col-md-4 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Appointment <span class="text text-danger">*</span></label>
                                <select id="edit_appointment_id" onchange="checkAppointmentVal()" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="appointment_id" required>
                                    <option value="">Select Appointment</option>
                                </select>
                                <small class="text-danger error-class"><b id='edit_appointment_id_error' class="error-msg"></b></small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ep-section">
                    <div class="ep-section-title">Service Details</div>
                    <div class="form-group">
                        <div class="row">
                            <div class="fv-row col-md-4 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Services <span class="text text-danger">*</span></label>
                                <select id="edit_service_id" onchange="editServiceDiscount($(this));" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="service_id">
                                    <option value="">Select Service</option>
                                </select>
                                <small class="text-danger error-class"><b id='service_id' class="error-msg"></b></small>
                            </div>

                            <div class="fv-row col-md-4 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Discounts/Vouchers</label>
                                <select id="edit_discount_id" onchange="editDiscountInfo($(this));" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="discount_id">
                                    <option value="">Select Discount</option>
                                </select>
                            </div>

                            <div class="fv-row col-md-4 mt-2" id="select_edit_discount_type">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Discount/Voucher Type</label>
                                <select id="edit_discount_type" onchange="changeDiscount($(this),'edit');" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="discount_type">
                                    <option value="">Select Discount Type</option>
                                    <option value="Fixed">Fixed</option>
                                    <option value="Percentage">Percentage</option>
                                </select>
                                <small class="text-danger error-class"><b id='discount_type_error' class="error-msg"></b></small>
                            </div>

                            <div class="fv-row col-md-4 mt-4" id="edit_discount_value_div">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Discount/Voucher Value </label>
                                <input type="number" onkeyup="editDiscountValue($(this));" name="discount_value" class="form-control" id="edit_discount_value_1" min="0" oninput="validity.valid||(value='');" disabled step=".001">
                                <small class="text-danger error-class"><b id='discount_payment_error' class="error-msg"></b></small>
                            </div>

                            <div class="fv-row col-md-3 mt-4">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Price</label>
                                <div class="blockui input-spinner" style="display: none; background: transparent; box-shadow: none; position: absolute;margin-top: 28px;margin-left: 15%;">
                                    <span>Please wait...</span>
                                    <span>
                                        <div class="spinner spinner-primary"></div>
                                    </span>
                                </div>
                                <input type="text" readonly name="net_amount_1" class="form-control" id="edit_net_amount_1">
                            </div>
                            <div class="fv-row col-md-3 mt-4" id="edit_sold_by_div">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Sold By <span class="text text-danger">*</span></label>
                                <select  id="edit_sold_by" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="sold_by">
                                    <option value="">Select </option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="edit_sold_by_errorr" class="create-plan-error"></b></small>
                            </div>
                            <div class="fv-row col-md-2 mt-4">
                                <div class="text-center" style="margin-top: 24px;">
                                    <button type="button" id="EditPackage" class="ep-btn-add spinner-button-edit-add">
                                        <span class="indicator-label">+ Add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive add_center_target_table" style="margin-bottom: 16px;">
                    <table id="edit_centre_target_location" class="ep-table">
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
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody id="edit_plan_services">
                            <tr class="text-center service_not_found">
                                <td colspan="12">No record found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="ep-summary">
                    <div class="ep-section-title">Payment Summary</div>
                    <div class="form-group">
                        <div class="row">
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Total </label>
                                <input type="text" id="edit_package_total_1" readonly class="form-control" name="package_total_1" value="0" style="font-weight: 700; color: #3D4A35; font-size: 15px;">
                            </div>

                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Payment Mode <span class="text text-danger"></span></label>
                                <select id="edit_payment_mode_id" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="payment_mode_id" onchange="checkpaymentMode()">
                                    <option value="">Select Payment Mode</option>
                                </select>
                                <small class="text-danger error-class"><b id='payment_mode_id' class="error-msg"></b></small>
                            </div>

                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Cash Amount</label>
                                <input type="number" min="0" id="edit_cash_amount_1" class="form-control" value="0" name="cash_amount" disabled oninput="validity.valid||(value='');" disabled>
                                <small class="text-danger error-class"><b id='cash_amount_error' class="error-msg"></b></small>
                            </div>

                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Cash Received Remain</label>
                                <input type="text" readonly min="0" name="total_price" value="0" class="form-control" id="edit_grand_total_1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="ep-history">
            <div class="ep-history-title">Payment History</div>
            <div class="table-responsive">
                <table id="edit_plan_history" class="ep-table">
                    <thead>
                        <tr>
                            <th>Payment Mode</th>
                            <th>Cash Flow</th>
                            <th>Cash Amount</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody class="edit_plan_history">
                        <tr>
                            <td id="edit_payment_mode"></td>
                            <td id="edit_cash_flow"></td>
                            <td id="edit_cash_amount"></td>
                            <td id="edit_Created_at"></td>
                            <td id="edit_action"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <div class="ep-footer">
        <button type="button" class="ep-btn-cancel" onclick="resetVoucherEdit(event)">Cancel</button>
        <button id="EditPackageFinal" type="button" class="ep-btn-save spinner-button-edit-save">
            <span class="indicator-label">Save</span>
        </button>
    </div>
</div>
<!--end::Modal content-->