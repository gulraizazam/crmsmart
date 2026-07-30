<!--begin::Modal content-->
<style>
    .td-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .td-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #343a40 50%, #7A8B6A 100%); padding: 22px 28px; position: relative; overflow: hidden; }
    .td-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .td-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .td-modal-title { color: #fff; font-size: 1.1rem; font-weight: 600; margin: 0; }
    .td-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .td-modal-close:hover { background: rgba(255,255,255,0.25); }
    .td-body { padding: 0; background: #fff; }
    .td-layout { display: flex; min-height: 300px; }
    .td-sidebar { width: 200px; min-width: 200px; background: #F8F6F1; border-right: 1px solid #E8EDE5; padding: 20px 16px; }
    .td-sidebar .calendar-left-menu { margin: 0; padding: 0; }
    .td-sidebar .calendar-left-menu li { margin-bottom: 6px; }
    .td-sidebar .calendar-left-menu li a, .td-sidebar .calendar-left-menu li button { border-radius: 8px; font-size: 12px; transition: all 0.2s; }
    .td-main { flex: 1; padding: 24px 28px; }
    .td-info-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #E8EDE5; border-radius: 10px; overflow: hidden; }
    .td-info-table th { background: #F4F7F2; color: #343a40; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; border-bottom: 1px solid #E8EDE5; width: 18%; }
    .td-info-table td { padding: 12px 16px; font-size: 13px; color: #3D4A35; border-bottom: 1px solid #E8EDE5; }
    .td-info-table tr:last-child th, .td-info-table tr:last-child td { border-bottom: none; }
    .td-notes-section { margin-top: 28px; border-top: 2px solid #E8EDE5; padding-top: 20px; }
    .td-notes-title { font-size: 15px; font-weight: 700; color: #3D4A35; margin-bottom: 16px; padding-left: 2px; display: flex; align-items: center; gap: 8px; }
    .td-notes-title::before { content: ''; width: 4px; height: 18px; background: linear-gradient(180deg, #343a40, #C4A265); border-radius: 2px; }
    .td-body #treatment_commentsection { margin-bottom: 16px; }
    .td-body .mt-comment { background: #F8F6F1; border-radius: 10px; padding: 14px 16px; margin-bottom: 10px; }
    .td-body .mt-comment-author { font-weight: 600; color: #3D4A35; }
    .td-body .mt-comment-date { color: #8A9580; font-size: 11px; margin-left: 8px; }
    .td-body .mt-comment-text { color: #4A5540; font-size: 13px; margin-top: 6px; line-height: 1.5; }
    .td-body .form-control { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 10px 14px; transition: border-color 0.2s; }
    .td-body .form-control:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .td-body .btn-success { background: linear-gradient(135deg, #343a40, #7A8B6A); border: none; border-radius: 8px; padding: 9px 20px; font-weight: 600; font-size: 13px; transition: all 0.3s; color: #fff; }
    .td-body .btn-success:hover { background: linear-gradient(135deg, #4A5540, #343a40); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
<div class="modal-content td-modal">
    <!--begin::Modal header-->
    <div class="td-modal-header">
        <div class="td-modal-header-top">
            <h2 class="td-modal-title" id="treatment_service_consultancy_name_title"></h2>
            <div class="td-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <!--end::Modal header-->
    <!--begin::Modal body-->
    <div class="scroll-y" style="max-height: 75vh;">
        <div class="td-body">
            <div class="td-layout">
                <div class="td-sidebar">
                    <ul class="calendar-left-menu list-unstyled treatment-detail-actions"></ul>
                </div>
                <div class="td-main">
                    <table class="td-info-table">
                        <tbody>
                        <tr>
                            <th>Patient Name</th>
                            <td id="treatment_patient_name"></td>
                            <th>Patient Phone</th>
                            <td id="treatment_patient_phone"></td>
                        </tr>
                        <tr>
                            <th>Patient ID</th>
                            <td id="treatment_customer_id"></td>
                            <th>Gender</th>
                            <td id="treatment_patient_gender"></td>
                        </tr>
                        <tr>
                            <th>Appointment Time</th>
                            <td id="treatment_patient_scheduled_time"></td>
                            <th>Doctor</th>
                            <td id="treatment_doctor_name"></td>
                        </tr>
                        <tr>
                            <th>City</th>
                            <td id="treatment_city_name"></td>
                            <th>Centre</th>
                            <td id="treatment_center_name"></td>
                        </tr>
                        <tr>
                            <th>Appointment Status</th>
                            <td id="treatment_appointment_status"></td>
                            <th>Treatment</th>
                            <td id="treatment_service_consultancy_name"></td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="td-notes-section">
                        <div class="td-notes-title">Notes</div>
                        <div id="treatment_commentsection"></div>

                        <form id="treatment_cment">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12">
                                        <textarea id="treatment_comment" name="comment" class="form-control" placeholder="Write a note..." required=""></textarea>
                                    </div>
                                    <input type="hidden" name="appointment_id" id="treatment_comment_appointment_id" class="form-control" value=""><br>
                                    <div class="col-md-12 mt-5">
                                        <button type="button" name="Add_treatment_comment" id="Add_treatment_comment" class="btn btn-success">Comment</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal body-->
</div>
<!--end::Modal content-->
