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
        $_facePath = public_path('images/consultation-face-chart.png');
        $faceChartSrc = is_readable($_facePath)
            ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($_facePath))
            : asset('images/consultation-face-chart.png');
    @endphp
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            background: #fff;
            font-size: 10.5px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .cf-page { max-width: 210mm; margin: 0 auto; padding: 10mm 12mm 8mm; }

        .cf-logo {
            text-align: center;
            letter-spacing: 0.28em;
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            font-family: Arial, Helvetica, sans-serif;
            margin-bottom: 2px;
        }
        .cf-sub {
            text-align: center;
            letter-spacing: 0.18em;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            color: #333;
            font-family: Arial, Helvetica, sans-serif;
            margin-bottom: 10px;
        }
        .cf-title {
            text-align: center;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-family: Arial, Helvetica, sans-serif;
            margin: 12px 0 14px;
        }

        .cf-heading-serif {
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-size: 12px;
            font-weight: 700;
            color: #222;
            margin: 14px 0 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #ccc;
        }

        .cf-client-block .cf-heading-serif { margin-top: 0; }

        .cf-inline-label { font-weight: 600; color: #333; }
        .cf-field {
            display: inline-block;
            border-bottom: 1px solid #333;
            min-height: 14px;
            min-width: 120px;
            padding: 0 4px 1px;
            vertical-align: baseline;
        }
        .cf-field-long { min-width: 200px; }

        table.cf-client { width: 100%; border-collapse: collapse; margin-bottom: 2px; }
        table.cf-client td { vertical-align: baseline; padding: 4px 12px 4px 0; font-size: 10.5px; }

        .cf-gender { margin: 6px 0 8px; font-size: 10.5px; }
        .cf-gender span { margin-right: 18px; }
        .cf-box {
            display: inline-block;
            width: 11px;
            height: 11px;
            border: 1px solid #333;
            margin-right: 5px;
            vertical-align: -1px;
            text-align: center;
            font-size: 9px;
            line-height: 11px;
        }
        .cf-box.checked::after { content: '\2713'; font-size: 10px; line-height: 11px; }

        /* Skin analysis + face chart row */
        table.cf-skin-face { width: 100%; border-collapse: collapse; margin: 6px 0 18px; }
        table.cf-skin-face > tbody > tr > td {
            vertical-align: top;
            padding: 0;
        }
        td.cf-skin-col { width: 52%; padding-right: 14px; }
        td.cf-face-col { width: 48%; text-align: center; vertical-align: middle; }

        .cf-skin-panel {
            background: #e8efe8;
            border-radius: 10px;
            padding: 14px 16px 16px;
            border: 1px solid #c5d4c5;
            height: 100%;
        }
        .cf-skin-panel .cf-heading-serif {
            border-bottom: none;
            margin: 0 0 12px;
            padding: 0;
            font-size: 13px;
            color: #1a1a1a;
        }
        .cf-skin-group { margin-bottom: 12px; }
        .cf-skin-group:last-child { margin-bottom: 0; }
        .cf-skin-group-label {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 700;
            font-size: 10px;
            color: #222;
            margin-bottom: 6px;
            display: block;
        }
        .cf-opts-row span { margin-right: 11px; margin-bottom: 4px; white-space: nowrap; display: inline-block; font-size: 10px; color: #444; }

        .cf-face-chart {
            max-width: 100%;
            width: 220px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* Medical */
        .cf-med-intro {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #666;
            margin: 2px 0 12px;
        }

        table.cf-med { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.cf-med td {
            width: 33.33%;
            vertical-align: top;
            padding: 2px 14px 5px 0;
            font-size: 10px;
            color: #555;
        }
        .cf-med-item { margin-bottom: 5px; page-break-inside: avoid; line-height: 1.35; }
        .cf-med-item .cf-box {
            display: inline-block;
            vertical-align: top;
            margin-right: 6px;
            margin-top: 2px;
        }

        .cf-remarks { margin-top: 14px; }
        .cf-remarks-lines { margin-top: 8px; }
        .cf-remarks-lines div {
            border-bottom: 1px dotted #333;
            height: 22px;
            margin-bottom: 4px;
        }

        .cf-footer-contact {
            text-align: center;
            font-size: 9px;
            letter-spacing: 0.02em;
            margin: 16px 0 12px;
            line-height: 1.6;
            color: #444;
        }
        .cf-sig-wrap {
            margin-top: 18px;
            display: table;
            width: 100%;
        }
        .cf-sig {
            display: table-cell;
            width: 45%;
            vertical-align: bottom;
        }
        .cf-sig-line {
            border-top: 1px solid #333;
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

    <div class="cf-client-block">
        <div class="cf-heading-serif">Client's Information</div>

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
    </div>

    <table class="cf-skin-face" role="presentation">
        <tr>
            <td class="cf-skin-col">
                <div class="cf-skin-panel">
                    <div class="cf-heading-serif">Skin Analysis</div>

                    <div class="cf-skin-group">
                        <span class="cf-skin-group-label">Fitzpatrick Skin Type</span>
                        <div class="cf-opts-row">
                            <span><span class="cf-box"></span> I</span>
                            <span><span class="cf-box"></span> II</span>
                            <span><span class="cf-box"></span> III</span>
                            <span><span class="cf-box"></span> IV</span>
                            <span><span class="cf-box"></span> V</span>
                            <span><span class="cf-box"></span> VI</span>
                        </div>
                    </div>

                    <div class="cf-skin-group">
                        <span class="cf-skin-group-label">Skin Type</span>
                        <div class="cf-opts-row">
                            <span><span class="cf-box"></span> Oily</span>
                            <span><span class="cf-box"></span> Dry</span>
                            <span><span class="cf-box"></span> Sensitive</span>
                            <span><span class="cf-box"></span> Combination</span>
                            <span><span class="cf-box"></span> Acne-Prone</span>
                        </div>
                    </div>

                    <div class="cf-skin-group">
                        <span class="cf-skin-group-label">Skin Sensitivity</span>
                        <div class="cf-opts-row">
                            <span><span class="cf-box"></span> No Sensitivity</span>
                            <span><span class="cf-box"></span> Low</span>
                            <span><span class="cf-box"></span> High</span>
                            <span><span class="cf-box"></span> Moderate</span>
                        </div>
                    </div>
                </div>
            </td>
            <td class="cf-face-col">
                <img class="cf-face-chart" src="{{ $faceChartSrc }}" alt="" />
            </td>
        </tr>
    </table>

    <div class="cf-heading-serif">Medical Information</div>
    <p class="cf-med-intro">Please check any of the following that apply to you:</p>

    <table class="cf-med">
        <tr>
            <td>
                <div class="cf-med-item"><span class="cf-box"></span> Illness or injury within 5 years</div>
                <div class="cf-med-item"><span class="cf-box"></span> History of heart disease</div>
                <div class="cf-med-item"><span class="cf-box"></span> Any surgeries done</div>
                <div class="cf-med-item"><span class="cf-box"></span> Heart surgery/prosthesis/stents</div>
                <div class="cf-med-item"><span class="cf-box"></span> History of cardiovascular problems</div>
                <div class="cf-med-item"><span class="cf-box"></span> Dental implants/bridge/ti plates</div>
                <div class="cf-med-item"><span class="cf-box"></span> Anemia</div>
                <div class="cf-med-item"><span class="cf-box"></span> History of hernia/hernia surgery</div>
                <div class="cf-med-item"><span class="cf-box"></span> Kidney disease or dialysis</div>
            </td>
            <td>
                <div class="cf-med-item"><span class="cf-box"></span> Psychiatric disorders/depression</div>
                <div class="cf-med-item"><span class="cf-box"></span> Nervous disorders</div>
                <div class="cf-med-item"><span class="cf-box"></span> HIV Aids</div>
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
                <div class="cf-med-item"><span class="cf-box"></span> Hormonal disorders/hormonal therapy</div>
                <div class="cf-med-item"><span class="cf-box"></span> Polycystic ovaries</div>
                <div class="cf-med-item"><span class="cf-box"></span> Fibroids</div>
                <div class="cf-med-item"><span class="cf-box"></span> Pregnancy</div>
                <div class="cf-med-item"><span class="cf-box"></span> Cancer</div>
                <div class="cf-med-item"><span class="cf-box"></span> Others</div>
            </td>
        </tr>
    </table>

    <div class="cf-remarks">
        <div class="cf-heading-serif" style="border-bottom: none; margin-bottom: 6px;">Remarks</div>
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
