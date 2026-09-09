<?php

namespace App\Services\Dashboard\Support;

use Illuminate\Support\Facades\DB;

class AppointmentCountsQuery
{
    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  int  $appointmentTypeId
     * @param  int[]  $qualifyingStatusIds
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  int|null  $doctorId
     * @return array{all_count: int, done_count: int}
     */
    public static function forType(
        $accountId,
        array $locationIds,
        $appointmentTypeId,
        array $qualifyingStatusIds,
        $startDate,
        $endDate,
        $doctorId = null
    ) {
        $query = DB::table('appointments')
            ->where('account_id', $accountId)
            ->where('appointment_type_id', $appointmentTypeId)
            ->whereBetween('scheduled_date', [$startDate, $endDate])
            ->whereNull('deleted_at');

        if ($locationIds !== []) {
            $query->whereIn('location_id', $locationIds);
        }
        if ($doctorId !== null) {
            $query->where('doctor_id', $doctorId);
        }

        if ($qualifyingStatusIds === []) {
            $row = $query->selectRaw('COUNT(*) AS all_count, 0 AS done_count')->first();
        } else {
            $placeholders = implode(',', array_fill(0, count($qualifyingStatusIds), '?'));
            $row = $query->selectRaw(
                "COUNT(*) AS all_count, SUM(CASE WHEN appointment_status_id IN ({$placeholders}) THEN 1 ELSE 0 END) AS done_count",
                $qualifyingStatusIds
            )->first();
        }

        return [
            'all_count' => (int) ($row->all_count ?? 0),
            'done_count' => (int) ($row->done_count ?? 0),
        ];
    }
}
