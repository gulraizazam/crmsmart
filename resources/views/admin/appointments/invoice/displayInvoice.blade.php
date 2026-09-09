<!--begin::Modal content-->
<style>
    .di-modal .modal-content { border: none; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 0.25rem 1.125rem rgba(17, 24, 39, 0.12); }
    .di-modal-header { background: #fff; border-bottom: 1px solid #d9dee3; padding: 1rem 1.5rem; }
    .di-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .di-modal-title { color: #566a7f; font-size: 1.05rem; font-weight: 600; margin: 0; font-family: "Public Sans", "Segoe UI", sans-serif; }
    .di-modal-title span { color: #696cff; }
    .di-modal-close { background: #f5f5f9; border: 1px solid #d9dee3; color: #566a7f; width: 32px; height: 32px; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; cursor: pointer; }
    .di-modal-close:hover { background: #eceef1; }
    .di-invoice-brand { display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.5rem; background: #fff; border-bottom: 1px solid #e7e7e8; }
    .di-brand-left { display: flex; flex-direction: column; gap: 4px; }
    .di-brand-address { font-size: 12px; color: #697a8d; margin-top: 2px; }
    .di-brand-contact { font-size: 11px; color: #a1acb8; margin-top: 4px; line-height: 1.5; }
    .di-badge-invoice { background: #f5f5f9; color: #566a7f; border: 1px solid #d9dee3; padding: 0.4rem 0.9rem; border-radius: 0.375rem; font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; }
    .di-meta { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; padding: 0.85rem 1.5rem; background: #fafafa; border-bottom: 1px solid #e7e7e8; }
    .di-meta-item { font-size: 13px; color: #566a7f; }
    .di-meta-item strong { color: #111827; font-weight: 700; }
    .di-body { padding: 1.1rem 1.5rem 1.4rem; background: #fff; }
    .di-body .table { border-radius: 0.375rem; overflow: hidden; border: 1px solid #e7e7e8; margin-bottom: 0; }
    .di-body .table thead th { background: #f9fafb; color: #566a7f; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; padding: 10px 12px; border-bottom: 1px solid #e7e7e8; white-space: nowrap; }
    .di-body .table tbody td { padding: 10px 12px; font-size: 13px; color: #566a7f; vertical-align: middle; }
    .di-total { text-align: right; padding: 14px 0 16px; font-size: 14px; color: #566a7f; font-weight: 600; }
    .di-total span { color: #111827; }
    .di-actions { display: flex; justify-content: center; gap: 10px; padding-top: 4px; }
    .di-body .btn-success { background: #696cff; border: none; border-radius: 0.375rem; padding: 8px 18px; font-weight: 600; font-size: 13px; color: #fff; }
    .di-body .btn-success:hover { background: #5f61e6; color: #fff; }
    .di-body .btn-info { background: #fff; border: 1px solid #d9dee3; border-radius: 0.375rem; padding: 8px 18px; font-weight: 600; font-size: 13px; color: #566a7f; }
    .di-body .btn-info:hover { background: #f5f5f9; color: #566a7f; }
</style>
<div class="modal-content di-modal">
    <!--begin::Modal header-->
    <div class="di-modal-header">
        <div class="di-modal-header-top">
            <h2 class="di-modal-title">{{ ucfirst($patient->name) }}@if(isset($doctor) && $doctor) &mdash; {{ $Invoiceinfo->appointment_type_id == 1 ? 'Consultation' : 'Treatment' }} with <span>{{ $doctor->name }}</span>@endif</h2>
            <div class="di-modal-close popup-close" data-kt-users-modal-action="close">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    </div>
    <!--end::Modal header-->
    <!--begin::Modal body-->
    <div class="scroll-y" style="max-height: 75vh;">
        <div class="di-invoice-brand">
            <div class="di-brand-left">
                <div class="di-brand-logo"><img src="{{ asset('logoClarity.jpg') }}?v=1" alt="Smart Aesthetics" style="height: 50px; width: auto; max-width: 220px; display: inline-block; vertical-align: middle;"></div>
                <div class="di-brand-address">{{$location_info->address}}</div>
                <div class="di-brand-contact">Phone. {{$location_info->fdo_phone}} &nbsp;|&nbsp; Email. {{$account->email}} &nbsp;|&nbsp; https://aestheticlinics.net &nbsp;|&nbsp; NTN. {{$location_info->ntn}} &nbsp;|&nbsp; STN. {{$location_info->stn}}</div>
            </div>
            <div class="di-badge-invoice">Invoice</div>
        </div>
        <div class="di-meta">
            <div class="di-meta-item">{{\Carbon\Carbon::parse($Invoiceinfo->created_at)->format('F j, Y')}}, {{\Carbon\Carbon::parse($Invoiceinfo->created_at)->format('h:i a')}}</div>
            <div class="di-meta-item">Consumption Invoice <strong>#{{$Invoiceinfo->id}}</strong></div>
            <div class="di-meta-item">{{ucfirst($patient->name)}}, <strong>C-{{$patient->id}}</strong></div>
        </div>

        <!--begin::Form-->
        <div class="di-body">
            <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_resourcerotas_scroll">

                <div class="form-group">

                    <div class="row">
                        <div class="table-responsive">
                            <table id="allocate_services" class="table table-bordered table-advance">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Consultancy\Service</th>
                                    <th>Service Price</th>
                                    <th>Discount Name</th>
                                    <th>Discount Price</th>
                                    <th>Subtotal</th>
                                    <th>Tax %</th>
                                    <th>Tax</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                    <td>1</td>
                                    <td>{{$service->name}}</td>
                                    <td>{{number_format($service_price)}}</td>
                                    <td>
                                        @if($Invoiceinfo->discount_name)
                                            {{$Invoiceinfo->discount_name}}
                                        @elseif($discount != null)
                                            {{$discount->name}}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{number_format($Invoiceinfo->tax_including_price)}}</td>
                                    <td>{{number_format($Invoiceinfo->tax_exclusive_serviceprice)}}</td>
                                    <td>{{$Invoiceinfo->tax_percenatage}}</td>
                                    <td>{{$Invoiceinfo->tax_price}}</td>
                                    <td>{{number_format($Invoiceinfo->tax_including_price)}}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="di-total">Total: <span><?php echo number_format($Invoiceinfo->total_price);?>/-</span></div>

                    <div class="di-actions">
                        @if($Invoiceinfo->appointment_type_id == 1)
                            <a class="btn btn-success hidden-print margin-bottom-5" target="_blank"
                            href="{{ route('admin.invoices.invoice_pdf',[$Invoiceinfo->id, 'print', 1]) }}"><i class="fa fa-print"></i> Print Invoice
                            </a>
                            <a class="btn btn-info hidden-print margin-bottom-5" target="_blank"
                            href="{{ route('admin.invoices.invoice_pdf',[$Invoiceinfo->id]) }}"><i class="fa fa-print"></i> Print Consultation Form
                            </a>
                        @else
                            <a class="btn btn-success hidden-print margin-bottom-5" target="_blank"
                            href="{{ route('admin.invoices.invoice_pdf',[$Invoiceinfo->id]) }}"><i class="fa fa-print"></i> Print Invoice
                            </a>
                        @endif
                    </div>

                </div>

            </div>
        </div>
        <!--end::Scroll-->
    </div>
    <!--end::Modal body-->
</div>
<!--end::Modal content-->
