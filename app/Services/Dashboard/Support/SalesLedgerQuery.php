<?php

namespace App\Services\Dashboard\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SalesLedgerQuery
{
    /**
     * @var string[]
     */
    const VALID_PAYMENT_MODES = ['Cash', 'Card', 'Bank/Wire Transfer'];

    /**
     * @param  int  $accountId
     * @param  string  $startDate
     * @param  string  $endDate
     * @return Builder
     */
    public static function forRange($accountId, $startDate, $endDate)
    {
        return DB::table('package_advances as pa')
            ->join('payment_modes as pm', 'pa.payment_mode_id', '=', 'pm.id')
            ->where('pa.account_id', $accountId)
            ->where('pa.created_at', '>=', $startDate.' 00:00:00')
            ->where('pa.created_at', '<=', $endDate.' 23:59:59')
            ->where('pa.is_adjustment', 0)
            ->where('pa.is_tax', 0)
            ->where('pa.is_cancel', 0)
            ->where('pa.cash_amount', '!=', 0)
            ->whereNull('pa.deleted_at');
    }

    /**
     * @return string
     */
    public static function netCollectionSelectRaw()
    {
        $modes = "'".implode("','", self::VALID_PAYMENT_MODES)."'";

        return "COALESCE(SUM(CASE WHEN pa.cash_flow = 'in' AND pm.name IN ({$modes}) THEN pa.cash_amount ELSE 0 END), 0)"
            .' - '
            ."COALESCE(SUM(CASE WHEN pa.cash_flow = 'out' AND pa.is_refund = 1 THEN pa.cash_amount ELSE 0 END), 0)";
    }

    /**
     * @param  int  $accountId
     * @param  int[]  $locationIds
     * @param  string  $startDate
     * @param  string  $endDate
     * @return float
     */
    public static function totalNet($accountId, array $locationIds, $startDate, $endDate)
    {
        $query = self::forRange($accountId, $startDate, $endDate);

        if ($locationIds !== []) {
            $query->whereIn('pa.location_id', $locationIds);
        }

        $value = $query->selectRaw(self::netCollectionSelectRaw().' AS sales')->value('sales');

        return round((float) ($value ?? 0), 2);
    }
}
