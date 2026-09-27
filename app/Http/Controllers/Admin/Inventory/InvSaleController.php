<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStore;
use App\Services\Inventory\OutboundService;
use App\Services\Inventory\StockEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvSaleController extends Controller
{
    private OutboundService $outbound;
    private StockEngine $stockEngine;

    public function __construct(OutboundService $outbound, StockEngine $stockEngine)
    {
        $this->outbound = $outbound;
        $this->stockEngine = $stockEngine;
    }

    public function index()
    {
        $this->authorizeMove();

        $documents = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_SALE)
            ->withCount('lines')
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.inventory-erp.sales.index', compact('documents'));
    }

    public function create()
    {
        $this->authorizeMove();
        $accountId = (int) Auth::user()->account_id;

        return view('admin.inventory-erp.sales.create', [
            'items' => InvItem::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'sku', 'name', 'uom']),
            'stores' => InvStore::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']),
            'balances' => $this->balanceMap($accountId),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeMove();
        $accountId = (int) Auth::user()->account_id;

        $data = $request->validate([
            'document_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'customer_ref' => 'nullable|string|max:120',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|integer',
            'lines.*.store_id' => 'required|integer',
            'lines.*.quantity' => 'required|numeric|gt:0',
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
                'quantity' => $line['quantity'],
            ];
        }

        $notes = $data['notes'] ?? null;
        if (! empty($data['customer_ref'])) {
            $notes = trim(($notes ? $notes.' | ' : '').'Customer: '.$data['customer_ref']);
        }

        try {
            $document = $this->outbound->post(
                $accountId,
                InvStockDocument::TYPE_SALE,
                $data['document_date'],
                $lines,
                $notes,
                Auth::id()
            );
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();

            return redirect()->back()->withInput();
        }

        flash('Sale posted: '.$document->document_no)->success()->important();

        return redirect()->route('admin.inventory-erp.sales.show', $document->id);
    }

    public function show(int $id)
    {
        if (! Gate::allows('inv_move_manage') && ! Gate::allows('inv_report_manage')) {
            abort(401);
        }

        $document = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_SALE)
            ->with(['lines.item', 'lines.store'])
            ->findOrFail($id);

        $cogs = $document->lines->sum(function ($line) {
            return (float) $line->quantity * (float) $line->unit_cost;
        });

        return view('admin.inventory-erp.sales.show', compact('document', 'cogs'));
    }

    public function reverse(int $id)
    {
        $this->authorizeMove();

        $document = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_SALE)
            ->findOrFail($id);

        try {
            $rev = $this->stockEngine->reverse($document, 'Reverse sale '.$document->document_no, Auth::id());
            flash('Sale reversed via '.$rev->document_no)->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.sales.show', $id);
    }

    private function authorizeMove(): void
    {
        if (! Gate::allows('inv_move_manage')) {
            abort(401);
        }
    }

    private function balanceMap(int $accountId): array
    {
        $map = [];
        foreach (InvStockBalance::where('account_id', $accountId)->get(['item_id', 'store_id', 'quantity']) as $row) {
            $map[$row->item_id.':'.$row->store_id] = (float) $row->quantity;
        }

        return $map;
    }
}
