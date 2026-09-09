<!--begin::Modal content-->
<div class="modal-content pat-modal">
    <div class="modal-header pat-modal-header" id="kt_modal_password_header">
        <h2 class="fw-bolder">Add Patient</h2>
        <div class="btn btn-icon btn-sm btn-active-icon-primary popup-close" data-kt-users-modal-action="close">
            <span class="svg-icon svg-icon-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                </svg>
            </span>
        </div>
    </div>
    <form id="modal_add_patients_form" method="post" action="{{route('admin.patients.store')}}">
        <div class="modal-body pat-modal-body scroll-y">
            <div class="d-flex flex-column scroll-y" id="kt_modal_patients_type_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                <div class="form-group">
                    <div class="row">

                        <div class="fv-row col-md-6 mt-5">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Name <span class="text text-danger">*</span> </label>
                            <input type="text" name="name" id="add_name" class="form-control">
                        </div>

                        <div class="fv-row col-md-6 mt-5">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Phone <span class="text text-danger">*</span> </label>
                            <input oninput="phoneField(this);" type="text" name="phone" id="add_phone" class="form-control">
                        </div>

                        <div class="fv-row col-md-6 mt-5">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Email </label>
                            <input type="email" id="add_email" name="email" class="form-control">
                        </div>

                        <div class="fv-row col-md-6 mt-5">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Gender <span class="text text-danger">*</span></label>
                            <select id="add_gender_id" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="gender">
                            </select>
                        </div>

                    </div>
                </div>

            </div>
        </div>
        <div class="pat-footer">
            <button type="reset" class="btn btn-light me-3 popup-close" data-kt-users-modal-action="cancel">Cancel</button>
            <button type="submit" class="btn btn-primary spinner-button">
                <span class="indicator-label">Submit</span>
            </button>
        </div>
    </form>
</div>
<!--end::Modal content-->
