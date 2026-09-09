<!--begin::Modal content-->
<div class="modal-content">
    <div class="modal-header" id="kt_modal_password_header">
        <h2 class="fw-bolder rota-title">Plan SMS Logs</h2>
        <div class="btn btn-icon btn-sm btn-active-icon-primary popup-close" data-kt-users-modal-action="close">
            <span class="svg-icon svg-icon-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                </svg>
            </span>
        </div>
    </div>
    <div class="modal-body scroll-y">
        <div class="d-flex flex-column scroll-y" id="kt_modal_resourcerotas_scroll">
            <div class="form-group">
                <div class="table-responsive">
                    <table id="sms_log_table" class="table table-bordered table-advance">
                        <thead>
                            <tr>
                                <th>Phone</th>
                                <th>Text</th>
                                <th>Sent</th>
                                <th>Is_Refund</th>
                                <th>Created at</th>
                            </tr>
                        </thead>
                        <tbody id="sms_log_rows"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Modal content-->
