<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>DEMO - Invoice #{{$Invoiceinfo->id}}</title>
    <meta content="DEMO Medical Spa" name="description" />
    <meta content="Red Signal" name="author"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #3D4A35; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .invoice-pdf { max-width: 700px; margin: 0 auto; padding: 0; }

        /* Header */
        .inv-header { background: #ffffff; padding: 20px 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #E8EDE5; }
        .inv-brand { font-family: 'Georgia', serif; font-size: 26px; font-weight: 700; color: #3D4A35; letter-spacing: 1.5px; line-height: 1; }
        .inv-brand img { height: 90px; max-width: 320px; width: auto; display: block; }
        .inv-brand .amp { color: #C4A265; }
        .inv-badge { background: #3D4A35; border: 1px solid #3D4A35; color: #fff; padding: 10px 26px; border-radius: 6px; font-size: 13px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; }

        /* Contact bar */
        .inv-contact { background: #F4F7F2; padding: 14px 32px; border-bottom: 1px solid #E0E6DC; }
        .inv-address { font-size: 12px; color: #007bff; font-weight: 500; margin-bottom: 3px; }
        .inv-details { font-size: 10.5px; color: #8A9580; line-height: 1.6; }

        /* Meta section */
        .inv-meta { padding: 20px 32px; display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #E8EDE5; }
        .inv-meta-left { display: flex; flex-direction: column; gap: 4px; }
        .inv-meta-label { font-size: 10px; color: #8A9580; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        .inv-meta-value { font-size: 14px; color: #3D4A35; font-weight: 600; }
        .inv-meta-right { text-align: right; display: flex; flex-direction: column; gap: 4px; }
        .inv-invoice-num { font-size: 20px; color: #007bff; font-weight: 700; }

        /* Table */
        .inv-table-wrap { padding: 20px 32px 16px; }
        table.inv-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.inv-table th { background: #007bff; color: #fff; font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px 12px; text-align: left; }
        table.inv-table th:first-child { border-radius: 6px 0 0 0; }
        table.inv-table th:last-child { border-radius: 0 6px 0 0; }
        table.inv-table td { padding: 11px 12px; border-bottom: 1px solid #E8EDE5; color: #4A5540; font-size: 12px; }
        table.inv-table tr:last-child td { border-bottom: none; }
        table.inv-table tr:nth-child(even) td { background: #FAFDF8; }

        /* Grand tax (hidden by default, kept for compatibility) */
        .grand-tax { display: none; }

        /* Feedback section */
        .inv-feedback { padding: 24px 32px 8px; }
        .inv-feedback-title { font-size: 14px; font-weight: 600; color: #3D4A35; margin-bottom: 14px; }
        .inv-rating { display: flex; align-items: center; gap: 8px; margin-bottom: 24px; }
        .inv-rating-label { font-size: 12px; color: #8A9580; font-weight: 500; }
        .inv-rating-circle { width: 34px; height: 34px; border: 1.5px solid #C4A265; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; color: #007bff; font-weight: 500; }
        .inv-feedback-lines { padding: 0 32px 10px; }
        .inv-feedback-lines-title { font-size: 14px; font-weight: 600; color: #3D4A35; margin-bottom: 16px; }
        .inv-feedback-line { border-bottom: 1px dotted #C4A265; height: 28px; margin-bottom: 4px; }

        /* Signature section */
        .inv-signatures { padding: 50px 32px 28px; display: flex; justify-content: space-between; }
        .inv-sig-block { width: 200px; }
        .inv-sig-line { border-top: 1.5px solid #3D4A35; padding-top: 10px; }
        .inv-sig-title { font-size: 12px; font-weight: 600; color: #007bff; }
        .inv-sig-name { font-size: 11px; font-weight: 700; color: #3D4A35; margin-top: 2px; }

        /* Footer accent */
        .inv-footer { height: 5px; background: linear-gradient(90deg, #3D4A35, #7A8B6A, #C4A265); }

        @if($download != 'download')
            @media not print {
                .invoice-pdf { margin-top: 40px; margin-bottom: 40px; border: 1px solid #E8EDE5; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
            }
            @page { size: auto; margin: 10mm 8mm; }
        @endif

        @media print {
            body { background: #fff; }
            .invoice-pdf { max-width: 100%; border: none; box-shadow: none; border-radius: 0; }
        }
    </style>
</head>

<body>
<div class="invoice-pdf">
    @if($invoicestatus->slug == 'cancelled')
        {{-- cancelled watermark placeholder --}}
    @endif

    <!-- Header -->
    <div class="inv-header">
        <div class="inv-brand"><img src="{{ asset('logoClarity.jpg') }}" alt="Clarity Aesthetic"></div>
        <div class="inv-badge">Invoice</div>
    </div>

    <!-- Contact bar -->
    <div class="inv-contact">
        <div class="inv-address">{{$location_info->address}}</div>
        <div class="inv-details">Phone. {{$location_info->fdo_phone}} &nbsp;|&nbsp; Email. {{$account->email}} &nbsp;|&nbsp; www.demo.com &nbsp;|&nbsp; NTN. {{$location_info->ntn}} &nbsp;|&nbsp; STN. {{$location_info->stn}}</div>
    </div>

    <!-- Meta -->
    <div class="inv-meta">
        <div class="inv-meta-left">
            <div class="inv-meta-label">Date</div>
            <div class="inv-meta-value">{{\Carbon\Carbon::parse($Invoiceinfo->created_at)->format('F j, Y')}}, {{\Carbon\Carbon::parse($Invoiceinfo->created_at)->format('h:i a')}}</div>
            <div style="margin-top: 8px;">
                <div class="inv-meta-label">Patient</div>
                <div class="inv-meta-value">{{ucfirst($patient->name)}}, C-{{$patient->id}}</div>
            </div>
        </div>
        <div class="inv-meta-right">
            <div class="inv-meta-label">Invoice Number</div>
            <div class="inv-invoice-num">#{{$Invoiceinfo->id}}</div>
        </div>
    </div>

    <!-- Invoice Table -->
    <div class="inv-table-wrap">
        <table class="inv-table">
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
                    <td>
                        @if($appointment_info?->appointment_type_id == 2 && isset($service_price_display))
                            {{number_format($service_price_display)}}
                        @elseif($Invoiceinfo->is_exclusive == '0' && isset($bundle) && $bundle?->type == 'single')
                            {{number_format(($Invoiceinfo->service_price)-($Invoiceinfo->tax_price))}}
                        @elseif($Invoiceinfo->is_exclusive == '0' && isset($bundle) && $bundle?->type == 'multiple')
                            {{number_format($Invoiceinfo->service_price)}}
                        @elseif($Invoiceinfo->is_exclusive == '1')
                            {{number_format($Invoiceinfo->service_price)}}
                        @else
                            {{number_format($Invoiceinfo->service_price)}}
                        @endif
                    </td>
                    <td>
                        @if($discount != null)
                            {{$discount->name}}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{number_format($Invoiceinfo->tax_including_price)}}</td>
                    <td>
                        @if($Invoiceinfo->is_exclusive == '0')
                            @if($Invoiceinfo->discount_price == null && isset($bundle) && $bundle?->type == 'single')
                                {{number_format(($Invoiceinfo->service_price)-($Invoiceinfo->tax_price))}}
                            @else
                                {{number_format($Invoiceinfo->tax_exclusive_serviceprice)}}
                            @endif
                        @elseif($Invoiceinfo->is_exclusive == '1')
                            {{number_format($Invoiceinfo->tax_exclusive_serviceprice)}}
                        @else
                            {{number_format($Invoiceinfo->tax_exclusive_serviceprice)}}
                        @endif
                    </td>
                    <td>{{$Invoiceinfo->tax_percenatage}}</td>
                    <td>{{$Invoiceinfo->tax_price}}</td>
                    <td>{{number_format($Invoiceinfo->tax_including_price)}}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <table class="grand-tax">
        <tbody>
        <tr>
            <td style="text-align: right;"><strong>Total:</strong> <?php echo number_format($Invoiceinfo->total_price);?>/-</td>
        </tr>
        <tr>
            <td><strong>Note:</strong> All treatment prices are inclusive of taxes.</td>
        </tr>
        </tbody>
    </table>

    <!-- Signatures -->
    <div class="inv-signatures">
        <div class="inv-sig-block">
            <div class="inv-sig-line">
                <div class="inv-sig-title">Customer Signature</div>
                <div class="inv-sig-name">{{ucfirst($patient->name)}}</div>
            </div>
        </div>
        <div class="inv-sig-block">
            <div class="inv-sig-line">
                <div class="inv-sig-title">Doctor's Signature</div>
                <div class="inv-sig-name">{{$appointment_info?->doctor?->name}}</div>
            </div>
        </div>
    </div>

    <!-- Footer accent line -->
    <div class="inv-footer"></div>
</div>

<script>
    window.onload = function() {
        window.print();
    };
    window.onafterprint = function() {
        window.close();
    };
</script>

</body>

</html>
