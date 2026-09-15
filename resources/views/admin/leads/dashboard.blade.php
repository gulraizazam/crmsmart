@extends('admin.layouts.master')
@section('title', 'Leads Dashboard')
@section('content')
@php
    $kpiIcons = [
        'leads' => 'la la-user-plus',
        'booked' => 'la la-calendar-check-o',
        'arrived' => 'la la-sign-in',
        'converted' => 'la la-check-circle',
        'conversion_rate' => 'la la-percent',
        'collection' => 'la la-money',
        'avg_value' => 'la la-line-chart',
        'junk' => 'la la-trash',
    ];
@endphp

@push('css')
    <link rel="stylesheet" href="{{ asset('assets/css/sneat-dashboard.css') }}?v=3">
@endpush

<div class="content d-flex flex-column flex-column-fluid sneat-dashboard" id="kt_content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid dash-container">
            <div class="dash-toolbar">
                <div>
                    <h2>Leads dashboard</h2>
                    <p class="dash-range">{{ $range_label }}</p>
                </div>
                <div class="dash-toolbar__actions">
                    <a href="{{ route('admin.leads.index') }}" class="dash-toolbar__link">
                        <i class="la la-briefcase"></i>
                        View leads
                    </a>
                    <form method="get" action="{{ route('admin.leads.dashboard') }}" class="dash-period" aria-label="Dashboard period">
                        @foreach ($periods as $item)
                            <button type="submit" name="period" value="{{ $item['value'] }}"
                                class="dash-period__btn {{ $period->value === $item['value'] ? 'is-active' : '' }}">
                                {{ $item['label'] }}
                            </button>
                        @endforeach
                    </form>
                </div>
            </div>

            <div class="row dash-kpi-row">
                @foreach ($kpis as $key => $kpi)
                    @if (!empty($kpi['visible']))
                        <div class="col-6 col-xl-3">
                            <div class="dash-kpi dash-kpi--{{ $kpi['theme'] }}">
                                <div class="dash-kpi__top">
                                    <div class="dash-kpi__icon"><i class="{{ $kpiIcons[$key] ?? 'la la-bar-chart' }}"></i></div>
                                    <div class="dash-kpi__copy">
                                        <span class="dash-kpi__value">
                                            @if ($kpi['format'] === 'money')
                                                PKR {{ number_format((float) $kpi['value'], 2) }}
                                            @elseif ($kpi['format'] === 'percent')
                                                {{ number_format((float) $kpi['value'], 1) }}%
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

            <div class="dash-section">
                <h3 class="dash-section__title">Pipeline</h3>
                <div class="row dash-chart-row">
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Lead volume',
                        'subtitle' => 'New leads created vs those that converted',
                        'id' => 'chartLeadsTrend',
                        'cols' => 'col-xl-8',
                        'height' => '320',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Conversion funnel',
                        'subtitle' => 'Outcome of leads created in this period',
                        'id' => 'chartConversionFunnel',
                        'cols' => 'col-xl-4',
                        'height' => '320',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Leads by status',
                        'subtitle' => 'Current pipeline status',
                        'id' => 'chartLeadStatus',
                        'cols' => 'col-xl-4',
                        'height' => '340',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Assignment mix',
                        'subtitle' => 'Assigned CSR vs still unassigned',
                        'id' => 'chartAssignmentMix',
                        'cols' => 'col-xl-4',
                        'height' => '340',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Channel mix',
                        'subtitle' => 'Meta Ads ingest vs all other sources',
                        'id' => 'chartChannelMix',
                        'cols' => 'col-xl-4',
                        'height' => '340',
                    ])
                </div>
            </div>

            <div class="dash-section">
                <h3 class="dash-section__title">Sources & earnings</h3>
                <div class="row dash-chart-row">
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Leads by source',
                        'subtitle' => 'Where new leads originated',
                        'id' => 'chartLeadSource',
                        'cols' => 'col-xl-6',
                        'height' => '380',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Collection by lead source',
                        'subtitle' => 'Cash / Card / Bank received from lead appointments',
                        'id' => 'chartCollectionBySource',
                        'cols' => 'col-xl-6',
                        'height' => '380',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Conversion rate by source',
                        'subtitle' => 'Converted ÷ new leads for each source',
                        'id' => 'chartConversionRateBySource',
                        'cols' => 'col-xl-6',
                        'height' => '360',
                    ])
                </div>
            </div>

            <div class="dash-section">
                <h3 class="dash-section__title">Agents & centres</h3>
                <div class="row dash-chart-row">
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Leads by agent',
                        'subtitle' => 'Assigned CSR workload',
                        'id' => 'chartLeadsByAgent',
                        'cols' => 'col-xl-6',
                        'height' => '380',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Conversion rate by agent',
                        'subtitle' => 'Converted ÷ new leads for each CSR',
                        'id' => 'chartConversionRateByAgent',
                        'cols' => 'col-xl-6',
                        'height' => '380',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Leads by creator',
                        'subtitle' => 'Who captured the lead',
                        'id' => 'chartLeadsByCreator',
                        'cols' => 'col-xl-6',
                        'height' => '360',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Leads by centre',
                        'subtitle' => 'Clinic attached to the lead',
                        'id' => 'chartLeadsByCentre',
                        'cols' => 'col-xl-6',
                        'height' => '360',
                    ])
                </div>
            </div>

            <div class="dash-section">
                <h3 class="dash-section__title">Demand & profile</h3>
                <div class="row dash-chart-row">
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Leads by department',
                        'id' => 'chartLeadsByDepartment',
                        'cols' => 'col-xl-4',
                        'height' => '340',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Leads by gender',
                        'id' => 'chartLeadsByGender',
                        'cols' => 'col-xl-4',
                        'height' => '340',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Leads by city',
                        'id' => 'chartLeadsByCity',
                        'cols' => 'col-xl-4',
                        'height' => '340',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Top services',
                        'subtitle' => 'Treatments requested on new leads',
                        'id' => 'chartLeadsByService',
                        'cols' => 'col-xl-6',
                        'height' => '380',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Leads by weekday',
                        'subtitle' => 'When leads are created',
                        'id' => 'chartLeadsByWeekday',
                        'cols' => 'col-xl-6',
                        'height' => '380',
                    ])
                    @include('admin.partials.dashboard-chart-card', [
                        'title' => 'Leads by hour of day',
                        'subtitle' => 'Capture pattern across 24 hours',
                        'id' => 'chartLeadsByHour',
                        'cols' => 'col-12',
                        'height' => '300',
                    ])
                </div>
            </div>
        </div>
    </div>
</div>

@push('datatable-js')
<script>
window.leadsDashboardCharts = @json($charts ?? []);
</script>
<script src="{{ asset('assets/js/pages/leads/dashboard.js') }}?v={{ @filemtime(public_path('assets/js/pages/leads/dashboard.js')) }}"></script>
@endpush
@endsection
