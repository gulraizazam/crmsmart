<!--begin::Modal content-->
<style>
    .ci-modal .modal-content { border: none; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 0.25rem 1.125rem rgba(17, 24, 39, 0.12); }
    .ci-modal-header { background: #fff; border-bottom: 1px solid #d9dee3; padding: 1rem 1.5rem; }
    .ci-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .ci-modal-title { color: #566a7f; font-size: 1.05rem; font-weight: 600; margin: 0; font-family: "Public Sans", "Segoe UI", sans-serif; }
    .ci-modal-title span { color: #696cff; }
    .ci-modal-close { background: #f5f5f9; border: 1px solid #d9dee3; color: #566a7f; width: 32px; height: 32px; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; cursor: pointer; }
    .ci-modal-close:hover { background: #eceef1; }
    .ci-invoice-brand { display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.5rem; background: #fff; border-bottom: 1px solid #e7e7e8; }
    .ci-brand-left { display: flex; flex-direction: column; gap: 4px; }
    .ci-brand-address { font-size: 12px; color: #697a8d; margin-top: 2px; }
    .ci-brand-contact { font-size: 11px; color: #a1acb8; margin-top: 4px; line-height: 1.5; }
    .ci-badge-invoice { background: #f5f5f9; color: #566a7f; border: 1px solid #d9dee3; padding: 0.4rem 0.9rem; border-radius: 0.375rem; font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }
    .ci-meta { display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1.5rem; background: #fafafa; border-bottom: 1px solid #e7e7e8; }
    .ci-meta-item { font-size: 13px; color: #566a7f; }
    .ci-meta-item strong { color: #111827; font-weight: 700; }
    .ci-body { padding: 1.1rem 1.5rem 1.4rem; background: #fff; }
    .ci-body .table { border-radius: 0.375rem; overflow: hidden; border: 1px solid #e7e7e8; margin-bottom: 0; }
    .ci-body .table thead th { background: #f9fafb; color: #566a7f; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 12px; border-bottom: 1px solid #e7e7e8; white-space: nowrap; }
    .ci-body .table tbody td { padding: 10px 12px; font-size: 13px; color: #566a7f; vertical-align: middle; }
    .ci-paid-badge { text-align: center; padding: 40px 20px; }
    .ci-paid-badge h2 { color: #71dd37; font-weight: 700; font-size: 1.3rem; }
    .ci-body .btn-success { background: #696cff; border: none; border-radius: 0.375rem; padding: 8px 18px; font-weight: 600; font-size: 13px; }
    .ci-body .btn-success:hover { background: #5f61e6; }
    .ci-body .btn-info { background: #fff; border: 1px solid #d9dee3; border-radius: 0.375rem; padding: 8px 18px; font-weight: 600; font-size: 13px; color: #566a7f; }
    .ci-body .btn-info:hover { background: #f5f5f9; color: #566a7f; }
    .ci-body .form-control { border: 1px solid #d9dee3; border-radius: 0.375rem; font-size: 13px; padding: 8px 12px; }
    .ci-body .form-control:focus { border-color: #696cff; box-shadow: 0 0 0 3px rgba(105, 108, 255, 0.12); }
    .ci-body label strong { color: #566a7f; font-size: 13px; }
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
                <div class="ci-brand-logo"><img src="{{ asset('logoClarity.jpg') }}?v=1" alt="Smart Aesthetics" style="height: 50px; width: auto; max-width: 220px; display: inline-block; vertical-align: middle;"></div>
                <div class="ci-brand-address">{{$location_info->address ?? ''}}</div>
                <div class="ci-brand-contact">Phone. {{$location_info->fdo_phone ?? ''}} &nbsp;|&nbsp; Email. {{$account->email ?? ''}} &nbsp;|&nbsp; https://aestheticlinics.net &nbsp;|&nbsp; NTN. {{$location_info->ntn ?? ''}} &nbsp;|&nbsp; STN. {{$location_info->stn ?? ''}}</div>
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




