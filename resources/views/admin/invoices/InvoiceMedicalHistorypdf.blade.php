<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Skin &amp; Co. — Consultation Form</title>
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
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
            background: #fff;
            font-size: 11px;
            line-height: 1.35;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .cf-page { max-width: 210mm; margin: 0 auto; padding: 12mm 14mm 10mm; }

        .cf-logo {
            text-align: center;
            letter-spacing: 0.28em;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .cf-sub {
            text-align: center;
            letter-spacing: 0.18em;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            color: #222;
            margin-bottom: 10px;
        }
        .cf-title {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin: 14px 0 12px;
        }

        .cf-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin: 14px 0 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #000;
        }

        .cf-row { margin-bottom: 7px; }
        .cf-inline-label { font-weight: 600; }
        .cf-field {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-height: 14px;
            min-width: 120px;
            padding: 0 4px 1px;
            vertical-align: baseline;
        }
        .cf-field-long { min-width: 220px; }

        table.cf-client { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        table.cf-client td { vertical-align: baseline; padding: 4px 8px 4px 0; font-size: 11px; }

        .cf-gender { margin: 6px 0 10px; font-size: 11px; }
        .cf-gender span { margin-right: 18px; }
        .cf-box {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #000;
            margin-right: 5px;
            vertical-align: -1px;
            text-align: center;
            font-size: 9px;
            line-height: 11px;
        }
        .cf-box.checked::after { content: '\2713'; font-size: 10px; line-height: 11px; }

        .cf-med-intro { font-size: 10px; margin: 2px 0 10px; font-style: italic; }

        table.cf-med { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.cf-med td {
            width: 33.33%;
            vertical-align: top;
            padding: 2px 10px 5px 0;
            font-size: 10px;
        }
        .cf-med-item { margin-bottom: 4px; page-break-inside: avoid; }
        .cf-med-item .cf-box {
            display: inline-block;
            vertical-align: top;
            margin-right: 6px;
            margin-top: 2px;
        }

        .cf-skin-block { margin-top: 8px; }
        .cf-skin-row { margin: 8px 0; font-size: 10px; }
        .cf-skin-row .cf-inline-label { display: block; margin-bottom: 5px; font-size: 10px; }
        .cf-opts span { margin-right: 14px; white-space: nowrap; display: inline-block; margin-bottom: 4px; }
        .cf-fitz { margin-top: 6px; font-size: 10px; }
        .cf-fitz-nums { margin-top: 5px; }
        .cf-fitz-nums span { margin-right: 10px; }

        .cf-remarks { margin-top: 12px; }
        .cf-remarks-lines { margin-top: 8px; }
        .cf-remarks-lines div {
            border-bottom: 1px dotted #000;
            height: 22px;
            margin-bottom: 4px;
        }

        .cf-footer-contact {
            text-align: center;
            font-size: 9px;
            letter-spacing: 0.02em;
            margin: 18px 0 14px;
            line-height: 1.6;
        }
        .cf-sig-wrap {
            margin-top: 22px;
            display: table;
            width: 100%;
        }
        .cf-sig {
            display: table-cell;
            width: 45%;
            vertical-align: bottom;
        }
        .cf-sig-line {
            border-top: 1px solid #000;
            padding-top: 6px;
            font-size: 10px;
            font-weight: 600;
        }

        @if(!isset($download) || $download !== 'download')
        @media not print {
            .cf-page {
                margin-top: 20px;
                margin-bottom: 20px;
                box-shadow: 0 0 0 1px #ccc;
            }
        }
        @endif

        @page { size: A4 portrait; margin: 10mm 12mm; }
        @media print {
            body { background: #fff; }
            .cf-page { max-width: 100%; padding: 0; }
        }
    </style>
</head>
<body>
<div class="cf-page">
    <div class="cf-logo">Skin &amp; Co.</div>
    <div class="cf-sub">Aesthetic &amp; Wellness Clinic</div>
    <div class="cf-title">Consultation Form</div>

    <div class="cf-section-label">Client's Information</div>

    <div class="cf-gender">
        <span><span class="cf-box {{ $isFemale ? 'checked' : '' }}"></span>Female</span>
        <span><span class="cf-box {{ $isMale ? 'checked' : '' }}"></span>Male</span>
    </div>

    <table class="cf-client">
        <tr>
            <td><span class="cf-inline-label">Patient Name:</span> <span class="cf-field cf-field-long">{{ $patient->name ?? '' }}</span></td>
            <td><span class="cf-inline-label">Patient ID:</span> <span class="cf-field">{{ $patient->id ? 'C-' . $patient->id : '' }}</span></td>
        </tr>
        <tr>
            <td><span class="cf-inline-label">Age:</span> <span class="cf-field">{{ $patientAge }}</span></td>
            <td><span class="cf-inline-label">Consultant:</span> <span class="cf-field cf-field-long">{{ $consultantName }}</span></td>
        </tr>
        <tr>
            <td><span class="cf-inline-label">Height:</span> <span class="cf-field"></span></td>
            <td><span class="cf-inline-label">Weight:</span> <span class="cf-field"></span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="cf-inline-label">BMI:</span> <span class="cf-field"></span></td>
        </tr>
    </table>

    <div class="cf-section-label">Medical Information</div>
    <p class="cf-med-intro">Please check any of the following that apply to you:</p>

    <table class="cf-med">
        <tr>
            <td>
                <div class="cf-med-item"><span class="cf-box"></span> Illness or injury within 5 years</div>
                <div class="cf-med-item"><span class="cf-box"></span> Any surgeries done</div>
                <div class="cf-med-item"><span class="cf-box"></span> History of cardiovascular problems</div>
                <div class="cf-med-item"><span class="cf-box"></span> Anemia</div>
                <div class="cf-med-item"><span class="cf-box"></span> Kidney disease or dialysis</div>
                <div class="cf-med-item"><span class="cf-box"></span> Nervous disorders</div>
                <div class="cf-med-item"><span class="cf-box"></span> Thyroid disorders</div>
                <div class="cf-med-item"><span class="cf-box"></span> Liver disease</div>
                <div class="cf-med-item"><span class="cf-box"></span> History of drug or alcohol use</div>
            </td>
            <td>
                <div class="cf-med-item"><span class="cf-box"></span> History of heart disease</div>
                <div class="cf-med-item"><span class="cf-box"></span> Heart surgery/prosthesis/stents</div>
                <div class="cf-med-item"><span class="cf-box"></span> Dental implants/bridge/ti plates</div>
                <div class="cf-med-item"><span class="cf-box"></span> History of hernia/hernia surgery</div>
                <div class="cf-med-item"><span class="cf-box"></span> Psychiatric disorders/depression</div>
                <div class="cf-med-item"><span class="cf-box"></span> HIV Aids</div>
                <div class="cf-med-item"><span class="cf-box"></span> Hepatitis</div>
                <div class="cf-med-item"><span class="cf-box"></span> Cushing's Syndrome</div>
                <div class="cf-med-item"><span class="cf-box"></span> Diabetes</div>
            </td>
            <td>
                <div class="cf-med-item"><span class="cf-box"></span> History of seizures or epilepsy</div>
                <div class="cf-med-item"><span class="cf-box"></span> Skin disease</div>
                <div class="cf-med-item"><span class="cf-box"></span> High blood pressure</div>
                <div class="cf-med-item"><span class="cf-box"></span> Hormonal disorders/hormonal therapy</div>
                <div class="cf-med-item"><span class="cf-box"></span> Polycystic ovaries</div>
                <div class="cf-med-item"><span class="cf-box"></span> Fibroids</div>
                <div class="cf-med-item"><span class="cf-box"></span> Pregnancy</div>
                <div class="cf-med-item"><span class="cf-box"></span> Cancer</div>
                <div class="cf-med-item"><span class="cf-box"></span> Others</div>
            </td>
        </tr>
    </table>

    <div class="cf-section-label">Skin Analysis</div>
    <div class="cf-skin-block">
        <div class="cf-skin-row">
            <span class="cf-inline-label">Skin Type</span>
            <div class="cf-opts">
                <span><span class="cf-box"></span> Oily</span>
                <span><span class="cf-box"></span> Sensitive</span>
                <span><span class="cf-box"></span> Dry</span>
                <span><span class="cf-box"></span> Acne-Prone</span>
                <span><span class="cf-box"></span> Combination</span>
            </div>
        </div>
        <div class="cf-skin-row">
            <span class="cf-inline-label">Skin Sensitivity</span>
            <div class="cf-opts">
                <span><span class="cf-box"></span> No Sensitivity</span>
                <span><span class="cf-box"></span> Low</span>
                <span><span class="cf-box"></span> Moderate</span>
                <span><span class="cf-box"></span> High</span>
            </div>
        </div>
        <div class="cf-fitz">
            <span class="cf-inline-label">Fitzpatrick Skin Type</span>
            <div class="cf-fitz-nums">
                <span><span class="cf-box"></span> I</span>
                <span><span class="cf-box"></span> II</span>
                <span><span class="cf-box"></span> III</span>
                <span><span class="cf-box"></span> IV</span>
                <span><span class="cf-box"></span> V</span>
                <span><span class="cf-box"></span> VI</span>
            </div>
        </div>
    </div>

    <div class="cf-remarks">
        <div class="cf-section-label" style="border-bottom: none; margin-bottom: 4px;">Remarks</div>
        <div class="cf-remarks-lines">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <div class="cf-footer-contact">
        www.skinandco.pk &nbsp;&nbsp; Info.skinandco@gmail.com &nbsp;&nbsp; +92 333 314 3669
    </div>

    <div class="cf-sig-wrap">
        <div class="cf-sig">
            <div class="cf-sig-line">Consultant signature</div>
        </div>
    </div>
</div>

@if(empty($download))
<script>
    window.onload = function () {
        window.print();
    };
    window.onafterprint = function () {
        window.close();
    };
</script>
@endif

</body>
</html>
