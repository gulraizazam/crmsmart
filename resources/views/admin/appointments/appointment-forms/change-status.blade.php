<!--begin::Modal content-->
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
