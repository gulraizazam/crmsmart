<!--begin::Modal content-->
<div class="modal-content ec-modal">
    <div class="ec-modal-header" id="kt_modal_password_header">
        <div class="ec-modal-header-top">
            <h2 class="ec-modal-title" id="edit_consultation_heading">Edit Consultation</h2>
            <div class="ec-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="ec-modal-body scroll-y">
        <form id="modal_edit_appointment_form" method="post" action="">

            @method('put')

            <input type="hidden" id="appointment_manager" value="{{config('constants.appointment_type_consultancy_string')}}">
            <input type="hidden" id="back-date">
            <input type="hidden" id="old_phone" name="old_phone">
            <input type="hidden" name="lead_id" id="lead_id">
            <input type="hidden" id="appointment_id">
            <input type="hidden" id="resourceRotaDayID">
            <input type="hidden" id="start_time">
            <input type="hidden" id="end_time">
            <input type="hidden" id="scheduled_date_old">
            <input type="hidden" id="scheduled_time_old">
            <input type="hidden" name="appointment_type_id" id="consultancy_appointment_type">
            <input type="hidden" name="treatment_service_id" id="consultancy_service_id">
            <input type="hidden" name="location_id" id="edit_location_id">

            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_appointment_type_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                <div class="form-group">
                    <div class="row">

                        <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Service <span class="text text-danger">*</span> </label>
                            <select id="edit_treatment" class="form-control select2" name="treatment_id"></select>
                        </div>

                        <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Doctor <span class="text text-danger">*</span> </label>
                            <select id="edit_doctor" class="form-control select2" name="doctor_id"></select>
                        </div>

                        <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Scheduled Date <span class="text text-danger">*</span></label>
                            <input type="text" id="edit_scheduled_date" name="scheduled_date" class="form-control custom-datepicker">
                        </div>

                        <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Scheduled Time <span class="text text-danger">*</span></label>
                            <input type="text" id="edit_scheduled_time" name="scheduled_time" class="form-control scheduled_time default-timepicker">
                        </div>

                    </div>
                </div>

            </div>

            <div class="ec-footer">
                <button type="reset" class="ec-btn-cancel popup-close" data-kt-users-modal-action="cancel">Cancel</button>
                <button type="submit" class="ec-btn-submit spinner-button">
                    <span class="indicator-label">Submit</span>
                </button>
            </div>
        </form>
    </div>
</div>
<!--end::Modal content-->



