<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvPurchaseOrder;
use App\Models\Inventory\InvStore;
use App\Models\Inventory\InvSupplier;
use App\Services\Inventory\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvPurchaseOrderController extends Controller
{
    private PurchaseService $purchases;

    public function __construct(PurchaseService $purchases)
    {
        $this->purchases = $purchases;
    }

    public function index(Request $request)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $query = InvPurchaseOrder::forAccount($accountId)
            ->with(['supplier:id,name'])
            ->withCount('lines')
            ->orderByDesc('order_date')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', (int) $request->get('supplier_id'));
        }
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('po_number', 'like', "%{$search}%");
        }

        return view('admin.inventory-erp.purchase-orders.index', [
            'orders' => $query->paginate(25)->appends($request->query()),
            'suppliers' => InvSupplier::forAccount($accountId)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;

        return view('admin.inventory-erp.purchase-orders.create', [
            'suppliers' => InvSupplier::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']),
            'items' => InvItem::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'sku', 'name', 'uom']),
            'stores' => InvStore::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $data = $request->validate([
            'supplier_id' => 'required|integer',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|integer',
            'lines.*.store_id' => 'required|integer',
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_cost' => 'required|numeric|gte:0',
        ]);

        if (! InvSupplier::forAccount($accountId)->where('id', $data['supplier_id'])->where('active', true)->exists()) {
            flash('Invalid supplier.')->error()->important();

            return redirect()->back()->withInput();
        }

        $itemIds = InvItem::forAccount($accountId)->whereIn('id', collect($data['lines'])->pluck('item_id'))->pluck('id')->all();
        $storeIds = InvStore::forAccount($accountId)->whereIn('id', collect($data['lines'])->pluck('store_id'))->pluck('id')->all();

        $lines = [];
        foreach ($data['lines'] as $line) {
            if (! in_array((int) $line['item_id'], $itemIds, true) || ! in_array((int) $line['store_id'], $storeIds, true)) {
                flash('One or more lines reference an invalid item or store.')->error()->important();

                return redirect()->back()->withInput();
            }
            $lines[] = [
                'item_id' => (int) $line['item_id'],
                'store_id' => (int) $line['store_id'],
                'quantity' => $line['quantity'],
                'unit_cost' => $line['unit_cost'],
            ];
        }

        try {
            $po = $this->purchases->createDraftPo(
                $accountId,
                (int) $data['supplier_id'],
                $data['order_date'],
                $lines,
                $data['expected_date'] ?? null,
                $data['notes'] ?? null,
                Auth::id()
            );
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();

            return redirect()->back()->withInput();
        }

        flash('Purchase order created: '.$po->po_number)->success()->important();

        return redirect()->route('admin.inventory-erp.purchase-orders.show', $po->id);
    }

    public function show(int $id)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $po = InvPurchaseOrder::forAccount((int) Auth::user()->account_id)
            ->with(['supplier', 'lines.item', 'lines.store', 'receipts'])
            ->findOrFail($id);

        return view('admin.inventory-erp.purchase-orders.show', compact('po'));
    }

    public function approve(int $id)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $po = InvPurchaseOrder::forAccount((int) Auth::user()->account_id)->findOrFail($id);

        try {
            $this->purchases->approve($po, Auth::id());
            flash('Purchase order approved: '.$po->po_number)->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.purchase-orders.show', $id);
    }

    public function cancel(int $id)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $po = InvPurchaseOrder::forAccount((int) Auth::user()->account_id)->findOrFail($id);

        try {
            $this->purchases->cancel($po, Auth::id());
            flash('Purchase order cancelled.')->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.purchase-orders.show', $id);
    }
}
