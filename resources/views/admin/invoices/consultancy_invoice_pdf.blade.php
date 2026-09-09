<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Smart Aesthetics — Consultation Invoice #{{$Invoiceinfo->id}}</title>
    <meta content="Smart Aesthetics" name="description" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    @include('admin.invoices.partials.print-styles')
</head>
<body>
<div class="cinv-shell">
    <div class="cinv-head">
        <div class="cinv-brand">
            <img src="{{ asset('logoClarity.jpg') }}" alt="Smart Aesthetics">
            <div class="cinv-brand-sub">{{ $location_info->name ?? 'Smart Aesthetics' }}</div>
        </div>
        <div class="cinv-doc-type">
            <div class="cinv-kicker">Invoice</div>
            <div class="cinv-title">Consultation</div>
            <div class="cinv-subtitle">#{{ $Invoiceinfo->id }}</div>
        </div>
    </div>

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

    <div class="cinv-summary">
        <div class="cinv-summary-title">Service</div>
        <div class="cinv-service-name">{{ $service->name }}</div>
        <div class="cinv-service-meta">
            {{ $location_info->address ?? '' }}
            @if(!empty($location_info->fdo_phone)) &nbsp;&middot;&nbsp; {{ $location_info->fdo_phone }} @endif
        </div>
    </div>

    <div class="cinv-ledger">
        <table>
            <tr class="row">
                <td class="label">Consultation fee</td>
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
                    <td class="label">Discount — {{ $discount->name }}</td>
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
                <td class="label">Total payable</td>
                <td class="amount">Rs. {{ number_format($Invoiceinfo->total_price) }}/-</td>
            </tr>
        </table>
    </div>

    <div class="cinv-note">
        <strong>Note.</strong> All treatment prices are inclusive of applicable taxes.
        This document confirms your consultation appointment at Smart Aesthetics.
    </div>

    <div class="cinv-signatures">
        <div class="cinv-sig-block">
            <div class="cinv-sig-line">Patient signature</div>
            <div class="cinv-sig-name">{{ ucfirst($patient->name) }}</div>
        </div>
        <div class="cinv-sig-block">
            <div class="cinv-sig-line">Consultant signature</div>
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
