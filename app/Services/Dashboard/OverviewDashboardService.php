<?php

namespace App\Services\Dashboard;

use App\Helpers\DashboardHelper;
use App\Models\AppointmentStatuses;
use App\Models\CashFlow\Expense;
use App\Models\LeadSources;
use App\Models\LeadStatuses;
use App\Models\Locations;
use App\Models\Services;
use App\Services\CashFlow\PoolService;
use App\Services\Dashboard\Support\AppointmentCountsQuery;
use App\Services\Dashboard\Support\RevenueInvoiceQuery;
use App\Services\Dashboard\Support\SalesLedgerQuery;
use App\Support\DashboardPeriod;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class OverviewDashboardService
{
    /**
     * @var string[]
     */
    const PALETTE = [
        '#696cff', '#71dd37', '#03c3ec', '#ffab00', '#ff3e1d',
        '#8592a3', '#233d4d', '#a8aaae', '#28c76f', '#00cfe8',
        '#ea5455', '#7367f0', '#ff9f43', '#00bad1',
    ];

    /** @var PoolService */
    private $poolService;

    public function __construct(PoolService $poolService)
    {
        $this->poolService = $poolService;
    }

    /**
     * @return array<string, mixed>
     */
    public function build(DashboardPeriod $period)
    {
        $accountId = (int) Auth::user()->account_id;
        $locationIds = array_map('intval', DashboardHelper::getUserCentres() ?: []);
        list($start, $end) = $period->dateRange();
        list($prevStart, $prevEnd) = $period->previousDateRange();

        $consultancyType = (int) Config::get('constants.appointment_type_consultancy', 1);
        $treatmentType = (int) Config::get('constants.appointment_type_service', 2);

        return [
            'period' => $period,
            'periods' => DashboardPeriod::options(),
            'range_label' => Carbon::parse($start)->format('d M Y').' – '.Carbon::parse($end)->format('d M Y'),
            'kpis' => $this->kpis($accountId, $locationIds, $start, $end, $prevStart, $prevEnd, $consultancyType, $treatmentType),
            'charts' => [
                'collection_revenue_trend' => $this->collectionRevenueTrend($accountId, $locationIds, $start, $end),
                'appointment_trend' => $this->appointmentTrend($accountId, $locationIds, $start, $end, $consultancyType, $treatmentType),
                'collection_by_centre' => Gate::allows('dashboard_collection_by_centre')
                    ? $this->collectionByCentre($accountId, $locationIds, $start, $end)
                    : $this->emptyNamedChart(),
                'revenue_by_centre' => Gate::allows('dashboard_revenue_by_centre')
                    ? $this->revenueByCentre($accountId, $locationIds, $start, $end)
                    : $this->emptyNamedChart(),
                'collection_by_mode' => Gate::allows('dashboard_collection_by_centre')
                    ? $this->collectionByMode($accountId, $locationIds, $start, $end)
                    : $this->emptyNamedChart(),
                'appointments_by_status' => Gate::allows('dashboard_states')
                    ? $this->appointmentsByStatus($accountId, $locationIds, $start, $end, $consultancyType)
                    : $this->emptyNamedChart(),
                'appointments_by_type' => Gate::allows('dashboard_states')
                    ? $this->appointmentsByType($accountId, $locationIds, $start, $end)
                    : $this->emptyNamedChart(),
                'appointments_by_centre' => Gate::allows('dashboard_states')
                    ? $this->appointmentsByCentre($accountId, $locationIds, $start, $end, $consultancyType, $treatmentType)
                    : $this->emptyStackedChart(),
                'revenue_by_category' => Gate::allows('dashboard_revenue_by_service')
                    ? $this->revenueByServiceCategory($accountId, $locationIds, $start, $end)
                    : $this->emptyNamedChart(),
                'revenue_by_service' => Gate::allows('dashboard_revenue_by_service')
                    ? $this->revenueByService($accountId, $locationIds, $start, $end)
                    : $this->emptyNamedChart(),
                'leads_by_source' => Gate::allows('dashboard_states')
                    ? $this->leadsBySource($accountId, $start, $end)
                    : $this->emptyNamedChart(),
                'leads_by_status' => Gate::allows('dashboard_states')
                    ? $this->leadsByStatus($accountId, $start, $end)
                    : $this->emptyNamedChart(),
                'patients_by_gender' => Gate::allows('dashboard_states')
                    ? $this->patientsByGender($accountId, $start, $end)
                    : $this->emptyNamedChart(),
                'patients_trend' => Gate::allows('dashboard_states')
                    ? $this->patientsTrend($accountId, $start, $end)
                    : $this->emptyLineChart(),
                'doctors_by_appointments' => Gate::allows('dashboard_states')
                    ? $this->doctorsByAppointments($accountId, $locationIds, $start, $end)
                    : $this->emptyNamedChart(),
                'expenses_by_category' => Gate::allows('cashflow_dashboard')
                    ? $this->expensesByCategory($accountId, $start, $end)
                    : $this->emptyNamedChart(),
                'pool_balances' => Gate::allows('cashflow_dashboard')
                    ? $this->poolBalances($accountId)
                    : $this->emptyNamedChart(),
            ],
        ];
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @param  string  $prevStart
     * @param  string  $prevEnd
     * @param  int  $consultancyType
     * @param  int  $treatmentType
     * @return array<string, array<string, mixed>>
     */
    private function kpis($accountId, array $locationIds, $start, $end, $prevStart, $prevEnd, $consultancyType, $treatmentType)
    {
        $showStats = Gate::allows('dashboard_states');
        $showCollection = Gate::allows('dashboard_collection_by_centre');
        $showRevenue = Gate::allows('dashboard_revenue_by_centre') || $showStats;

        $consultancy = $showStats
            ? AppointmentCountsQuery::forType($accountId, $locationIds, $consultancyType, DashboardHelper::getArrivedAndConvertedStatusIds(), $start, $end)
            : ['all_count' => 0, 'done_count' => 0];
        $consultancyPrev = $showStats
            ? AppointmentCountsQuery::forType($accountId, $locationIds, $consultancyType, DashboardHelper::getArrivedAndConvertedStatusIds(), $prevStart, $prevEnd)
            : ['all_count' => 0, 'done_count' => 0];

        $treatment = $showStats
            ? AppointmentCountsQuery::forType($accountId, $locationIds, $treatmentType, [DashboardHelper::getArrivedStatusId()], $start, $end)
            : ['all_count' => 0, 'done_count' => 0];
        $treatmentPrev = $showStats
            ? AppointmentCountsQuery::forType($accountId, $locationIds, $treatmentType, [DashboardHelper::getArrivedStatusId()], $prevStart, $prevEnd)
            : ['all_count' => 0, 'done_count' => 0];

        $collection = $showCollection ? SalesLedgerQuery::totalNet($accountId, $locationIds, $start, $end) : 0.0;
        $collectionPrev = $showCollection ? SalesLedgerQuery::totalNet($accountId, $locationIds, $prevStart, $prevEnd) : 0.0;

        $revenue = $showRevenue ? RevenueInvoiceQuery::paidTotal($accountId, $locationIds, $start, $end) : 0.0;
        $revenuePrev = $showRevenue ? RevenueInvoiceQuery::paidTotal($accountId, $locationIds, $prevStart, $prevEnd) : 0.0;

        $leads = $showStats ? $this->leadCount($accountId, $start, $end) : 0;
        $leadsPrev = $showStats ? $this->leadCount($accountId, $prevStart, $prevEnd) : 0;

        $patients = $showStats ? $this->patientCount($accountId, $start, $end) : 0;
        $patientsPrev = $showStats ? $this->patientCount($accountId, $prevStart, $prevEnd) : 0;

        $showExpenses = Gate::allows('cashflow_dashboard');
        $expenses = $showExpenses ? $this->expenseTotal($accountId, $start, $end) : 0.0;
        $expensesPrev = $showExpenses ? $this->expenseTotal($accountId, $prevStart, $prevEnd) : 0.0;

        return [
            'collection' => $this->kpi($collection, $collectionPrev, 'Collection', 'Cash / Card / Bank received, less refunds', 'money', 'success', $showCollection),
            'revenue' => $this->kpi($revenue, $revenuePrev, 'Revenue', 'Paid invoices (recognised)', 'money', 'primary', $showRevenue),
            'consultancies' => $this->kpi($consultancy['done_count'], $consultancyPrev['done_count'], 'Consultancies done', $consultancy['done_count'].' of '.$consultancy['all_count'].' scheduled', 'count', 'info', $showStats),
            'treatments' => $this->kpi($treatment['done_count'], $treatmentPrev['done_count'], 'Treatments done', $treatment['done_count'].' of '.$treatment['all_count'].' scheduled', 'count', 'warning', $showStats),
            'leads' => $this->kpi($leads, $leadsPrev, 'New leads', 'Created in period', 'count', 'secondary', $showStats),
            'patients' => $this->kpi($patients, $patientsPrev, 'New patients', 'Registered in period', 'count', 'dark', $showStats),
            'expenses' => $this->kpi($expenses, $expensesPrev, 'Expenses', 'Non-voided, non-rejected', 'money', 'danger', $showExpenses),
            'consultancies_all' => $this->kpi($consultancy['all_count'], $consultancyPrev['all_count'], 'Consultancies booked', 'All scheduled consultancies', 'count', 'info', $showStats),
        ];
    }

    /**
     * @param  float|int  $value
     * @param  float|int  $previous
     * @param  string  $label
     * @param  string  $hint
     * @param  string  $format
     * @param  string  $theme
     * @param  bool  $visible
     * @return array<string, mixed>
     */
    private function kpi($value, $previous, $label, $hint, $format, $theme, $visible)
    {
        $delta = 0.0;
        if ((float) $previous !== 0.0) {
            $delta = round((((float) $value - (float) $previous) / abs((float) $previous)) * 100, 1);
        } elseif ((float) $value !== 0.0) {
            $delta = 100.0;
        }

        return [
            'value' => $value,
            'previous' => $previous,
            'delta' => $delta,
            'label' => $label,
            'hint' => $hint,
            'format' => $format,
            'theme' => $theme,
            'visible' => $visible,
        ];
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function collectionRevenueTrend($accountId, array $locationIds, $start, $end)
    {
        $days = $this->eachDay($start, $end);

        $collectionRows = SalesLedgerQuery::forRange($accountId, $start, $end);
        if ($locationIds !== []) {
            $collectionRows->whereIn('pa.location_id', $locationIds);
        }
        $collectionByDay = $collectionRows
            ->selectRaw('DATE(pa.created_at) AS day, '.SalesLedgerQuery::netCollectionSelectRaw().' AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $paidStatusId = DashboardHelper::getPaidInvoiceStatusId();
        $revenueByDay = collect();
        if ($paidStatusId !== null) {
            $revenueQuery = DB::table('invoices')
                ->where('account_id', $accountId)
                ->where('invoice_status_id', $paidStatusId)
                ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);
            if ($locationIds !== []) {
                $revenueQuery->whereIn('location_id', $locationIds);
            }
            $revenueByDay = $revenueQuery
                ->selectRaw('DATE(created_at) AS day, SUM(total_price) AS total')
                ->groupBy('day')
                ->pluck('total', 'day');
        }

        $collection = [];
        $revenue = [];
        foreach ($days as $day) {
            $collection[] = round((float) ($collectionByDay[$day] ?? 0), 2);
            $revenue[] = round((float) ($revenueByDay[$day] ?? 0), 2);
        }

        return [
            'labels' => array_map(function ($day) {
                return Carbon::parse($day)->format('d M');
            }, $days),
            'datasets' => [
                ['label' => 'Collection', 'data' => $collection],
                ['label' => 'Revenue', 'data' => $revenue],
            ],
        ];
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @param  int  $consultancyType
     * @param  int  $treatmentType
     * @return array<string, mixed>
     */
    private function appointmentTrend($accountId, array $locationIds, $start, $end, $consultancyType, $treatmentType)
    {
        $days = $this->eachDay($start, $end);

        $query = DB::table('appointments')
            ->where('account_id', $accountId)
            ->whereBetween('scheduled_date', [$start, $end])
            ->whereNull('deleted_at');
        if ($locationIds !== []) {
            $query->whereIn('location_id', $locationIds);
        }

        $rows = $query
            ->selectRaw('scheduled_date AS day, appointment_type_id, COUNT(*) AS total')
            ->groupBy('scheduled_date', 'appointment_type_id')
            ->get();

        $consult = [];
        $treat = [];
        foreach ($days as $day) {
            $consult[$day] = 0;
            $treat[$day] = 0;
        }
        foreach ($rows as $row) {
            $day = Carbon::parse((string) $row->day)->format('Y-m-d');
            if (! isset($consult[$day])) {
                continue;
            }
            if ((int) $row->appointment_type_id === $consultancyType) {
                $consult[$day] = (int) $row->total;
            } elseif ((int) $row->appointment_type_id === $treatmentType) {
                $treat[$day] = (int) $row->total;
            }
        }

        return [
            'labels' => array_map(function ($day) {
                return Carbon::parse($day)->format('d M');
            }, $days),
            'datasets' => [
                ['label' => 'Consultancies', 'data' => array_values($consult)],
                ['label' => 'Treatments', 'data' => array_values($treat)],
            ],
        ];
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function collectionByCentre($accountId, array $locationIds, $start, $end)
    {
        $query = SalesLedgerQuery::forRange($accountId, $start, $end);
        if ($locationIds !== []) {
            $query->whereIn('pa.location_id', $locationIds);
        }

        $rows = $query
            ->selectRaw('pa.location_id, '.SalesLedgerQuery::netCollectionSelectRaw().' AS total')
            ->groupBy('pa.location_id')
            ->get()
            ->keyBy('location_id');

        return $this->namedFromLocations($locationIds, $rows, 'total');
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function revenueByCentre($accountId, array $locationIds, $start, $end)
    {
        $paidStatusId = DashboardHelper::getPaidInvoiceStatusId();
        if ($paidStatusId === null) {
            return $this->emptyNamedChart();
        }

        $query = DB::table('invoices')
            ->where('account_id', $accountId)
            ->where('invoice_status_id', $paidStatusId)
            ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59']);
        if ($locationIds !== []) {
            $query->whereIn('location_id', $locationIds);
        }

        $rows = $query
            ->selectRaw('location_id, SUM(total_price) AS total')
            ->groupBy('location_id')
            ->get()
            ->keyBy('location_id');

        return $this->namedFromLocations($locationIds, $rows, 'total');
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function collectionByMode($accountId, array $locationIds, $start, $end)
    {
        $query = SalesLedgerQuery::forRange($accountId, $start, $end)
            ->where('pa.cash_flow', 'in')
            ->whereIn('pm.name', SalesLedgerQuery::VALID_PAYMENT_MODES);
        if ($locationIds !== []) {
            $query->whereIn('pa.location_id', $locationIds);
        }

        $rows = $query
            ->selectRaw('pm.name AS label, SUM(pa.cash_amount) AS total')
            ->groupBy('pm.name')
            ->orderByDesc('total')
            ->get();

        return $this->namedFromRows($rows, 'label', 'total');
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @param  int  $appointmentTypeId
     * @return array<string, mixed>
     */
    private function appointmentsByStatus($accountId, array $locationIds, $start, $end, $appointmentTypeId)
    {
        $arrivedStatusId = DashboardHelper::getArrivedStatusId();
        $convertedStatusId = DashboardHelper::getConvertedStatusId();

        $statuses = AppointmentStatuses::query()
            ->where('account_id', $accountId)
            ->where('active', 1)
            ->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('parent_id', 0);
            })
            ->where('id', '!=', $convertedStatusId)
            ->orderBy('sort_no')
            ->get()
            ->keyBy('id');

        $query = DB::table('appointments')
            ->where('account_id', $accountId)
            ->where('scheduled_date', '>=', $start)
            ->where('scheduled_date', '<=', $end)
            ->where('appointment_type_id', $appointmentTypeId)
            ->whereNull('deleted_at');
        if ($locationIds !== []) {
            $query->whereIn('location_id', $locationIds);
        }

        $records = $query
            ->selectRaw('base_appointment_status_id AS appointment_status_id, COUNT(id) AS total')
            ->groupBy('base_appointment_status_id')
            ->get()
            ->keyBy('appointment_status_id');

        $convertedRow = $records->get($convertedStatusId);
        $convertedCount = $convertedRow ? (int) $convertedRow->total : 0;

        $labels = [];
        $values = [];
        foreach ($statuses as $statusId => $status) {
            $record = $records->get($statusId);
            if (! $record) {
                continue;
            }
            $total = (int) $record->total;
            if ((int) $statusId === $arrivedStatusId) {
                $total += $convertedCount;
            }
            $labels[] = (string) $status->name;
            $values[] = $total;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => $this->colors(count($labels)),
        ];
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function appointmentsByType($accountId, array $locationIds, $start, $end)
    {
        $typesQuery = DB::table('appointment_types')
            ->where('account_id', $accountId)
            ->where('active', 1)
            ->orderBy('id');
        if (Schema::hasColumn('appointment_types', 'deleted_at')) {
            $typesQuery->whereNull('deleted_at');
        }
        $types = $typesQuery->get(['id', 'name']);

        if ($types->isEmpty()) {
            return $this->emptyNamedChart();
        }

        $query = DB::table('appointments')
            ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->whereNull('deleted_at');
        if ($locationIds !== []) {
            $query->whereIn('location_id', $locationIds);
        }

        $records = $query
            ->selectRaw('appointment_type_id, COUNT(id) AS total')
            ->groupBy('appointment_type_id')
            ->get()
            ->keyBy('appointment_type_id');

        $labels = [];
        $values = [];
        $colors = [];
        foreach ($types as $type) {
            $record = $records->get($type->id);
            if (! $record) {
                continue;
            }
            $labels[] = (string) $type->name;
            $values[] = (int) $record->total;
            $colors[] = $this->color(count($colors));
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $colors];
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @param  int  $consultancyType
     * @param  int  $treatmentType
     * @return array<string, mixed>
     */
    private function appointmentsByCentre($accountId, array $locationIds, $start, $end, $consultancyType, $treatmentType)
    {
        $query = DB::table('appointments')
            ->where('account_id', $accountId)
            ->whereBetween('scheduled_date', [$start, $end])
            ->whereNull('deleted_at');
        if ($locationIds !== []) {
            $query->whereIn('location_id', $locationIds);
        }

        $rows = $query
            ->selectRaw('location_id, appointment_type_id, COUNT(*) AS total')
            ->groupBy('location_id', 'appointment_type_id')
            ->get();

        $names = $this->locationNames($locationIds);
        $consult = [];
        $treat = [];
        foreach ($names as $id => $name) {
            $consult[$id] = 0;
            $treat[$id] = 0;
        }
        foreach ($rows as $row) {
            $id = (int) $row->location_id;
            if (! isset($names[$id])) {
                continue;
            }
            if ((int) $row->appointment_type_id === $consultancyType) {
                $consult[$id] = (int) $row->total;
            } elseif ((int) $row->appointment_type_id === $treatmentType) {
                $treat[$id] = (int) $row->total;
            }
        }

        return [
            'labels' => array_values($names),
            'datasets' => [
                ['label' => 'Consultancies', 'data' => array_values($consult)],
                ['label' => 'Treatments', 'data' => array_values($treat)],
            ],
        ];
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function revenueByServiceCategory($accountId, array $locationIds, $start, $end)
    {
        $paidStatusId = DashboardHelper::getPaidInvoiceStatusId();
        if ($paidStatusId === null) {
            return $this->emptyNamedChart();
        }

        $query = DB::table('invoices')
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->whereBetween('invoices.created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->where('invoices.invoice_status_id', $paidStatusId)
            ->where('invoices.account_id', $accountId);
        if ($locationIds !== []) {
            $query->whereIn('invoices.location_id', $locationIds);
        }

        $records = $query
            ->selectRaw('invoice_details.service_id, SUM(invoices.total_price) AS total_price')
            ->groupBy('invoice_details.service_id')
            ->get();

        if ($records->isEmpty()) {
            return $this->emptyNamedChart();
        }

        $services = Services::query()
            ->with('parent')
            ->whereIn('id', $records->pluck('service_id')->filter())
            ->get()
            ->keyBy('id');

        $grouped = [];
        foreach ($records as $record) {
            $service = $services->get($record->service_id);
            if (! $service) {
                continue;
            }
            $parent = $service->parent ?: $service;
            if (! isset($grouped[$parent->id])) {
                $grouped[$parent->id] = ['name' => $parent->name, 'total' => 0.0, 'color' => $parent->color];
            }
            $grouped[$parent->id]['total'] += (float) $record->total_price;
        }

        uasort($grouped, function ($a, $b) {
            if ($a['total'] == $b['total']) {
                return 0;
            }

            return ($a['total'] < $b['total']) ? 1 : -1;
        });

        $labels = [];
        $values = [];
        $colors = [];
        foreach ($grouped as $item) {
            $labels[] = (string) $item['name'];
            $values[] = round((float) $item['total'], 2);
            $colors[] = $item['color'] ?: $this->color(count($colors));
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $colors];
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function revenueByService($accountId, array $locationIds, $start, $end)
    {
        $paidStatusId = DashboardHelper::getPaidInvoiceStatusId();
        if ($paidStatusId === null) {
            return $this->emptyNamedChart();
        }

        $query = DB::table('invoices')
            ->join('invoice_details', 'invoices.id', '=', 'invoice_details.invoice_id')
            ->whereBetween('invoices.created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->where('invoices.invoice_status_id', $paidStatusId)
            ->where('invoices.account_id', $accountId);
        if ($locationIds !== []) {
            $query->whereIn('invoices.location_id', $locationIds);
        }

        $records = $query
            ->selectRaw('invoice_details.service_id, SUM(invoices.total_price) AS total_price')
            ->groupBy('invoice_details.service_id')
            ->orderByDesc('total_price')
            ->limit(12)
            ->get();

        $services = Services::query()
            ->whereIn('id', $records->pluck('service_id')->filter())
            ->get()
            ->keyBy('id');

        $labels = [];
        $values = [];
        $colors = [];
        foreach ($records as $record) {
            $service = $services->get($record->service_id);
            if (! $service) {
                continue;
            }
            $labels[] = (string) $service->name;
            $values[] = round((float) $record->total_price, 2);
            $colors[] = $service->color ?: $this->color(count($colors));
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $colors];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsBySource($accountId, $start, $end)
    {
        $rows = $this->leadBase($accountId)
            ->whereBetween('leads.created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->selectRaw('leads.lead_source_id, COUNT(*) AS total')
            ->groupBy('leads.lead_source_id')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $names = LeadSources::query()
            ->whereIn('id', $rows->pluck('lead_source_id')->filter())
            ->pluck('name', 'id');

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) ($names[$row->lead_source_id] ?? 'Unknown');
            $values[] = (int) $row->total;
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsByStatus($accountId, $start, $end)
    {
        $rows = $this->leadBase($accountId)
            ->whereBetween('leads.created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->selectRaw('leads.lead_status_id, COUNT(*) AS total')
            ->groupBy('leads.lead_status_id')
            ->orderByDesc('total')
            ->get();

        $names = LeadStatuses::query()
            ->whereIn('id', $rows->pluck('lead_status_id')->filter())
            ->pluck('name', 'id');

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) ($names[$row->lead_status_id] ?? 'Unknown');
            $values[] = (int) $row->total;
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function patientsByGender($accountId, $start, $end)
    {
        $rows = DB::table('users')
            ->where('account_id', $accountId)
            ->where('user_type_id', (int) Config::get('constants.patient_id'))
            ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->whereNull('deleted_at')
            ->selectRaw('gender, COUNT(*) AS total')
            ->groupBy('gender')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $gender = (int) $row->gender;
            if ($gender === 1) {
                $label = 'Male';
            } elseif ($gender === 2) {
                $label = 'Female';
            } else {
                $label = 'Unspecified';
            }
            $labels[] = $label;
            $values[] = (int) $row->total;
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function patientsTrend($accountId, $start, $end)
    {
        $days = $this->eachDay($start, $end);
        $rows = DB::table('users')
            ->where('account_id', $accountId)
            ->where('user_type_id', (int) Config::get('constants.patient_id'))
            ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->whereNull('deleted_at')
            ->selectRaw('DATE(created_at) AS day, COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $values = [];
        foreach ($days as $day) {
            $values[] = (int) ($rows[$day] ?? 0);
        }

        return [
            'labels' => array_map(function ($day) {
                return Carbon::parse($day)->format('d M');
            }, $days),
            'datasets' => [
                ['label' => 'New patients', 'data' => $values],
            ],
        ];
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function doctorsByAppointments($accountId, array $locationIds, $start, $end)
    {
        $query = DB::table('appointments')
            ->where('account_id', $accountId)
            ->whereBetween('scheduled_date', [$start, $end])
            ->whereNotNull('doctor_id')
            ->whereNull('deleted_at');
        if ($locationIds !== []) {
            $query->whereIn('location_id', $locationIds);
        }

        $rows = $query
            ->selectRaw('doctor_id, COUNT(*) AS total')
            ->groupBy('doctor_id')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $names = DB::table('users')->whereIn('id', $rows->pluck('doctor_id'))->pluck('name', 'id');

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) ($names[$row->doctor_id] ?? 'Unknown');
            $values[] = (int) $row->total;
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function expensesByCategory($accountId, $start, $end)
    {
        if (! Schema::hasTable('expenses') || ! Schema::hasTable('expense_categories')) {
            return $this->emptyNamedChart();
        }
        $rows = Expense::forAccount($accountId)
            ->whereNull('voided_at')
            ->where('status', '!=', Expense::STATUS_REJECTED)
            ->whereBetween('expense_date', [$start, $end])
            ->selectRaw('category_id, SUM(amount) AS total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        $names = DB::table('expense_categories')
            ->whereIn('id', $rows->pluck('category_id')->filter())
            ->pluck('name', 'id');

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) ($names[$row->category_id] ?? 'Uncategorised');
            $values[] = round((float) $row->total, 2);
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  int  $accountId
     * @return array<string, mixed>
     */
    private function poolBalances($accountId)
    {
        if (! Schema::hasTable('cash_pools')) {
            return $this->emptyNamedChart();
        }
        $pools = $this->poolService->getAllPools($accountId)->where('is_active', true);

        $labels = [];
        $values = [];
        foreach ($pools as $pool) {
            $labels[] = (string) $pool->name;
            $values[] = round((float) $pool->cached_balance, 2);
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return int
     */
    private function leadCount($accountId, $start, $end)
    {
        return (int) $this->leadBase($accountId)
            ->whereBetween('leads.created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->count();
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return int
     */
    private function patientCount($accountId, $start, $end)
    {
        return (int) DB::table('users')
            ->where('account_id', $accountId)
            ->where('user_type_id', (int) Config::get('constants.patient_id'))
            ->whereBetween('created_at', [$start.' 00:00:00', $end.' 23:59:59'])
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return float
     */
    private function expenseTotal($accountId, $start, $end)
    {
        if (! Schema::hasTable('expenses')) {
            return 0.0;
        }
        return round((float) Expense::forAccount($accountId)
            ->whereNull('voided_at')
            ->where('status', '!=', Expense::STATUS_REJECTED)
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount'), 2);
    }

    /**
     * Same city-scope rule as DashboardStatsService::getLeads().
     *
     * @param  int  $accountId
     * @return Builder
     */
    private function leadBase($accountId)
    {
        $userCities = DashboardHelper::getUserCities();

        return DB::table('leads')
            ->join('users', 'users.id', '=', 'leads.patient_id')
            ->where('users.user_type_id', Config::get('constants.patient_id'))
            ->where('leads.account_id', $accountId)
            ->whereNull('leads.deleted_at')
            ->where(function ($query) use ($userCities) {
                $query->where('leads.active', 1);
                $query->whereIn('leads.city_id', $userCities ?: [0]);
                $query->orWhereNull('leads.city_id');
            });
    }

    /**
     * @param  int[]  $locationIds
     * @return array<int, string>
     */
    private function locationNames(array $locationIds)
    {
        $query = Locations::query()
            ->where('active', 1)
            ->whereNotIn('name', ['All South Region', 'All Central Region', 'All Centres']);
        if ($locationIds !== []) {
            $query->whereIn('id', $locationIds);
        }

        return $query->orderBy('name')->pluck('name', 'id')->map(function ($name) {
            return (string) $name;
        })->all();
    }

    /**
     * @param  int[]  $locationIds
     * @param  Collection  $rows
     * @param  string  $valueKey
     * @return array<string, mixed>
     */
    private function namedFromLocations(array $locationIds, Collection $rows, $valueKey)
    {
        $names = $this->locationNames($locationIds);
        $labels = [];
        $values = [];
        foreach ($names as $id => $name) {
            $row = $rows->get($id);
            $labels[] = $name;
            $values[] = round((float) ($row ? $row->{$valueKey} : 0), 2);
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  Collection  $rows
     * @param  string  $labelKey
     * @param  string  $valueKey
     * @return array<string, mixed>
     */
    private function namedFromRows(Collection $rows, $labelKey, $valueKey)
    {
        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) $row->{$labelKey};
            $values[] = round((float) $row->{$valueKey}, 2);
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  string  $start
     * @param  string  $end
     * @return string[]
     */
    private function eachDay($start, $end)
    {
        $days = [];
        $cursor = Carbon::parse($start)->startOfDay();
        $last = Carbon::parse($end)->startOfDay();
        while ($cursor->lte($last)) {
            $days[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        return $days;
    }

    /**
     * @param  int  $count
     * @return string[]
     */
    private function colors($count)
    {
        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $out[] = $this->color($i);
        }

        return $out;
    }

    /**
     * @param  int  $index
     * @return string
     */
    private function color($index)
    {
        return self::PALETTE[$index % count(self::PALETTE)];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyNamedChart()
    {
        return ['labels' => [], 'values' => [], 'colors' => []];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyLineChart()
    {
        return ['labels' => [], 'datasets' => []];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyStackedChart()
    {
        return ['labels' => [], 'datasets' => []];
    }
}
