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
@endphp

<div class="dash-toolbar">
    <div>
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
</div>

<div class="row dash-kpi-row" id="dash-overview-kpis">
    @foreach ($kpis as $key => $kpi)
        @if (!empty($kpi['visible']))
            <div class="col-6 col-lg-3">
                <div class="dash-kpi dash-kpi--{{ $kpi['theme'] }}">
                    <div class="dash-kpi__top">
                        <div class="dash-kpi__icon"><i class="{{ $kpiIcons[$key] ?? 'la la-bar-chart' }}"></i></div>
                        <div class="dash-kpi__copy">
                            <span class="dash-kpi__value">
                                @if ($kpi['format'] === 'money')
                                    PKR {{ number_format((float) $kpi['value'], 2) }}
                                @else
                                    {{ number_format((float) $kpi['value']) }}
                                @endif
                            </span>
                            <span class="dash-kpi__label">{{ $kpi['label'] }}</span>
                        </div>
                    </div>
                    <span class="dash-kpi__delta {{ $kpi['delta'] < 0 ? 'is-down' : ($kpi['delta'] > 0 ? 'is-up' : '') }}">
                        {{ $kpi['delta'] > 0 ? '+' : '' }}{{ $kpi['delta'] }}% vs prior
                    </span>
                    <p class="dash-kpi__hint">{{ $kpi['hint'] }}</p>
                </div>
            </div>
        @endif
    @endforeach
</div>

<div class="row dash-chart-row">
    @if (\Illuminate\Support\Facades\Gate::allows('dashboard_collection_by_centre') || \Illuminate\Support\Facades\Gate::allows('dashboard_revenue_by_centre'))
        @include('admin.partials.dashboard-chart-card', ['title' => 'Collection vs revenue', 'id' => 'chartCollectionRevenue', 'cols' => 'col-xl-8', 'height' => '320'])
    @endif
    @can('dashboard_collection_by_centre')
        @include('admin.partials.dashboard-chart-card', ['title' => 'Collection by payment mode', 'id' => 'chartCollectionMode', 'cols' => 'col-xl-4', 'height' => '320'])
        @include('admin.partials.dashboard-chart-card', ['title' => 'Collection by centre', 'id' => 'chartCollectionCentre', 'cols' => 'col-xl-6', 'height' => '360'])
    @endcan
    @can('dashboard_revenue_by_centre')
        @include('admin.partials.dashboard-chart-card', ['title' => 'Revenue by centre', 'id' => 'chartRevenueCentre', 'cols' => 'col-xl-6', 'height' => '360'])
    @endcan
    @can('dashboard_states')
        @include('admin.partials.dashboard-chart-card', ['title' => 'Appointments scheduled', 'id' => 'chartAppointmentTrend', 'cols' => 'col-xl-8', 'height' => '320'])
        @include('admin.partials.dashboard-chart-card', ['title' => 'Appointments by type', 'id' => 'chartAppointmentType', 'cols' => 'col-xl-4', 'height' => '320'])
        @include('admin.partials.dashboard-chart-card', ['title' => 'Consultancies by status', 'id' => 'chartAppointmentStatus', 'cols' => 'col-xl-6', 'height' => '340'])
        @include('admin.partials.dashboard-chart-card', ['title' => 'Appointments by centre', 'id' => 'chartAppointmentCentre', 'cols' => 'col-xl-6', 'height' => '340'])
    @endcan
    @can('dashboard_revenue_by_service')
        @include('admin.partials.dashboard-chart-card', ['title' => 'Revenue by service category', 'id' => 'chartRevenueCategory', 'cols' => 'col-xl-6', 'height' => '380'])
        @include('admin.partials.dashboard-chart-card', ['title' => 'Top services by revenue', 'id' => 'chartRevenueService', 'cols' => 'col-xl-6', 'height' => '380'])
    @endcan
    @can('dashboard_states')
        @include('admin.partials.dashboard-chart-card', ['title' => 'Leads by source', 'id' => 'chartLeadSource', 'cols' => 'col-xl-6', 'height' => '340'])
        @include('admin.partials.dashboard-chart-card', ['title' => 'Leads by status', 'id' => 'chartLeadStatus', 'cols' => 'col-xl-6', 'height' => '340'])
        @include('admin.partials.dashboard-chart-card', ['title' => 'New patients by gender', 'id' => 'chartPatientGender', 'cols' => 'col-xl-4', 'height' => '320'])
        @include('admin.partials.dashboard-chart-card', ['title' => 'New patients', 'id' => 'chartPatientTrend', 'cols' => 'col-xl-8', 'height' => '320'])
        @include('admin.partials.dashboard-chart-card', ['title' => 'Appointments by doctor', 'id' => 'chartDoctors', 'cols' => 'col-xl-6', 'height' => '380'])
    @endcan
    @can('cashflow_dashboard')
        @include('admin.partials.dashboard-chart-card', ['title' => 'Expenses by category', 'id' => 'chartExpenses', 'cols' => 'col-xl-6', 'height' => '380'])
        @include('admin.partials.dashboard-chart-card', ['title' => 'Cash pool balances', 'id' => 'chartPools', 'cols' => 'col-12', 'height' => '280'])
    @endcan
</div>
