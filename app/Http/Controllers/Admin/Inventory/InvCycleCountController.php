<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStore;
use App\Services\Inventory\AdjustmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvCycleCountController extends Controller
{
    private AdjustmentService $adjustments;

    public function __construct(AdjustmentService $adjustments)
    {
        $this->adjustments = $adjustments;
    }

    public function create(Request $request)
    {
        $this->authorizeAdjust();
        $accountId = (int) Auth::user()->account_id;
        $storeId = $request->filled('store_id') ? (int) $request->get('store_id') : null;

        $stores = InvStore::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']);
        $rows = collect();

        if ($storeId) {
            $balances = InvStockBalance::where('account_id', $accountId)
                ->where('store_id', $storeId)
                ->with('item:id,sku,name,uom')
                ->get()
                ->keyBy('item_id');

            $items = InvItem::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'sku', 'name', 'uom']);
            $rows = $items->map(function ($item) use ($balances) {
                $bal = $balances->get($item->id);

                return (object) [
                    'item' => $item,
                    'system_qty' => $bal ? (float) $bal->quantity : 0.0,
                    'avg_cost' => $bal ? (float) $bal->avg_cost : 0.0,
                ];
            });
        }

        return view('admin.inventory-erp.cycle-counts.create', compact('stores', 'storeId', 'rows'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdjust();
        $accountId = (int) Auth::user()->account_id;

        $data = $request->validate([
            'store_id' => 'required|integer',
            'document_date' => 'required|date',
            'counts' => 'required|array|min:1',
            'counts.*.item_id' => 'required|integer',
            'counts.*.counted_qty' => 'nullable|numeric|gte:0',
        ]);

        if (! InvStore::forAccount($accountId)->where('id', $data['store_id'])->exists()) {
            flash('Invalid store.')->error()->important();

            return redirect()->back()->withInput();
        }

        $counts = [];
        foreach ($data['counts'] as $row) {
            if (! array_key_exists('counted_qty', $row) || $row['counted_qty'] === null || $row['counted_qty'] === '') {
                continue;
            }
            $counts[] = [
                'item_id' => (int) $row['item_id'],
                'counted_qty' => $row['counted_qty'],
            ];
        }

        if ($counts === []) {
            flash('Enter at least one counted quantity.')->error()->important();

            return redirect()->back()->withInput();
        }

        try {
            $document = $this->adjustments->createFromCycleCount(
                $accountId,
                (int) $data['store_id'],
                $data['document_date'],
                $counts,
                Auth::id()
            );
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();

            return redirect()->back()->withInput();
        }

        flash('Cycle count draft created: '.$document->document_no)->success()->important();

        return redirect()->route('admin.inventory-erp.adjustments.show', $document->id);
    }

    private function authorizeAdjust(): void
    {
        if (! Gate::allows('inv_adjust_manage')) {
            abort(401);
        }
    }
}
