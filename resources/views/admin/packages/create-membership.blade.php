<!--begin::Modal content-->
<style>
    .am-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .am-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .am-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .am-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .am-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .am-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .am-modal-close:hover { background: rgba(255,255,255,0.25); }
    .am-modal-body { padding: 24px 28px; background: #fff; }
    .am-modal-body .alert { border-radius: 8px; font-size: 13px; padding: 10px 16px; border: none; }
    .am-modal-body .alert-danger { background: #FEF2F2; color: #991B1B; }
    .am-modal-body .alert-success { background: #F0FDF4; color: #166534; }
    .am-section { background: #F8F6F1; border: 1px solid #E8EDE5; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .am-section-title { font-size: 13px; font-weight: 700; color: #3D4A35; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .am-section-title::before { content: ''; width: 4px; height: 16px; background: linear-gradient(180deg, #007bff, #C4A265); border-radius: 2px; }
    .am-modal-body label { font-size: 12px; font-weight: 600; color: #007bff; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .am-modal-body .form-control, .am-modal-body .form-control-solid { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 9px 14px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff; }
    .am-modal-body .form-control:focus, .am-modal-body .form-control-solid:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .am-modal-body .select2-container--default .select2-selection--single { border: 1px solid #D5DDD0; border-radius: 8px; height: 38px; }
    .am-modal-body .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; color: #3D4A35; padding-left: 14px; }
    .am-modal-body .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .am-btn-add { background: linear-gradient(135deg, #C4A265, #D4B87A); border: none; border-radius: 8px; padding: 9px 24px; font-weight: 600; font-size: 13px; color: #3D4A35; transition: all 0.3s; cursor: pointer; }
    .am-btn-add:hover { background: linear-gradient(135deg, #B8954F, #C4A265); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(196,162,101,0.3); color: #3D4A35; }
    .am-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #E8EDE5; border-radius: 10px; overflow: hidden; }
    .am-table thead th { background: #F4F7F2; color: #007bff; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 14px; border-bottom: 2px solid #E8EDE5; white-space: nowrap; }
    .am-table tbody td { padding: 10px 14px; font-size: 13px; color: #3D4A35; border-bottom: 1px solid #E8EDE5; }
    .am-table tbody tr:last-child td { border-bottom: none; }
    .am-table tbody tr:hover { background: #FAFDF8; }
    .am-summary { background: linear-gradient(135deg, #F8F6F1 0%, #F4F7F2 100%); border: 1px solid #E8EDE5; border-radius: 12px; padding: 20px; margin-top: 20px; }
    .am-docs { background: #F8F6F1; border: 1px solid #E8EDE5; border-radius: 12px; padding: 20px; margin-top: 16px; }
    .am-docs-title { font-size: 13px; font-weight: 700; color: #3D4A35; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .am-docs-title::before { content: ''; width: 4px; height: 16px; background: linear-gradient(180deg, #C4A265, #007bff); border-radius: 2px; }
    .am-docs-info { background: #EDF2EA; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #007bff; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .am-footer { display: flex; align-items: center; justify-content: center; gap: 12px; padding-top: 20px; margin-top: 8px; border-top: 2px solid #E8EDE5; }
    .am-btn-cancel { background: #fff; border: 1px solid #D5DDD0; border-radius: 8px; padding: 10px 28px; font-weight: 600; font-size: 13px; color: #007bff; transition: all 0.2s; cursor: pointer; }
    .am-btn-cancel:hover { background: #F8F6F1; border-color: #7A8B6A; }
    .am-btn-save { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 8px; padding: 10px 32px; font-weight: 600; font-size: 13px; color: #fff; transition: all 0.3s; cursor: pointer; }
    .am-btn-save:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
<div class="modal-content am-modal">
    <div class="am-modal-header" id="kt_modal_password_header">
        <div class="am-modal-header-top">
            <h2 class="am-modal-title">Assign Membership</h2>
            <div class="am-modal-close popup-close"
                 data-kt-users-modal-action="close"
                 onclick="resetVoucherAddMembership(event); return false;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="am-modal-body scroll-y">
        <div id="duplicateErrMembership" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Duplicate record found, please select another one.
        </div>
        <div id="successMessageMembership" class="alert alert-success display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Membership successfully created
        </div>
        <div id="inputfieldMessageMembership" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Kindly enter required fields or you enter wrong value.
        </div>
        <div id="wrongMessageMembership" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Something went wrong!
        </div>
        <div id="percentageMessageMembership" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Your discount limit exceeded.
        </div>
        <div id="AlreadyExitMessageMembership" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Unable to enter same service with different price.
        </div>
        <div id="datanotexistMembership" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            That center not have any service.
        </div>
        <div id="DiscountRangeMembership" class="alert alert-danger" style="display: none;">
            <button class="close" data-close="alert"></button>
            Your discount limit exceeded.
        </div>
        <input type="hidden" name="random_id_membership" id="random_id_membership" class="form-control">
        <input type="hidden" name="slug_membership" id="slug_membership" class="form-control">
        <input type="hidden" id="client_id_membership" class="form-control">
        <input type="hidden" name="patient_id_membership" id="parent_id_membership" class="form-control">
        <div class="d-flex flex-column scroll-y me-n7 pe-7" id="modal_appointment_membership_section" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

            <div class="am-section">
                <div class="am-section-title">Patient & Appointment</div>
                <div class="form-group">
                    <div class="row">
                        @if(isset($isPatientCard) && $isPatientCard)
                            {{-- Patient Card Context: Show patient info as static text like edit modal --}}
                            <div class="fv-row col-md-2 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Patient Name</label>
                                <h3 id="add-patient-name-membership"></h3>
                                <input type="hidden" id="add_patient_id_membership" name="patient_id_membership">
                            </div>
                            <div class="fv-row col-md-2 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Membership</label>
                                <h4 id="patient_membership_membership" style="font-size:15px">No Membership</h4>
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Location <span class="text text-danger">*</span></label>
                                <select onchange="getServicesMembership();" id="add_membership_location_id" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="location_id_membership">
                                    <option value="">Select Centre</option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="add_membership_location_id_error" class="create-membership-error"></b></small>
                            </div>
                            <div class="fv-row col-md-4 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Appointment <span class="text text-danger">*</span></label>
                                <select id="add_appointment_id_membership" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="appointment_id_membership">
                                    <option value="">Select Appointment</option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="add_appointment_id_membership_error" class="create-membership-error"></b></small>
                            </div>
                        @else
                            {{-- Main Plans Module: Show patient search dropdown --}}
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Location <span class="text text-danger">*</span></label>
                                <select onchange="getServicesMembership();" id="add_membership_location_id" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="location_id_membership">
                                    <option value="">Select Centre</option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="add_membership_location_id_error" class="create-membership-error"></b></small>
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Patient Search <span class="text text-danger">*</span></label>
                                <select id="add_patient_id_membership" class="form-control form-control-solid mb-3 mb-lg-0 select2-patient-search" name="patient_id_membership">
                                    <option value="">Search Patient by Name or Phone</option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="add_patient_id_membership_error" class="create-membership-error"></b></small>
                            </div>
                            <div class="fv-row col-md-2 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Membership </label>
                                <input type="text" id="patient_membership_membership" class="form-control form-control-solid mb-3 mb-lg-0" disabled placeholder="No data">
                            </div>
                            <div class="fv-row col-md-4 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Appointment <span class="text text-danger">*</span></label>
                                <select id="add_appointment_id_membership" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="appointment_id_membership">
                                    <option value="">Select Appointment</option>
                                </select>
                                <small class="text-danger ml-1 mt-1"><b id="add_appointment_id_membership_error" class="create-membership-error"></b></small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="am-section">
                <div class="am-section-title">Membership Details</div>
                <div class="form-group">
                    <div class="row">
                        <div class="fv-row col-md-3 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Membership Type <span class="text text-danger">*</span></label>
                            <select id="add_service_id_membership" onchange="getServiceDiscountMembership($(this));" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="service_id_membership">
                                <option value="">Select Service</option>
                            </select>
                            <small class="text-danger ml-1 mt-1"><b id="add_service_id_membership_error" class="create-membership-error"></b></small>
                        </div>
                        <div class="fv-row col-md-2 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Code <span class="text text-danger">*</span></label>
                            <select id="add_membership_code" class="form-control form-control-solid mb-3 mb-lg-0" name="membership_code">
                                <option value="">Search Code</option>
                            </select>
                            <small class="text-danger ml-1 mt-1"><b id="add_membership_code_error" class="create-membership-error"></b></small>
                        </div>
                        <div class="fv-row col-md-2 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Price</label>
                            <div class="blockui input-spinner" style="display: none; background: transparent; box-shadow: none; position: absolute;margin-top: 28px;margin-left: 15%;">
                                <span>Please wait...</span>
                                <span>
                                    <div class="spinner spinner-primary"></div>
                                </span>
                            </div>
                            <input type="text" readonly name="net_amount_membership" class="form-control" id="net_amount_membership">
                        </div>
                        <div class="fv-row col-md-3 mt-2" id="sold_by_div_membership">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Sold By <span class="text text-danger">*</span></label>
                            <select  id="add_sold_by_membership" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="sold_by_membership">

                            </select>
                            <small class="text-danger ml-1 mt-1"><b id="add_sold_by_membership_errorr" class="create-membership-error"></b></small>
                        </div>
                        <div class="fv-row col-md-2 mt-2">
                            <div class="text-center" style="margin-top: 24px;">
                                <button type="button" id="AddPackageMembership" class="am-btn-add spinner-button-add">
                                    <span class="indicator-label">+ Add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive add_center_target_table" style="margin-bottom: 16px;">
                <table id="appointment_detail_membership" class="am-table">
                    <thead>
                        <tr>
                            <th>Service Name</th>
                            <th>Regular Price</th>
                            <th>Amount</th>
                            <th>Tax </th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="membership_services"></tbody>
                </table>
            </div>

            <div class="am-summary">
                <div class="am-section-title">Payment Summary</div>
                <div class="form-group">
                    <div class="row">
                        <div class="fv-row col-md-3 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Total </label>
                            <input type="text" readonly oninput="phoneField(this)" id="package_total_membership" class="form-control" value="0" name="package_total_membership" style="font-weight: 700; color: #3D4A35; font-size: 15px;">
                        </div>
                        <div class="fv-row col-md-3 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Payment Mode <span class="text text-danger">*</span></label>
                            <select id="payment_mode_id_membership" class="form-control form-control-solid mb-3 mb-lg-0" name="payment_mode_id_membership">
                                <option value="">Select Payment Mode</option>
                            </select>
                        </div>
                        <div class="fv-row col-md-3 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Cash Amount</label>
                            <input type="number" id="cash_amount_membership" class="form-control" placeholder="Enter Amount" name="cash_amount_membership" disabled min="0">
                            <small class="text-danger ml-1 mt-1"><b id="cash_amount_membership_error" class="create-membership-error"></b></small>
                        </div>
                        <div class="fv-row col-md-3 mt-2">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Cash Received Remain</label>
                            <input type="text" readonly min="0" name="total_price_membership" value="0" class="form-control" id="grand_total_membership">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Membership Document Upload Section (moved below payment fields) -->
            <div id="student_document_section" class="am-docs" style="display: none;">
                <div class="am-docs-title">
                    <i class="la la-file-image" style="color: #007bff;"></i>
                    Student Verification Documents
                </div>
                <div class="am-docs-info">
                    <i class="la la-info-circle"></i>
                    <span>Upload student card and ID card images (Max 4 documents, JPG/PNG only)</span>
                </div>

                <div id="document_upload_container">
                    <!-- Document Upload Item 1 (Default) -->
                    <div class="document-upload-item mb-2" data-index="0">
                        <div class="d-flex align-items-start gap-2">
                            <div class="flex-grow-1">
                                <input type="file" 
                                       name="student_documents[]" 
                                       class="form-control form-control-sm student-document-input" 
                                       accept="image/jpeg,image/png,image/jpg"
                                       data-index="0">
                                <div class="mt-1 document-preview" id="preview_0" style="display: none;">
                                    <img src="" class="img-thumbnail" style="max-height: 60px;">
                                    <button type="button" class="btn btn-sm btn-light-danger ms-1 remove-preview" data-index="0">
                                        <i class="la la-times"></i>
                                    </button>
                                </div>
                                <small class="text-danger d-block"><b class="document-error" id="document_error_0"></b></small>
                            </div>
                            <button type="button" id="add_document_btn" class="btn btn-sm btn-icon btn-primary" title="Add Document" style="margin-right: 5px;margin-left: 7px;">
                                <i class="la la-plus"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-sm btn-icon btn-light-danger remove-document-btn" 
                                    data-index="0"
                                    style="display: none;"
                                    title="Remove">
                                <i class="la la-trash"></i>
                            </button>
                            <small class="text-muted" id="document_count_text" style="margin-left:3px;">1 of 4</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="am-footer">
        <button type="button" class="am-btn-cancel" onclick="resetVoucherAddMembership(event)">Cancel</button>
        <button id="AddPackageFinalMembership" type="submit" class="am-btn-save spinner-button-save">
            <span class="indicator-label">Save</span>
        </button>
    </div>
</div>
<!--end::Modal content-->
