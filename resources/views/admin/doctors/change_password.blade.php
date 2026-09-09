<!--begin::Modal content-->
<div class="modal-content doc-modal">
    <div class="modal-header doc-modal-header" id="kt_modal_password_header">
        <h2 class="fw-bolder">Change Password</h2>
        <div class="btn btn-icon btn-sm btn-active-icon-primary popup-close" data-kt-users-modal-action="close">
            <span class="svg-icon svg-icon-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                </svg>
            </span>
        </div>
    </div>
    <form id="modal_change_form" method="PATCH" action="{{route('admin.doctors.save_password')}}">
        <input type="hidden" id="password_change_id" name="id">
        <div class="modal-body doc-modal-body scroll-y">
            <div class="d-flex flex-column scroll-y" id="kt_modal_password_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">
                <div class="form-group">
                    <div class="row">
                        <div class="fv-row col-md-6">
                            <label class="required fw-bold fs-6 mb-2 pl-0">New Password</label>
                            <input type="password" id="password" name="password" class="form-control form-control-lg form-control-solid mb-2">
                        </div>
                        <div class="fv-row col-md-6">
                            <label class="required fw-bold fs-6 mb-2 pl-0">New Password Confirmation</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control form-control-lg form-control-solid" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="doc-footer">
            <button type="reset" class="btn btn-light me-3 popup-close" data-kt-users-modal-action="cancel">Cancel</button>
            <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
                <span class="indicator-label">Submit</span>
            </button>
        </div>
    </form>
</div>
<!--end::Modal content-->
