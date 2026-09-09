<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Smart Aesthetics — Consultation Invoice #{{$Invoiceinfo->id}}</title>
    <meta content="Smart Aesthetics" name="description" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Georgia', 'Times New Roman', serif;
            color: #2A2A2A;
            background: #F5F1EA;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .cinv-shell {
            max-width: 760px;
            margin: 0 auto;
            background: #FFFFFF;
            position: relative;
            padding: 0 0 0 42px;
            border: 1px solid #E4DCCB;
        }

        /* Vertical decorative side stripe */
        .cinv-stripe {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 42px;
            background: linear-gradient(180deg, #3D4A35 0%, #5C6B4F 45%, #C4A265 100%);
        }
        .cinv-stripe-label {
            position: absolute;
            left: 6px;
            top: 50%;
            transform: rotate(-90deg) translateX(50%);
            transform-origin: left center;
            color: #FFFFFF;
            font-family: 'Georgia', serif;
            font-size: 11px;
            letter-spacing: 8px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* Header */
        .cinv-head {
            padding: 34px 38px 20px;
            border-bottom: 1px solid #E4DCCB;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .cinv-brand img { height: 76px; width: auto; max-width: 260px; display: block; }
        .cinv-brand-sub {
            font-family: 'Georgia', serif;
            font-size: 11px;
            color: #8A7A5C;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-top: 8px;
        }
        .cinv-doc-type {
            text-align: right;
            font-family: 'Georgia', serif;
        }
        .cinv-doc-type .cinv-title {
            font-size: 22px;
            color: #3D4A35;
            font-weight: 400;
            font-style: italic;
            letter-spacing: 1px;
        }
        .cinv-doc-type .cinv-subtitle {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #8A7A5C;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 6px;
        }

        /* Ornamental divider */
        .cinv-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 14px 38px 6px;
        }
        .cinv-divider-line { flex: 1; height: 1px; background: #C4A265; opacity: .6; }
        .cinv-divider-dot { width: 6px; height: 6px; background: #C4A265; border-radius: 50%; margin: 0 10px; }

        /* Client info card */
        .cinv-info-wrap { padding: 12px 38px 6px; }
        .cinv-info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .cinv-info-grid td {
            width: 25%;
            padding: 10px 12px;
            border: 1px solid #EEE5D2;
            vertical-align: top;
            font-family: Arial, Helvetica, sans-serif;
        }
        .cinv-info-label {
            font-size: 9px;
            color: #A38F6A;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .cinv-info-value {
            font-family: 'Georgia', serif;
            font-size: 13px;
            color: #2E3226;
            font-weight: 700;
        }

        /* Consultation summary */
        .cinv-summary {
            margin: 22px 38px 0;
            background: #FBF7EE;
            border-left: 4px solid #C4A265;
            padding: 18px 22px;
        }
        .cinv-summary-title {
            font-family: 'Georgia', serif;
            font-style: italic;
            color: #3D4A35;
            font-size: 17px;
            margin-bottom: 6px;
        }
        .cinv-service-name {
            font-family: 'Georgia', serif;
            font-size: 20px;
            color: #2E3226;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .cinv-service-meta {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #7A6F55;
            letter-spacing: 0.4px;
        }

        /* Amount ledger */
        .cinv-ledger { margin: 24px 38px 0; }
        .cinv-ledger table { width: 100%; border-collapse: collapse; }
        .cinv-ledger .row { border-bottom: 1px dotted #C4A265; }
        .cinv-ledger td {
            padding: 10px 4px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12.5px;
            color: #3D3D3D;
        }
        .cinv-ledger td.label {
            color: #8A7A5C;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-size: 10px;
        }
        .cinv-ledger td.amount {
            text-align: right;
            font-family: 'Georgia', serif;
            font-weight: 700;
            color: #2E3226;
        }
        .cinv-ledger .total-row td {
            border-top: 2px solid #3D4A35;
            padding-top: 14px;
            padding-bottom: 4px;
            font-size: 14px;
        }
        .cinv-ledger .total-row td.label {
            font-family: 'Georgia', serif;
            font-style: italic;
            color: #3D4A35;
            font-size: 14px;
            letter-spacing: 0.5px;
            text-transform: none;
        }
        .cinv-ledger .total-row td.amount {
            color: #3D4A35;
            font-size: 22px;
        }

        /* Note */
        .cinv-note {
            margin: 16px 38px 0;
            padding: 12px 16px;
            background: #F5F1EA;
            border: 1px dashed #C4A265;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            color: #6B5F45;
            line-height: 1.5;
        }
        .cinv-note strong { color: #3D4A35; font-family: 'Georgia', serif; }

        /* Signatures */
        .cinv-signatures {
            margin: 40px 38px 0;
            padding: 0 0 26px;
            display: flex;
            justify-content: space-between;
            gap: 40px;
        }
        .cinv-sig-block { flex: 1; text-align: center; }
        .cinv-sig-line {
            border-top: 1px solid #3D4A35;
            padding-top: 8px;
            font-family: 'Georgia', serif;
            font-style: italic;
            color: #3D4A35;
            font-size: 12px;
        }
        .cinv-sig-name {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #6B5F45;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }

        /* Footer */
        .cinv-footer {
            background: #3D4A35;
            padding: 14px 38px;
            color: #F5F1EA;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            letter-spacing: 1px;
            text-align: center;
        }
        .cinv-footer strong {
            font-family: 'Georgia', serif;
            color: #C4A265;
            letter-spacing: 4px;
            font-weight: 400;
        }

        @if($download != 'download')
            @media not print {
                .cinv-shell { margin-top: 30px; margin-bottom: 30px; box-shadow: 0 8px 40px rgba(60, 55, 40, 0.15); }
            }
            @page { size: A4 portrait; margin: 10mm 8mm; }
        @endif

        @media print {
            body { background: #FFFFFF; }
            .cinv-shell { box-shadow: none; margin: 0; border: none; }
        }
    </style>
</head>

<body>
<div class="cinv-shell">
    <div class="cinv-stripe">
        <div class="cinv-stripe-label">Smart Aesthetics</div>
    </div>

    <!-- Header -->
    <div class="cinv-head">
        <div class="cinv-brand">
            <img src="{{ asset('logoClarity.jpg') }}" alt="Smart Aesthetics">
            <div class="cinv-brand-sub">Aesthetic Care &middot; Consultation</div>
        </div>
        <div class="cinv-doc-type">
            <div class="cinv-title">Consultation Invoice</div>
            <div class="cinv-subtitle">Ref &middot; #{{$Invoiceinfo->id}}</div>
        </div>
    </div>

    <div class="cinv-divider">
        <div class="cinv-divider-line"></div>
        <div class="cinv-divider-dot"></div>
        <div class="cinv-divider-dot"></div>
        <div class="cinv-divider-dot"></div>
        <div class="cinv-divider-line"></div>
    </div>

    <!-- Client info -->
    <div class="cinv-info-wrap">
        <table class="cinv-info-grid">
            <tr>
                <td>
                    <div class="cinv-info-label">Patient</div>
                    <div class="cinv-info-value">{{ ucfirst($patient->name) }}</div>
                </td>
                <td>
                    <div class="cinv-info-label">Patient ID</div>
                    <div class="cinv-info-value">C-{{ $patient->id }}</div>
                </td>
                <td>
                    <div class="cinv-info-label">Date</div>
                    <div class="cinv-info-value">{{ \Carbon\Carbon::parse($Invoiceinfo->created_at)->format('d M Y') }}</div>
                </td>
                <td>
                    <div class="cinv-info-label">Time</div>
                    <div class="cinv-info-value">{{ \Carbon\Carbon::parse($Invoiceinfo->created_at)->format('h:i A') }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="cinv-info-label">Consultant</div>
                    <div class="cinv-info-value">{{ $appointment_info?->doctor?->name ?? '—' }}</div>
                </td>
                <td colspan="2">
                    <div class="cinv-info-label">Centre</div>
                    <div class="cinv-info-value">{{ $location_info->name ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Consultation summary -->
    <div class="cinv-summary">
        <div class="cinv-summary-title">Consultation Service</div>
        <div class="cinv-service-name">{{ $service->name }}</div>
        <div class="cinv-service-meta">
            {{ $location_info->address ?? '' }}
            @if(!empty($location_info->fdo_phone)) &nbsp;&middot;&nbsp; {{ $location_info->fdo_phone }} @endif
        </div>
    </div>

    <!-- Amount ledger -->
    <div class="cinv-ledger">
        <table>
            <tr class="row">
                <td class="label">Consultation Fee</td>
                <td class="amount">
                    @if(isset($service_price_display))
                        {{ number_format($service_price_display) }}
                    @else
                        {{ number_format($Invoiceinfo->service_price) }}
                    @endif
                </td>
            </tr>
            @if($discount != null)
                <tr class="row">
                    <td class="label">Discount &mdash; {{ $discount->name }}</td>
                    <td class="amount">
                        @if($Invoiceinfo->discount_price != null)
                            − {{ number_format($Invoiceinfo->discount_price) }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endif
            <tr class="row">
                <td class="label">Subtotal</td>
                <td class="amount">{{ number_format($Invoiceinfo->tax_exclusive_serviceprice) }}</td>
            </tr>
            <tr class="row">
                <td class="label">Tax ({{ $Invoiceinfo->tax_percenatage }}%)</td>
                <td class="amount">{{ number_format($Invoiceinfo->tax_price) }}</td>
            </tr>
            <tr class="total-row">
                <td class="label">Total Payable</td>
                <td class="amount">Rs. {{ number_format($Invoiceinfo->total_price) }}/-</td>
            </tr>
        </table>
    </div>

    <div class="cinv-note">
        <strong>Note.</strong> All treatment prices are inclusive of applicable taxes.
        This document confirms your consultation appointment at Smart Aesthetics.
    </div>

    <!-- Signatures -->
    <div class="cinv-signatures">
        <div class="cinv-sig-block">
            <div class="cinv-sig-line">Patient Signature</div>
            <div class="cinv-sig-name">{{ ucfirst($patient->name) }}</div>
        </div>
        <div class="cinv-sig-block">
            <div class="cinv-sig-line">Consultant Signature</div>
            <div class="cinv-sig-name">{{ $appointment_info?->doctor?->name ?? '' }}</div>
        </div>
    </div>

    <div class="cinv-footer">
        <strong>Smart Aesthetics</strong>
        &nbsp;&middot;&nbsp;
        https://aestheticlinics.net
        &nbsp;&middot;&nbsp;
        @if(!empty($location_info->ntn)) NTN {{ $location_info->ntn }} &nbsp;&middot;&nbsp; @endif
        @if(!empty($location_info->stn)) STN {{ $location_info->stn }} &nbsp;&middot;&nbsp; @endif
        {{ $account->email ?? '' }}
    </div>
</div>

<script>
    window.onload = function () { window.print(); };
    window.onafterprint = function () { window.close(); };
</script>
</body>
</html>
