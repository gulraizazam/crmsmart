<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Clarity Aesthetic — Plan Statement {{ $package->name ?? '' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Trebuchet MS', 'Helvetica Neue', Arial, sans-serif;
            color: #1B2233;
            background: #EDEEF2;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .pinv-shell {
            max-width: 780px;
            margin: 0 auto;
            background: #FFFFFF;
            position: relative;
        }

        /* Top ornamental band */
        .pinv-topband {
            height: 12px;
            background: linear-gradient(90deg, #1B2233 0%, #1B2233 50%, #C8A650 50%, #C8A650 100%);
        }

        /* Header */
        .pinv-head {
            padding: 34px 44px 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #EDEEF2;
        }
        .pinv-brand img { height: 78px; width: auto; max-width: 260px; display: block; }
        .pinv-brand-tag {
            margin-top: 10px;
            font-size: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: #C8A650;
        }
        .pinv-doc-head { text-align: right; }
        .pinv-doc-head .pinv-monogram {
            display: inline-block;
            padding: 4px 14px;
            border: 1px solid #1B2233;
            color: #1B2233;
            font-size: 9.5px;
            letter-spacing: 4px;
            text-transform: uppercase;
        }
        .pinv-doc-head h1 {
            font-family: 'Trebuchet MS', sans-serif;
            font-weight: 300;
            font-size: 30px;
            color: #1B2233;
            margin-top: 10px;
            letter-spacing: 4px;
            text-transform: uppercase;
        }
        .pinv-doc-head h1 strong { font-weight: 700; color: #C8A650; }
        .pinv-doc-head .pinv-doc-ref {
            font-size: 11px;
            color: #6C7284;
            margin-top: 4px;
            letter-spacing: 1px;
        }

        /* Meta strip - 3 columns */
        .pinv-meta-strip {
            display: table;
            width: 100%;
            table-layout: fixed;
            background: #1B2233;
            color: #FFFFFF;
        }
        .pinv-meta-cell {
            display: table-cell;
            padding: 18px 20px;
            border-right: 1px solid rgba(255,255,255,0.08);
            vertical-align: top;
        }
        .pinv-meta-cell:last-child { border-right: none; }
        .pinv-meta-cell .k {
            font-size: 9px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #C8A650;
            margin-bottom: 6px;
        }
        .pinv-meta-cell .v {
            font-size: 14px;
            font-weight: 600;
            color: #FFFFFF;
        }
        .pinv-meta-cell .vs {
            font-size: 11px;
            color: #B0B8CC;
            margin-top: 3px;
        }

        /* Body */
        .pinv-body { padding: 26px 44px 14px; }

        .pinv-section-head {
            border-left: 4px solid #C8A650;
            padding-left: 12px;
            margin: 6px 0 14px;
        }
        .pinv-section-head h3 {
            font-size: 13px;
            color: #1B2233;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 700;
        }
        .pinv-section-head p {
            font-size: 11px;
            color: #7C8397;
            margin-top: 2px;
            font-style: italic;
        }

        /* Services table */
        table.pinv-services {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        table.pinv-services thead th {
            background: #F5F3EC;
            color: #1B2233;
            padding: 12px 10px;
            text-align: left;
            font-size: 10px;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            font-weight: 700;
            border-bottom: 2px solid #C8A650;
        }
        table.pinv-services tbody td {
            padding: 12px 10px;
            border-bottom: 1px solid #ECEDF0;
            color: #2B3245;
            vertical-align: top;
        }
        table.pinv-services tbody tr:nth-child(even) td { background: #FBFAF6; }
        table.pinv-services tbody tr:hover td { background: #F5F3EC; }
        table.pinv-services td.money { text-align: right; font-variant-numeric: tabular-nums; }
        table.pinv-services td.name { font-weight: 700; color: #1B2233; }

        /* Grand total block */
        .pinv-grand-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 18px;
        }
        .pinv-grand-box {
            min-width: 320px;
            border: 2px solid #1B2233;
            border-radius: 2px;
            overflow: hidden;
        }
        .pinv-grand-box .row {
            display: flex;
            justify-content: space-between;
            padding: 10px 18px;
            font-size: 12px;
            color: #4A5065;
        }
        .pinv-grand-box .row.total {
            background: #1B2233;
            color: #FFFFFF;
            padding: 16px 18px;
        }
        .pinv-grand-box .row.total .label {
            font-size: 11px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #C8A650;
        }
        .pinv-grand-box .row.total .amount {
            font-size: 22px;
            font-weight: 700;
            color: #FFFFFF;
        }

        /* Payments table */
        .pinv-pay-wrap { margin-top: 30px; }
        table.pinv-pay {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }
        table.pinv-pay thead th {
            background: #1B2233;
            color: #C8A650;
            padding: 10px;
            text-align: left;
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        table.pinv-pay tbody td {
            padding: 10px;
            border-bottom: 1px solid #ECEDF0;
            color: #3A4055;
        }
        table.pinv-pay tbody tr.total-row td {
            background: #F5F3EC;
            color: #1B2233;
            font-weight: 700;
            border-top: 2px solid #C8A650;
        }

        /* Notes */
        .pinv-notes {
            margin-top: 28px;
            padding: 16px 20px;
            background: #F8F7F1;
            border: 1px solid #E5DFC5;
            font-size: 11px;
            color: #5A5844;
            line-height: 1.6;
        }
        .pinv-notes strong { color: #1B2233; }

        /* Signature block */
        .pinv-thank {
            text-align: center;
            padding: 30px 44px 8px;
            font-family: Georgia, 'Times New Roman', serif;
            font-style: italic;
            color: #1B2233;
            font-size: 14px;
            letter-spacing: 1px;
        }
        .pinv-thank .em { color: #C8A650; font-weight: 700; font-style: normal; letter-spacing: 3px; text-transform: uppercase; font-size: 11px; }

        .pinv-sigs {
            display: table;
            width: 100%;
            padding: 22px 44px 8px;
        }
        .pinv-sig-cell {
            display: table-cell;
            width: 45%;
            vertical-align: bottom;
        }
        .pinv-sig-cell.right { text-align: right; }
        .pinv-sig-line {
            border-top: 1px solid #1B2233;
            padding-top: 8px;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #1B2233;
            font-weight: 700;
        }
        .pinv-sig-name { font-size: 11px; color: #7C8397; margin-top: 3px; font-style: italic; }

        /* Footer */
        .pinv-footer {
            margin-top: 26px;
            padding: 16px 44px;
            background: #1B2233;
            color: #B0B8CC;
            font-size: 10px;
            letter-spacing: 1px;
            text-align: center;
        }
        .pinv-footer .brandmark {
            color: #C8A650;
            letter-spacing: 5px;
            text-transform: uppercase;
            font-weight: 700;
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
        }

        @media not print {
            .pinv-shell { margin-top: 30px; margin-bottom: 30px; box-shadow: 0 12px 60px rgba(27, 34, 51, 0.18); border-radius: 4px; overflow: hidden; }
        }
        @page { size: A4 portrait; margin: 8mm 6mm; }
        @media print {
            body { background: #FFFFFF; }
            .pinv-shell { box-shadow: none; margin: 0; border-radius: 0; }
        }
    </style>
</head>
<body>
<div class="pinv-shell">

    <div class="pinv-topband"></div>

    <!-- Header -->
    <div class="pinv-head">
        <div class="pinv-brand">
            <img src="{{ asset('logoClarity.jpg') }}" alt="Clarity Aesthetic">
            <div class="pinv-brand-tag">Aesthetic &middot; Plan Statement</div>
        </div>
        <div class="pinv-doc-head">
            <div class="pinv-monogram">Plan &middot; Statement</div>
            <h1>Plan <strong>Invoice</strong></h1>
            <div class="pinv-doc-ref">{{ $package->name ?? '' }}</div>
        </div>
    </div>

    <!-- Meta strip -->
    <div class="pinv-meta-strip">
        <div class="pinv-meta-cell">
            <div class="k">Plan</div>
            <div class="v">{{ $package->name ?? '' }}</div>
            <div class="vs">{{ ucfirst($package->plan_type ?? 'plan') }}</div>
        </div>
        <div class="pinv-meta-cell">
            <div class="k">Client</div>
            <div class="v">{{ ucfirst($package->user->name ?? '') }}</div>
            <div class="vs">C-{{ $package->user->id ?? '' }}</div>
        </div>
        <div class="pinv-meta-cell">
            <div class="k">Issued</div>
            <div class="v">{{ \Carbon\Carbon::parse($package->created_at)->format('d M Y') }}</div>
            <div class="vs">{{ \Carbon\Carbon::parse($package->created_at)->format('h:i A') }}</div>
        </div>
    </div>

    <!-- Body: Services -->
    <div class="pinv-body">
        <div class="pinv-section-head">
            <h3>Plan Components</h3>
            <p>Services and bundles included in this plan</p>
        </div>

        <table class="pinv-services">
            <thead>
                <tr>
                    <th style="width: 34%;">Service / Bundle</th>
                    <th style="width: 12%;" class="money">Price</th>
                    <th style="width: 14%;">Discount</th>
                    <th style="width: 12%;" class="money">Subtotal</th>
                    <th style="width: 10%;" class="money">Tax %</th>
                    <th style="width: 10%;" class="money">Tax</th>
                    <th style="width: 12%;" class="money">Total</th>
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
                            <td class="money">{{ number_format($packagebundles->service_price) }}</td>
                            <td>
                                @if($packagebundles->discount_id == null)
                                    <span style="color:#B0B8CC;">—</span>
                                @elseif($packagebundles->discount_name)
                                    {{ $packagebundles->discount_name }}
                                @else
                                    {{ $packagebundles->discount->name }}
                                @endif
                                @if($packagebundles->discount_price)
                                    <div style="font-size:10px;color:#8A8F9E;margin-top:2px;">− {{ number_format($packagebundles->discount_price) }}</div>
                                @endif
                            </td>
                            <td class="money">{{ number_format($packagebundles->tax_exclusive_net_amount ?? 0) }}</td>
                            <td class="money">{{ $packagebundles->tax_percenatage }}%</td>
                            <td class="money">{{ number_format($packagebundles->tax_price ?? 0) }}</td>
                            <td class="money"><strong>{{ number_format($packagebundles->tax_including_price ?? 0) }}</strong></td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <div class="pinv-grand-wrap">
            <div class="pinv-grand-box">
                <div class="row total">
                    <span class="label">Plan Total</span>
                    <span class="amount">Rs. {{ $grand_total ?? 0 }}/-</span>
                </div>
            </div>
        </div>

        <!-- Payments -->
        <div class="pinv-pay-wrap">
            <div class="pinv-section-head">
                <h3>Payments Received</h3>
                <p>Cash flow records against this plan</p>
            </div>

            <table class="pinv-pay">
                <thead>
                    <tr>
                        <th>Payment Mode</th>
                        <th>Flow</th>
                        <th style="text-align:right;">Amount</th>
                        <th style="text-align:right;">Received At</th>
                    </tr>
                </thead>
                <tbody>
                    @if($packageadvances)
                        <?php $total_received = 0; ?>
                        @foreach($packageadvances as $packageadvances)
                            @if($packageadvances->cash_amount != '0' && $packageadvances->cash_flow == 'in')
                                <tr>
                                    <td>{{ $packageadvances->paymentmode->name ?? '—' }}</td>
                                    <td style="text-transform:uppercase;letter-spacing:1px;font-size:10px;color:#C8A650;">{{ $packageadvances->cash_flow }}</td>
                                    <td style="text-align:right;">{{ number_format($packageadvances->cash_amount) }}/-</td>
                                    <td style="text-align:right;color:#7C8397;">{{ \Carbon\Carbon::parse($packageadvances->created_at)->format('d M Y, h:i A') }}</td>
                                </tr>
                                <?php $total_received += $packageadvances->cash_amount; ?>
                            @endif
                        @endforeach
                        <tr class="total-row">
                            <td>Total Received</td>
                            <td></td>
                            <td style="text-align:right;">Rs. {{ number_format($total_received) }}/-</td>
                            <td></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="pinv-notes">
            <strong>Note.</strong> All treatment prices are inclusive of applicable taxes.
            For questions about this plan statement, please reach out to the centre where it was issued.
        </div>
    </div>

    <!-- Thank you -->
    <div class="pinv-thank">
        Thank you for choosing Clarity Aesthetic. <br>
        <span class="em">Where Precision Meets Radiance</span>
    </div>

    <!-- Signatures -->
    <div class="pinv-sigs">
        <div class="pinv-sig-cell">
            <div class="pinv-sig-line">Client Signature</div>
            <div class="pinv-sig-name">{{ ucfirst($package->user->name ?? '') }}</div>
        </div>
        <div class="pinv-sig-cell right">
            <div class="pinv-sig-line">Authorised Signature</div>
            <div class="pinv-sig-name">Clarity Aesthetic</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="pinv-footer">
        <span class="brandmark">Clarity Aesthetic</span>
        {{ $location_info->address ?? '' }}
        @if(!empty($location_info->fdo_phone)) &nbsp;&middot;&nbsp; {{ $location_info->fdo_phone }} @endif
        @if(!empty($account_info->email)) &nbsp;&middot;&nbsp; {{ $account_info->email }} @endif
        &nbsp;&middot;&nbsp; https://clarityaesthetic.pk
        @if(!empty($location_info->ntn)) &nbsp;&middot;&nbsp; NTN {{ $location_info->ntn }} @endif
        @if(!empty($location_info->stn)) &nbsp;&middot;&nbsp; STN {{ $location_info->stn }} @endif
    </div>
</div>
</body>
</html>
