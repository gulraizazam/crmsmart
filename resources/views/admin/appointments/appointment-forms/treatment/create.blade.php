<!--begin::Modal content-->
<style>
    .tc-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .tc-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #343a40 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .tc-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .tc-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .tc-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .tc-modal-title span { color: #C4A265; }
    .tc-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .tc-modal-close:hover { background: rgba(255,255,255,0.25); }
    .tc-modal-body { padding: 24px 28px; background: #fff; }
    .tc-modal-body label { font-size: 12px; font-weight: 600; color: #343a40; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .tc-modal-body .form-control, .tc-modal-body .form-control-solid { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 9px 14px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff; }
    .tc-modal-body .form-control:focus, .tc-modal-body .form-control-solid:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .tc-modal-body .select2-container--default .select2-selection--single { border: 1px solid #D5DDD0; border-radius: 8px; height: 38px; }
    .tc-modal-body .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; color: #3D4A35; padding-left: 14px; }
    .tc-modal-body .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .tc-modal-body .alert-warning { background: #FFFBEB; border: 1px solid #F59E0B; border-radius: 10px; color: #92400E; padding: 14px 16px; }
    .tc-footer { display: flex; align-items: center; justify-content: center; gap: 12px; padding-top: 20px; margin-top: 8px; border-top: 2px solid #E8EDE5; }
    .tc-btn-cancel { background: #fff; border: 1px solid #D5DDD0; border-radius: 8px; padding: 10px 28px; font-weight: 600; font-size: 13px; color: #343a40; transition: all 0.2s; cursor: pointer; }
    .tc-btn-cancel:hover { background: #F8F6F1; border-color: #7A8B6A; }
    .tc-btn-submit { background: linear-gradient(135deg, #343a40, #7A8B6A); border: none; border-radius: 8px; padding: 10px 32px; font-weight: 600; font-size: 13px; color: #fff; transition: all 0.3s; cursor: pointer; }
    .tc-btn-submit:hover { background: linear-gradient(135deg, #4A5540, #343a40); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
<div class="modal-content tc-modal">
    <div class="tc-modal-header" id="kt_modal_password_header">
        <div class="tc-modal-header-top">
            <h2 class="tc-modal-title">New Treatment with <span id="treatment_modal_doctor_name"></span> - <span id="treatment_modal_date"></span></h2>
            <div class="tc-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="tc-modal-body scroll-y">
        <form id="modal_create_treatment_form" method="post" action="{{route('admin.treatments.store')}}">

            <input type="hidden" id="treatment_lead_id" name="lead_id">
            <input type="hidden" id="treatment_patient_id" value="0">
            <input type="hidden" id="treatment_city_id" name="city_id">
            <input type="hidden" id="treatment_location_id" name="location_id">
            <input type="hidden" id="treatment_doctor_id" name="doctor_id">
            <input type="hidden" id="treatment_start" name="start">
            <input type="hidden" id="treatment_resource_id" name="resource_id">
            <input type="hidden" id="treatment_appointment_type" name="appointment_type" value="treatment">
            <input type="hidden" id="treatment_cnic" name="cnic">
            <input type="hidden" id="treatment_email" name="email">
            <input type="hidden" id="treatment_dob" name="dob">
            <input type="hidden" id="treatment_address" name="address">
            <input type="hidden" id="treatment_town_id" name="town_id">


            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_appointment_type_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                {{-- Warning div for doctor mismatch --}}
                <div id="treatment_doctor_warning" class="alert alert-warning d-none" style="background-color: #FFEB3B; border-color: #FDD835; color: #000000;">
                    <div class="mb-3">
                        <strong>Attention:</strong> <span id="warning_message"></span>
                    </div>
                    <div class="form-check mb-2" style="display: flex; align-items: center;">
                        <input class="form-check-input" type="radio" id="use_previous_doctor" name="doctor_choice" value="previous" style="width: 20px; height: 20px; cursor: pointer; margin: 0; flex-shrink: 0;">
                        <label class="form-check-label" for="use_previous_doctor" style="cursor: pointer; margin-left: 10px; margin-bottom: 0;">
                            <span id="previous_doctor_option" style="margin-left:15px"></span>
                        </label>
                    </div>
                    <div class="form-check d-none" style="display: flex; align-items: center;">
                        <input class="form-check-input" type="radio" id="use_selected_doctor" name="doctor_choice" value="selected" style="width: 20px; height: 20px; cursor: pointer; margin: 0; flex-shrink: 0;">
                        <label class="form-check-label" for="use_selected_doctor" style="cursor: pointer; margin-left: 24px; margin-bottom: 0;">
                            Proceed with <span id="selected_doctor_option"></span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">

                        {{-- Hide base service dropdown - validation removed since it's auto-populated from service_id --}}
                        <input type="hidden" id="create_treatment_base_service" name="base_service_id">

                        <div class="fv-row col-md-12 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Patient Search <span class="text text-danger">*</span></label>
                            <select class="form-control select2-patient-search-treatment" id="create_treatment_patient_id" name="patient_id" onchange="getTreatmentPatientDetailFromSelect(this)">
                            </select>
                        </div>

                        <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Service <span class="text text-danger">*</span> </label>
                            <select id="create_treatment_service" class="form-control select2" name="service_id"></select>
                        </div>

                        <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Scheduled Time <span class="text text-danger">*</span> </label>
                            <input type="text" id="create_treatment_scheduled_time" name="scheduled_time" class="form-control treatment-timepicker">
                        </div>

                        <input type="hidden" id="create_treatment_phone" name="phone">
                        <input type="hidden" id="create_old_treatment_phone" name="old_phone">
                        <input type="hidden" id="create_treatment_patient_name" name="name">
                        <input type="hidden" id="create_treatment_gender" name="gender">
                        <input type="hidden" id="create_treatment_c_id" name="client_id">

                        {{--<div class="fv-row col-md-6 mt-5">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Lead Source</label>
                            <select id="create_treatment_lead" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="lead_source">
                            </select>
                        </div>

                        <div class="fv-row col-md-6 mt-5">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Referred By</label>
                            <select id="create_treatment_referred_by" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="referred_by">
                            </select>
                        </div>--}}

                    </div>
                </div>

            </div>

            <div class="tc-footer">
                <button type="reset" class="tc-btn-cancel popup-close" data-kt-users-modal-action="cancel">Cancel</button>
                <button type="submit" class="tc-btn-submit spinner-button">
                    <span class="indicator-label">Submit</span>
                </button>
            </div>
        </form>
    </div>
</div>
<!--end::Modal content-->
