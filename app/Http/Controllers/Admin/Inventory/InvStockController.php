<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockMovement;
use App\Models\Inventory\InvStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvStockController extends Controller
{
    public function balances(Request $request)
    {
        if (! Gate::allows('inv_report_manage') && ! Gate::allows('inv_erp_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $query = InvStockBalance::query()
            ->where('inv_stock_balances.account_id', $accountId)
            ->join('inv_items', 'inv_items.id', '=', 'inv_stock_balances.item_id')
            ->join('inv_stores', 'inv_stores.id', '=', 'inv_stock_balances.store_id')
            ->whereNull('inv_items.deleted_at')
            ->whereNull('inv_stores.deleted_at')
            ->select([
                'inv_stock_balances.*',
                'inv_items.sku as item_sku',
                'inv_items.name as item_name',
                'inv_items.uom as item_uom',
                'inv_stores.name as store_name',
            ]);

        if ($request->filled('item_id')) {
            $query->where('inv_stock_balances.item_id', (int) $request->get('item_id'));
        }
        if ($request->filled('store_id')) {
            $query->where('inv_stock_balances.store_id', (int) $request->get('store_id'));
        }
        if ($request->get('non_zero') === '1') {
            $query->where('inv_stock_balances.quantity', '!=', 0);
        }

        $balances = $query
            ->orderBy('inv_items.name')
            ->orderBy('inv_stores.name')
            ->paginate(50)
            ->appends($request->query());

        return view('admin.inventory-erp.stock.balances', [
            'balances' => $balances,
            'items' => InvItem::forAccount($accountId)->orderBy('name')->get(['id', 'sku', 'name']),
            'stores' => InvStore::forAccount($accountId)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function card(Request $request)
    {
        if (! Gate::allows('inv_report_manage') && ! Gate::allows('inv_erp_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $itemId = $request->filled('item_id') ? (int) $request->get('item_id') : null;
        $storeId = $request->filled('store_id') ? (int) $request->get('store_id') : null;

        $balance = null;
        $movements = collect();

        if ($itemId && $storeId) {
            $balance = InvStockBalance::where('account_id', $accountId)
                ->where('item_id', $itemId)
                ->where('store_id', $storeId)
                ->first();

            $movements = InvStockMovement::where('account_id', $accountId)
                ->where('item_id', $itemId)
                ->where('store_id', $storeId)
                ->with(['document:id,document_no,document_type,document_date'])
                ->orderBy('moved_at')
                ->orderBy('id')
                ->paginate(100)
                ->appends($request->query());
        }

        return view('admin.inventory-erp.stock.card', [
            'items' => InvItem::forAccount($accountId)->orderBy('name')->get(['id', 'sku', 'name']),
            'stores' => InvStore::forAccount($accountId)->orderBy('name')->get(['id', 'name']),
            'itemId' => $itemId,
            'storeId' => $storeId,
            'balance' => $balance,
            'movements' => $movements,
        ]);
    }
}
