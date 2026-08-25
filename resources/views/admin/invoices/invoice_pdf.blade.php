<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Clarity Aesthetic — Treatment Invoice #{{$Invoiceinfo->id}}</title>
    <meta content="Clarity Aesthetic" name="description" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #22252B;
            background: #EEF0F3;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .tinv-shell {
            max-width: 760px;
            margin: 0 auto;
            background: #FFFFFF;
            overflow: hidden;
        }

        /* Dark header band */
        .tinv-hero {
            background: #1E2229;
            color: #FFFFFF;
            padding: 30px 36px 28px;
            position: relative;
            overflow: hidden;
        }
        .tinv-hero::after {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: linear-gradient(135deg, #D89B8A 0%, #B5735F 100%);
            opacity: 0.16;
        }
        .tinv-hero::before {
            content: '';
            position: absolute;
            left: 0; top: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(180deg, #D89B8A 0%, #B5735F 100%);
        }
        .tinv-hero-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        .tinv-brand img {
            height: 74px;
            width: auto;
            max-width: 240px;
            display: block;
            filter: brightness(0) invert(1);
        }
        .tinv-hero-doc { text-align: right; }
        .tinv-hero-doc .tinv-tag {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(216, 155, 138, 0.2);
            border: 1px solid #D89B8A;
            color: #D89B8A;
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
            border-radius: 20px;
            margin-bottom: 8px;
        }
        .tinv-hero-doc .tinv-num {
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 1px;
            color: #FFFFFF;
        }
        .tinv-hero-doc .tinv-num strong { font-weight: 700; color: #D89B8A; }

        /* Ribbon between hero and body */
        .tinv-ribbon {
            background: #F7EEEA;
            padding: 14px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #EAD9D2;
        }
        .tinv-ribbon-block { display: flex; align-items: center; gap: 10px; }
        .tinv-ribbon-icon {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: #FFFFFF;
            color: #B5735F;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-family: Georgia, serif;
            font-weight: 700;
            font-size: 13px;
            border: 1px solid #D89B8A;
        }
        .tinv-ribbon-label {
            font-size: 9.5px;
            color: #8A6A5F;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .tinv-ribbon-value {
            font-size: 13px;
            color: #22252B;
            font-weight: 700;
            margin-top: 2px;
        }

        /* Body */
        .tinv-body { padding: 26px 36px 18px; }

        .tinv-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }
        .tinv-section-title .bar {
            width: 24px;
            height: 3px;
            background: linear-gradient(90deg, #D89B8A, #B5735F);
        }
        .tinv-section-title h4 {
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #4A4E58;
            font-weight: 700;
        }

        /* Line items */
        table.tinv-items {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #E7E5E3;
        }
        table.tinv-items thead th {
            background: #22252B;
            color: #F7EEEA;
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 12px 10px;
            text-align: left;
        }
        table.tinv-items thead th:last-child,
        table.tinv-items tbody td:last-child { text-align: right; }
        table.tinv-items tbody td {
            padding: 14px 10px;
            font-size: 12px;
            color: #3A3D45;
            border-bottom: 1px solid #EFECEA;
            vertical-align: middle;
        }
        table.tinv-items tbody tr:last-child td { border-bottom: none; }
        .tinv-item-name {
            font-family: Georgia, serif;
            font-size: 14px;
            color: #22252B;
            font-weight: 700;
        }
        .tinv-item-sub {
            font-size: 10.5px;
            color: #7A7D85;
            margin-top: 2px;
            font-style: italic;
        }

        /* Totals stack */
        .tinv-totals {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .tinv-totals-card {
            width: 320px;
            background: #FBFAF9;
            border: 1px solid #E7E5E3;
            border-radius: 4px;
            padding: 16px 20px;
        }
        .tinv-totals-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 12.5px;
            color: #4A4E58;
        }
        .tinv-totals-row.divide { border-top: 2px solid #22252B; margin-top: 8px; padding-top: 12px; }
        .tinv-totals-row.grand {
            background: #22252B;
            color: #FFFFFF;
            margin: 10px -20px -16px;
            padding: 14px 20px;
            border-radius: 0 0 3px 3px;
            font-size: 14px;
        }
        .tinv-totals-row.grand .amt {
            font-family: Georgia, serif;
            font-size: 22px;
            color: #D89B8A;
        }

        /* Payment strip */
        .tinv-pay-strip {
            margin: 22px 0 0;
            padding: 14px 18px;
            background: #FFF9F6;
            border-left: 3px solid #B5735F;
            font-size: 11.5px;
            color: #6B4F44;
            line-height: 1.5;
        }
        .tinv-pay-strip strong { color: #22252B; font-family: Georgia, serif; }

        /* Signatures */
        .tinv-sigs {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            padding: 46px 36px 24px;
            border-top: 1px dashed #E7E5E3;
            margin-top: 20px;
        }
        .tinv-sig {
            flex: 1;
            text-align: left;
        }
        .tinv-sig-line {
            border-top: 2px solid #22252B;
            padding-top: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #22252B;
        }
        .tinv-sig-name {
            font-family: Georgia, serif;
            font-size: 12px;
            color: #4A4E58;
            margin-top: 3px;
            font-style: italic;
        }

        /* Footer band */
        .tinv-footer {
            background: linear-gradient(90deg, #22252B 0%, #3A3D45 50%, #22252B 100%);
            color: #EEE2DD;
            padding: 14px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            letter-spacing: 1.2px;
        }
        .tinv-footer .brand-mark {
            font-family: Georgia, serif;
            color: #D89B8A;
            letter-spacing: 3px;
            font-style: italic;
        }
        .tinv-footer .foot-meta { color: #B8B0AB; }

        @if($download != 'download')
            @media not print {
                .tinv-shell { margin-top: 30px; margin-bottom: 30px; box-shadow: 0 10px 50px rgba(30, 34, 41, 0.18); border-radius: 6px; }
            }
            @page { size: A4 portrait; margin: 10mm 8mm; }
        @endif

        @media print {
            body { background: #FFFFFF; }
            .tinv-shell { max-width: 100%; box-shadow: none; margin: 0; border-radius: 0; }
        }
    </style>
</head>

<body>
<div class="tinv-shell">
    @if($invoicestatus->slug == 'cancelled')
        {{-- cancelled watermark placeholder --}}
    @endif

    <!-- Hero -->
    <div class="tinv-hero">
        <div class="tinv-hero-row">
            <div class="tinv-brand">
                <img src="{{ asset('logoClarity.jpg') }}" alt="Clarity Aesthetic">
            </div>
            <div class="tinv-hero-doc">
                <div class="tinv-tag">Treatment Invoice</div>
                <div class="tinv-num">No. <strong>#{{ $Invoiceinfo->id }}</strong></div>
            </div>
        </div>
    </div>

    <!-- Ribbon -->
    <div class="tinv-ribbon">
        <div class="tinv-ribbon-block">
            <div class="tinv-ribbon-icon">{{ strtoupper(substr($patient->name, 0, 1)) }}</div>
            <div>
                <div class="tinv-ribbon-label">Billed To</div>
                <div class="tinv-ribbon-value">{{ ucfirst($patient->name) }} &middot; C-{{ $patient->id }}</div>
            </div>
        </div>
        <div class="tinv-ribbon-block">
            <div>
                <div class="tinv-ribbon-label" style="text-align:right;">Invoice Date</div>
                <div class="tinv-ribbon-value" style="text-align:right;">
                    {{ \Carbon\Carbon::parse($Invoiceinfo->created_at)->format('d M Y') }} &middot; {{ \Carbon\Carbon::parse($Invoiceinfo->created_at)->format('h:i A') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Body -->
    <div class="tinv-body">
        <div class="tinv-section-title">
            <span class="bar"></span>
            <h4>Treatment Details</h4>
        </div>

        <table class="tinv-items">
            <thead>
                <tr>
                    <th style="width: 45%;">Service</th>
                    <th style="width: 15%;">Discount</th>
                    <th style="width: 12%;">Tax %</th>
                    <th style="width: 14%;">Tax</th>
                    <th style="width: 14%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="tinv-item-name">{{ $service->name }}</div>
                        <div class="tinv-item-sub">Attending: {{ $appointment_info?->doctor?->name ?? '—' }} &middot; {{ $location_info->name ?? '' }}</div>
                    </td>
                    <td>
                        @if($discount != null)
                            <div>{{ $discount->name }}</div>
                            <div class="tinv-item-sub">− {{ number_format($Invoiceinfo->discount_price ?? 0) }}</div>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $Invoiceinfo->tax_percenatage }}%</td>
                    <td>{{ number_format($Invoiceinfo->tax_price) }}</td>
                    <td><strong>{{ number_format($Invoiceinfo->tax_including_price) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="tinv-totals">
            <div class="tinv-totals-card">
                <div class="tinv-totals-row">
                    <span>Service Price</span>
                    <span>
                        @if($appointment_info?->appointment_type_id == 2 && isset($service_price_display))
                            {{ number_format($service_price_display) }}
                        @elseif($Invoiceinfo->is_exclusive == '0' && isset($bundle) && $bundle?->type == 'single')
                            {{ number_format(($Invoiceinfo->service_price)-($Invoiceinfo->tax_price)) }}
                        @else
                            {{ number_format($Invoiceinfo->service_price) }}
                        @endif
                    </span>
                </div>
                @if($discount != null && $Invoiceinfo->discount_price != null)
                    <div class="tinv-totals-row">
                        <span>Discount</span>
                        <span>− {{ number_format($Invoiceinfo->discount_price) }}</span>
                    </div>
                @endif
                <div class="tinv-totals-row">
                    <span>Subtotal</span>
                    <span>
                        @if($Invoiceinfo->is_exclusive == '0')
                            @if($Invoiceinfo->discount_price == null && isset($bundle) && $bundle?->type == 'single')
                                {{ number_format(($Invoiceinfo->service_price)-($Invoiceinfo->tax_price)) }}
                            @else
                                {{ number_format($Invoiceinfo->tax_exclusive_serviceprice) }}
                            @endif
                        @else
                            {{ number_format($Invoiceinfo->tax_exclusive_serviceprice) }}
                        @endif
                    </span>
                </div>
                <div class="tinv-totals-row divide">
                    <span>Tax ({{ $Invoiceinfo->tax_percenatage }}%)</span>
                    <span>{{ number_format($Invoiceinfo->tax_price) }}</span>
                </div>
                <div class="tinv-totals-row grand">
                    <span>Total Due</span>
                    <span class="amt">Rs. {{ number_format($Invoiceinfo->total_price) }}/-</span>
                </div>
            </div>
        </div>

        <div class="tinv-pay-strip">
            <strong>Payment Note.</strong> All treatment prices are inclusive of applicable taxes.
            Thank you for choosing Clarity Aesthetic for your treatment.
        </div>
    </div>

    <!-- Signatures -->
    <div class="tinv-sigs">
        <div class="tinv-sig">
            <div class="tinv-sig-line">Client Signature</div>
            <div class="tinv-sig-name">{{ ucfirst($patient->name) }}</div>
        </div>
        <div class="tinv-sig">
            <div class="tinv-sig-line">Doctor Signature</div>
            <div class="tinv-sig-name">{{ $appointment_info?->doctor?->name ?? '' }}</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="tinv-footer">
        <span class="brand-mark">Clarity Aesthetic</span>
        <span class="foot-meta">
            {{ $location_info->address ?? '' }}
            @if(!empty($location_info->fdo_phone)) &nbsp;&middot;&nbsp; {{ $location_info->fdo_phone }} @endif
            @if(!empty($account->email)) &nbsp;&middot;&nbsp; {{ $account->email }} @endif
            &nbsp;&middot;&nbsp; https://clarityaesthetic.pk
        </span>
    </div>
</div>

<script>
    window.onload = function () { window.print(); };
    window.onafterprint = function () { window.close(); };
</script>
</body>
</html>
