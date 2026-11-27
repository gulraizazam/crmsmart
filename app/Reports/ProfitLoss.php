<?php

namespace App\Reports;

use App\Helpers\ACL;
use App\Models\InvoiceStatuses;
use App\Models\Locations;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class ProfitLoss
{
    public const MAX_MONTHS = 12;

    public static function monthCount(Carbon $start, Carbon $end): int
    {
        return $start->copy()->startOfMonth()->diffInMonths($end->copy()->startOfMonth()) + 1;
    }

    public static function build(Carbon $start, Carbon $end, array $selectedLocationIds, int $accountId): array
    {
        $userCentres = array_map('intval', ACL::getUserCentres());
        $selectedLocationIds = array_values(array_intersect(array_map('intval', $selectedLocationIds), $userCentres));
        $includeGeneral = empty($selectedLocationIds);
        $locationIds = $includeGeneral ? $userCentres : $selectedLocationIds;

        $months = self::months($start, $end);
        $from = $start->copy()->startOfDay()->toDateTimeString();
        $to = $end->copy()->endOfDay()->toDateTimeString();

        $paidStatusId = InvoiceStatuses::where('slug', 'paid')->value('id');

        // Settlement invoices hold unrefunded plan balances kept when a plan is settled.
        $invoiceRows = DB::table('invoices')
            ->leftJoin('appointments', 'appointments.id', '=', 'invoices.appointment_id')
            ->leftJoin('appointment_types', 'appointment_types.id', '=', 'appointments.appointment_type_id')
            ->where('invoices.account_id', $accountId)
            ->where('invoices.invoice_status_id', $paidStatusId)
            ->whereNull('invoices.deleted_at')
            ->whereIn('invoices.location_id', $locationIds)
            ->whereBetween('invoices.created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(invoices.created_at, '%Y-%m') as ym")
            ->selectRaw("CASE
                WHEN invoices.is_settlement = 1 THEN 'settlement'
                WHEN appointment_types.slug = 'treatment' OR EXISTS (
                    SELECT 1 FROM invoice_details
                    WHERE invoice_details.invoice_id = invoices.id
                      AND invoice_details.package_service_id IS NOT NULL
                      AND invoice_details.deleted_at IS NULL
                ) THEN 'treatment'
                ELSE 'consultation' END as kind")
            ->selectRaw('SUM(invoices.total_price) as total')
            ->groupBy('ym', 'kind')
            ->get();

        // Refunds reduce the original sale order's total_price, so sale totals are already net.
        $productRows = DB::table('orders')
            ->where('account_id', $accountId)
            ->where('order_type', 'sale')
            ->where('status', 1)
            ->whereIn('location_id', $locationIds)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, SUM(total_price) as total")
            ->groupBy('ym')
            ->get();

        $revenue = [
            self::row('Treatment Sales (Consumed)', $months, $invoiceRows->where('kind', 'treatment')),
            self::row('Consultation Sales', $months, $invoiceRows->where('kind', 'consultation')),
            self::row('Plan Settlements', $months, $invoiceRows->where('kind', 'settlement')),
            self::row('Product Sales', $months, $productRows),
        ];

        // General (company-wide) expenses can't be attributed to a centre, so they only count for "All".
        $expenseRows = DB::table('expenses')
            ->where('account_id', $accountId)
            ->whereNull('deleted_at')
            ->whereNull('voided_at')
            ->where('status', '!=', 'rejected')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->where(function ($q) use ($locationIds, $includeGeneral) {
                $q->whereIn('for_branch_id', $locationIds);
                if ($includeGeneral) {
                    $q->orWhereNull('for_branch_id');
                }
            })
            ->selectRaw("category_id, DATE_FORMAT(expense_date, '%Y-%m') as ym, SUM(amount) as total")
            ->groupBy('category_id', 'ym')
            ->get();

        $usedCategoryIds = $expenseRows->pluck('category_id')->unique()->values()->all();
        $categories = DB::table('expense_categories')
            ->where('account_id', $accountId)
            ->where(function ($q) use ($usedCategoryIds) {
                $q->where(function ($q) {
                    $q->where('is_active', 1)->whereNull('deleted_at');
                })->orWhereIn('id', $usedCategoryIds);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        $expenses = $categories->map(function ($category) use ($months, $expenseRows) {
            return self::row($category->name, $months, $expenseRows->where('category_id', $category->id));
        })->all();

        $totalRevenue = self::sumRows($revenue, $months);
        $totalExpenses = self::sumRows($expenses, $months);
        $net = [];
        foreach (array_keys($totalRevenue) as $key) {
            $net[$key] = $totalRevenue[$key] - $totalExpenses[$key];
        }

        return [
            'months' => $months,
            'show_total' => count($months) > 1,
            'revenue' => $revenue,
            'total_revenue' => $totalRevenue,
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'net' => $net,
            'centres' => $includeGeneral
                ? 'All Centres'
                : Locations::whereIn('id', $selectedLocationIds)->orderBy('name')->pluck('name')->implode(', '),
            'include_general' => $includeGeneral,
        ];
    }

    public static function money($value): string
    {
        $value = round((float) $value);
        if ($value == 0) {
            return '-';
        }

        return $value < 0 ? '(' . number_format(abs($value)) . ')' : number_format($value);
    }

    private static function months(Carbon $start, Carbon $end): array
    {
        $months = [];
        foreach (CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth()) as $month) {
            $periodStart = $month->copy()->max($start->copy()->startOfDay());
            $periodEnd = $month->copy()->endOfMonth()->min($end->copy()->endOfDay());
            $isPartial = !$periodStart->isSameDay($month) || !$periodEnd->isSameDay($month->copy()->endOfMonth());

            $months[] = [
                'key' => $month->format('Y-m'),
                'label' => $month->format('F Y'),
                'range' => $isPartial ? $periodStart->format('d M') . ' - ' . $periodEnd->format('d M') : null,
            ];
        }

        return $months;
    }

    private static function row(string $label, array $months, $rows): array
    {
        $totals = $rows->groupBy('ym')->map(fn ($group) => (float) $group->sum('total'));

        $values = [];
        foreach ($months as $month) {
            $values[$month['key']] = $totals[$month['key']] ?? 0.0;
        }
        $values['total'] = array_sum($values);

        return ['label' => $label, 'values' => $values];
    }

    private static function sumRows(array $rows, array $months): array
    {
        $keys = array_merge(array_column($months, 'key'), ['total']);
        $sums = array_fill_keys($keys, 0.0);
        foreach ($rows as $row) {
            foreach ($keys as $key) {
                $sums[$key] += $row['values'][$key];
            }
        }

        return $sums;
    }
}
