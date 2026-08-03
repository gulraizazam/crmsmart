<!--begin::Modal content-->
<style>
    .di-modal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
    .di-modal-header { background: linear-gradient(135deg, #3D4A35 0%, #007bff 50%, #7A8B6A 100%); padding: 22px 28px; position: relative; overflow: hidden; }
    .di-modal-header::before { content: ''; position: absolute; top: -50%; right: -10%; width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.04); pointer-events: none; }
    .di-modal-header-top { display: flex; align-items: center; justify-content: space-between; }
    .di-modal-title { color: #fff; font-size: 1.1rem; font-weight: 600; margin: 0; }
    .di-modal-title span { color: #C4A265; }
    .di-modal-close { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); color: #fff; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
    .di-modal-close:hover { background: rgba(255,255,255,0.25); }
    .di-invoice-brand { display: flex; align-items: center; justify-content: space-between; padding: 20px 28px; background: #F8F6F1; border-bottom: 1px solid #E8EDE5; }
    .di-brand-left { display: flex; flex-direction: column; gap: 4px; }
    .di-brand-logo { font-family: 'Georgia', serif; font-size: 22px; font-weight: 700; color: #3D4A35; letter-spacing: 1.5px; }
    .di-brand-logo .amp { color: #C4A265; }
    .di-brand-address { font-size: 12px; color: #6B7A60; margin-top: 2px; }
    .di-brand-contact { font-size: 11px; color: #8A9580; margin-top: 4px; line-height: 1.5; }
    .di-badge-invoice { background: linear-gradient(135deg, #3D4A35, #007bff); color: #fff; padding: 8px 22px; border-radius: 8px; font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; }
    .di-meta { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; padding: 16px 28px; background: #fff; border-bottom: 1px solid #E8EDE5; }
    .di-meta-item { font-size: 13px; color: #3D4A35; }
    .di-meta-item strong { color: #007bff; font-weight: 700; }
    .di-body { padding: 20px 28px 24px; background: #fff; }
    .di-body .table { border-radius: 10px; overflow: hidden; border: 1px solid #E8EDE5; margin-bottom: 0; }
    .di-body .table thead th { background: #F4F7F2; color: #3D4A35; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px 14px; border-bottom: 2px solid #D5DDD0; border-right: 1px solid #E8EDE5; white-space: nowrap; }
    .di-body .table thead th:last-child { border-right: none; }
    .di-body .table tbody td { padding: 12px 14px; font-size: 13px; color: #4A5540; border-right: 1px solid #E8EDE5; vertical-align: middle; }
    .di-body .table tbody td:last-child { border-right: none; }
    .di-body .table tbody tr:hover { background: #FAFDF8; }
    .di-total { text-align: right; padding: 14px 0 18px; font-size: 15px; color: #3D4A35; font-weight: 700; }
    .di-total span { color: #007bff; }
    .di-actions { display: flex; justify-content: center; gap: 12px; padding-top: 8px; }
    .di-body .btn-success { background: linear-gradient(135deg, #007bff, #7A8B6A); border: none; border-radius: 10px; padding: 10px 22px; font-weight: 600; font-size: 13px; letter-spacing: 0.3px; transition: all 0.3s; color: #fff; }
    .di-body .btn-success:hover { background: linear-gradient(135deg, #4A5540, #007bff); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(92,107,79,0.3); color: #fff; }
    .di-body .btn-info { background: linear-gradient(135deg, #7B9BAD, #6A8A9C); border: none; border-radius: 10px; padding: 10px 22px; font-weight: 600; font-size: 13px; letter-spacing: 0.3px; transition: all 0.3s; color: #fff; }
    .di-body .btn-info:hover { background: linear-gradient(135deg, #6A8A9C, #5A7A8C); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(123,155,173,0.3); color: #fff; }
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
                <div class="di-brand-logo"><img src="{{ asset('logoClarity.jpg') }}?v=1" alt="Clarity Aesthetic" style="height: 50px; width: auto; max-width: 220px; display: inline-block; vertical-align: middle;"></div>
                <div class="di-brand-address">{{$location_info->address}}</div>
                <div class="di-brand-contact">Phone. {{$location_info->fdo_phone}} &nbsp;|&nbsp; Email. {{$account->email}} &nbsp;|&nbsp; www.demo.com &nbsp;|&nbsp; NTN. {{$location_info->ntn}} &nbsp;|&nbsp; STN. {{$location_info->stn}}</div>
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
