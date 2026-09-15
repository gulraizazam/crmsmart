<?php

namespace App\Services\Dashboard;

use App\Helpers\DashboardHelper;
use App\Models\LeadSources;
use App\Models\LeadStatuses;
use App\Services\Dashboard\Support\SalesLedgerQuery;
use App\Support\DashboardPeriod;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeadsDashboardService
{
    /**
     * @var string[]
     */
    const PALETTE = [
        '#696cff', '#71dd37', '#03c3ec', '#ffab00', '#ff3e1d',
        '#8592a3', '#233d4d', '#a8aaae', '#28c76f', '#00cfe8',
        '#ea5455', '#7367f0', '#ff9f43', '#00bad1',
    ];

    /** @var bool */
    private $hasAssignedTo = false;

    /** @var bool */
    private $hasDepartment = false;

    /** @var bool */
    private $hasLocation = false;

    /** @var bool */
    private $hasMetaLead = false;

    /**
     * @return array<string, mixed>
     */
    public function build(DashboardPeriod $period)
    {
        $accountId = (int) Auth::user()->account_id;
        list($start, $end) = $period->dateRange();
        list($prevStart, $prevEnd) = $period->previousDateRange();

        $this->hasAssignedTo = Schema::hasColumn('leads', 'assigned_to');
        $this->hasDepartment = Schema::hasColumn('leads', 'department_id');
        $this->hasLocation = Schema::hasColumn('leads', 'location_id');
        $this->hasMetaLead = Schema::hasColumn('leads', 'meta_lead_id');

        return [
            'period' => $period,
            'periods' => DashboardPeriod::options(),
            'range_label' => Carbon::parse($start)->format('d M Y').' – '.Carbon::parse($end)->format('d M Y'),
            'kpis' => $this->kpis($accountId, $start, $end, $prevStart, $prevEnd),
            'charts' => [
                'leads_trend' => $this->leadsTrend($accountId, $start, $end),
                'conversion_funnel' => $this->conversionFunnel($accountId, $start, $end),
                'leads_by_status' => $this->leadsByStatus($accountId, $start, $end),
                'leads_by_source' => $this->leadsBySource($accountId, $start, $end),
                'collection_by_source' => $this->collectionBySource($accountId, $start, $end),
                'conversion_rate_by_source' => $this->conversionRateBySource($accountId, $start, $end),
                'leads_by_agent' => $this->leadsByAgent($accountId, $start, $end),
                'conversion_rate_by_agent' => $this->conversionRateByAgent($accountId, $start, $end),
                'leads_by_creator' => $this->leadsByCreator($accountId, $start, $end),
                'leads_by_centre' => $this->leadsByCentre($accountId, $start, $end),
                'leads_by_department' => $this->leadsByDepartment($accountId, $start, $end),
                'leads_by_gender' => $this->leadsByGender($accountId, $start, $end),
                'assignment_mix' => $this->assignmentMix($accountId, $start, $end),
                'channel_mix' => $this->channelMix($accountId, $start, $end),
                'leads_by_city' => $this->leadsByCity($accountId, $start, $end),
                'leads_by_service' => $this->leadsByService($accountId, $start, $end),
                'leads_by_weekday' => $this->leadsByWeekday($accountId, $start, $end),
                'leads_by_hour' => $this->leadsByHour($accountId, $start, $end),
            ],
        ];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @param  string  $prevStart
     * @param  string  $prevEnd
     * @return array<string, array<string, mixed>>
     */
    private function kpis($accountId, $start, $end, $prevStart, $prevEnd)
    {
        $total = $this->countCreated($accountId, $start, $end);
        $totalPrev = $this->countCreated($accountId, $prevStart, $prevEnd);

        $booked = $this->countWithAppointment($accountId, $start, $end, null);
        $bookedPrev = $this->countWithAppointment($accountId, $prevStart, $prevEnd, null);

        $arrived = $this->countWithAppointment($accountId, $start, $end, 'arrived_at');
        $arrivedPrev = $this->countWithAppointment($accountId, $prevStart, $prevEnd, 'arrived_at');

        $converted = $this->countWithAppointment($accountId, $start, $end, 'converted_at');
        $convertedPrev = $this->countWithAppointment($accountId, $prevStart, $prevEnd, 'converted_at');

        $junk = $this->countByStatusFlag($accountId, $start, $end, 'is_junk');
        $junkPrev = $this->countByStatusFlag($accountId, $prevStart, $prevEnd, 'is_junk');

        $rate = $total > 0 ? round(($converted / $total) * 100, 1) : 0.0;
        $ratePrev = $totalPrev > 0 ? round(($convertedPrev / $totalPrev) * 100, 1) : 0.0;

        $collection = $this->collectionTotal($accountId, $start, $end);
        $collectionPrev = $this->collectionTotal($accountId, $prevStart, $prevEnd);

        $avgValue = $converted > 0 ? round($collection / $converted, 2) : 0.0;
        $avgValuePrev = $convertedPrev > 0 ? round($collectionPrev / $convertedPrev, 2) : 0.0;

        $bookedHint = $total > 0 ? round(($booked / $total) * 100, 1).'% of new leads booked' : 'Leads with an appointment';
        $arrivedHint = $booked > 0 ? round(($arrived / $booked) * 100, 1).'% of booked leads arrived' : 'Leads with an arrival';

        return [
            'leads' => $this->kpi($total, $totalPrev, 'New leads', 'Created in this period', 'count', 'primary'),
            'booked' => $this->kpi($booked, $bookedPrev, 'Booked', $bookedHint, 'count', 'info'),
            'arrived' => $this->kpi($arrived, $arrivedPrev, 'Arrived', $arrivedHint, 'count', 'warning'),
            'converted' => $this->kpi($converted, $convertedPrev, 'Converted', 'First qualifying payment on the appointment', 'count', 'success'),
            'conversion_rate' => $this->kpi($rate, $ratePrev, 'Conversion rate', 'Converted ÷ new leads', 'percent', 'success'),
            'collection' => $this->kpi($collection, $collectionPrev, 'Collection', 'Cash / Card / Bank from lead appointments, less refunds', 'money', 'secondary'),
            'avg_value' => $this->kpi($avgValue, $avgValuePrev, 'Avg. converted value', 'Collection ÷ converted leads', 'money', 'dark'),
            'junk' => $this->kpi($junk, $junkPrev, 'Junk', 'Current status flagged as junk', 'count', 'danger'),
        ];
    }

    /**
     * @param  float|int  $value
     * @param  float|int  $previous
     * @param  string  $label
     * @param  string  $hint
     * @param  string  $format
     * @param  string  $theme
     * @return array<string, mixed>
     */
    private function kpi($value, $previous, $label, $hint, $format, $theme)
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
            'visible' => true,
        ];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsTrend($accountId, $start, $end)
    {
        $days = $this->eachDay($start, $end);
        $created = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('DATE(leads.created_at) AS day, COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $converted = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->whereExists(function ($sub) {
                $this->appointmentExists($sub, 'converted_at');
            })
            ->selectRaw('DATE(leads.created_at) AS day, COUNT(*) AS total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $createdValues = [];
        $convertedValues = [];
        foreach ($days as $day) {
            $createdValues[] = (int) ($created[$day] ?? 0);
            $convertedValues[] = (int) ($converted[$day] ?? 0);
        }

        return [
            'labels' => array_map(function ($day) {
                return Carbon::parse($day)->format('d M');
            }, $days),
            'datasets' => [
                ['label' => 'New leads', 'data' => $createdValues],
                ['label' => 'Converted', 'data' => $convertedValues],
            ],
        ];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function conversionFunnel($accountId, $start, $end)
    {
        $labels = ['New leads', 'Booked', 'Arrived', 'Converted'];
        $values = [
            $this->countCreated($accountId, $start, $end),
            $this->countWithAppointment($accountId, $start, $end, null),
            $this->countWithAppointment($accountId, $start, $end, 'arrived_at'),
            $this->countWithAppointment($accountId, $start, $end, 'converted_at'),
        ];

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
        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('leads.lead_status_id AS group_id, COUNT(*) AS total')
            ->groupBy('leads.lead_status_id')
            ->orderByDesc('total')
            ->get();

        $names = LeadStatuses::query()
            ->whereIn('id', $rows->pluck('group_id')->filter())
            ->pluck('name', 'id');

        return $this->namedCountChart($rows, $names, 'Unknown');
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsBySource($accountId, $start, $end)
    {
        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('leads.lead_source_id AS group_id, COUNT(*) AS total')
            ->groupBy('leads.lead_source_id')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        return $this->namedCountChart($rows, $this->sourceNames($rows->pluck('group_id')), 'Unknown');
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function collectionBySource($accountId, $start, $end)
    {
        $query = $this->collectionAttributed($accountId, $start, $end);
        if ($query === null) {
            return $this->emptyNamedChart();
        }

        $rows = $query
            ->selectRaw('leads.lead_source_id AS group_id, '.SalesLedgerQuery::netCollectionSelectRaw().' AS total')
            ->groupBy('leads.lead_source_id')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        return $this->namedMoneyChart($rows, $this->sourceNames($rows->pluck('group_id')), 'Unknown');
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function conversionRateBySource($accountId, $start, $end)
    {
        return $this->conversionRateGrouped($accountId, $start, $end, 'leads.lead_source_id', function ($ids) {
            return $this->sourceNames($ids);
        }, 'Unknown');
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsByAgent($accountId, $start, $end)
    {
        if (! $this->hasAssignedTo) {
            return $this->emptyNamedChart();
        }

        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('leads.assigned_to AS group_id, COUNT(*) AS total')
            ->groupBy('leads.assigned_to')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        return $this->namedCountChart($rows, $this->userNames($rows->pluck('group_id')), 'Unassigned');
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function conversionRateByAgent($accountId, $start, $end)
    {
        if (! $this->hasAssignedTo) {
            return $this->emptyNamedChart();
        }

        return $this->conversionRateGrouped($accountId, $start, $end, 'leads.assigned_to', function ($ids) {
            return $this->userNames($ids);
        }, 'Unassigned');
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsByCreator($accountId, $start, $end)
    {
        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('leads.created_by AS group_id, COUNT(*) AS total')
            ->groupBy('leads.created_by')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        return $this->namedCountChart($rows, $this->userNames($rows->pluck('group_id')), 'Unknown');
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsByCentre($accountId, $start, $end)
    {
        if (! $this->hasLocation) {
            return $this->emptyNamedChart();
        }

        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('leads.location_id AS group_id, COUNT(*) AS total')
            ->groupBy('leads.location_id')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $names = DB::table('locations')
            ->whereIn('id', $rows->pluck('group_id')->filter())
            ->pluck('name', 'id');

        return $this->namedCountChart($rows, $names, 'Unassigned centre');
    }

    private function leadsByDepartment($accountId, $start, $end)
    {
        if (! $this->hasDepartment || ! Schema::hasTable('lead_departments')) {
            return $this->emptyNamedChart();
        }

        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('leads.department_id AS group_id, COUNT(*) AS total')
            ->groupBy('leads.department_id')
            ->orderByDesc('total')
            ->get();

        $names = DB::table('lead_departments')
            ->whereIn('id', $rows->pluck('group_id')->filter())
            ->pluck('name', 'id');

        return $this->namedCountChart($rows, $names, 'Unassigned');
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsByGender($accountId, $start, $end)
    {
        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('leads.gender AS group_id, COUNT(*) AS total')
            ->groupBy('leads.gender')
            ->orderByDesc('total')
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $gender = (int) $row->group_id;
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
    private function assignmentMix($accountId, $start, $end)
    {
        if (! $this->hasAssignedTo) {
            return $this->emptyNamedChart();
        }

        $assigned = (int) $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->whereNotNull('leads.assigned_to')
            ->count();
        $unassigned = (int) $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->whereNull('leads.assigned_to')
            ->count();

        if ($assigned === 0 && $unassigned === 0) {
            return $this->emptyNamedChart();
        }

        return [
            'labels' => ['Assigned', 'Unassigned'],
            'values' => [$assigned, $unassigned],
            'colors' => ['#696cff', '#8592a3'],
        ];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function channelMix($accountId, $start, $end)
    {
        if (! $this->hasMetaLead) {
            return $this->emptyNamedChart();
        }

        $meta = (int) $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->whereNotNull('leads.meta_lead_id')
            ->count();
        $other = (int) $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->whereNull('leads.meta_lead_id')
            ->count();

        if ($meta === 0 && $other === 0) {
            return $this->emptyNamedChart();
        }

        return [
            'labels' => ['Meta Ads', 'Other channels'],
            'values' => [$meta, $other],
            'colors' => ['#696cff', '#03c3ec'],
        ];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsByCity($accountId, $start, $end)
    {
        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('leads.city_id AS group_id, COUNT(*) AS total')
            ->groupBy('leads.city_id')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $names = DB::table('cities')
            ->whereIn('id', $rows->pluck('group_id')->filter())
            ->pluck('name', 'id');

        return $this->namedCountChart($rows, $names, 'Unknown city');
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsByService($accountId, $start, $end)
    {
        if (! Schema::hasTable('leads_services') || ! Schema::hasTable('services')) {
            return $this->emptyNamedChart();
        }

        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->join('leads_services', function ($join) {
                $join->on('leads_services.lead_id', '=', 'leads.id')
                    ->where('leads_services.status', '=', 1);
            })
            ->leftJoin('services', 'services.id', '=', 'leads_services.service_id')
            ->selectRaw("leads_services.service_id AS group_id, COALESCE(services.name, 'Unknown') AS label, COUNT(DISTINCT leads.id) AS total")
            ->groupBy('leads_services.service_id', 'services.name')
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) $row->label;
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
    private function leadsByWeekday($accountId, $start, $end)
    {
        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('DAYOFWEEK(leads.created_at) AS dow, COUNT(*) AS total')
            ->groupBy('dow')
            ->pluck('total', 'dow');

        $order = [2, 3, 4, 5, 6, 7, 1];
        $names = [
            1 => 'Sunday',
            2 => 'Monday',
            3 => 'Tuesday',
            4 => 'Wednesday',
            5 => 'Thursday',
            6 => 'Friday',
            7 => 'Saturday',
        ];

        $labels = [];
        $values = [];
        foreach ($order as $dow) {
            $labels[] = $names[$dow];
            $values[] = (int) ($rows[$dow] ?? 0);
        }

        if (array_sum($values) === 0) {
            return $this->emptyNamedChart();
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return array<string, mixed>
     */
    private function leadsByHour($accountId, $start, $end)
    {
        $rows = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw('HOUR(leads.created_at) AS hr, COUNT(*) AS total')
            ->groupBy('hr')
            ->pluck('total', 'hr');

        $labels = [];
        $values = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $suffix = $hour < 12 ? 'am' : 'pm';
            $display = $hour % 12;
            if ($display === 0) {
                $display = 12;
            }
            $labels[] = $display.$suffix;
            $values[] = (int) ($rows[$hour] ?? 0);
        }

        if (array_sum($values) === 0) {
            return $this->emptyLineChart();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                ['label' => 'New leads', 'data' => $values],
            ],
        ];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @param  string  $column
     * @param  callable  $namesResolver
     * @param  string  $unknown
     * @return array<string, mixed>
     */
    private function conversionRateGrouped($accountId, $start, $end, $column, $namesResolver, $unknown)
    {
        $created = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->selectRaw($column.' AS group_id, COUNT(*) AS total')
            ->groupBy(DB::raw($column))
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        if ($created->isEmpty()) {
            return $this->emptyNamedChart();
        }

        $ids = $created->pluck('group_id')->filter()->unique()->values();
        $convertedQuery = $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->whereExists(function ($sub) {
                $this->appointmentExists($sub, 'converted_at');
            })
            ->selectRaw($column.' AS group_id, COUNT(*) AS total')
            ->groupBy(DB::raw($column));

        if ($ids->isNotEmpty()) {
            $convertedQuery->where(function ($query) use ($column, $created, $ids) {
                $query->whereIn($column, $ids->all());
                if ($created->contains(function ($row) {
                    return $row->group_id === null;
                })) {
                    $query->orWhereNull($column);
                }
            });
        }

        $converted = $convertedQuery->pluck('total', 'group_id');
        $names = $namesResolver($ids);

        $labels = [];
        $values = [];
        foreach ($created as $row) {
            $total = (int) $row->total;
            $won = (int) ($converted[$row->group_id] ?? 0);
            $labels[] = $row->group_id ? (string) ($names[$row->group_id] ?? $unknown) : $unknown;
            $values[] = $total > 0 ? round(($won / $total) * 100, 1) : 0.0;
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return int
     */
    private function countCreated($accountId, $start, $end)
    {
        return (int) $this->inPeriod($this->leadBase($accountId), $start, $end)->count();
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @param  string|null  $timestampColumn
     * @return int
     */
    private function countWithAppointment($accountId, $start, $end, $timestampColumn)
    {
        return (int) $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->whereExists(function ($sub) use ($timestampColumn) {
                $this->appointmentExists($sub, $timestampColumn);
            })
            ->count();
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @param  string  $flag
     * @return int
     */
    private function countByStatusFlag($accountId, $start, $end, $flag)
    {
        $statusId = LeadStatuses::query()
            ->where('account_id', $accountId)
            ->where($flag, 1)
            ->value('id');

        if (! $statusId) {
            return 0;
        }

        return (int) $this->inPeriod($this->leadBase($accountId), $start, $end)
            ->where('leads.lead_status_id', $statusId)
            ->count();
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return float
     */
    private function collectionTotal($accountId, $start, $end)
    {
        $query = $this->collectionAttributed($accountId, $start, $end);
        if ($query === null) {
            return 0.0;
        }

        $value = $query->selectRaw(SalesLedgerQuery::netCollectionSelectRaw().' AS total')->value('total');

        return round((float) ($value ?? 0), 2);
    }

    /**
     * @param  Builder  $sub
     * @param  string|null  $timestampColumn
     * @return void
     */
    private function appointmentExists($sub, $timestampColumn)
    {
        $sub->select(DB::raw(1))
            ->from('appointments')
            ->whereColumn('appointments.lead_id', 'leads.id')
            ->whereNull('appointments.deleted_at');

        if ($timestampColumn) {
            $sub->whereNotNull('appointments.'.$timestampColumn);
        }
    }

    /**
     * @param  int  $accountId
     * @return Builder
     */
    private function leadBase($accountId)
    {
        $query = DB::table('leads')
            ->where('leads.account_id', $accountId)
            ->whereNull('leads.deleted_at');

        return $this->applyCityScope($query);
    }

    /**
     * @param  Builder  $query
     * @param  string  $start
     * @param  string  $end
     * @return Builder
     */
    private function inPeriod($query, $start, $end)
    {
        return $query->whereBetween('leads.created_at', [$start.' 00:00:00', $end.' 23:59:59']);
    }

    /**
     * @param  Builder  $query
     * @return Builder
     */
    private function applyCityScope($query)
    {
        $userCities = DashboardHelper::getUserCities();

        return $query->where(function ($inner) use ($userCities) {
            $inner->whereIn('leads.city_id', $userCities ?: [0])
                ->orWhereNull('leads.city_id');
        });
    }

    /**
     * Payments in the period attributed to a lead via appointment or package.
     *
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return Builder|null
     */
    private function collectionAttributed($accountId, $start, $end)
    {
        if (! Schema::hasTable('package_advances')) {
            return null;
        }

        $query = SalesLedgerQuery::forRange($accountId, $start, $end)
            ->leftJoin('appointments as pay_appt', function ($join) {
                $join->on('pay_appt.id', '=', 'pa.appointment_id')
                    ->whereNull('pay_appt.deleted_at');
            });

        if (Schema::hasTable('packages')) {
            $query->leftJoin('packages as pay_pkg', function ($join) {
                $join->on('pay_pkg.id', '=', 'pa.package_id')
                    ->whereNull('pay_pkg.deleted_at');
            })->leftJoin('appointments as pkg_appt', function ($join) {
                $join->on('pkg_appt.id', '=', 'pay_pkg.appointment_id')
                    ->whereNull('pkg_appt.deleted_at');
            })->join('leads', function ($join) use ($accountId) {
                $join->whereRaw('leads.id = COALESCE(pay_appt.lead_id, pkg_appt.lead_id)')
                    ->where('leads.account_id', '=', $accountId)
                    ->whereNull('leads.deleted_at');
            });
        } else {
            $query->join('leads', function ($join) use ($accountId) {
                $join->on('leads.id', '=', 'pay_appt.lead_id')
                    ->where('leads.account_id', '=', $accountId)
                    ->whereNull('leads.deleted_at');
            });
        }

        return $this->applyCityScope($query);
    }

    /**
     * @param  Collection  $ids
     * @return Collection
     */
    private function sourceNames($ids)
    {
        return LeadSources::query()
            ->whereIn('id', $ids->filter())
            ->pluck('name', 'id');
    }

    /**
     * @param  Collection  $ids
     * @return Collection
     */
    private function userNames($ids)
    {
        return DB::table('users')
            ->whereIn('id', $ids->filter())
            ->pluck('name', 'id');
    }

    /**
     * @param  Collection  $rows
     * @param  Collection  $names
     * @param  string  $unknown
     * @return array<string, mixed>
     */
    private function namedCountChart($rows, $names, $unknown)
    {
        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = $row->group_id ? (string) ($names[$row->group_id] ?? $unknown) : $unknown;
            $values[] = (int) $row->total;
        }

        return ['labels' => $labels, 'values' => $values, 'colors' => $this->colors(count($labels))];
    }

    /**
     * @param  Collection  $rows
     * @param  Collection  $names
     * @param  string  $unknown
     * @return array<string, mixed>
     */
    private function namedMoneyChart($rows, $names, $unknown)
    {
        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = $row->group_id ? (string) ($names[$row->group_id] ?? $unknown) : $unknown;
            $values[] = round((float) $row->total, 2);
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
            $out[] = self::PALETTE[$i % count(self::PALETTE)];
        }

        return $out;
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
}
