<!--begin::Modal content-->
<style>
    .sl-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .sl-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .sl-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .sl-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .sl-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .sl-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .sl-modal-close:hover { background: rgba(255,255,255,0.25); }
    .sl-modal-body { padding: 24px 28px; background: #fff; }
    .sl-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #E8EDE5; border-radius: 10px; overflow: hidden; }
    .sl-table thead th { background: #F4F7F2; color: #007bff; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 14px; border-bottom: 2px solid #E8EDE5; white-space: nowrap; }
    .sl-table tbody td { padding: 10px 14px; font-size: 13px; color: #3D4A35; border-bottom: 1px solid #E8EDE5; }
    .sl-table tbody tr:last-child td { border-bottom: none; }
    .sl-table tbody tr:hover { background: #FAFDF8; }
</style>
<div class="modal-content sl-modal">
    <div class="sl-modal-header" id="kt_modal_password_header">
        <div class="sl-modal-header-top">
            <h2 class="sl-modal-title rota-title">Appointment SMS Logs</h2>
            <div class="sl-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="sl-modal-body scroll-y">
        <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_resourcerotas_scroll">

            <div class="form-group">

                <div class="row">

                    <div class="table-responsive">
                        <table id="allocate_services" class="sl-table">

                            <thead>
                            <tr>
                                <th>Phone</th>
                                <th>Text</th>
                                <th>Sent</th>
                                <th>Is Refund</th>
                                <th>Type</th>
                                <th>Created at</th>
                            </tr>
                            </thead>

                            <tbody id="appoint_sms_log_rows"></tbody>

                        </table>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
<!--end::Modal content-->



