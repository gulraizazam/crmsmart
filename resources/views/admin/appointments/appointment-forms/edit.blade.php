<!--begin::Modal content-->
<style>
    .ec-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .ec-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .ec-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .ec-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .ec-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .ec-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .ec-modal-close:hover { background: rgba(255,255,255,0.25); }
    .ec-modal-body { padding: 24px 28px; background: #fff; }
    .ec-modal-body label { font-size: 12px; font-weight: 600; color: #007bff; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .ec-modal-body .form-control, .ec-modal-body .form-control-solid { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 9px 14px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff; }
    .ec-modal-body .form-control:focus, .ec-modal-body .form-control-solid:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .ec-modal-body .select2-container--default .select2-selection--single { border: 1px solid #D5DDD0; border-radius: 8px; height: 38px; }
    .ec-modal-body .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; color: #3D4A35; padding-left: 14px; }
    .ec-modal-body .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .ec-footer { display: flex; align-items: center; justify-content: center; gap: 12px; padding-top: 20px; margin-top: 8px; border-top: 2px solid #E8EDE5; }
    .ec-btn-cancel { background: #fff; border: 1px solid #D5DDD0; border-radius: 8px; padding: 10px 28px; font-weight: 600; font-size: 13px; color: #007bff; transition: all 0.2s; cursor: pointer; }
    .ec-btn-cancel:hover { background: #F8F6F1; border-color: #7A8B6A; }
    .ec-btn-submit { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 8px; padding: 10px 32px; font-weight: 600; font-size: 13px; color: #fff; transition: all 0.3s; cursor: pointer; }
    .ec-btn-submit:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
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



