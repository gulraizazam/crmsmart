<!--begin::Modal content-->
<div class="modal-content us-modal">
    <div class="us-modal-header" id="kt_modal_password_header">
        <div class="us-modal-header-top">
            <h2 class="us-modal-title">Update Schedule</h2>
            <div class="us-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="us-modal-body scroll-y">
        <form id="modal_update_scheduled_form" method="post" action="{{route('admin.appointments.updateSchedule')}}">

            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_update_schedule_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">


                <input type="hidden" name="appointment_id" id="schedule_appointment_id">
                <input type="hidden" name="doctor_id" id="schedule_doctor_id">
                <input type="hidden" name="location_id" id="schedule_location_id">
                <div class="form-group">
                    <div class="row">

                        <div class="fv-row col-md-12 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Date </label>
                            <input type="text" name="scheduled_date" id="schedule_date" class="form-control scheduled_date form-control-solid mb-3 mb-lg-0">
                        </div>

                        <div class="fv-row col-md-12 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Time </label>
                            <input id="schedule_time" name="scheduled_time" class="form-control scheduled_time">
                        </div>

                    </div>
                </div>

            </div>

            <div class="us-footer">
                <button type="reset" class="us-btn-cancel popup-close" data-kt-users-modal-action="cancel">Cancel</button>
                <button type="submit" class="us-btn-submit spinner-button">
                    <span class="indicator-label">Submit</span>
                </button>
            </div>
        </form>
    </div>
</div>
<!--end::Modal content-->
