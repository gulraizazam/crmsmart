@php
    $kpiIcons = [
        'collection' => 'la la-money',
        'revenue' => 'la la-line-chart',
        'consultancies' => 'la la-stethoscope',
        'treatments' => 'la la-medkit',
        'leads' => 'la la-user-plus',
        'patients' => 'la la-users',
        'expenses' => 'la la-credit-card',
        'consultancies_all' => 'la la-calendar-check-o',
    ];

    $visibleKpis = collect($kpis ?? [])->filter(function ($kpi) {
        return !empty($kpi['visible']);
    });
    $kpiCount = max(1, $visibleKpis->count());
    $kpiCol = $kpiCount <= 4 ? 'col-6 col-lg-3' : ($kpiCount <= 6 ? 'col-6 col-md-4 col-xl-2' : 'col-6 col-md-4 col-xl-3');

    $rankList = function ($payload, $money = false, $limit = 5) {
        $labels = $payload['labels'] ?? [];
        $values = $payload['values'] ?? (($payload['datasets'][0]['data'] ?? []) ?: []);
        $rows = [];
        foreach ($labels as $i => $label) {
            $rows[] = [
                'label' => (string) $label,
                'value' => (float) ($values[$i] ?? 0),
            ];
        }
        usort($rows, function ($a, $b) {
            if ($a['value'] == $b['value']) {
                return 0;
            }
            return ($a['value'] < $b['value']) ? 1 : -1;
        });
        $rows = array_slice($rows, 0, $limit);
        $max = max(1, (float) (isset($rows[0]['value']) ? $rows[0]['value'] : 1));

        return array_map(function ($row) use ($max, $money) {
            $row['pct'] = round(($row['value'] / $max) * 100);
            $row['display'] = $money
                ? 'PKR ' . number_format($row['value'], 0)
                : number_format($row['value']);
            return $row;
        }, $rows);
    };

    $topCentres = $rankList($charts['collection_by_centre'] ?? [], true, 5);
    $topServices = $rankList($charts['revenue_by_service'] ?? [], true, 5);
    $topLeadSources = $rankList($charts['leads_by_source'] ?? [], false, 5);
    $canCollection = \Illuminate\Support\Facades\Gate::allows('dashboard_collection_by_centre');
    $canRevenue = \Illuminate\Support\Facades\Gate::allows('dashboard_revenue_by_centre');
    $canStates = \Illuminate\Support\Facades\Gate::allows('dashboard_states');
    $canServiceRevenue = \Illuminate\Support\Facades\Gate::allows('dashboard_revenue_by_service');
    $canCashflow = \Illuminate\Support\Facades\Gate::allows('cashflow_dashboard');
    // Performance shows consultancy status in place of the payment mix when collection is hidden.
    $statusInPerformance = !$canCollection && $canStates;
@endphp

<section class="dash-hero">
    <div class="dash-hero__copy">
        <p class="dash-hero__eyebrow">Operations overview</p>
        <h2>Dashboard</h2>
        <p class="dash-range">{{ $range_label }}</p>
    </div>
    <form method="get" action="{{ route('admin.home') }}" class="dash-period" aria-label="Dashboard period">
        @foreach ($periods as $item)
            <button type="submit" name="period" value="{{ $item['value'] }}"
                class="dash-period__btn {{ $period->value === $item['value'] ? 'is-active' : '' }}">
                {{ $item['label'] }}
            </button>
        @endforeach
    </form>
</section>

@if ($visibleKpis->isNotEmpty())
<section class="dash-section">
    <div class="row dash-kpi-row" id="dash-overview-kpis">
        @foreach ($visibleKpis as $key => $kpi)
            <div class="{{ $kpiCol }}">
                <article class="dash-kpi dash-kpi--{{ $kpi['theme'] }}">
                    <div class="dash-kpi__top">
                        <div class="dash-kpi__icon" aria-hidden="true"><i class="{{ $kpiIcons[$key] ?? 'la la-bar-chart' }}"></i></div>
                        <div class="dash-kpi__copy">
                            <span class="dash-kpi__label">{{ $kpi['label'] }}</span>
                            <span class="dash-kpi__value">
                                @if ($kpi['format'] === 'money')
                                    PKR {{ number_format((float) $kpi['value'], 0) }}
                                @else
                                    {{ number_format((float) $kpi['value']) }}
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="dash-kpi__footer">
                        <span class="dash-kpi__delta {{ $kpi['delta'] < 0 ? 'is-down' : ($kpi['delta'] > 0 ? 'is-up' : '') }}">
                            {{ $kpi['delta'] > 0 ? '+' : '' }}{{ $kpi['delta'] }}%
                        </span>
                        <span class="dash-kpi__hint">{{ $kpi['hint'] }}</span>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
