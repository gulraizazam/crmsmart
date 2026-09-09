<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Smart Aesthetics — Plan Invoice {{ $package->name ?? '' }}</title>
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
            padding: 22px 38px 14px;
            border-bottom: 1px solid #E4DCCB;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .cinv-brand img { height: 62px; width: auto; max-width: 220px; display: block; }
        .cinv-brand-sub {
            font-family: 'Georgia', serif;
            font-size: 10.5px;
            color: #8A7A5C;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-top: 6px;
        }
        .cinv-doc-type {
            text-align: right;
            font-family: 'Georgia', serif;
        }
        .cinv-doc-type .cinv-title {
            font-size: 20px;
            color: #3D4A35;
            font-weight: 400;
            font-style: italic;
            letter-spacing: 1px;
        }
        .cinv-doc-type .cinv-subtitle {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            color: #8A7A5C;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* Ornamental divider */
        .cinv-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px 38px 2px;
        }
        .cinv-divider-line { flex: 1; height: 1px; background: #C4A265; opacity: .6; }
        .cinv-divider-dot { width: 5px; height: 5px; background: #C4A265; border-radius: 50%; margin: 0 8px; }

        /* Client info card */
        .cinv-info-wrap { padding: 8px 38px 4px; }
        .cinv-info-grid { width: 100%; border-collapse: collapse; }
        .cinv-info-grid td {
            width: 25%;
            padding: 7px 10px;
            border: 1px solid #EEE5D2;
            vertical-align: top;
            font-family: Arial, Helvetica, sans-serif;
        }
        .cinv-info-label {
            font-size: 8.5px;
            color: #A38F6A;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .cinv-info-value {
            font-family: 'Georgia', serif;
            font-size: 12px;
            color: #2E3226;
            font-weight: 700;
        }

        /* Section title */
        .cinv-section-title {
            margin: 14px 38px 6px;
            font-family: 'Georgia', serif;
            font-style: italic;
            color: #3D4A35;
            font-size: 14px;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .cinv-section-title::before {
            content: '';
            width: 18px;
            height: 2px;
            background: #C4A265;
            display: inline-block;
        }

        /* Services table */
        .cinv-items-wrap { margin: 4px 38px 0; }
        table.cinv-items {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }
        table.cinv-items thead th {
            background: #FBF7EE;
            color: #8A7A5C;
            text-align: left;
            padding: 8px 8px;
            font-size: 9px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            font-weight: 700;
            border-bottom: 1px solid #C4A265;
        }
        table.cinv-items thead th.right { text-align: right; }
        table.cinv-items tbody td {
            padding: 8px 8px;
            border-bottom: 1px dotted #C4A265;
            color: #3D3D3D;
            vertical-align: middle;
        }
        table.cinv-items tbody td.name {
            font-family: 'Georgia', serif;
            color: #2E3226;
            font-weight: 700;
            font-size: 12px;
        }
        table.cinv-items tbody td.right { text-align: right; font-family: 'Georgia', serif; }
        table.cinv-items tbody tr:last-child td { border-bottom: 1px solid #C4A265; }

        /* Total row (ledger style) */
        .cinv-ledger { margin: 6px 38px 0; }
        .cinv-ledger table { width: 100%; border-collapse: collapse; }
        .cinv-ledger td {
            padding: 6px 4px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #3D3D3D;
        }
        .cinv-ledger td.amount { text-align: right; font-family: 'Georgia', serif; font-weight: 700; color: #2E3226; }
        .cinv-ledger .total-row td {
            border-top: 2px solid #3D4A35;
            padding-top: 10px;
            font-size: 13px;
        }
        .cinv-ledger .total-row td.label {
            font-family: 'Georgia', serif;
            font-style: italic;
            color: #3D4A35;
            font-size: 14px;
        }
        .cinv-ledger .total-row td.amount {
            color: #3D4A35;
            font-size: 20px;
        }

        /* Payments received - compact */
        .cinv-pay-wrap { margin: 6px 38px 0; }
        table.cinv-pay {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
        }
        table.cinv-pay thead th {
            background: #FBF7EE;
            color: #8A7A5C;
            padding: 6px 8px;
            font-size: 8.5px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            text-align: left;
            border-bottom: 1px solid #C4A265;
        }
        table.cinv-pay thead th.right { text-align: right; }
        table.cinv-pay tbody td {
            padding: 6px 8px;
            border-bottom: 1px dotted #C4A265;
            color: #3D3D3D;
        }
        table.cinv-pay tbody td.right { text-align: right; font-family: 'Georgia', serif; }
        table.cinv-pay tbody tr.tot td {
            background: #FBF7EE;
            font-family: 'Georgia', serif;
            font-weight: 700;
            color: #3D4A35;
            border-bottom: 1px solid #C4A265;
        }

        /* Note */
        .cinv-note {
            margin: 10px 38px 0;
            padding: 8px 14px;
            background: #F5F1EA;
            border: 1px dashed #C4A265;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #6B5F45;
            line-height: 1.45;
        }
        .cinv-note strong { color: #3D4A35; font-family: 'Georgia', serif; }

        /* Signatures - compact */
        .cinv-signatures {
            margin: 18px 38px 0;
            padding: 0 0 14px;
            display: flex;
            justify-content: space-between;
            gap: 40px;
        }
        .cinv-sig-block { flex: 1; text-align: center; }
        .cinv-sig-line {
            border-top: 1px solid #3D4A35;
            padding-top: 6px;
            font-family: 'Georgia', serif;
            font-style: italic;
            color: #3D4A35;
            font-size: 11.5px;
        }
        .cinv-sig-name {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5px;
            color: #6B5F45;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }

        /* Footer */
        .cinv-footer {
            background: #3D4A35;
            padding: 10px 38px;
            color: #F5F1EA;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5px;
            letter-spacing: 1px;
            text-align: center;
        }
        .cinv-footer strong {
            font-family: 'Georgia', serif;
            color: #C4A265;
            letter-spacing: 4px;
            font-weight: 400;
        }

        @media not print {
            .cinv-shell { margin-top: 20px; margin-bottom: 20px; box-shadow: 0 8px 40px rgba(60, 55, 40, 0.15); }
        }
        @page { size: A4 portrait; margin: 8mm 6mm; }
        @media print {
            body { background: #FFFFFF; }
            .cinv-shell { box-shadow: none; margin: 0; border: none; }
            table.cinv-items tbody tr, table.cinv-pay tbody tr { page-break-inside: avoid; }
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
            <div class="cinv-brand-sub">Aesthetic Care &middot; Plan</div>
        </div>
        <div class="cinv-doc-type">
            <div class="cinv-title">Plan Invoice</div>
            <div class="cinv-subtitle">Ref &middot; {{ $package->name ?? '' }}</div>
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
                    <div class="cinv-info-label">Client</div>
                    <div class="cinv-info-value">{{ ucfirst($package->user->name ?? '') }}</div>
                </td>
                <td>
                    <div class="cinv-info-label">Client ID</div>
                    <div class="cinv-info-value">C-{{ $package->user->id ?? '' }}</div>
                </td>
                <td>
                    <div class="cinv-info-label">Date</div>
                    <div class="cinv-info-value">{{ \Carbon\Carbon::parse($package->created_at)->format('d M Y') }}</div>
                </td>
                <td>
                    <div class="cinv-info-label">Time</div>
                    <div class="cinv-info-value">{{ \Carbon\Carbon::parse($package->created_at)->format('h:i A') }}</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="cinv-info-label">Plan</div>
                    <div class="cinv-info-value">{{ $package->name ?? '' }}</div>
                </td>
                <td colspan="2">
                    <div class="cinv-info-label">Centre</div>
                    <div class="cinv-info-value">{{ $location_info->name ?? '' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Plan components -->
    <div class="cinv-section-title">Plan Components</div>
    <div class="cinv-items-wrap">
        <table class="cinv-items">
            <thead>
                <tr>
                    <th style="width: 34%;">Service / Bundle</th>
                    <th class="right" style="width: 11%;">Price</th>
                    <th style="width: 17%;">Discount</th>
                    <th class="right" style="width: 12%;">Subtotal</th>
                    <th class="right" style="width: 8%;">Tax %</th>
                    <th class="right" style="width: 8%;">Tax</th>
                    <th class="right" style="width: 10%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @if($packagebundles)
                    @foreach($packagebundles as $packagebundles)
                        <?php
                            $ptype = $package->plan_type ?? 'plan';
                            $st = $packagebundles->source_type ?? null;
                            $nameShown = '-';

                            if ($st === 'service' && $packagebundles->service) {
                                $nameShown = $packagebundles->service->name;
                            } elseif ($st === 'bundle' && $packagebundles->bundle) {
                                $nameShown = $packagebundles->bundle->name;
                            } elseif ($st === 'membership' && $packagebundles->membershipType) {
                                $nameShown = $packagebundles->membershipType->name;
                            } elseif ($ptype === 'bundle' && $packagebundles->bundle) {
                                $nameShown = $packagebundles->bundle->name;
                            } elseif ($ptype === 'plan') {
                                if ($packagebundles->service) {
                                    $nameShown = $packagebundles->service->name;
                                } elseif ($packagebundles->bundle) {
                                    $nameShown = $packagebundles->bundle->name;
                                }
                            } elseif ($ptype === 'membership' && $packagebundles->membershipType) {
                                $nameShown = $packagebundles->membershipType->name;
                            } elseif ($packagebundles->bundle) {
                                $nameShown = $packagebundles->bundle->name;
                            } elseif ($packagebundles->service) {
                                $nameShown = $packagebundles->service->name;
                            } elseif ($packagebundles->membershipType) {
                                $nameShown = $packagebundles->membershipType->name;
                            }
                        ?>
                        <tr>
                            <td class="name">{{ $nameShown ?: '-' }}</td>
                            <td class="right">{{ number_format($packagebundles->service_price) }}</td>
                            <td>
                                @if($packagebundles->discount_id == null)
                                    —
                                @elseif($packagebundles->discount_name)
                                    {{ $packagebundles->discount_name }}@if($packagebundles->discount_price) <span style="color:#8A7A5C;">(− {{ number_format($packagebundles->discount_price) }})</span>@endif
                                @else
                                    {{ $packagebundles->discount->name }}@if($packagebundles->discount_price) <span style="color:#8A7A5C;">(− {{ number_format($packagebundles->discount_price) }})</span>@endif
                                @endif
                            </td>
                            <td class="right">{{ number_format($packagebundles->tax_exclusive_net_amount ?? 0) }}</td>
                            <td class="right">{{ $packagebundles->tax_percenatage }}%</td>
                            <td class="right">{{ number_format($packagebundles->tax_price ?? 0) }}</td>
                            <td class="right"><strong>{{ number_format($packagebundles->tax_including_price ?? 0) }}</strong></td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <!-- Grand total ledger -->
    <div class="cinv-ledger">
        <table>
            <tr class="total-row">
                <td class="label">Plan Total</td>
                <td class="amount">Rs. {{ $grand_total ?? 0 }}/-</td>
            </tr>
        </table>
    </div>

    <!-- Payments Received -->
    @php
        $hasPayments = false;
        $total_received = 0;
        if (isset($packageadvances)) {
            foreach ($packageadvances as $p) {
                if ($p->cash_amount != '0' && $p->cash_flow == 'in') { $hasPayments = true; break; }
            }
        }
    @endphp
    @if($hasPayments)
        <div class="cinv-section-title">Payments Received</div>
        <div class="cinv-pay-wrap">
            <table class="cinv-pay">
                <thead>
                    <tr>
                        <th style="width: 30%;">Payment Mode</th>
                        <th style="width: 15%;">Flow</th>
                        <th class="right" style="width: 25%;">Amount</th>
                        <th class="right" style="width: 30%;">Received At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packageadvances as $packageadvances)
                        @if($packageadvances->cash_amount != '0' && $packageadvances->cash_flow == 'in')
                            <tr>
                                <td>{{ $packageadvances->paymentmode->name ?? '—' }}</td>
                                <td style="text-transform:uppercase;letter-spacing:1px;font-size:9px;color:#C4A265;">{{ $packageadvances->cash_flow }}</td>
                                <td class="right">{{ number_format($packageadvances->cash_amount) }}/-</td>
                                <td class="right" style="color:#8A7A5C;">{{ \Carbon\Carbon::parse($packageadvances->created_at)->format('d M Y, h:i A') }}</td>
                            </tr>
                            <?php $total_received += $packageadvances->cash_amount; ?>
                        @endif
                    @endforeach
                    <tr class="tot">
                        <td colspan="2">Total Received</td>
                        <td class="right">Rs. {{ number_format($total_received) }}/-</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <div class="cinv-note">
        <strong>Note.</strong> All treatment prices are inclusive of applicable taxes.
        Thank you for choosing Smart Aesthetics.
    </div>

    <!-- Signatures -->
    <div class="cinv-signatures">
        <div class="cinv-sig-block">
            <div class="cinv-sig-line">Client Signature</div>
            <div class="cinv-sig-name">{{ ucfirst($package->user->name ?? '') }}</div>
        </div>
        <div class="cinv-sig-block">
            <div class="cinv-sig-line">Authorised Signature</div>
            <div class="cinv-sig-name">Smart Aesthetics</div>
        </div>
    </div>

    <div class="cinv-footer">
        <strong>Smart Aesthetics</strong>
        &nbsp;&middot;&nbsp;
        https://aestheticlinics.net
        &nbsp;&middot;&nbsp;
        @if(!empty($location_info->ntn)) NTN {{ $location_info->ntn }} &nbsp;&middot;&nbsp; @endif
        @if(!empty($location_info->stn)) STN {{ $location_info->stn }} &nbsp;&middot;&nbsp; @endif
        {{ $account_info->email ?? '' }}
    </div>
</div>
</body>
</html>
