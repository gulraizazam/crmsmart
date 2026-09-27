<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvItemStoreControl;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InvStockControlController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeControls();

        $accountId = (int) Auth::user()->account_id;
        $controls = InvItemStoreControl::forAccount($accountId)
            ->with(['item:id,sku,name', 'store:id,name'])
            ->orderBy('id')
            ->paginate(50);

        return view('admin.inventory-erp.stock-controls.index', [
            'controls' => $controls,
            'items' => InvItem::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'sku', 'name']),
            'stores' => InvStore::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeControls();
        $accountId = (int) Auth::user()->account_id;

        $data = $request->validate([
            'item_id' => 'required|integer',
            'store_id' => 'required|integer',
            'reorder_level' => 'required|numeric|gte:0',
            'reorder_qty' => 'required|numeric|gte:0',
        ]);

        if (! InvItem::forAccount($accountId)->where('id', $data['item_id'])->exists()
            || ! InvStore::forAccount($accountId)->where('id', $data['store_id'])->exists()) {
            flash('Invalid item or store.')->error()->important();

            return redirect()->back()->withInput();
        }

        InvItemStoreControl::updateOrCreate(
            [
                'item_id' => (int) $data['item_id'],
                'store_id' => (int) $data['store_id'],
            ],
            [
                'account_id' => $accountId,
                'reorder_level' => $data['reorder_level'],
                'reorder_qty' => $data['reorder_qty'],
            ]
        );

        flash('Reorder level saved.')->success()->important();

        return redirect()->route('admin.inventory-erp.stock-controls.index');
    }

    public function destroy(int $id)
    {
        $this->authorizeControls();

        InvItemStoreControl::forAccount((int) Auth::user()->account_id)->where('id', $id)->delete();
        flash('Reorder level removed.')->success()->important();

        return redirect()->route('admin.inventory-erp.stock-controls.index');
    }

    public function lowStock()
    {
        if (! Gate::allows('inv_adjust_manage') && ! Gate::allows('inv_report_manage')) {
            abort(401);
        }

        $accountId = (int) Auth::user()->account_id;

        $rows = DB::table('inv_item_store_controls as c')
            ->join('inv_items as i', 'i.id', '=', 'c.item_id')
            ->join('inv_stores as s', 's.id', '=', 'c.store_id')
            ->leftJoin('inv_stock_balances as b', function ($join) {
                $join->on('b.item_id', '=', 'c.item_id')->on('b.store_id', '=', 'c.store_id');
            })
            ->where('c.account_id', $accountId)
            ->whereNull('i.deleted_at')
            ->whereNull('s.deleted_at')
            ->where('c.reorder_level', '>', 0)
            ->whereRaw('COALESCE(b.quantity, 0) <= c.reorder_level')
            ->orderBy('i.name')
            ->orderBy('s.name')
            ->select([
                'i.sku', 'i.name as item_name', 'i.uom',
                's.name as store_name',
                'c.reorder_level', 'c.reorder_qty',
                DB::raw('COALESCE(b.quantity, 0) as quantity'),
            ])
            ->get();

        return view('admin.inventory-erp.stock-controls.low-stock', compact('rows'));
    }

    private function authorizeControls(): void
    {
        if (! Gate::allows('inv_adjust_manage') && ! Gate::allows('inv_item_manage')) {
            abort(401);
        }
    }
}