</section>
@endif

@if ($canCollection || $canRevenue || $canStates)
<section class="dash-section">
    <div class="dash-section__head">
        <div>
            <h3 class="dash-section__title">Performance</h3>
            <p class="dash-section__sub">Core money and appointment movement for this period</p>
        </div>
    </div>
    <div class="row dash-chart-row">
        @if ($canCollection || $canRevenue)
            @include('admin.partials.dashboard-chart-card', [
                'title' => 'Collection vs revenue',
                'subtitle' => 'Daily trend',
                'id' => 'chartCollectionRevenue',
                'cols' => 'col-xl-8',
                'height' => '300',
            ])
        @endif
        @if ($canCollection)
            @include('admin.partials.dashboard-chart-card', [
                'title' => 'Payment mix',
                'subtitle' => 'Collection by mode',
                'id' => 'chartCollectionMode',
                'cols' => 'col-xl-4',
                'height' => '300',
            ])
        @elseif ($statusInPerformance)
            @include('admin.partials.dashboard-chart-card', [
                'title' => 'Consultancies by status',
                'subtitle' => 'Current period',
                'id' => 'chartAppointmentStatus',
                'cols' => 'col-xl-4',
                'height' => '300',
            ])
        @endif

        @if ($canCollection)
            @include('admin.partials.dashboard-chart-card', [
                'title' => 'Collection by centre',
                'subtitle' => 'Where money is coming from',
                'id' => 'chartCollectionCentre',
                'cols' => $canStates ? 'col-xl-6' : 'col-12',
                'height' => '300',
            ])
        @endif
        @if ($canStates)
            @include('admin.partials.dashboard-chart-card', [
                'title' => 'Appointments scheduled',
                'subtitle' => 'Consultancy & treatment volume',
                'id' => 'chartAppointmentTrend',
                'cols' => $canCollection ? 'col-xl-6' : 'col-12',
                'height' => '300',
            ])
        @endif
    </div>
</section>
@endif

@if (($canCollection && count($topCentres)) || ($canServiceRevenue && count($topServices)) || ($canStates && count($topLeadSources)))
<section class="dash-section">
    <div class="dash-section__head">
        <div>
            <h3 class="dash-section__title">Top movers</h3>
            <p class="dash-section__sub">Highest contributors — no chart noise</p>
        </div>
    </div>
    <div class="row dash-rank-row">
        @if ($canCollection && count($topCentres))
            <div class="col-md-4">
                <div class="dash-rank-card">
                    <h4>Top centres <span>by collection</span></h4>
                    <ol class="dash-rank-list">
                        @foreach ($topCentres as $row)
                            <li>
                                <div class="dash-rank-list__meta">
                                    <strong>{{ $row['label'] }}</strong>
                                    <span>{{ $row['display'] }}</span>
                                </div>
                                <div class="dash-rank-list__bar" aria-hidden="true"><i style="width: {{ $row['pct'] }}%"></i></div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        @endif
        @if ($canServiceRevenue && count($topServices))
            <div class="col-md-4">
                <div class="dash-rank-card">
                    <h4>Top services <span>by revenue</span></h4>
                    <ol class="dash-rank-list">
                        @foreach ($topServices as $row)
                            <li>
                                <div class="dash-rank-list__meta">
                                    <strong>{{ $row['label'] }}</strong>
                                    <span>{{ $row['display'] }}</span>
                                </div>
                                <div class="dash-rank-list__bar" aria-hidden="true"><i style="width: {{ $row['pct'] }}%"></i></div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        @endif
        @if ($canStates && count($topLeadSources))
            <div class="col-md-4">
                <div class="dash-rank-card">
                    <h4>Lead sources <span>new leads</span></h4>
                    <ol class="dash-rank-list">
                        @foreach ($topLeadSources as $row)
                            <li>
                                <div class="dash-rank-list__meta">
                                    <strong>{{ $row['label'] }}</strong>
                                    <span>{{ $row['display'] }}</span>
                                </div>
                                <div class="dash-rank-list__bar" aria-hidden="true"><i style="width: {{ $row['pct'] }}%"></i></div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        @endif
    </div>
</section>
@endif

