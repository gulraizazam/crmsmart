<!--begin::Modal content-->
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



