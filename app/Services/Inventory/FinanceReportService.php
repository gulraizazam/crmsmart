<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinanceReportService
{
    /**
     * Current inventory valuation from balances.
     *
     * @return Collection<int, object>
     */
    public function valuation(int $accountId, ?int $storeId = null, ?int $locationId = null): Collection
    {
        $query = InvStockBalance::query()
            ->where('inv_stock_balances.account_id', $accountId)
            ->join('inv_items', 'inv_items.id', '=', 'inv_stock_balances.item_id')
            ->join('inv_stores', 'inv_stores.id', '=', 'inv_stock_balances.store_id')
            ->leftJoin('locations', 'locations.id', '=', 'inv_stores.location_id')
            ->whereNull('inv_items.deleted_at')
            ->whereNull('inv_stores.deleted_at')
            ->where('inv_stock_balances.quantity', '!=', 0)
            ->select([
                'inv_items.sku',
                'inv_items.name as item_name',
                'inv_items.uom',
                'inv_stores.name as store_name',
                'inv_stores.location_id',
                'locations.name as centre_name',
                'inv_stock_balances.quantity',
                'inv_stock_balances.avg_cost',
                DB::raw('(inv_stock_balances.quantity * inv_stock_balances.avg_cost) as value'),
            ])
            ->orderBy('inv_items.name')
            ->orderBy('inv_stores.name');

        if ($storeId) {
            $query->where('inv_stock_balances.store_id', $storeId);
        }
        if ($locationId) {
            $query->where('inv_stores.location_id', $locationId);
        }

        return $query->get();
    }

    /**
     * COGS from posted sale/issue OUT movements in date range.
     *
     * @return Collection<int, object>
     */
    public function cogs(int $accountId, string $from, string $to, ?int $locationId = null, ?int $storeId = null): Collection
    {
        $query = InvStockMovement::query()
            ->join('inv_stock_documents', 'inv_stock_documents.id', '=', 'inv_stock_movements.document_id')
            ->join('inv_items', 'inv_items.id', '=', 'inv_stock_movements.item_id')
            ->join('inv_stores', 'inv_stores.id', '=', 'inv_stock_movements.store_id')
            ->leftJoin('locations', 'locations.id', '=', 'inv_stores.location_id')
            ->where('inv_stock_movements.account_id', $accountId)
            ->where('inv_stock_movements.direction', 'out')
            ->whereIn('inv_stock_documents.document_type', [
                InvStockDocument::TYPE_SALE,
                InvStockDocument::TYPE_ISSUE,
            ])
            ->where('inv_stock_documents.status', InvStockDocument::STATUS_POSTED)
            ->whereDate('inv_stock_movements.moved_at', '>=', $from)
            ->whereDate('inv_stock_movements.moved_at', '<=', $to)
            ->whereNull('inv_items.deleted_at')
            ->whereNull('inv_stores.deleted_at')
            ->select([
                'inv_stock_documents.document_no',
                'inv_stock_documents.document_type',
                'inv_stock_movements.moved_at',
                'inv_items.sku',
                'inv_items.name as item_name',
                'inv_stores.name as store_name',
                'locations.name as centre_name',
                'inv_stock_movements.quantity',
                'inv_stock_movements.unit_cost',
                DB::raw('(inv_stock_movements.quantity * inv_stock_movements.unit_cost) as cogs'),
            ])
            ->orderBy('inv_stock_movements.moved_at')
            ->orderBy('inv_stock_documents.document_no');

        if ($storeId) {
            $query->where('inv_stock_movements.store_id', $storeId);
        }
        if ($locationId) {
            $query->where('inv_stores.location_id', $locationId);
        }

        return $query->get();
    }

    /**
     * COGS totals grouped by centre (location).
     *
     * @return Collection<int, object>
     */
    public function cogsByCentre(int $accountId, string $from, string $to): Collection
    {
        return InvStockMovement::query()
            ->join('inv_stock_documents', 'inv_stock_documents.id', '=', 'inv_stock_movements.document_id')
            ->join('inv_stores', 'inv_stores.id', '=', 'inv_stock_movements.store_id')
            ->leftJoin('locations', 'locations.id', '=', 'inv_stores.location_id')
            ->where('inv_stock_movements.account_id', $accountId)
            ->where('inv_stock_movements.direction', 'out')
            ->whereIn('inv_stock_documents.document_type', [
                InvStockDocument::TYPE_SALE,
                InvStockDocument::TYPE_ISSUE,
            ])
            ->where('inv_stock_documents.status', InvStockDocument::STATUS_POSTED)
            ->whereDate('inv_stock_movements.moved_at', '>=', $from)
            ->whereDate('inv_stock_movements.moved_at', '<=', $to)
            ->groupBy('inv_stores.location_id', 'locations.name')
            ->orderBy('locations.name')
            ->select([
                'inv_stores.location_id',
                DB::raw("COALESCE(locations.name, 'Unassigned') as centre_name"),
                DB::raw('SUM(inv_stock_movements.quantity * inv_stock_movements.unit_cost) as cogs'),
                DB::raw('SUM(inv_stock_movements.quantity) as quantity'),
            ])
            ->get();
    }
}
