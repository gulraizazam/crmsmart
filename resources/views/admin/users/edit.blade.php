<!--begin::Modal content-->
<div class="modal-content usr-modal">
    <div class="modal-header usr-modal-header" id="kt_modal_edit_user_header">
        <h2 class="fw-bolder">Edit User</h2>
        <div class="btn btn-icon btn-sm btn-active-icon-primary popup-close" data-kt-users-modal-action="close">
            <span class="svg-icon svg-icon-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                </svg>
            </span>
        </div>
    </div>
    <form id="modal_edit_user_form" method="post" action="">
        @method('put')
        <div class="modal-body usr-modal-body scroll-y">
            <div class="d-flex flex-column scroll-y" id="kt_modal_edit_user_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                <div class="form-group">
                    <div class="row">
                        <div class="fv-row col-md-12">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Name <span class="text text-danger">*</span></label>
                            <input id="edit_user_name" type="text" name="name" value="{{$user->name ?? ''}}" class="form-control form-control-lg form-control-solid mb-2">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="fv-row col-md-6">
                            <i onclick="phoneReset('phone-field')" class="fa fa-edit sneat-phone-reset"></i>
                            <label class="required fw-bold fs-6 mb-2 pl-0">Phone <span class="text text-danger">*</span></label>
                            <input id="edit_user_phone" type="text" oninput="phoneField(this)" name="phone" value="{{$user->phone ?? ''}}" class="form-control phone-field form-control-lg form-control-solid mb-2" />
                            <input id="edit_old_user_phone" type="hidden" name="old_phone" class="form-control phone-field form-control-lg form-control-solid mb-2" />
                        </div>
                        <div class="fv-row col-md-6">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Email <span class="text text-danger">*</span></label>
                            <input id="edit_user_email" type="email" name="email" value="{{$user->email ?? ''}}" class="form-control form-control-lg form-control-solid" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="fv-row col-md-12">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Gender <span class="text text-danger">*</span></label>
                            <select id="edit_user_gender" class="form-control form-control-solid mb-3 mb-lg-0 select2" name="gender">
                                <option value="">Select</option>
                                <option value="1">Male</option>
                                <option value="2">Female</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="fv-row col-md-12">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Centres <span class="text text-danger">*</span></label>
                            <select id="edit_user_centers" class="form-control form-control-solid mb-3 mb-lg-0 select2" multiple="multiple" name="centers[]">
                            </select>
                        </div>
                    </div>
                    <div class="row mt-10">
                        <div class="fv-row col-md-12">
                            <label class="required fw-bold fs-6 mb-2 pl-0">Roles <span class="text text-danger">*</span></label>
                            <select id="edit_user_roles" class="form-control form-control-solid mb-3 mb-lg-0 select2" multiple="multiple" name="roles[]">
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="usr-footer">
            <button type="reset" class="btn btn-light me-3 popup-close" data-kt-users-modal-action="cancel">Cancel</button>
            <button type="submit" class="btn btn-primary spinner-button">
                <span class="indicator-label">Submit</span>
            </button>
        </div>
    </form>
</div>
<!--end::Modal content-->
