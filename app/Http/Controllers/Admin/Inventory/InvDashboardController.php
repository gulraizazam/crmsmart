<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvPurchaseOrder;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStore;
use App\Models\Inventory\InvTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class InvDashboardController extends Controller
{
    public function index()
    {
        if (! Gate::allows('inv_erp_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;

        $stats = [
            'items' => InvItem::forAccount($accountId)->where('active', true)->count(),
            'stores' => InvStore::forAccount($accountId)->where('active', true)->count(),
            'on_hand_value' => (float) InvStockBalance::query()
                ->where('account_id', $accountId)
                ->selectRaw('COALESCE(SUM(quantity * avg_cost), 0) as total')
                ->value('total'),
            'in_transit' => InvTransfer::forAccount($accountId)
                ->where('status', InvTransfer::STATUS_IN_TRANSIT)
                ->count(),
            'open_pos' => 0,
            'low_stock' => 0,
            'docs_today' => InvStockDocument::query()
                ->where('account_id', $accountId)
                ->whereDate('document_date', now()->toDateString())
                ->where('status', InvStockDocument::STATUS_POSTED)
                ->count(),
        ];

        if (Schema::hasTable('inv_purchase_orders')) {
            $stats['open_pos'] = InvPurchaseOrder::forAccount($accountId)
                ->whereIn('status', [
                    InvPurchaseOrder::STATUS_DRAFT,
                    InvPurchaseOrder::STATUS_ORDERED,
                ])
                ->count();
        }

        if (Schema::hasTable('inv_item_store_controls')) {
            $stats['low_stock'] = (int) DB::table('inv_item_store_controls as c')
                ->join('inv_items as i', 'i.id', '=', 'c.item_id')
                ->leftJoin('inv_stock_balances as b', function ($join) {
                    $join->on('b.item_id', '=', 'c.item_id')->on('b.store_id', '=', 'c.store_id');
                })
                ->where('c.account_id', $accountId)
                ->whereNull('i.deleted_at')
                ->where('c.reorder_level', '>', 0)
                ->whereRaw('COALESCE(b.quantity, 0) <= c.reorder_level')
                ->count();
        }

        $quickLinks = collect([
            ['label' => 'Items', 'route' => 'admin.inventory-erp.items.index', 'icon' => 'la-cube', 'perm' => 'inv_item_manage'],
            ['label' => 'Stores', 'route' => 'admin.inventory-erp.stores.index', 'icon' => 'la-building', 'perm' => 'inv_store_manage'],
            ['label' => 'Purchase Orders', 'route' => 'admin.inventory-erp.purchase-orders.index', 'icon' => 'la-file-text', 'perm' => 'inv_purchase_manage'],
            ['label' => 'GRN', 'route' => 'admin.inventory-erp.grns.create', 'icon' => 'la-download', 'perm' => 'inv_purchase_manage'],
            ['label' => 'Transfers', 'route' => 'admin.inventory-erp.transfers.create', 'icon' => 'la-exchange', 'perm' => 'inv_transfer_manage'],
            ['label' => 'Issues', 'route' => 'admin.inventory-erp.issues.create', 'icon' => 'la-share', 'perm' => 'inv_move_manage'],
            ['label' => 'Sales', 'route' => 'admin.inventory-erp.sales.create', 'icon' => 'la-shopping-cart', 'perm' => 'inv_move_manage'],
            ['label' => 'Adjustments', 'route' => 'admin.inventory-erp.adjustments.create', 'icon' => 'la-sliders', 'perm' => 'inv_adjust_manage'],
            ['label' => 'Low Stock', 'route' => 'admin.inventory-erp.stock-controls.low-stock', 'icon' => 'la-warning', 'perm' => 'inv_adjust_manage'],
            ['label' => 'Balances', 'route' => 'admin.inventory-erp.stock.balances', 'icon' => 'la-database', 'perm' => 'inv_report_manage'],
            ['label' => 'Valuation', 'route' => 'admin.inventory-erp.finance.valuation', 'icon' => 'la-money', 'perm' => 'inv_report_manage'],
            ['label' => 'COGS', 'route' => 'admin.inventory-erp.finance.cogs', 'icon' => 'la-pie-chart', 'perm' => 'inv_report_manage'],
        ])->filter(function ($link) {
            return Gate::allows($link['perm']) || Gate::allows('inv_erp_manage');
        })->values();

        return view('admin.inventory-erp.dashboard', compact('stats', 'quickLinks'));
    }
}
