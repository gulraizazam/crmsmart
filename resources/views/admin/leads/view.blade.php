<!--begin::Modal content-->
<div class="modal-content ld-modal">
    <div class="modal-header ld-modal-header" id="kt_modal_password_header">
        <h2 class="fw-bolder">Lead Details</h2>
        <div class="btn btn-icon btn-sm btn-active-icon-primary popup-close" data-kt-users-modal-action="close">
            <span class="svg-icon svg-icon-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                    <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                </svg>
            </span>
        </div>
    </div>
    <div class="modal-body ld-modal-body scroll-y">
        <div class="sneat-lead-meta">
            <div class="sneat-lead-meta-item">
                <label>Full Name</label>
                <span id="full_name"></span>
            </div>
            <div class="sneat-lead-meta-item">
                <label>Phone</label>
                <span id="phone"></span>
            </div>
            <div class="sneat-lead-meta-item">
                <label>City</label>
                <span id="city"></span>
            </div>
            <div class="sneat-lead-meta-item">
                <label>Centre</label>
                <span id="centre"></span>
            </div>
            <div class="sneat-lead-meta-item">
                <label>Gender</label>
                <span id="gender"></span>
            </div>
            <div class="sneat-lead-meta-item">
                <label>Lead Source</label>
                <span id="lead_source"></span>
            </div>
            <div class="sneat-lead-meta-item">
                <label>Lead Status</label>
                <span id="lead_status"></span>
            </div>
            <div class="sneat-lead-meta-item">
                <label>Active Service</label>
                <span id="activeservice"></span>
            </div>
            <div class="sneat-lead-meta-item">
                <label>SMS Status</label>
                <span id="sms_status"></span>
            </div>
            <div class="sneat-lead-meta-item">
                <label>All Service</label>
                <span id="allservices"></span>
            </div>
            <div class="sneat-lead-meta-item">
                <label>Treatment</label>
                <span id="childservice"></span>
            </div>
        </div>

        <div class="sneat-lead-section">
            <h3 class="sneat-lead-section-title">Services History</h3>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Service</th>
                            <th>Treatment</th>
                            <th>Lead Status</th>
                            <th>Status</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <tbody id="services_history_table">
                        <tr>
                            <td colspan="6" class="text-center">No services found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="sneat-lead-section">
            <h3 class="sneat-lead-section-title">Comments</h3>
            <div class="portlet-body" id="commentsection"></div>
        </div>

        @if(Gate::allows('leads_manage'))
            <div class="sneat-lead-section sneat-lead-comment-form">
                <form id="cment">
                    <label>Comment</label>
                    <input type="text" name="comment" class="form-control" required/>
                    <input type="hidden" name="lead_id" id="comment_lead_id" class="form-control" value="" />
                    <button type="button" name="Add_comment" id="Add_comment" class="btn btn-primary">Comment</button>
                </form>
            </div>
        @endif
    </div>
</div>
<!--end::Modal content-->
