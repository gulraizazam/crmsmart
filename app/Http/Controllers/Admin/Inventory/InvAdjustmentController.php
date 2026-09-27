<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvAdjustmentReason;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStore;
use App\Services\Inventory\AdjustmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvAdjustmentController extends Controller
{
    private AdjustmentService $adjustments;

    public function __construct(AdjustmentService $adjustments)
    {
        $this->adjustments = $adjustments;
    }

    public function index()
    {
        $this->authorizeAdjust();

        $documents = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_ADJUSTMENT)
            ->with(['adjustmentReason:id,name,code'])
            ->withCount('lines')
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.inventory-erp.adjustments.index', compact('documents'));
    }

    public function create()
    {
        $this->authorizeAdjust();
        $accountId = (int) Auth::user()->account_id;

        return view('admin.inventory-erp.adjustments.create', [
            'reasons' => InvAdjustmentReason::forAccount($accountId)->where('active', true)->orderBy('sort_order')->get(),
            'items' => InvItem::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'sku', 'name']),
            'stores' => InvStore::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']),
            'balances' => $this->balanceMap($accountId),
            'threshold' => AdjustmentService::APPROVAL_THRESHOLD,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdjust();
        $accountId = (int) Auth::user()->account_id;

        $data = $request->validate([
            'adjustment_reason_id' => 'required|integer',
            'document_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|integer',
            'lines.*.store_id' => 'required|integer',
            'lines.*.direction' => 'required|in:in,out',
            'lines.*.quantity' => 'required|numeric|gt:0',
            'lines.*.unit_cost' => 'nullable|numeric|gte:0',
        ]);

        $itemIds = InvItem::forAccount($accountId)->whereIn('id', collect($data['lines'])->pluck('item_id'))->pluck('id')->all();
        $storeIds = InvStore::forAccount($accountId)->whereIn('id', collect($data['lines'])->pluck('store_id'))->pluck('id')->all();

        $lines = [];
        foreach ($data['lines'] as $line) {
            if (! in_array((int) $line['item_id'], $itemIds, true) || ! in_array((int) $line['store_id'], $storeIds, true)) {
                flash('Invalid item or store on a line.')->error()->important();

                return redirect()->back()->withInput();
            }
            $lines[] = [
                'item_id' => (int) $line['item_id'],
                'store_id' => (int) $line['store_id'],
                'direction' => $line['direction'],
                'quantity' => $line['quantity'],
                'unit_cost' => $line['unit_cost'] ?? null,
            ];
        }

        try {
            $document = $this->adjustments->createDraft(
                $accountId,
                (int) $data['adjustment_reason_id'],
                $data['document_date'],
                $lines,
                $data['notes'] ?? null,
                Auth::id()
            );
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();

            return redirect()->back()->withInput();
        }

        flash('Adjustment draft created: '.$document->document_no)->success()->important();

        return redirect()->route('admin.inventory-erp.adjustments.show', $document->id);
    }

    public function show(int $id)
    {
        if (! Gate::allows('inv_adjust_manage') && ! Gate::allows('inv_report_manage')) {
            abort(401);
        }

        $document = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_ADJUSTMENT)
            ->with(['lines.item', 'lines.store', 'adjustmentReason', 'approvedByUser'])
            ->findOrFail($id);

        return view('admin.inventory-erp.adjustments.show', [
            'document' => $document,
            'needsElevated' => $this->adjustments->needsElevatedApproval($document),
            'canApprove' => $this->adjustments->userCanApprove($document),
            'threshold' => AdjustmentService::APPROVAL_THRESHOLD,
        ]);
    }

    public function approve(int $id)
    {
        $this->authorizeAdjust();

        $document = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_ADJUSTMENT)
            ->findOrFail($id);

        $elevated = Gate::allows('inv_erp_manage');

        try {
            $this->adjustments->approve($document, Auth::id(), $elevated);
            flash('Adjustment approved.')->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.adjustments.show', $id);
    }

    public function post(int $id)
    {
        $this->authorizeAdjust();

        $document = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_ADJUSTMENT)
            ->findOrFail($id);

        try {
            $posted = $this->adjustments->post($document, Auth::id());
            flash('Adjustment posted: '.$posted->document_no)->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.adjustments.show', $id);
    }

    private function authorizeAdjust(): void
    {
        if (! Gate::allows('inv_adjust_manage')) {
            abort(401);
        }
    }

    private function balanceMap(int $accountId): array
    {
        $map = [];
        foreach (InvStockBalance::where('account_id', $accountId)->get(['item_id', 'store_id', 'quantity', 'avg_cost']) as $row) {
            $map[$row->item_id.':'.$row->store_id] = [
                'qty' => (float) $row->quantity,
                'avg' => (float) $row->avg_cost,
            ];
        }

        return $map;
    }
}
