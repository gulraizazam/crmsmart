<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStockDocumentLine;
use App\Models\Inventory\InvStore;
use App\Services\Inventory\StockEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvOpeningController extends Controller
{
    private StockEngine $stockEngine;

    public function __construct(StockEngine $stockEngine)
    {
        $this->stockEngine = $stockEngine;
    }

    public function index()
    {
        if (! Gate::allows('inv_adjust_manage') && ! Gate::allows('inv_move_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $documents = InvStockDocument::forAccount($accountId)
            ->where('document_type', InvStockDocument::TYPE_OPENING)
            ->withCount('lines')
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.inventory-erp.openings.index', compact('documents'));
    }

    public function create()
    {
        if (! Gate::allows('inv_adjust_manage') && ! Gate::allows('inv_move_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;

        return view('admin.inventory-erp.openings.create', [
            'items' => InvItem::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'sku', 'name', 'uom']),
            'stores' => InvStore::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        if (! Gate::allows('inv_adjust_manage') && ! Gate::allows('inv_move_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;

        $data = $request->validate([
            'document_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|integer',
            'lines.*.store_id' => 'required|integer',
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_cost' => 'required|numeric|gte:0',
        ]);

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
                'direction' => InvStockDocumentLine::DIRECTION_IN,
                'quantity' => $line['quantity'],
                'unit_cost' => $line['unit_cost'],
            ];
        }

        try {
            $document = $this->stockEngine->createDraft(
                $accountId,
                InvStockDocument::TYPE_OPENING,
                $data['document_date'],
                $lines,
                $data['notes'] ?? null,
                Auth::id()
            );
            $this->stockEngine->post($document, Auth::id());
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();

            return redirect()->back()->withInput();
        }

        flash('Opening stock posted: '.$document->document_no)->success()->important();

        return redirect()->route('admin.inventory-erp.openings.index');
    }

    public function show(int $id)
    {
        if (! Gate::allows('inv_adjust_manage') && ! Gate::allows('inv_move_manage') && ! Gate::allows('inv_report_manage')) {
            return abort(401);
        }

        $document = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_OPENING)
            ->with(['lines.item', 'lines.store'])
            ->findOrFail($id);

        return view('admin.inventory-erp.openings.show', compact('document'));
    }
}
