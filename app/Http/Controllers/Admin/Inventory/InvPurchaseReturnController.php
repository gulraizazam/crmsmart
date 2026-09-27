<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStockDocumentLine;
use App\Models\Inventory\InvStore;
use App\Models\Inventory\InvSupplier;
use App\Services\Inventory\StockEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvPurchaseReturnController extends Controller
{
    private StockEngine $stockEngine;

    public function __construct(StockEngine $stockEngine)
    {
        $this->stockEngine = $stockEngine;
    }

    public function index()
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $documents = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_PURCHASE_RETURN)
            ->withCount('lines')
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.inventory-erp.purchase-returns.index', compact('documents'));
    }

    public function create()
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;

        return view('admin.inventory-erp.purchase-returns.create', [
            'items' => InvItem::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'sku', 'name', 'uom']),
            'stores' => InvStore::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']),
            'suppliers' => InvSupplier::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $data = $request->validate([
            'document_date' => 'required|date',
            'supplier_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|integer',
            'lines.*.store_id' => 'required|integer',
            'lines.*.quantity' => 'required|numeric|gt:0',
        ]);

        if (! empty($data['supplier_id'])) {
            if (! InvSupplier::forAccount($accountId)->where('id', $data['supplier_id'])->exists()) {
                flash('Invalid supplier.')->error()->important();

                return redirect()->back()->withInput();
            }
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
                'direction' => InvStockDocumentLine::DIRECTION_OUT,
                'quantity' => $line['quantity'],
            ];
        }

        $notes = $data['notes'] ?? null;
        if (! empty($data['supplier_id'])) {
            $supplierName = optional(InvSupplier::find($data['supplier_id']))->name;
            $notes = trim(($notes ? $notes.' | ' : '').'Supplier: '.$supplierName);
        }

        try {
            $document = $this->stockEngine->createDraft(
                $accountId,
                InvStockDocument::TYPE_PURCHASE_RETURN,
                $data['document_date'],
                $lines,
                $notes,
                Auth::id(),
                [
                    'reference_type' => ! empty($data['supplier_id']) ? 'supplier' : null,
                    'reference_id' => ! empty($data['supplier_id']) ? (int) $data['supplier_id'] : null,
                ]
            );
            $document = $this->stockEngine->post($document, Auth::id());
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();

            return redirect()->back()->withInput();
        }

        flash('Purchase return posted: '.$document->document_no)->success()->important();

        return redirect()->route('admin.inventory-erp.purchase-returns.show', $document->id);
    }

    public function show(int $id)
    {
        if (! Gate::allows('inv_purchase_manage') && ! Gate::allows('inv_report_manage')) {
            return abort(401);
        }

        $document = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_PURCHASE_RETURN)
            ->with(['lines.item', 'lines.store'])
            ->findOrFail($id);

        return view('admin.inventory-erp.purchase-returns.show', compact('document'));
    }
}
