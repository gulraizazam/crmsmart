<!--begin::Modal content-->
<div class="modal-content perm-modal">
    <div class="modal-header perm-modal-header" id="kt_modal_edit_user_header">
        <h2 class="fw-bolder">Edit Permission</h2>
        <div class="btn btn-icon btn-sm btn-active-icon-primary popup-close" data-kt-users-modal-action="close">
            <span class="svg-icon svg-icon-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                </svg>
            </span>
        </div>
    </div>
    <form id="modal_edit_permission_form" class="form" method="post" action="">
        @method('put')
        @csrf
        <div class="modal-body perm-modal-body scroll-y">
            <div class="d-flex flex-column scroll-y" id="kt_modal_edit_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                <div class="form-group">
                    <div class="fv-rowS">
                        <label class="required fw-bold fs-6 mb-2 pl-0">Name</label>
                        <input type="text" id="permission_name" name="name" class="form-control form-control-lg form-control-solid mb-2" value="" placeholder="Name" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="fv-row">
                        <label class="required fw-bold fs-6 mb-2 pl-0">Title</label>
                        <input type="text" id="permission_title" name="title" class="form-control form-control-lg form-control-solid" value="" placeholder="Title" />
                    </div>
                </div>
                <div class="form-group">
                    <div class="fv-row">
                        <label class="required fw-bold fs-6 mb-2 pl-0">Parent</label>
                        <select id="permission_parent" class="form-control edit-permissions-dropdown form-control-solid mb-3 mb-lg-0 select2" name="parent_id">
                        </select>
                    </div>
                </div>

            </div>
        </div>
        <div class="perm-footer">
            <button type="reset" class="btn btn-light me-3 popup-close" data-kt-users-modal-action="cancel">Cancel</button>
            <button type="submit" class="btn btn-primary spinner-button">
                <span class="indicator-label">Submit</span>
            </button>
        </div>
    </form>
</div>
<!--end::Modal content-->
