<!--begin::Modal content-->
<style>
    .cs-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .cs-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .cs-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .cs-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .cs-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .cs-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .cs-modal-close:hover { background: rgba(255,255,255,0.25); }
    .cs-modal-body { padding: 24px 28px; background: #fff; }
    .cs-modal-body label { font-size: 12px; font-weight: 600; color: #007bff; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .cs-modal-body .form-control, .cs-modal-body .form-control-solid { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 9px 14px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff; }
    .cs-modal-body .form-control:focus, .cs-modal-body .form-control-solid:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .cs-modal-body .select2-container--default .select2-selection--single { border: 1px solid #D5DDD0; border-radius: 8px; height: 38px; }
    .cs-modal-body .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; color: #3D4A35; padding-left: 14px; }
    .cs-modal-body .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .cs-footer { display: flex; align-items: center; justify-content: center; gap: 12px; padding-top: 20px; margin-top: 8px; border-top: 2px solid #E8EDE5; }
    .cs-btn-cancel { background: #fff; border: 1px solid #D5DDD0; border-radius: 8px; padding: 10px 28px; font-weight: 600; font-size: 13px; color: #007bff; transition: all 0.2s; cursor: pointer; }
    .cs-btn-cancel:hover { background: #F8F6F1; border-color: #7A8B6A; }
    .cs-btn-submit { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 8px; padding: 10px 32px; font-weight: 600; font-size: 13px; color: #fff; transition: all 0.3s; cursor: pointer; }
    .cs-btn-submit:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
<div class="modal-content cs-modal">
    <div class="cs-modal-header" id="kt_modal_password_header">
        <div class="cs-modal-header-top">
            <h2 class="cs-modal-title">Update Appointment Status</h2>
            <div class="cs-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="cs-modal-body scroll-y">
        <form id="modal_update_status_form" method="post" action="">

            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_update_status_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                @method('put')

                <input type="hidden" name="id" id="appointment_id">
                <input type="hidden" name="appointment_type_id" id="appointment_type_id">
                <input type="hidden" name="appointment_status_not_show" value="" id="appointment_status_not_show">
                <input type="hidden" name="cancellation_reason_other_reason" value="" id="cancellation_reason_other_reason">

                <div class="form-group">
                    <div class="row">

                        <div class="fv-row col-md-12 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Status </label>
                            <select type="text" onchange="loadChildStatuses($(this).val());" name="base_appointment_status_id" id="base_appointment_status_id" class="form-control form-control-solid mb-3 mb-lg-0 select2">
                            </select>
                        </div>

                        <div class="fv-row col-md-12 mt-3 appointment_status_id" id="appointment_status_id_section">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Child Status </label>
                            <select id="appointment_status_id" onchange="statusListener($(this).val());" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="appointment_status_id">
                            </select>
                        </div>

                        <div class="fv-row col-md-12 mt-3 reason" id="appointment_reason">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Comment</label>
                            <textarea id="reason" name="reason" rows="3" class="form-control mb-3 mb-lg-0" placeholder="Type your comment.."></textarea>
                        </div>

                    </div>
                </div>

            </div>

            <div class="cs-footer">
                <button type="reset" class="cs-btn-cancel popup-close" data-kt-users-modal-action="cancel">Cancel</button>
                <button type="submit" class="cs-btn-submit spinner-button">
                    <span class="indicator-label">Submit</span>
                </button>
            </div>
        </form>
    </div>
</div>
<!--end::Modal content-->
