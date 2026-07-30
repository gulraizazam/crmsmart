<!--begin::Modal content-->
<style>
    .ti-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .ti-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 22px 28px; position: relative; overflow: hidden; }
    .ti-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .ti-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .ti-modal-title { color: #fff; font-size: 1.1rem; font-weight: 600; margin: 0; }
    .ti-modal-title span { color: #C4A265; }
    .ti-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .ti-modal-close:hover { background: rgba(255,255,255,0.25); }
    .ti-body { padding: 20px 28px 24px; background: #fff; }
    .ti-body .table { border-radius: 10px; overflow: hidden; border: 1px solid #E8EDE5; margin-bottom: 0; }
    .ti-body .table thead th { background: #F4F7F2; color: #3D4A35; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 14px; border-bottom: 2px solid #D5DDD0; border-right: 1px solid #E8EDE5; white-space: nowrap; }
    .ti-body .table thead th:last-child { border-right: none; }
    .ti-body .table tbody td { padding: 12px 14px; font-size: 13px; color: #4A5540; border-right: 1px solid #E8EDE5; vertical-align: middle; }
    .ti-body .table tbody td:last-child { border-right: none; }
    .ti-body .table tbody tr:hover { background: #FAFDF8; }
    .ti-body .table tbody tr.HR_555 td { padding: 12px 14px; }
    .ti-body .form-control, .ti-body .select2-container .select2-selection--single { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 8px 12px; transition: border-color 0.2s; }
    .ti-body .form-control:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); }
    .ti-body .btn-primary { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 10px; padding: 10px 22px; font-weight: 600; font-size: 13px; letter-spacing: 0.3px; transition: all 0.3s; }
    .ti-body .btn-primary:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); }
    .ti-body label strong, .ti-body .col-md-10 strong { color: #3D4A35; font-size: 13px; }
    .ti-body .col-md-10 .float-right { color: #007bff; }
    .ti-body .alert { border-radius: 8px; font-size: 13px; }
    .ti-paid-badge { text-align: center; padding: 40px 20px; }
    .ti-paid-badge h2 { color: #7A8B6A; font-weight: 700; font-size: 1.3rem; }
</style>
<div class="modal-content ti-modal">
    <!--begin::Modal header-->
    <div class="ti-modal-header">
        <div class="ti-modal-header-top">
            <h2 class="ti-modal-title">Generate Invoice for <span>{{ isset($patient) ? ucfirst($patient->name) : '' }}</span></h2>
            <div class="ti-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <!--end::Modal header-->
    <!--begin::Modal body-->
    <div class="scroll-y" style="max-height: 75vh;">
        <!--begin::Form-->
        <div class="ti-body">
            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_invoie_type_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                <div class="form-group">

                    @if($invoice_status != true)
                        @include('admin.appointments.invoice_fields')
                    @else
                        <div class="ti-paid-badge"><h2>Invoice Already Paid</h2></div>
                    @endif

                </div>

            </div>
        </div>
        <!--end::Scroll-->
        <!--end::Form-->
    </div>
    <!--end::Modal body-->
</div>
<!--end::Modal content-->
