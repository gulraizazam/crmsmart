<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Smart Aesthetics — Plan Invoice {{ $package->name ?? '' }}</title>
    <meta content="Smart Aesthetics" name="description" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    @include('admin.invoices.partials.print-styles', ['compact' => true])
</head>
<body>
<div class="cinv-shell">
    <div class="cinv-head">
        <div class="cinv-brand">
            <img src="{{ asset('logoClarity.jpg') }}?v=4" alt="Smart Aesthetics">
            <div class="cinv-brand-sub">{{ $location_info->name ?? 'Smart Aesthetics' }}</div>
        </div>
        <div class="cinv-doc-type">
            <div class="cinv-kicker">Invoice</div>
            <div class="cinv-title">Plan</div>
            <div class="cinv-subtitle">{{ $package->name ?? '' }}</div>
        </div>
    </div>

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

    <div class="cinv-section-title">Plan components</div>
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
                                    {{ $packagebundles->discount_name }}@if($packagebundles->discount_price) (− {{ number_format($packagebundles->discount_price) }})@endif
                                @else
                                    {{ $packagebundles->discount->name }}@if($packagebundles->discount_price) (− {{ number_format($packagebundles->discount_price) }})@endif
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

    <div class="cinv-ledger">
        <table>
            <tr class="total-row">
                <td class="label">Plan total</td>
                <td class="amount">Rs. {{ $grand_total ?? 0 }}/-</td>
            </tr>
        </table>
    </div>

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
        <div class="cinv-section-title">Payments received</div>
        <div class="cinv-pay-wrap">
            <table class="cinv-pay">
                <thead>
                    <tr>
                        <th style="width: 30%;">Payment mode</th>
                        <th style="width: 15%;">Flow</th>
                        <th class="right" style="width: 25%;">Amount</th>
                        <th class="right" style="width: 30%;">Received at</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packageadvances as $packageadvances)
                        @if($packageadvances->cash_amount != '0' && $packageadvances->cash_flow == 'in')
                            <tr>
                                <td>{{ $packageadvances->paymentmode->name ?? '—' }}</td>
                                <td>{{ strtoupper($packageadvances->cash_flow) }}</td>
                                <td class="right">{{ number_format($packageadvances->cash_amount) }}/-</td>
                                <td class="right">{{ \Carbon\Carbon::parse($packageadvances->created_at)->format('d M Y, h:i A') }}</td>
                            </tr>
                            <?php $total_received += $packageadvances->cash_amount; ?>
                        @endif
                    @endforeach
                    <tr class="tot">
                        <td colspan="2">Total received</td>
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

    <div class="cinv-signatures">
        <div class="cinv-sig-block">
            <div class="cinv-sig-line">Client signature</div>
            <div class="cinv-sig-name">{{ ucfirst($package->user->name ?? '') }}</div>
        </div>
        <div class="cinv-sig-block">
            <div class="cinv-sig-line">Authorised signature</div>
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
