<!--begin::Modal content-->
<style>
    .ci-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .ci-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 22px 28px; position: relative; overflow: hidden; }
    .ci-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .ci-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .ci-modal-title { color: #fff; font-size: 1.1rem; font-weight: 600; margin: 0; }
    .ci-modal-title span { color: #C4A265; }
    .ci-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .ci-modal-close:hover { background: rgba(255,255,255,0.25); }
    .ci-invoice-brand { display: flex; align-items: center; justify-content: space-between; padding: 20px 28px; background: #F8F6F1; border-bottom: 1px solid #E8EDE5; }
    .ci-brand-left { display: flex; flex-direction: column; gap: 4px; }
    .ci-brand-logo { font-family: 'Georgia', serif; font-size: 22px; font-weight: 700; color: #3D4A35; letter-spacing: 1.5px; }
    .ci-brand-logo .amp { color: #C4A265; }
    .ci-brand-address { font-size: 12px; color: #6B7A60; margin-top: 2px; }
    .ci-brand-contact { font-size: 11px; color: #8A9580; margin-top: 4px; line-height: 1.5; }
    .ci-badge-invoice { background: linear-gradient(135deg, #3D4A35, #007bff); color: #fff; padding: 8px 22px; border-radius: 8px; font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; }
    .ci-meta { display: flex; align-items: center; justify-content: space-between; padding: 16px 28px; background: #fff; border-bottom: 1px solid #E8EDE5; }
    .ci-meta-item { font-size: 13px; color: #3D4A35; }
    .ci-meta-item strong { color: #007bff; font-weight: 700; }
    .ci-body { padding: 20px 28px 24px; background: #fff; }
    .ci-body .table { border-radius: 10px; overflow: hidden; border: 1px solid #E8EDE5; margin-bottom: 0; }
    .ci-body .table thead th { background: #F4F7F2; color: #3D4A35; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 14px; border-bottom: 2px solid #D5DDD0; border-right: 1px solid #E8EDE5; white-space: nowrap; }
    .ci-body .table thead th:last-child { border-right: none; }
    .ci-body .table tbody td { padding: 12px 14px; font-size: 13px; color: #4A5540; border-right: 1px solid #E8EDE5; vertical-align: middle; }
    .ci-body .table tbody td:last-child { border-right: none; }
    .ci-body .table tbody tr:hover { background: #FAFDF8; }
    .ci-paid-badge { text-align: center; padding: 40px 20px; }
    .ci-paid-badge h2 { color: #7A8B6A; font-weight: 700; font-size: 1.3rem; }
    .ci-body .btn-success { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 10px; padding: 10px 22px; font-weight: 600; font-size: 13px; letter-spacing: 0.3px; transition: all 0.3s; }
    .ci-body .btn-success:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); }
    .ci-body .btn-info { background: linear-gradient(135deg, #7B9BAD, #6A8A9C); border: none; border-radius: 10px; padding: 10px 22px; font-weight: 600; font-size: 13px; letter-spacing: 0.3px; transition: all 0.3s; }
    .ci-body .btn-info:hover { background: linear-gradient(135deg, #6A8A9C, #5A7A8C); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(123,155,173,0.3); }
    .ci-body .form-control { border: 1px solid #D5DDD0; border-radius: 8px; font-size: 13px; padding: 8px 12px; transition: border-color 0.2s; }
    .ci-body .form-control:focus { border-color: #7A8B6A; box-shadow: 0 0 0 3px rgba(122,139,106,0.12); }
    .ci-body label strong { color: #3D4A35; font-size: 13px; }
</style>
<div class="modal-content ci-modal">
    <!--begin::Modal header-->
    <div class="ci-modal-header">
        <div class="ci-modal-header-top">
            <h2 class="ci-modal-title">{{ isset($patient) ? ucfirst($patient->name) : 'Generate Invoice' }}@if(isset($doctor) && $doctor) &mdash; Consultation with <span>{{ $doctor->name }}</span>@endif</h2>
            <div class="ci-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <!--end::Modal header-->
    <!--begin::Modal body-->
    <div class="scroll-y" style="max-height: 75vh;">
        
        @if($invoice_status != true)
        <div class="ci-invoice-brand">
            <div class="ci-brand-left">
                <div class="ci-brand-logo">DEMO</div>
                <div class="ci-brand-address">{{$location_info->address ?? ''}}</div>
                <div class="ci-brand-contact">Phone. {{$location_info->fdo_phone ?? ''}} &nbsp;|&nbsp; Email. {{$account->email ?? ''}} &nbsp;|&nbsp; www.demo.com &nbsp;|&nbsp; NTN. {{$location_info->ntn ?? ''}} &nbsp;|&nbsp; STN. {{$location_info->stn ?? ''}}</div>
            </div>
            <div class="ci-badge-invoice">Invoice</div>
        </div>
        <div class="ci-meta">
            <div class="ci-meta-item">{{\Carbon\Carbon::now()->format('F j, Y')}}, {{\Carbon\Carbon::now()->format('h:i a')}}</div>
            <div class="ci-meta-item">{{isset($patient) ? ucfirst($patient->name) : ''}}, <strong>C-{{$patient->id ?? ''}}</strong></div>
        </div>

        <!--begin::Form-->
        <div class="ci-body">
            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_appointment_type_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_user_header" data-kt-scroll-wrappers="#kt_modal_add_user_scroll" data-kt-scroll-offset="300px">

                <div class="form-group">
                    @include('admin.appointments.consultancyinvoice.fields')
                </div>

            </div>
        </div>
        <!--end::Scroll-->
        @else
            <div class="ci-paid-badge"><h2>Invoice Already Paid</h2></div>
        @endif
        <!--end::Form-->
    </div>
    <!--end::Modal body-->
</div>
<!--end::Modal content-->




