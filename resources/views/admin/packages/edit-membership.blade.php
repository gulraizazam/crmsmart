<!--begin::Modal content-->
<style>
    .em-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .em-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .em-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .em-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .em-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .em-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .em-modal-close:hover { background: rgba(255,255,255,0.25); }
    .em-modal-body { padding: 24px 28px; background: #fff; }
    .em-modal-body .alert { border-radius: 8px; font-size: 13px; padding: 10px 16px; border: none; }
    .em-modal-body .alert-danger { background: #FEF2F2; color: #991B1B; }
    .em-modal-body .alert-success { background: #F0FDF4; color: #166534; }
    .em-section { background: #F8F6F1; border: 1px solid #E8EDE5; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .em-section-title { font-size: 13px; font-weight: 700; color: #3D4A35; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .em-section-title::before { content: ''; width: 4px; height: 16px; background: linear-gradient(180deg, #007bff, #C4A265); border-radius: 2px; }
    .em-modal-body label { font-size: 12px; font-weight: 600; color: #007bff; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .em-modal-body .form-control, .em-modal-body .form-control-solid { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 9px 14px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff; }
    .em-modal-body .form-control:focus, .em-modal-body .form-control-solid:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .em-modal-body .select2-container--default .select2-selection--single { border: 1px solid #D5DDD0; border-radius: 8px; height: 38px; }
    .em-modal-body .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; color: #3D4A35; padding-left: 14px; }
    .em-modal-body .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .em-btn-add-disabled { background: #E8EDE5; border: none; border-radius: 8px; padding: 9px 24px; font-weight: 600; font-size: 13px; color: #9CA896; cursor: not-allowed; }
    .em-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #E8EDE5; border-radius: 10px; overflow: hidden; }
    .em-table thead th { background: #F4F7F2; color: #007bff; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 14px; border-bottom: 2px solid #E8EDE5; white-space: nowrap; }
    .em-table tbody td { padding: 10px 14px; font-size: 13px; color: #3D4A35; border-bottom: 1px solid #E8EDE5; }
    .em-table tbody tr:last-child td { border-bottom: none; }
    .em-table tbody tr:hover { background: #FAFDF8; }
    .em-summary { background: linear-gradient(135deg, #F8F6F1 0%, #F4F7F2 100%); border: 1px solid #E8EDE5; border-radius: 12px; padding: 20px; margin-top: 20px; }
    .em-docs { background: #F8F6F1; border: 1px solid #E8EDE5; border-radius: 12px; padding: 20px; margin-top: 16px; }
    .em-docs-title { font-size: 13px; font-weight: 700; color: #3D4A35; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .em-docs-title::before { content: ''; width: 4px; height: 16px; background: linear-gradient(180deg, #C4A265, #007bff); border-radius: 2px; }
    .em-docs-info { background: #EDF2EA; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #007bff; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .em-history { margin-top: 24px; }
    .em-history-title { font-size: 14px; font-weight: 700; color: #3D4A35; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .em-history-title::before { content: ''; width: 4px; height: 16px; background: linear-gradient(180deg, #C4A265, #007bff); border-radius: 2px; }
    .em-footer { display: flex; align-items: center; justify-content: center; gap: 12px; padding-top: 20px; margin-top: 8px; border-top: 2px solid #E8EDE5; }
    .em-btn-cancel { background: #fff; border: 1px solid #D5DDD0; border-radius: 8px; padding: 10px 28px; font-weight: 600; font-size: 13px; color: #007bff; transition: all 0.2s; cursor: pointer; }
    .em-btn-cancel:hover { background: #F8F6F1; border-color: #7A8B6A; }
    .em-btn-save { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 8px; padding: 10px 32px; font-weight: 600; font-size: 13px; color: #fff; transition: all 0.3s; cursor: pointer; }
    .em-btn-save:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
<div class="modal-content em-modal">
    <div class="em-modal-header" id="kt_modal_edit_membership_header">
        <div class="em-modal-header-top">
            <h2 class="em-modal-title">Edit Membership</h2>
            <div class="em-modal-close popup-close"
                 data-kt-users-modal-action="close"
                 onclick="closeEditMembershipModal();">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    
    <div class="em-modal-body scroll-y">
        <div id="edit_membership_successMessage" class="alert alert-success display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Record updated successfully
        </div>
        <div id="edit_membership_wrongMessage" class="alert alert-danger display-hide" style="display: none;">
            <button class="close" data-close="alert"></button>
            Something went wrong!
        </div>

        <form id="edit_membership_form">
            <input type="hidden" name="package_id" id="edit_package_id_membership">
            <input type="hidden" name="random_id" id="edit_random_id_membership">
            <input type="hidden" name="patient_id" id="edit_patient_id_membership">
            <input type="hidden" name="location_id" id="edit_location_id_membership">
            <input type="hidden" id="edit_membership_has_edit_permission" value="{{ Gate::allows('plans_edit') ? '1' : '0' }}">
            <input type="hidden" id="edit_membership_has_edit_sold_by_permission" value="{{ Gate::allows('plans_edit_sold_by') ? '1' : '0' }}">
            
            <div class="d-flex flex-column scroll-y me-n7 pe-7">
                <!-- Patient and Location Info (Read-only) -->
                <div class="em-section">
                    <div class="em-section-title">Patient & Appointment</div>
                    <div class="form-group">
                        <div class="row">
                            <div class="fv-row col-md-2 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Patient Name</label>
                                <h3 id="edit_patient_name_membership" style="font-size: 15px; font-weight: 700; color: #3D4A35; margin: 0;"></h3>
                            </div>
                            <div class="fv-row col-md-2 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Membership</label>
                                <h4 id="edit_patient_membership_membership" style="font-size:14px; color: #3D4A35; margin: 0;">-</h4>
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Location</label>
                                <h3 id="edit_location_name_membership" style="font-size: 15px; font-weight: 700; color: #3D4A35; margin: 0;"></h3>
                            </div>
                            <div class="fv-row col-md-4 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Appointment <span class="text text-danger">*</span></label>
                                <select id="edit_membership_appointment_id" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="appointment_id">
                                    <option value="">Select Appointment</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services Section (Read-only for membership) -->
                <div class="em-section">
                    <div class="em-section-title">Service Details</div>
                    <div class="form-group">
                        <div class="row">
                            <div class="fv-row col-md-4 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Services <span class="text text-danger">*</span></label>
                                <select id="edit_membership_service_id" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="service_id" disabled>
                                    <option value="">Select Service</option>
                                </select>
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Price</label>
                                <input type="text" readonly name="net_amount" class="form-control" id="edit_membership_net_amount">
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Sold By <span class="text text-danger">*</span></label>
                                <select id="edit_membership_sold_by" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="sold_by" disabled>
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="fv-row col-md-2 mt-2">
                                <div class="text-center" style="margin-top: 24px;">
                                    <button type="button" class="em-btn-add-disabled" disabled>
                                        <span class="indicator-label">+ Add</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Membership Items Table -->
                <div class="table-responsive add_center_target_table" style="margin-bottom: 16px;">
                    <table class="em-table">
                        <thead>
                            <tr>
                                <th>Service Name</th>
                                <th>Regular Price</th>
                                <th>Amount</th>
                                <th>Tax</th>
                                <th>Total</th>
                                <th>Sold By</th>
                                @if(Gate::allows('plans_edit'))
                                <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody id="edit_membership_services"></tbody>
                    </table>
                </div>

                <!-- Payment Section -->
                <div class="em-summary">
                    <div class="em-section-title">Payment Summary</div>
                    <div class="form-group">
                        <div class="row">
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Total</label>
                                <input type="text" readonly id="edit_package_total_membership" class="form-control" value="0" name="package_total" style="font-weight: 700; color: #3D4A35; font-size: 15px;">
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Payment Mode</label>
                                <select id="edit_membership_payment_mode_id" class="form-control form-control-solid mb-3 mb-lg-0" name="payment_mode_id">
                                    <option value="">Select Payment Mode</option>
                                </select>
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Cash Amount</label>
                                <input type="number" min="0" id="edit_membership_cash_amount" class="form-control" value="0" name="cash_amount" disabled>
                            </div>
                            <div class="fv-row col-md-3 mt-2">
                                <label class="required fw-bold fs-6 mb-2 pl-0">Cash Received Remain</label>
                                <input type="text" readonly min="0" name="grand_total" value="0" class="form-control" id="edit_membership_grand_total">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student Document Upload Section (only shown for student memberships) -->
                <div id="edit_student_document_section" class="em-docs" style="display: none;">
                    <input type="hidden" id="edit_membership_type_id" value="">
                    <input type="hidden" id="edit_is_student_membership" value="0">
                    
                    <div class="em-docs-title">
                        <i class="la la-file-image" style="color: #007bff;"></i>
                        Student Verification Documents
                    </div>
                    <div class="em-docs-info">
                        <i class="la la-info-circle"></i>
                        <span>Upload student card and ID card images (Max 4 documents, JPG/PNG only)</span>
                    </div>

                    <!-- Existing Documents -->
                    <div id="edit_existing_documents" class="mb-3" style="display: none;">
                        <label class="fw-bold fs-6 mb-2 text-success">Previously Uploaded Documents</label>
                        <div id="edit_existing_documents_list" class="d-flex flex-wrap gap-2"></div>
                        <hr style="border-color: #E8EDE5;">
                    </div>

                    <div id="edit_document_upload_container">
                        <!-- Document Upload Item 1 (Default) -->
                        <div class="edit-document-upload-item mb-2" data-index="0">
                            <div class="d-flex align-items-start gap-2">
                                <div class="flex-grow-1">
                                    <input type="file" 
                                           name="edit_student_documents[]" 
                                           class="form-control form-control-sm edit-student-document-input" 
                                           accept="image/jpeg,image/png,image/jpg"
                                           data-index="0"
                                           onchange="previewEditDocument(this, 0)">
                                    <div class="mt-1 edit-document-preview" id="edit_document_preview_0" style="display: none;">
                                        <img src="" class="img-thumbnail" style="max-height: 60px;">
                                    </div>
                                </div>
                                <button type="button" id="edit_add_document_btn" class="btn btn-sm btn-icon btn-primary" onclick="addEditDocumentField()" title="Add Document" style="margin-left: 7px; margin-right: 5px;">
                                    <i class="la la-plus"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-icon btn-light-danger edit-remove-document-btn" 
                                        data-index="0"
                                        onclick="removeEditDocumentField(0)"
                                        style="display: none;"
                                        title="Remove">
                                    <i class="la la-trash"></i>
                                </button>
                                <small class="text-muted" id="edit_document_count" style="margin-left: 3px;">1 of 4</small>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </form>

        <div class="em-history">
            <div class="em-history-title">Payment History</div>
            <div class="table-responsive">
                <table class="em-table">
                    <thead>
                        <tr>
                            <th>Payment Mode</th>
                            <th>Cash Flow</th>
                            <th>Cash Amount</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="edit_membership_payment_history"></tbody>
                </table>
            </div>
        </div>

    </div>
    <div class="em-footer">
        <button type="button" class="em-btn-cancel" onclick="closeEditMembershipModal();">Cancel</button>
        <button id="EditMembershipFinal" type="button" class="em-btn-save spinner-button-edit-save">
            <span class="indicator-label">Save</span>
        </button>
    </div>
</div>
<!--end::Modal content-->
