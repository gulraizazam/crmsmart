<!--begin::Modal content-->
<div class="modal-content ld-modal">
    <div class="modal-header ld-modal-header" id="kt_modal_password_header">
        <h2 class="fw-bolder">Update Lead Status</h2>
        <div class="btn btn-icon btn-sm btn-active-icon-primary popup-close" data-kt-users-modal-action="close">
            <span class="svg-icon svg-icon-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                </svg>
            </span>
        </div>
    </div>
    <form id="modal_change_status_form" method="post" action="">
        <div class="modal-body ld-modal-body scroll-y">
            <div class="d-flex flex-column scroll-y" id="kt_modal_user_type_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">
                <div class="form-group">
                    <div class="row">
                        <input type="hidden" id="lead_id">
                        <div class="fv-row col-md-12 mt-5">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Status </label>
                            <select id="update_status_id" class="form-control form-control-solid mb-3 mb-lg-0 select2">
                            </select>
                        </div>
                        <div class="fv-row col-md-12 mt-5 d-none">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Status </label>
                            <select id="update_child_status_id" class="form-control form-control-solid mb-3 mb-lg-0 select2">
                            </select>
                        </div>
                        <div class="fv-row col-md-12 mt-5 d-none">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Comment </label>
                            <textarea id="lead_status_comment1_id" name="" placeholder="Type your comment.." class="form-control" rows="5"></textarea>
                        </div>
                        <div class="fv-row col-md-12 mt-5">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Comment </label>
                            <textarea id="lead_status_comment2_id" name="comment2" placeholder="Type your comment.." class="form-control" rows="5"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ld-footer">
            <button type="reset" class="btn btn-light me-3 popup-close" data-kt-users-modal-action="cancel">Cancel</button>
            <button type="button" onclick="updateLeadStatus();" class="btn btn-primary spinner-button">
                <span class="indicator-label">Submit</span>
            </button>
        </div>
    </form>
</div>
<!--end::Modal content-->
