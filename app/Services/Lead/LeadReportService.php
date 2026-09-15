<?php

namespace App\Services\Lead;

use App\Helpers\DashboardHelper;
use App\Models\LeadSources;
use App\Services\Dashboard\Support\SalesLedgerQuery;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeadReportService
{
    const DETAIL_LIMIT = 3000;

    /**
     * @return array<string, string>
     */
    public static function reportTypes()
    {
        return [
            'source' => 'By lead source',
            'agent' => 'By agent',
            'status' => 'By status',
            'centre' => 'By centre',
            'creator' => 'By creator',
            'department' => 'By department',
            'city' => 'By city',
            'service' => 'By service',
            'channel' => 'By channel',
            'gender' => 'By gender',
            'detail' => 'Lead detail',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function build(Request $request)
    {
        $accountId = (int) Auth::user()->account_id;
        list($start, $end) = $this->dateRange($request);
        $filters = $this->filters($request);
        $type = $this->reportType($request->get('report_type'));

        $kpis = $this->kpis($accountId, $start, $end, $filters);

        if ($type === 'detail') {
            $rows = $this->detailRows($accountId, $start, $end, $filters);
            $truncated = $rows->count() >= self::DETAIL_LIMIT;
        } else {
            $rows = $this->groupedRows($accountId, $start, $end, $filters, $type);
            $truncated = false;
        }

        return [
            'type' => $type,
            'title' => self::reportTypes()[$type],
            'group_label' => $this->groupLabel($type),
            'start_date' => Carbon::parse($start)->format('d M Y'),
            'end_date' => Carbon::parse($end)->format('d M Y'),
            'kpis' => $kpis,
            'rows' => $rows,
            'truncated' => $truncated,
            'is_detail' => $type === 'detail',
        ];
    }

    /**
     * @param  Request  $request
     * @return array{0: string, 1: string}
     */
    public function dateRange(Request $request)
    {
        $raw = trim((string) $request->get('date_range', ''));
        if ($raw !== '') {
            $parts = explode(' - ', $raw);
            if (count($parts) === 2) {
                $start = date('Y-m-d', strtotime($parts[0]));
                $end = date('Y-m-d', strtotime($parts[1]));
                if ($start && $end) {
                    return [$start, $end];
                }
            }
        }

        return [
            Carbon::today()->subDays(29)->format('Y-m-d'),
            Carbon::today()->format('Y-m-d'),
        ];
    }

    /**
     * @param  string|null  $type
     * @return string
     */
    private function reportType($type)
    {
        $types = self::reportTypes();
        if ($type && isset($types[$type])) {
            return $type;
        }

        return 'source';
    }

    /**
     * @param  string  $type
     * @return string
     */
    private function groupLabel($type)
    {
        $labels = [
            'source' => 'Lead source',
            'agent' => 'Agent',
            'status' => 'Status',
            'centre' => 'Centre',
            'creator' => 'Created by',
            'department' => 'Department',
            'city' => 'City',
            'service' => 'Service',
            'channel' => 'Channel',
            'gender' => 'Gender',
        ];

        return isset($labels[$type]) ? $labels[$type] : 'Group';
    }

    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    private function filters(Request $request)
    {
        return [
            'location_id' => $this->idList($request->get('location_id')),
            'city_id' => $this->idList($request->get('city_id')),
            'lead_source_id' => $this->scalarId($request->get('lead_source_id')),
            'lead_status_id' => $this->scalarId($request->get('lead_status_id')),
            'assigned_to' => $this->scalarId($request->get('assigned_to')),
            'created_by' => $this->scalarId($request->get('created_by')),
            'department_id' => $this->scalarId($request->get('department_id')),
            'gender' => $this->scalarId($request->get('gender')),
        ];
    }

    /**
     * @param  mixed  $value
     * @return int[]
     */
    private function idList($value)
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }
        if (! is_array($value)) {
            $value = [$value];
        }

        $ids = [];
        foreach ($value as $item) {
            if ($item === '' || $item === null) {
                continue;
            }
            $ids[] = (int) $item;
        }

        return $ids;
    }

    /**
     * @param  mixed  $value
     * @return int|null
     */
    private function scalarId($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function kpis($accountId, $start, $end, array $filters)
    {
        $row = $this->filteredLeads($accountId, $start, $end, $filters)
            ->leftJoinSub($this->appointmentOutcomes($accountId), 'appt', function ($join) {
                $join->on('appt.lead_id', '=', 'leads.id');
            })
            ->leftJoinSub($this->collectionByLead($accountId, $start, $end), 'pay', function ($join) {
                $join->on('pay.lead_id', '=', 'leads.id');
            })
            ->leftJoin('lead_statuses', 'lead_statuses.id', '=', 'leads.lead_status_id')
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN appt.booked = 1 THEN 1 ELSE 0 END) AS booked')
            ->selectRaw('SUM(CASE WHEN appt.arrived = 1 THEN 1 ELSE 0 END) AS arrived')
            ->selectRaw('SUM(CASE WHEN appt.converted = 1 THEN 1 ELSE 0 END) AS converted')
            ->selectRaw('SUM(CASE WHEN lead_statuses.is_junk = 1 THEN 1 ELSE 0 END) AS junk')
            ->selectRaw('COALESCE(SUM(pay.collection), 0) AS collection')
            ->first();

        $total = (int) ($row->total ?? 0);
        $converted = (int) ($row->converted ?? 0);
        $collection = round((float) ($row->collection ?? 0), 2);

        return [
            'total' => $total,
            'booked' => (int) ($row->booked ?? 0),
            'arrived' => (int) ($row->arrived ?? 0),
            'converted' => $converted,
            'junk' => (int) ($row->junk ?? 0),
            'collection' => $collection,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0.0,
            'avg_value' => $converted > 0 ? round($collection / $converted, 2) : 0.0,
        ];
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @param  array<string, mixed>  $filters
     * @param  string  $type
     * @return Collection
     */
    private function groupedRows($accountId, $start, $end, array $filters, $type)
    {
        $spec = $this->groupSpec($type);
        $query = $this->filteredLeads($accountId, $start, $end, $filters);

        if (! empty($spec['join_services'])) {
            $query->join('leads_services', function ($join) {
                $join->on('leads_services.lead_id', '=', 'leads.id')
                    ->where('leads_services.status', '=', 1);
            })->leftJoin('services', 'services.id', '=', 'leads_services.service_id');
        }

        $query->leftJoinSub($this->appointmentOutcomes($accountId), 'appt', function ($join) {
            $join->on('appt.lead_id', '=', 'leads.id');
        })
            ->leftJoinSub($this->collectionByLead($accountId, $start, $end), 'pay', function ($join) {
                $join->on('pay.lead_id', '=', 'leads.id');
            })
            ->leftJoin('lead_statuses', 'lead_statuses.id', '=', 'leads.lead_status_id');

        $countExpr = ! empty($spec['join_services']) ? 'COUNT(DISTINCT leads.id)' : 'COUNT(*)';

        $rows = $query
            ->selectRaw($spec['select'].' AS group_id')
            ->selectRaw($countExpr.' AS total')
            ->selectRaw('COUNT(DISTINCT CASE WHEN appt.booked = 1 THEN leads.id END) AS booked')
            ->selectRaw('COUNT(DISTINCT CASE WHEN appt.arrived = 1 THEN leads.id END) AS arrived')
            ->selectRaw('COUNT(DISTINCT CASE WHEN appt.converted = 1 THEN leads.id END) AS converted')
            ->selectRaw('COUNT(DISTINCT CASE WHEN lead_statuses.is_junk = 1 THEN leads.id END) AS junk')
            ->selectRaw('COALESCE(SUM(pay.collection), 0) AS collection')
            ->groupBy(DB::raw($spec['group']))
            ->orderByRaw($countExpr.' DESC')
            ->get();

        $names = $this->groupNames($type, $rows->pluck('group_id'));
        $out = collect();
        foreach ($rows as $row) {
            $total = (int) $row->total;
            $converted = (int) $row->converted;
            $collection = round((float) $row->collection, 2);
            $label = $row->group_id === null || $row->group_id === ''
                ? $spec['unknown']
                : (string) ($names[$row->group_id] ?? $row->group_id);
            if (in_array($type, ['channel', 'gender'], true)) {
                $label = (string) ($row->group_id ?: $spec['unknown']);
            }
            $out->push([
                'label' => $label,
                'total' => $total,
                'booked' => (int) $row->booked,
                'arrived' => (int) $row->arrived,
                'converted' => $converted,
                'junk' => (int) $row->junk,
                'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0.0,
                'collection' => $collection,
                'avg_value' => $converted > 0 ? round($collection / $converted, 2) : 0.0,
            ]);
        }

        return $out;
    }

    /**
     * @param  string  $type
     * @return array<string, mixed>
     */
    private function groupSpec($type)
    {
        $map = [
            'source' => ['select' => 'leads.lead_source_id', 'group' => 'leads.lead_source_id', 'unknown' => 'Unknown'],
            'agent' => ['select' => 'leads.assigned_to', 'group' => 'leads.assigned_to', 'unknown' => 'Unassigned'],
            'status' => ['select' => 'leads.lead_status_id', 'group' => 'leads.lead_status_id', 'unknown' => 'Unknown'],
            'centre' => ['select' => 'leads.location_id', 'group' => 'leads.location_id', 'unknown' => 'Unassigned centre'],
            'creator' => ['select' => 'leads.created_by', 'group' => 'leads.created_by', 'unknown' => 'Unknown'],
            'department' => ['select' => 'leads.department_id', 'group' => 'leads.department_id', 'unknown' => 'Unassigned'],
            'city' => ['select' => 'leads.city_id', 'group' => 'leads.city_id', 'unknown' => 'Unknown city'],
            'service' => [
                'select' => 'leads_services.service_id',
                'group' => 'leads_services.service_id',
                'unknown' => 'Unknown',
                'join_services' => true,
            ],
            'channel' => [
                'select' => "CASE WHEN leads.meta_lead_id IS NULL OR leads.meta_lead_id = '' THEN 'Other channels' ELSE 'Meta Ads' END",
                'group' => "CASE WHEN leads.meta_lead_id IS NULL OR leads.meta_lead_id = '' THEN 'Other channels' ELSE 'Meta Ads' END",
                'unknown' => 'Other channels',
            ],
            'gender' => [
                'select' => "CASE WHEN leads.gender = 1 THEN 'Male' WHEN leads.gender = 2 THEN 'Female' ELSE 'Unspecified' END",
                'group' => "CASE WHEN leads.gender = 1 THEN 'Male' WHEN leads.gender = 2 THEN 'Female' ELSE 'Unspecified' END",
                'unknown' => 'Unspecified',
            ],
        ];

        return isset($map[$type]) ? $map[$type] : $map['source'];
    }

    /**
     * @param  string  $type
     * @param  Collection  $ids
     * @return Collection
     */
    private function groupNames($type, $ids)
    {
        $ids = $ids->filter(function ($id) {
            return $id !== null && $id !== '';
        });

        if (in_array($type, ['channel', 'gender'], true) || $ids->isEmpty()) {
            return collect();
        }

        if ($type === 'source') {
            return LeadSources::query()->whereIn('id', $ids)->pluck('name', 'id');
        }
        if ($type === 'status') {
            return DB::table('lead_statuses')->whereIn('id', $ids)->pluck('name', 'id');
        }
        if ($type === 'centre') {
            return DB::table('locations')->whereIn('id', $ids)->pluck('name', 'id');
        }
        if ($type === 'city') {
            return DB::table('cities')->whereIn('id', $ids)->pluck('name', 'id');
        }
        if ($type === 'department' && Schema::hasTable('lead_departments')) {
            return DB::table('lead_departments')->whereIn('id', $ids)->pluck('name', 'id');
        }
        if ($type === 'service') {
            return DB::table('services')->whereIn('id', $ids)->pluck('name', 'id');
        }
        if (in_array($type, ['agent', 'creator'], true)) {
            return DB::table('users')->whereIn('id', $ids)->pluck('name', 'id');
        }

        return collect();
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @param  array<string, mixed>  $filters
     * @return Collection
     */
    private function detailRows($accountId, $start, $end, array $filters)
    {
        $rows = $this->filteredLeads($accountId, $start, $end, $filters)
            ->leftJoin('lead_sources', 'lead_sources.id', '=', 'leads.lead_source_id')
            ->leftJoin('lead_statuses', 'lead_statuses.id', '=', 'leads.lead_status_id')
            ->leftJoin('locations', 'locations.id', '=', 'leads.location_id')
            ->leftJoin('cities', 'cities.id', '=', 'leads.city_id')
            ->leftJoin('users as assigned_user', 'assigned_user.id', '=', 'leads.assigned_to')
            ->leftJoin('users as creator_user', 'creator_user.id', '=', 'leads.created_by')
            ->leftJoin('lead_departments', 'lead_departments.id', '=', 'leads.department_id')
            ->leftJoinSub($this->appointmentOutcomes($accountId), 'appt', function ($join) {
                $join->on('appt.lead_id', '=', 'leads.id');
            })
            ->leftJoinSub($this->collectionByLead($accountId, $start, $end), 'pay', function ($join) {
                $join->on('pay.lead_id', '=', 'leads.id');
            })
            ->select([
                'leads.id',
                'leads.name',
                'leads.phone',
                'leads.gender',
                'leads.created_at',
                'lead_sources.name as source_name',
                'lead_statuses.name as status_name',
                'locations.name as centre_name',
                'cities.name as city_name',
                'assigned_user.name as agent_name',
                'creator_user.name as creator_name',
                'lead_departments.name as department_name',
            ])
            ->selectRaw('CASE WHEN appt.booked = 1 THEN 1 ELSE 0 END AS booked')
            ->selectRaw('CASE WHEN appt.arrived = 1 THEN 1 ELSE 0 END AS arrived')
            ->selectRaw('CASE WHEN appt.converted = 1 THEN 1 ELSE 0 END AS converted')
            ->selectRaw('COALESCE(pay.collection, 0) AS collection')
            ->selectRaw("CASE WHEN leads.meta_lead_id IS NULL OR leads.meta_lead_id = '' THEN 'Other' ELSE 'Meta Ads' END AS channel")
            ->orderByDesc('leads.created_at')
            ->limit(self::DETAIL_LIMIT)
            ->get();

        return $rows->map(function ($row) {
            $gender = (int) $row->gender;
            if ($gender === 1) {
                $genderLabel = 'Male';
            } elseif ($gender === 2) {
                $genderLabel = 'Female';
            } else {
                $genderLabel = '';
            }

            return [
                'id' => (int) $row->id,
                'name' => (string) ($row->name ?: ''),
                'phone' => (string) ($row->phone ?: ''),
                'gender' => $genderLabel,
                'source' => (string) ($row->source_name ?: 'Unknown'),
                'status' => (string) ($row->status_name ?: 'Unknown'),
                'centre' => (string) ($row->centre_name ?: ''),
                'city' => (string) ($row->city_name ?: ''),
                'agent' => (string) ($row->agent_name ?: 'Unassigned'),
                'creator' => (string) ($row->creator_name ?: ''),
                'department' => (string) ($row->department_name ?: ''),
                'channel' => (string) $row->channel,
                'created_at' => $row->created_at ? Carbon::parse($row->created_at)->format('d M Y H:i') : '',
                'booked' => (int) $row->booked ? 'Yes' : 'No',
                'arrived' => (int) $row->arrived ? 'Yes' : 'No',
                'converted' => (int) $row->converted ? 'Yes' : 'No',
                'collection' => round((float) $row->collection, 2),
            ];
        });
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @param  array<string, mixed>  $filters
     * @return Builder
     */
    private function filteredLeads($accountId, $start, $end, array $filters)
    {
        $query = DB::table('leads')
            ->where('leads.account_id', $accountId)
            ->whereNull('leads.deleted_at')
            ->whereBetween('leads.created_at', [$start.' 00:00:00', $end.' 23:59:59']);

        $this->applyCityScope($query);

        if ($filters['location_id'] !== []) {
            $query->whereIn('leads.location_id', $filters['location_id']);
        }
        if ($filters['city_id'] !== []) {
            $query->whereIn('leads.city_id', $filters['city_id']);
        }
        if ($filters['lead_source_id']) {
            $query->where('leads.lead_source_id', $filters['lead_source_id']);
        }
        if ($filters['lead_status_id']) {
            $query->where('leads.lead_status_id', $filters['lead_status_id']);
        }
        if ($filters['assigned_to']) {
            $query->where('leads.assigned_to', $filters['assigned_to']);
        }
        if ($filters['created_by']) {
            $query->where('leads.created_by', $filters['created_by']);
        }
        if ($filters['department_id']) {
            $query->where('leads.department_id', $filters['department_id']);
        }
        if ($filters['gender']) {
            $query->where('leads.gender', $filters['gender']);
        }

        return $query;
    }

    /**
     * @param  int  $accountId
     * @return Builder
     */
    private function appointmentOutcomes($accountId)
    {
        return DB::table('appointments')
            ->where('account_id', $accountId)
            ->whereNull('deleted_at')
            ->whereNotNull('lead_id')
            ->selectRaw('lead_id')
            ->selectRaw('1 AS booked')
            ->selectRaw('MAX(CASE WHEN arrived_at IS NOT NULL THEN 1 ELSE 0 END) AS arrived')
            ->selectRaw('MAX(CASE WHEN converted_at IS NOT NULL THEN 1 ELSE 0 END) AS converted')
            ->groupBy('lead_id');
    }

    /**
     * @param  int  $accountId
     * @param  string  $start
     * @param  string  $end
     * @return Builder
     */
    private function collectionByLead($accountId, $start, $end)
    {
        $empty = DB::table('leads')
            ->selectRaw('id AS lead_id, 0 AS collection')
            ->whereRaw('1 = 0');

        $query = $this->collectionAttributed($accountId, $start, $end);
        if ($query === null) {
            return $empty;
        }

        return $query
            ->selectRaw('leads.id AS lead_id, '.SalesLedgerQuery::netCollectionSelectRaw().' AS collection')
            ->groupBy('leads.id');
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
}
