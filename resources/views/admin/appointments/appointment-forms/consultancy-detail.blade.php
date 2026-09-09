<!--begin::Modal content-->
<div class="modal-content ad-modal">
    <!--begin::Modal header-->
    <div class="ad-modal-header">
        <div class="ad-modal-header-top">
            <h2 class="ad-modal-title" id="service_consultancy_name_title"></h2>
            <div class="ad-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <!--end::Modal header-->
    <!--begin::Modal body-->
    <div class="scroll-y" style="max-height: 75vh;">
        <div class="ad-body">
            <div class="ad-layout">
                <div class="ad-sidebar">
                    <ul class="calendar-left-menu list-unstyled detail-actions"></ul>
                    @if(Gate::allows('appointments_destroy'))
                    <div class="mt-3">
                        <button type="button" id="delete_appointment_btn" class="btn btn-danger btn-block font-weight-bolder" onclick="deleteAppointmentFromModal(this);">
                            <i class="la la-trash"></i> Delete Appointment
                        </button>
                    </div>
                    @endif
                </div>
                <div class="ad-main">
                    <table class="ad-info-table">
                        <tbody>
                        <tr>
                            <th>Patient Name</th>
                            <td id="patient_name"></td>
                            <th>Patient Phone</th>
                            <td id="patient_phone"></td>
                        </tr>
                        <tr>
                            <th>Patient ID</th>
                            <td id="patient_c_id"></td>
                            <th>Gender</th>
                            <td id="patient_gender"></td>
                        </tr>
                        <tr>
                            <th>Appointment Time</th>
                            <td id="patient_scheduled_time"></td>
                            <th>Doctor</th>
                            <td id="doctor_name"></td>
                        </tr>
                        <tr>
                            <th>City</th>
                            <td id="city_name"></td>
                            <th>Centre</th>
                            <td id="center_name"></td>
                        </tr>
                        <tr>
                            <th>Appointment Status</th>
                            <td id="appointment_status"></td>
                            <th>Treatment</th>
                            <td id="service_consultancy_name"></td>
                        </tr>
                        </tbody>
                    </table>

                    <div class="ad-comments-section">
                        <div class="ad-comments-title">Comments</div>
                        <div id="commentsection"></div>

                        <form id="cment">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" name="comment" id="consultancy_comment" class="form-control" placeholder="Write a comment..." required="">
                                    </div>
                                    <input type="hidden" name="appointment_id" id="comment_appointment_id" class="form-control" value=""><br>
                                    <div class="col-md-12 mt-5">
                                        <button type="button" name="Add_comment" id="Add_comment" class="btn btn-success">Comment</button>
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
