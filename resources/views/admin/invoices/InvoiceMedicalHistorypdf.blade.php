<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Smart Aesthetics — Consultation Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    @php
        $patientAge = '';
        if (!empty($patient->dob)) {
            try {
                $patientAge = (string) \Carbon\Carbon::parse($patient->dob)->age;
            } catch (\Exception $e) {
                $patientAge = '';
            }
        }
        $isFemale = isset($patient->gender) && (int) $patient->gender === 2;
        $isMale = isset($patient->gender) && (int) $patient->gender === 1;
        $consultantName = $appointment_info ? (optional($appointment_info->doctor)->name ?? '') : '';
        $consultDate = $Invoiceinfo ? \Carbon\Carbon::parse($Invoiceinfo->created_at)->format('d M Y') : \Carbon\Carbon::now()->format('d M Y');
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #2A2E24;
            background: #F5F7F1;
            font-size: 10.5px;
            line-height: 1.45;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .cf-shell {
            max-width: 210mm;
            margin: 0 auto;
            background: #FFFFFF;
            padding: 0;
            position: relative;
        }

        /* Frame */
        .cf-frame-inner {
            padding: 14mm 14mm 10mm;
            border: 2px solid #D6DECC;
            margin: 6mm;
            position: relative;
        }
        .cf-frame-inner::before,
        .cf-frame-inner::after {
            content: '';
            position: absolute;
            width: 24px; height: 24px;
            border: 2px solid #C4A265;
        }
        .cf-frame-inner::before { top: -8px; left: -8px; border-right: none; border-bottom: none; }
        .cf-frame-inner::after { bottom: -8px; right: -8px; border-left: none; border-top: none; }

        /* Header */
        .cf-brand-wrap {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 1px solid #D6DECC;
            margin-bottom: 14px;
        }
        .cf-brand-logo {
            max-height: 80px;
            width: auto;
            max-width: 320px;
            display: block;
            margin: 0 auto 6px;
        }
        .cf-brand-tag {
            font-family: Georgia, 'Times New Roman', serif;
            font-style: italic;
            color: #6B7A55;
            font-size: 11px;
            letter-spacing: 3px;
            margin-top: 4px;
        }
        .cf-title-bar {
            background: #3D4A35;
            color: #F5F7F1;
            padding: 10px 20px;
            margin: 14px 0 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .cf-title-bar .title {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 15px;
            letter-spacing: 5px;
            text-transform: uppercase;
        }
        .cf-title-bar .date {
            font-size: 10.5px;
            color: #C4A265;
            letter-spacing: 2px;
        }

        /* Section headings */
        .cf-section {
            font-family: Georgia, 'Times New Roman', serif;
            font-size: 12.5px;
            font-weight: 700;
            color: #3D4A35;
            letter-spacing: 2px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 14px 0 10px;
        }
        .cf-section::before {
            content: '';
            display: inline-block;
            width: 18px;
            height: 2px;
            background: #C4A265;
        }
        .cf-section::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #E4DCCB;
        }

        /* Client information box */
        .cf-client-box {
            background: #FBFAF3;
            border: 1px solid #E4DCCB;
            padding: 14px 16px;
            border-radius: 3px;
        }
        .cf-client-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .cf-client-grid td {
            padding: 6px 12px;
            vertical-align: baseline;
            font-size: 10.5px;
        }
        .cf-label {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 700;
            font-size: 9.5px;
            color: #6B7A55;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            display: inline-block;
            margin-right: 6px;
        }
        .cf-field {
            display: inline-block;
            border-bottom: 1px solid #7A8B6A;
            min-height: 15px;
            min-width: 140px;
            padding: 0 6px 2px;
            vertical-align: baseline;
            font-family: Georgia, serif;
            color: #2A2E24;
            font-size: 11px;
        }
        .cf-field-long { min-width: 220px; }
        .cf-gender { margin: 8px 4px 4px; font-size: 10.5px; }
        .cf-gender span { margin-right: 22px; }
        .cf-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1.5px solid #3D4A35;
            margin-right: 6px;
            vertical-align: -2px;
            text-align: center;
            font-size: 9px;
            line-height: 11px;
            background: #FFFFFF;
        }
        .cf-box.checked { background: #3D4A35; color: #FFFFFF; }
        .cf-box.checked::after { content: '\2713'; font-size: 10px; line-height: 10px; color: #C4A265; }

        /* Skin analysis + face chart */
        .cf-skin-wrap {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 6px;
        }
        .cf-skin-cell {
            display: table-cell;
            vertical-align: top;
            padding: 0;
        }
        .cf-skin-cell.left { width: 60%; padding-right: 12px; }
        .cf-skin-cell.right { width: 40%; text-align: center; vertical-align: middle; }

        .cf-skin-panel {
            background: linear-gradient(180deg, #EFF4E9 0%, #E4EDD8 100%);
            border: 1px solid #D2DEC0;
            border-radius: 4px;
            padding: 12px 14px;
            border-left: 4px solid #C4A265;
        }
        .cf-skin-group { margin-bottom: 10px; }
        .cf-skin-group:last-child { margin-bottom: 0; }
        .cf-skin-group-label {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 700;
            font-size: 9.5px;
            color: #3D4A35;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }
        .cf-opts-row span {
            margin-right: 10px;
            margin-bottom: 4px;
            white-space: nowrap;
            display: inline-block;
            font-size: 10px;
            color: #3F4737;
        }
        .cf-face-chart {
            max-width: 100%;
            width: 210px;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .cf-face-caption {
            font-family: Georgia, serif;
            font-style: italic;
            font-size: 10px;
            color: #6B7A55;
            margin-top: 6px;
            letter-spacing: 1px;
        }

        /* Medical checklist */
        .cf-med-intro {
            font-family: Georgia, serif;
            font-style: italic;
            font-size: 10.5px;
            color: #6B7A55;
            margin: 2px 0 10px;
        }
        table.cf-med { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        table.cf-med td {
            width: 33.33%;
            vertical-align: top;
            padding: 2px 12px 4px 0;
            font-size: 10px;
            color: #3F4737;
        }
        .cf-med-item {
            margin-bottom: 5px;
            page-break-inside: avoid;
            line-height: 1.4;
            padding: 3px 6px;
            border-left: 2px solid transparent;
        }
        .cf-med-item .cf-box {
            display: inline-block;
            vertical-align: top;
            margin-right: 6px;
            margin-top: 2px;
        }

        /* Remarks */
        .cf-remarks-lines { margin-top: 6px; }
        .cf-remarks-lines div {
            border-bottom: 1px dotted #7A8B6A;
            height: 22px;
            margin-bottom: 4px;
        }

        /* Footer + signatures */
        .cf-footer-contact {
            text-align: center;
            font-size: 9.5px;
            letter-spacing: 1.5px;
            margin: 18px 0 10px;
            line-height: 1.6;
            color: #6B7A55;
            padding-top: 10px;
            border-top: 1px solid #D6DECC;
        }
        .cf-footer-contact .brand {
            font-family: Georgia, serif;
            color: #3D4A35;
            font-weight: 700;
            letter-spacing: 4px;
            font-size: 10px;
        }

        .cf-sig-wrap {
            margin-top: 14px;
            display: table;
            width: 100%;
        }
        .cf-sig {
            display: table-cell;
            width: 45%;
            vertical-align: bottom;
        }
        .cf-sig-line {
            border-top: 1px solid #3D4A35;
            padding-top: 6px;
            font-family: Georgia, serif;
            font-style: italic;
            font-size: 10.5px;
            color: #3D4A35;
        }

        @if(!isset($download) || $download !== 'download')
        @media not print {
            .cf-shell {
                margin-top: 20px;
                margin-bottom: 20px;
                box-shadow: 0 12px 40px rgba(60, 70, 40, 0.12);
                border-radius: 3px;
            }
        }
        @endif

        @page { size: A4 portrait; margin: 6mm 6mm; }
        @media print {
            body { background: #FFFFFF; }
            .cf-shell { max-width: 100%; box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body>
<div class="cf-shell">
    <div class="cf-frame-inner">

        <!-- Header -->
        <div class="cf-brand-wrap">
            <img class="cf-brand-logo" src="{{ asset('logoClarity.jpg') }}?v=4" alt="Smart Aesthetics">
            <div class="cf-brand-tag">Aesthetic &middot; Wellness &middot; Confidence</div>
        </div>

        <div class="cf-title-bar">
            <span class="title">Consultation Form</span>
            <span class="date">{{ $consultDate }}</span>
        </div>

        <!-- Client -->
        <div class="cf-section">Client Information</div>
        <div class="cf-client-box">
            <div class="cf-gender">
                <span><span class="cf-box {{ $isFemale ? 'checked' : '' }}"></span>Female</span>
                <span><span class="cf-box {{ $isMale ? 'checked' : '' }}"></span>Male</span>
            </div>
            <table class="cf-client-grid">
                <tr>
                    <td><span class="cf-label">Patient Name</span><span class="cf-field cf-field-long">{{ $patient->name ?? '' }}</span></td>
                    <td><span class="cf-label">Patient ID</span><span class="cf-field">{{ $patient->id ? 'C-' . $patient->id : '' }}</span></td>
                </tr>
                <tr>
                    <td><span class="cf-label">Age</span><span class="cf-field">{{ $patientAge }}</span></td>
                    <td><span class="cf-label">Consultant</span><span class="cf-field cf-field-long">{{ $consultantName }}</span></td>
                </tr>
                <tr>
                    <td><span class="cf-label">Height</span><span class="cf-field"></span></td>
                    <td><span class="cf-label">Weight</span><span class="cf-field"></span></td>
                </tr>
                <tr>
                    <td colspan="2"><span class="cf-label">BMI</span><span class="cf-field"></span></td>
                </tr>
            </table>
        </div>

        <!-- Medical -->
        <div class="cf-section">Medical Information</div>
        <p class="cf-med-intro">Please check any of the following that apply to you:</p>

        <table class="cf-med">
            <tr>
                <td>
                    <div class="cf-med-item"><span class="cf-box"></span> Illness or injury within 5 years</div>
                    <div class="cf-med-item"><span class="cf-box"></span> History of heart disease</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Any surgeries done</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Heart surgery / prosthesis / stents</div>
                    <div class="cf-med-item"><span class="cf-box"></span> History of cardiovascular problems</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Dental implants / bridge / Ti plates</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Anemia</div>
                    <div class="cf-med-item"><span class="cf-box"></span> History of hernia / hernia surgery</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Kidney disease or dialysis</div>
                </td>
                <td>
                    <div class="cf-med-item"><span class="cf-box"></span> Psychiatric disorders / depression</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Nervous disorders</div>
                    <div class="cf-med-item"><span class="cf-box"></span> HIV / Aids</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Thyroid disorders</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Hepatitis</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Liver disease</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Cushing's Syndrome</div>
                    <div class="cf-med-item"><span class="cf-box"></span> History of drug or alcohol use</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Diabetes</div>
                </td>
                <td>
                    <div class="cf-med-item"><span class="cf-box"></span> History of seizures or epilepsy</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Skin disease</div>
                    <div class="cf-med-item"><span class="cf-box"></span> High blood pressure</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Hormonal disorders / therapy</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Polycystic ovaries</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Fibroids</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Pregnancy</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Cancer</div>
                    <div class="cf-med-item"><span class="cf-box"></span> Others</div>
                </td>
            </tr>
        </table>

        <!-- Remarks -->
        <div class="cf-section">Consultant Remarks</div>
        <div class="cf-remarks-lines">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>

        <!-- Signatures -->
        <div class="cf-sig-wrap">
            <div class="cf-sig">
                <div class="cf-sig-line">Client Signature</div>
            </div>
            <div class="cf-sig" style="text-align:right;">
                <div class="cf-sig-line">Consultant Signature</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="cf-footer-contact">
            <div class="brand">Smart Aesthetics</div>
            {{ $location_info->address ?? '' }}
            @if(!empty($location_info->fdo_phone)) &nbsp;&middot;&nbsp; {{ $location_info->fdo_phone }} @endif
            @if(!empty($account->email)) &nbsp;&middot;&nbsp; {{ $account->email }} @endif
            &nbsp;&middot;&nbsp; https://aestheticlinics.net
        </div>
    </div>
</div>

@if(empty($download))
<script>
    window.onload = function () { window.print(); };
    window.onafterprint = function () { window.close(); };
</script>
@endif
</body>
</html>
