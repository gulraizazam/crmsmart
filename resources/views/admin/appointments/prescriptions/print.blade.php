<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Prescription #{{ $prescription->id }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    @include('admin.invoices.partials.print-styles')
    <style>
        .cinv-ledger table th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
            border-bottom: 1px solid #d1d5db;
            padding: 8px 8px 8px 0;
        }
        .cinv-ledger table td {
            font-size: 13px;
            padding: 8px 8px 8px 0;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .rx-notes {
            margin-top: 18px;
            font-size: 12px;
            color: #374151;
        }
        .rx-sign {
            margin-top: 36px;
            display: flex;
            justify-content: flex-end;
        }
        .rx-sign-box {
            width: 220px;
            text-align: center;
            border-top: 1px solid #d1d5db;
            padding-top: 8px;
            font-size: 12px;
            color: #4b5563;
        }
        .no-print { margin: 16px auto; max-width: 760px; text-align: right; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
@if(empty($forPdf))
<div class="no-print">
    <button type="button" onclick="window.print()">Print</button>
</div>
@endif
<div class="cinv-shell">
    <div class="cinv-head">
        <div class="cinv-brand">
            <img src="{{ asset('logoClarity.jpg') }}?v=4" alt="Smart Aesthetics">
            <div class="cinv-brand-sub">{{ $appointment->location->name ?? 'Smart Aesthetics' }}</div>
        </div>
        <div class="cinv-doc-type">
            <div class="cinv-kicker">Clinical</div>
            <div class="cinv-title">Prescription</div>
            <div class="cinv-subtitle">#{{ $prescription->id }}</div>
        </div>
    </div>

    <div class="cinv-info-wrap">
        <table class="cinv-info-grid">
            <tr>
                <td>
                    <div class="cinv-info-label">Patient</div>
                    <div class="cinv-info-value">{{ ucfirst($appointment->name ?: ($appointment->patient->name ?? '')) }}</div>
                </td>
                <td>
                    <div class="cinv-info-label">Patient ID</div>
                    <div class="cinv-info-value">C-{{ $appointment->patient_id }}</div>
                </td>
                <td>
                    <div class="cinv-info-label">Date</div>
                    <div class="cinv-info-value">{{ optional($prescription->prescribed_at)->format('d M Y') }}</div>
                </td>
                <td>
                    <div class="cinv-info-label">Doctor</div>
                    <div class="cinv-info-value">{{ $prescription->doctor->name ?? ($appointment->doctor->name ?? '—') }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="cinv-info-label">Centre</div>
                    <div class="cinv-info-value">{{ $appointment->location->name ?? '' }}</div>
                </td>
                <td colspan="2">
                    <div class="cinv-info-label">Diagnosis</div>
                    <div class="cinv-info-value">{{ $prescription->diagnosis ?: '—' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="cinv-ledger">
        <table>
            <tr>
                <th>#</th>
                <th>Medicine</th>
                <th>Dose</th>
                <th>Frequency</th>
                <th>Duration</th>
                <th>Instructions</th>
            </tr>
            @foreach($prescription->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->medicine_name }}</td>
                    <td>{{ $item->dose ?: '—' }}</td>
                    <td>{{ $item->frequency ?: '—' }}</td>
                    <td>{{ $item->duration ?: '—' }}</td>
                    <td>{{ $item->instructions ?: '—' }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    @if($prescription->notes)
        <div class="rx-notes"><strong>Notes:</strong> {{ $prescription->notes }}</div>
    @endif

    <div class="rx-sign">
        <div class="rx-sign-box">
            {{ $prescription->doctor->name ?? ($appointment->doctor->name ?? 'Doctor') }}<br>
            Signature
        </div>
    </div>
</div>
</body>
</html>
