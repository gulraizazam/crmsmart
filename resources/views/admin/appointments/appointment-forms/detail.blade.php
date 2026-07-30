<!--begin::Modal content-->
<style>
    .apd-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .apd-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 20px 28px; position: relative; overflow: hidden; }
    .apd-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .apd-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .apd-modal-title { color: #fff; font-size: 1.15rem; font-weight: 700; margin: 0; letter-spacing: 0.3px; }
    .apd-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .apd-modal-close:hover { background: rgba(255,255,255,0.25); }
    .apd-modal-body { padding: 24px 28px; background: #fff; }
    .apd-info-table { width: 100%; border-collapse: separate; border-spacing: 0; border: 1px solid #E8EDE5; border-radius: 10px; overflow: hidden; }
    .apd-info-table th { background: #F4F7F2; color: #007bff; font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 16px; border-bottom: 1px solid #E8EDE5; width: 18%; }
    .apd-info-table td { padding: 12px 16px; font-size: 13px; color: #3D4A35; border-bottom: 1px solid #E8EDE5; }
    .apd-info-table tr:last-child th, .apd-info-table tr:last-child td { border-bottom: none; }
    .apd-notes-section { margin-top: 28px; border-top: 2px solid #E8EDE5; padding-top: 20px; }
    .apd-notes-title { font-size: 15px; font-weight: 700; color: #3D4A35; margin-bottom: 16px; padding-left: 2px; display: flex; align-items: center; gap: 8px; }
    .apd-notes-title::before { content: ''; width: 4px; height: 18px; background: linear-gradient(180deg, #007bff, #C4A265); border-radius: 2px; }
    .apd-modal-body #appointment_commentsection { margin-bottom: 16px; }
    .apd-modal-body .mt-comment { background: #F8F6F1; border-radius: 10px; padding: 14px 16px; margin-bottom: 10px; }
    .apd-modal-body .mt-comment-author { font-weight: 600; color: #3D4A35; }
    .apd-modal-body .mt-comment-date { color: #8A9580; font-size: 11px; margin-left: 8px; }
    .apd-modal-body .mt-comment-text { color: #4A5540; font-size: 13px; margin-top: 6px; line-height: 1.5; }
    .apd-modal-body .form-control { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 10px 14px; transition: border-color 0.2s; }
    .apd-modal-body .form-control:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); outline: none; }
    .apd-modal-body .btn-success { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 8px; padding: 9px 20px; font-weight: 600; font-size: 13px; transition: all 0.3s; color: #fff; }
    .apd-modal-body .btn-success:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
</style>
<div class="modal-content apd-modal">
    <div class="apd-modal-header" id="kt_modal_password_header">
        <div class="apd-modal-header-top">
            <h2 class="apd-modal-title" id="appointment_service_consultancy_name_title"></h2>
            <div class="apd-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <div class="apd-modal-body scroll-y">
        <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_resourcerotas_scroll">

            <div class="form-group">

                <div class="row">

                    <div class="table-responsive">

                        <table class="apd-info-table">
                            <tbody>
                            <tr class="appointment-detail-actions"></tr>
                            <tr>
                                <th>Patient Name</th>
                                <td id="appointment_patient_name"></td>
                                <th>Patient Phone</th>
                                <td id="appointment_patient_phone"></td>
                            </tr>
                            <tr>
                                <th>Patient ID</th>
                                <td id="appointment_patient_c_id"></td>
                                <th>Gender</th>
                                <td id="appointment_patient_gender"></td>
                            </tr>
                            <tr>
                                <th>Appointment Time</th>
                                <td id="appointment_patient_scheduled_time"></td>
                                <th>Doctor</th>
                                <td id="appointment_doctor_name"></td>
                            </tr>
                            <tr>
                                <th>City</th>
                                <td id="appointment_city_name"></td>
                                <th>Centre</th>
                                <td id="appointment_center_name"></td>
                            </tr>
                            <tr>
                                <th>Appointment Status</th>
                                <td id="appointment_appointment_status"></td>
                                <th>Treatment</th>
                                <td id="appointment_service_consultancy_name"></td>
                            </tr>
                            <tr>
                            </tr>
                            </tbody>
                        </table>

                        <div class="apd-notes-section">
                            <form id="appointment_cment">

                                <div class="apd-notes-title">Notes</div>

                                <div class="row">
                                    <div class="col-md-11">
                                        <div class="col-md-12">
                                            <div class="portlet-body" id="appointment_commentsection">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group">
                                <div class="row">

                                    <div class="col-md-12">
                                        
                                        <textarea id="appointment_comment" name="comment" class="form-control" required="" placeholder="Write a note..."></textarea>
                                    </div>
                                    <input type="hidden" name="appointment_id" id="appointment_comment_appointment_id" class="form-control" value=""><br>
                                    <div class="col-md-12 mt-5">
                                        <button type="button" name="Add_appointment_comment" id="Add_appointment_comment" class="btn btn-success">Comment</button>
                                    </div>

                                </div>
                            </div>

                            </form>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
<!--end::Modal content-->



