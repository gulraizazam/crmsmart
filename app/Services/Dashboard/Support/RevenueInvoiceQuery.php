<?php

namespace App\Services\Dashboard\Support;

use App\Helpers\DashboardHelper;
use Illuminate\Support\Facades\DB;

class RevenueInvoiceQuery
{
    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $startDate
     * @param  string  $endDate
     * @return float
     */
    public static function paidTotal($accountId, array $locationIds, $startDate, $endDate)
    {
        $paidStatusId = DashboardHelper::getPaidInvoiceStatusId();
        if ($paidStatusId === null) {
            return 0.0;
        }

        $query = DB::table('invoices')
            ->where('account_id', $accountId)
            ->where('invoice_status_id', $paidStatusId)
            ->whereBetween('created_at', [
                $startDate.' 00:00:00',
                $endDate.' 23:59:59',
            ]);

        if ($locationIds !== []) {
            $query->whereIn('location_id', $locationIds);
        }

        return round((float) $query->sum('total_price'), 2);
    }
}