@if ($canRevenue || $canServiceRevenue)
<section class="dash-section">
    <div class="dash-section__head">
        <div>
            <h3 class="dash-section__title">Revenue breakdown</h3>
            <p class="dash-section__sub">Revenue split by centre and service</p>
        </div>
    </div>
    <div class="row dash-chart-row">
        @if ($canRevenue)
            @include('admin.partials.dashboard-chart-card', [
                'title' => 'Revenue by centre',
                'subtitle' => 'Revenue earned per centre',
                'id' => 'chartRevenueCentre',
                'cols' => $canServiceRevenue ? 'col-xl-6' : 'col-12',
                'height' => '340',
            ])
        @endif
        @if ($canServiceRevenue)
            @include('admin.partials.dashboard-chart-card', [
                'title' => 'Revenue by service category',
                'subtitle' => 'Share of revenue per category',
                'id' => 'chartRevenueCategory',
                'cols' => 'col-xl-6',
                'height' => '340',
            ])
            @include('admin.partials.dashboard-chart-card', [
                'title' => 'Top services by revenue',
                'subtitle' => 'Highest earning services',
                'id' => 'chartRevenueService',
                'cols' => $canRevenue ? 'col-12' : 'col-xl-6',
                'height' => '340',
            ])
        @endif
    </div>
</section>
@endif

@if ($canStates)
<section class="dash-section">
    <div class="dash-section__head">
        <div>
            <h3 class="dash-section__title">Appointments</h3>
            <p class="dash-section__sub">Appointment mix by type, status, centre and doctor</p>
        </div>
    </div>
    <div class="row dash-chart-row">
        @include('admin.partials.dashboard-chart-card', [
            'title' => 'Appointments by type',
            'subtitle' => 'Split by appointment type',
            'id' => 'chartAppointmentType',
            'cols' => $statusInPerformance ? 'col-xl-6' : 'col-xl-4',
            'height' => '320',
        ])
        @unless ($statusInPerformance)
            @include('admin.partials.dashboard-chart-card', [
                'title' => 'Consultancies by status',
                'subtitle' => 'Current period',
                'id' => 'chartAppointmentStatus',
                'cols' => 'col-xl-4',
                'height' => '320',
            ])
        @endunless
        @include('admin.partials.dashboard-chart-card', [
            'title' => 'Appointments by centre',
            'subtitle' => 'Volume per centre',
            'id' => 'chartAppointmentCentre',
            'cols' => $statusInPerformance ? 'col-xl-6' : 'col-xl-4',
            'height' => '320',
        ])
        @include('admin.partials.dashboard-chart-card', [
            'title' => 'Appointments by doctor',
            'subtitle' => 'Top doctors by appointments',
            'id' => 'chartDoctors',
            'cols' => 'col-12',
            'height' => '360',
        ])
    </div>
</section>

<section class="dash-section">
    <div class="dash-section__head">
        <div>
            <h3 class="dash-section__title">Leads &amp; patients</h3>
            <p class="dash-section__sub">Where leads come from and how new patients are growing</p>
        </div>
    </div>
    <div class="row dash-chart-row">
        @include('admin.partials.dashboard-chart-card', [
            'title' => 'Leads by source',
            'subtitle' => 'New leads per source',
            'id' => 'chartLeadSource',
            'cols' => 'col-xl-6',
            'height' => '320',
        ])
        @include('admin.partials.dashboard-chart-card', [
            'title' => 'Leads by status',
            'subtitle' => 'New leads by status',
            'id' => 'chartLeadStatus',
            'cols' => 'col-xl-6',
            'height' => '320',
        ])
        @include('admin.partials.dashboard-chart-card', [
            'title' => 'New patients by gender',
            'subtitle' => 'Current period',
            'id' => 'chartPatientGender',
            'cols' => 'col-xl-4',
            'height' => '300',
        ])
        @include('admin.partials.dashboard-chart-card', [
            'title' => 'New patients',
            'subtitle' => 'Daily registrations',
            'id' => 'chartPatientTrend',
            'cols' => 'col-xl-8',
            'height' => '300',
        ])
    </div>
</section>
@endif

@if ($canCashflow)
<section class="dash-section">
    <div class="dash-section__head">
        <div>
            <h3 class="dash-section__title">Cash flow</h3>
            <p class="dash-section__sub">Spending and cash pool positions</p>
        </div>
    </div>
    <div class="row dash-chart-row">
        @include('admin.partials.dashboard-chart-card', [
            'title' => 'Expenses by category',
            'subtitle' => 'Spend this period, excluding rejected',
            'id' => 'chartExpenses',
            'cols' => 'col-xl-6',
            'height' => '340',
        ])
        @include('admin.partials.dashboard-chart-card', [
            'title' => 'Cash pool balances',
            'subtitle' => 'Current balance per pool',
            'id' => 'chartPools',
            'cols' => 'col-xl-6',
            'height' => '340',
        ])
    </div>
</section>
@endif
