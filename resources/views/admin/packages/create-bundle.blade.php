<!--begin::Modal content-->
<style>
    .ab-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .ab-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .ab-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .ab-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .ab-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .ab-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .ab-modal-close:hover { background: rgba(255,255,255,0.25); }
    .ab-modal-body { padding: 24px 28px; background: #fff; }
    .ab-modal-body .alert { border-radius: 8px; font-size: 13px; padding: 10px 16px; border: none; }
    .ab-modal-body .alert-danger { background: #FEF2F2; color: #991B1B; }
    .ab-modal-body .alert-success { background: #F0FDF4; color: #166534; }
    .ab-section { background: #F8F6F1; border: 1px solid #E8EDE5; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .ab-section-title { font-size: 13px; font-weight: 700; color: #3D4A35; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .ab-section-title::before { content: ''; width: 4px; height: 16px; background: linear-gradient(180deg, #007bff, #C4A265); border-radius: 2px; }
    .ab-modal-body label { font-size: 12px; font-weight: 600; color: #007bff; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .ab-modal-body .form-control, .ab-modal-body .form-control-solid { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 9px 14px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff; }
    .ab-modal-body .form-control:focus, .ab-modal-body .form-control-solid:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .ab-modal-body .select2-container--default .select2-selection--single { border: 1px solid #D5DDD0; border-radius: 8px; height: 38px; }
    .ab-modal-body .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; color: #3D4A35; padding-left: 14px; }
    .ab-modal-body .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .ab-btn-add { background: linear-gradient(135deg, #C4A265, #D4B87A); border: none; border-radius: 8px; padding: 9px 24px; font-weight: 600; font-size: 13px; color: #3D4A35; transition: all 0.3s; cursor: pointer; }
    .ab-btn-add:hover { background: linear-gradient(135deg, #B8954F, #C4A265); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(196,162,101,0.3); color: #3D4A35; }
    .ab-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #E8EDE5; border-radius: 10px; overflow: hidden; }
    .ab-table thead th { background: #F4F7F2; color: #007bff; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 14px; border-bottom: 2px solid #E8EDE5; white-space: nowrap; }
    .ab-table tbody td { padding: 10px 14px; font-size: 13px; color: #3D4A35; border-bottom: 1px solid #E8EDE5; }
    .ab-table tbody tr:last-child td { border-bottom: none; }
    .ab-table tbody tr:hover { background: #FAFDF8; }
    .ab-summary { background: linear-gradient(135deg, #F8F6F1 0%, #F4F7F2 100%); border: 1px solid #E8EDE5; border-radius: 12px; padding: 20px; margin-top: 20px; }
    .ab-footer { display: flex; align-items: center; justify-content: center; gap: 12px; padding-top: 20px; margin-top: 8px; border-top: 2px solid #E8EDE5; }
    .ab-btn-cancel { background: #fff; border: 1px solid #D5DDD0; border-radius: 8px; padding: 10px 28px; font-weight: 600; font-size: 13px; color: #007bff; transition: all 0.2s; cursor: pointer; }
    .ab-btn-cancel:hover { background: #F8F6F1; border-color: #7A8B6A; }
    .ab-btn-save { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 8px; padding: 10px 32px; font-weight: 600; font-size: 13px; color: #fff; transition: all 0.3s; cursor: pointer; }
    .ab-btn-save:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
<div class="modal-content ab-modal">
    <div class="ab-modal-header" id="kt_modal_password_header">
        <div class="ab-modal-header-top">
            <h2 class="ab-modal-title">Add Bundle</h2>
            <div class="ab-modal-close popup-close"
                 data-kt-users-modal-action="close"
                 onclick="resetVoucherAddBundle(event); return false;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="ab-modal-body scroll-y">
        <div id="duplicateErrBundle" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Duplicate record found, please select another one.
        </div>
        <div id="successMessageBundle" class="alert alert-success display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Bundle successfully created
        </div>
        <div id="inputfieldMessageBundle" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Kindly enter required fields or you enter wrong value.
        </div>
        <div id="wrongMessageBundle" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Something went wrong!
        </div>
        <div id="percentageMessageBundle" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Your discount limit exceeded.
        </div>
        <div id="AlreadyExitMessageBundle" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Unable to enter same service with different price.
        </div>
        <div id="datanotexistBundle" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            That center not have any service.
        </div>
        <div id="DiscountRangeBundle" class="alert alert-danger" style="display: none;">
            <button class="close" data-close="alert"></button>
            Your discount limit exceeded.
        </div>
        <input type="hidden" name="random_id_bundle" id="random_id_bundle" class="form-control">
        <input type="hidden" name="slug_bundle" id="slug_bundle" class="form-control">
        <input type="hidden" id="client_id_bundle" class="form-control">
        <input type="hidden" name="patient_id_bundle" id="parent_id_bundle" class="form-control">
        <div class="d-flex flex-column scroll-y me-n7 pe-7" id="modal_appointment_bundle_section" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

            <div class="ab-section">
                <div class="ab-section-title">Patient & Appointment</div>
                <div class="form-group">
                    <div class="row">
                        @if(isset($isPatientCard) && $isPatientCard)
                            {{-- Patient Card Context: Show patient info as static text like edit modal --}}
                            <div class="fv-row col-md-2 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Patient Name</label>
                                <h3 id="add-patient-name-bundle"></h3>
                                <input type="hidden" id="add_patient_id_bundle" name="patient_id_bundle">
                            </div>
                            <div class="fv-row col-md-2 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Membership</label>
                                <h4 id="patient_membership_bundle" style="font-size:15px">No Membership</h4>
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Location <span class="text text-danger">*</span></label>
                                <select onchange="getServicesBundle();" id="add_bundle_location_id" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="location_id_bundle">
                                    <option value="">Select Centre</option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="add_bundle_location_id_error" class="create-bundle-error"></b></small>
                            </div>
                            <div class="fv-row col-md-4 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Appointment <span class="text text-danger">*</span></label>
                                <select id="add_appointment_id_bundle" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="appointment_id_bundle">
                                    <option value="">Select Appointment</option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="add_appointment_id_bundle_error" class="create-bundle-error"></b></small>
                            </div>
                        @else
                            {{-- Main Plans Module: Show patient search dropdown --}}
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Location <span class="text text-danger">*</span></label>
                                <select onchange="getServicesBundle();" id="add_bundle_location_id" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="location_id_bundle">
                                    <option value="">Select Centre</option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="add_bundle_location_id_error" class="create-bundle-error"></b></small>
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Patient Search <span class="text text-danger">*</span></label>
                                <select id="add_patient_id_bundle" class="form-control form-control-solid mb-3 mb-lg-0 select2-patient-search" name="patient_id_bundle">
                                    <option value="">Search Patient by Name or Phone</option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="add_patient_id_bundle_error" class="create-bundle-error"></b></small>
                            </div>
                            <div class="fv-row col-md-2 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Membership </label>
                                <input type="text" id="patient_membership_bundle" class="form-control form-control-solid mb-3 mb-lg-0" disabled placeholder="No data">
                            </div>
                            <div class="fv-row col-md-4 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Appointment <span class="text text-danger">*</span></label>
                                <select id="add_appointment_id_bundle" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="appointment_id_bundle">
                                    <option value="">Select Appointment</option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="add_appointment_id_bundle_error" class="create-bundle-error"></b></small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="ab-section">
                <div class="ab-section-title">Service Details</div>
                <div class="form-group">
                    <div class="row">
                        <div class="fv-row col-md-4 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Services <span class="text text-danger">*</span></label>
                            <select id="add_service_id_bundle" onchange="getServiceDiscountBundle($(this));" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="service_id_bundle">
                                <option value="">Select Service</option>
                            </select>
                            <small class="text-danger ml-1 mt-1"><b id="add_service_id_bundle_error" class="create-bundle-error"></b></small>
                        </div>
                        <div class="fv-row col-md-3 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Price</label>
                            <div class="blockui input-spinner" style="display: none; background: transparent; box-shadow: none; position: absolute;margin-top: 28px;margin-left: 15%;">
                                <span>Please wait...</span>
                                <span>
                                    <div class="spinner spinner-primary"></div>
                                </span>
                            </div>
                            <input type="text" readonly name="net_amount_bundle" class="form-control" id="net_amount_bundle">
                        </div>
                        <div class="fv-row col-md-3 mt-2" id="sold_by_div_bundle">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Sold By <span class="text text-danger">*</span></label>
                            <select  id="add_sold_by_bundle" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="sold_by_bundle">

                            </select>
                            <small class="text-danger ml-1 mt-1"><b id="add_sold_by_bundle_errorr" class="create-bundle-error"></b></small>
                        </div>
                        <div class="fv-row col-md-2 mt-2">
                            <div class="text-center" style="margin-top: 24px;">
                                <button type="button" id="AddPackageBundle" class="ab-btn-add spinner-button-add">
                                    <span class="indicator-label">+ Add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive add_center_target_table" style="margin-bottom: 16px;">
                <table id="appointment_detail_bundle" class="ab-table">
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Regular Price</th>
                            <th>Amount</th>
                            <th>Tax </th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="bundle_services"></tbody>
                </table>
            </div>

            <div class="ab-summary">
                <div class="ab-section-title">Payment Summary</div>
                <div class="form-group">
                    <div class="row">
                        <div class="fv-row col-md-3 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Total </label>
                            <input type="text" readonly oninput="phoneField(this)" id="package_total_bundle" class="form-control" value="0" name="package_total_bundle" style="font-weight: 700; color: #3D4A35; font-size: 15px;">
                        </div>
                        <div class="fv-row col-md-3 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Payment Mode <span class="text text-danger">*</span></label>
                            <select id="payment_mode_id_bundle" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="payment_mode_id_bundle">
                                <option value="">Select Payment Mode</option>
                            </select>
                        </div>
                        <div class="fv-row col-md-3 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Cash Amount</label>
                            <input type="number" id="cash_amount_bundle" class="form-control" placeholder="Enter Amount" name="cash_amount_bundle" disabled min="0" oninput="validity.valid||(value='');">
                            <small class="text-danger ml-1 mt-1"><b id="cash_amount_bundle_error" class="create-bundle-error"></b></small>
                        </div>
                        <div class="fv-row col-md-3 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Cash Received Remain</label>
                            <input type="text" readonly min="0" name="total_price_bundle" value="0" class="form-control" id="grand_total_bundle">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ab-footer">
            <button type="button" class="ab-btn-cancel" onclick="resetVoucherAddBundle(event)">Cancel</button>
            <button id="AddPackageFinalBundle" type="submit" class="ab-btn-save spinner-button-save">
                <span class="indicator-label">Save</span>
            </button>
        </div>
    </div>
</div>
<!--end::Modal content-->
