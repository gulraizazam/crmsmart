<!--begin::Modal content-->
<style>
    .af-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .af-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .af-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .af-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .af-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .af-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .af-modal-close:hover { background: rgba(255,255,255,0.25); }
    .af-modal-body { padding: 24px 28px; background: #fff; }
    .af-modal-body label { font-size: 12px; font-weight: 600; color: #007bff; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
    .af-modal-body .form-control, .af-modal-body .form-control-solid { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 9px 14px; transition: border-color 0.2s, box-shadow 0.2s; background: #fff; }
    .af-modal-body .form-control:focus, .af-modal-body .form-control-solid:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .af-modal-body .disable-filed { background: #F8F6F1; color: #3D4A35; }
    .af-modal-body .select2-container--default .select2-selection--single { border: 1px solid #D5DDD0; border-radius: 8px; height: 38px; }
    .af-modal-body .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; font-size: 13px; color: #3D4A35; padding-left: 14px; }
    .af-modal-body .select2-container--default .select2-selection--single .select2-selection__arrow { height: 38px; }
    .af-footer { display: flex; align-items: center; justify-content: center; gap: 12px; padding-top: 20px; margin-top: 8px; border-top: 2px solid #E8EDE5; }
    .af-btn-cancel { background: #fff; border: 1px solid #D5DDD0; border-radius: 8px; padding: 10px 28px; font-weight: 600; font-size: 13px; color: #007bff; transition: all 0.2s; cursor: pointer; }
    .af-btn-cancel:hover { background: #F8F6F1; border-color: #7A8B6A; }
    .af-btn-submit { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 8px; padding: 10px 32px; font-weight: 600; font-size: 13px; color: #fff; transition: all 0.3s; cursor: pointer; }
    .af-btn-submit:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
<div class="modal-content af-modal">
    <div class="af-modal-header" id="kt_modal_password_header">
        <div class="af-modal-header-top">
            <h2 class="af-modal-title">Add Feedback</h2>
            <div class="af-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="af-modal-body scroll-y">
        <form id="modal_add_feedback_form" method="post" action="{{route('admin.feedbacks.store')}}">

            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_resources_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                <div class="form-group">
                    <div class="row">

                    <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Patient Name <span class="text text-danger">*</span></label>

                            <input type="hidden" class="filter-field search_field" id="add_patients_id" name="patient_id">
                            <input type="hidden" class="filter-field search_field" id="add_treatment_id" name="treatment">
                            <input type="text" class="form-control disable-filed" id="add_patients_name" readonly="readonly">
                        </div>
                        <div class="fv-row col-md-6 mt-3">
                            <label for="add_plan_id" class="required fw-bold fs-6 mb-2 pl-0">Treatment <span class="text text-danger">*</span></label>
                            <input type="text" class="form-control disable-filed" id="treatment_name" readonly="readonly">
                        </div>

                        <div class="fv-row col-md-6 mt-3">
                            <label for="refund_note" class="required fw-bold fs-6 mb-2 pl-0">Doctor Name </label>
                            <input readonly="readonly" type="text"  id="add_doctor_name" class="form-control disable-filed" name="doctor_name" >
                        </div>
                        <div class="fv-row col-md-6 mt-3">
                            <label for="refund_note" class="required fw-bold fs-6 mb-2 pl-0">Location </label>
                            <input readonly="readonly" type="text"  id="location" class="form-control disable-filed" name="location" >
                        </div>
                        <div class="fv-row col-md-6 mt-3">
                            <label for="refund_note" class="required fw-bold fs-6 mb-2 pl-0">Treatment Scheduled Date</label>
                            <input readonly="readonly" type="text" id="scheduled_date" class="form-control disable-filed" name="scheduled_date">
                        </div>


                        <div class="fv-row col-md-6 mt-3">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Rating <span class="text text-danger">*</span></label>
                            <select id="rating" class="form-control  mb-3 mb-lg-0" name="rating">
                                <option value="">Select Rating</option>
                                <option value="1">1/10</option>
                                <option value="2">2/10</option>
                                <option value="3">3/10</option>
                                <option value="4">4/10</option>
                                <option value="5">5/10</option>
                                <option value="6">6/10</option>
                                <option value="7">7/10</option>
                                <option value="8">8/10</option>
                                <option value="9">9/10</option>
                                <option value="10">10/10</option>
                            </select>
                        </div>


                    </div>
                </div>

            </div>

            <div class="af-footer">
                <button type="reset" class="af-btn-cancel popup-close" data-kt-users-modal-action="cancel">Cancel</button>
                <button type="submit" class="af-btn-submit spinner-button">
                    <span class="indicator-label">Submit</span>
                </button>
            </div>
        </form>
    </div>
</div>
<!--end::Modal content-->



