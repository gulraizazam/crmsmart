<!--begin::Modal content-->
<style>
    .cc-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .cc-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .cc-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .cc-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .cc-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .cc-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .cc-modal-close:hover { background: rgba(255,255,255,0.25); }
    .cc-modal-body { padding: 24px 28px; background: #fff; }
    .cc-modal-body label { font-size: 12px; font-weight: 600; color: #007bff; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .cc-modal-body .form-control, .cc-modal-body .form-control-solid { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 9px 14px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff; }
    .cc-modal-body .form-control:focus, .cc-modal-body .form-control-solid:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .cc-modal-body .select2-container--default .select2-selection--single { border: 1px solid #D5DDD0; border-radius: 8px; height: 38px; }
    .cc-modal-body .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; color: #3D4A35; padding-left: 14px; }
    .cc-modal-body .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .cc-footer { display: flex; align-items: center; justify-content: center; gap: 12px; padding-top: 20px; margin-top: 8px; border-top: 2px solid #E8EDE5; }
    .cc-btn-cancel { background: #fff; border: 1px solid #D5DDD0; border-radius: 8px; padding: 10px 28px; font-weight: 600; font-size: 13px; color: #007bff; transition: all 0.2s; cursor: pointer; }
    .cc-btn-cancel:hover { background: #F8F6F1; border-color: #7A8B6A; }
    .cc-btn-submit { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 8px; padding: 10px 32px; font-weight: 600; font-size: 13px; color: #fff; transition: all 0.3s; cursor: pointer; }
    .cc-btn-submit:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
<div class="modal-content cc-modal">
    <div class="cc-modal-header" id="kt_modal_password_header">
        <div class="cc-modal-header-top">
            <h2 class="cc-modal-title" id="create_consultation_heading">Create New Consultation</h2>
            <div class="cc-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="cc-modal-body scroll-y">
        <form id="modal_create_consultancy_form" method="post" action="{{route('admin.appointments.store')}}">

            <input type="hidden" id="consultancy_lead_id" name="lead_id">
            <input type="hidden" id="consultancy_city_id" name="city_id">
            <input type="hidden" id="consultancy_location_id" name="location_id">
            <input type="hidden" id="consultancy_doctor_id" name="doctor_id">
            <input type="hidden" id="consultancy_start" name="start">
            <input type="hidden" id="consultancy_resource_id" name="resource_id">
            <input type="hidden" id="consultancy_appointment_type" name="appointment_type" value="consulting">
            <input type="hidden" id="consultancy_town_id" name="town_id">

            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_appointment_type_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                <div class="form-group">
                    <div class="row">

                        <div class="fv-row col-md-6 mt-3 consult-type">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Consultancy Type <span class="text text-danger">*</span> </label>
                            <select id="create_consultancy_types" class="form-control select2" name="consultancy_type"></select>
                        </div>

                        <div class="fv-row col-md-6 mt-3 consultancy-service">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Services <span class="text text-danger">*</span> </label>
                            <select id="create_consultancy_service" class="form-control select2" name="service_id"></select>
                        </div>

                        <div class="fv-row col-md-12 mt-3" id="lead_id">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Phone Number <span class="text text-danger">*</span></label>
                            <input class="form-control lead_search_id" name="phone" placeholder="Enter Phone Number" type="tel" pattern="[0-9]*" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" autofocus>

                            <input type="hidden" onchange="getLeadDetail($(this))"  name="lead_id" class="filter-field search_field" id="create_lead_search">
                            <input type="hidden" id="create_old_consultancy_phone" class="form-control" name="old_phone">
                            <input type="hidden" id="new_patient" name="new_patient" value="0">
                            <span onclick="addLeads()" class="croxcli" style="position:absolute; padding-left: 0% !important; top:37px; right:20px;"><i class="fa fa-times" aria-hidden="true"></i></span>
                            <div class="suggesstion-box" style="display: none;">
                                <ul class="suggestion-list"></ul>
                            </div>
                        </div>

                        <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Full Name <span class="text text-danger">*</span> </label>
                            <input readonly id="create_patient_name" class="form-control" name="name">
                        </div>

                        <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Gender <span class="text text-danger">*</span></label>
                            <select disabled id="create_consultancy_gender" class="form-control" name="gender"></select>
                        </div>

                        <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Scheduled Time <span class="text text-danger">*</span></label>
                            <input type="text" id="create_scheduled_time" name="scheduled_time" class="form-control scheduled_time default-timepicker">
                        </div>

                        <div class="fv-row col-md-6 mt-3">
                            <label class="fw-bold fs-6 mb-2 pl-0">Referred By (Patient Search)</label>
                            <select id="create_consultancy_referred_by" class="form-control form-control-solid mb-3 mb-lg-0 select2-patient-search" name="referred_by">
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <div class="cc-footer">
                <button type="reset" class="cc-btn-cancel popup-close" data-kt-users-modal-action="cancel">Cancel</button>
                <button type="submit" class="cc-btn-submit spinner-button">
                    <span class="indicator-label">Submit</span>
                </button>
            </div>
        </form>
    </div>
</div>
<!--end::Modal content-->



