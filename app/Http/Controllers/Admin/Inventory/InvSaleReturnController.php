<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStore;
use App\Services\Inventory\OutboundService;
use App\Services\Inventory\StockEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvSaleReturnController extends Controller
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
            ->where('document_type', InvStockDocument::TYPE_SALE_RETURN)
            ->withCount('lines')
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.inventory-erp.sale-returns.index', compact('documents'));
    }

    public function create()
    {
        $this->authorizeMove();
        $accountId = (int) Auth::user()->account_id;

        return view('admin.inventory-erp.sale-returns.create', [
            'items' => InvItem::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'sku', 'name', 'uom']),
            'stores' => InvStore::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeMove();
        $accountId = (int) Auth::user()->account_id;

        $data = $request->validate([
            'document_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|integer',
            'lines.*.store_id' => 'required|integer',
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_cost' => 'nullable|numeric|gte:0',
        ]);

        $itemIds = InvItem::forAccount($accountId)->whereIn('id', collect($data['lines'])->pluck('item_id'))->pluck('id')->all();
        $storeIds = InvStore::forAccount($accountId)->whereIn('id', collect($data['lines'])->pluck('store_id'))->pluck('id')->all();

        $lines = [];
        foreach ($data['lines'] as $line) {
            if (! in_array((int) $line['item_id'], $itemIds, true) || ! in_array((int) $line['store_id'], $storeIds, true)) {
                flash('One or more lines reference an invalid item or store.')->error()->important();

                return redirect()->back()->withInput();
            }
            $entry = [
                'item_id' => (int) $line['item_id'],
                'store_id' => (int) $line['store_id'],
                'quantity' => $line['quantity'],
            ];
            if (isset($line['unit_cost']) && $line['unit_cost'] !== null && $line['unit_cost'] !== '') {
                $entry['unit_cost'] = $line['unit_cost'];
            }
            $lines[] = $entry;
        }

        try {
            $document = $this->outbound->post(
                $accountId,
                InvStockDocument::TYPE_SALE_RETURN,
                $data['document_date'],
                $lines,
                $data['notes'] ?? null,
                Auth::id()
            );
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();

            return redirect()->back()->withInput();
        }

        flash('Sales return posted: '.$document->document_no)->success()->important();

        return redirect()->route('admin.inventory-erp.sale-returns.show', $document->id);
    }

    public function show(int $id)
    {
        if (! Gate::allows('inv_move_manage') && ! Gate::allows('inv_report_manage')) {
            abort(401);
        }

        $document = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_SALE_RETURN)
            ->with(['lines.item', 'lines.store'])
            ->findOrFail($id);

        return view('admin.inventory-erp.sale-returns.show', compact('document'));
    }

    public function reverse(int $id)
    {
        $this->authorizeMove();

        $document = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_SALE_RETURN)
            ->findOrFail($id);

        try {
            $rev = $this->stockEngine->reverse($document, 'Reverse sales return '.$document->document_no, Auth::id());
            flash('Sales return reversed via '.$rev->document_no)->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.sale-returns.show', $id);
    }

    private function authorizeMove(): void
    {
        if (! Gate::allows('inv_move_manage')) {
            abort(401);
        }
    }
}
