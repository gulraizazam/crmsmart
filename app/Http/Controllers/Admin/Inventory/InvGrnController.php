<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvPurchaseOrder;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStore;
use App\Services\Inventory\PurchaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvGrnController extends Controller
{
    private PurchaseService $purchases;

    public function __construct(PurchaseService $purchases)
    {
        $this->purchases = $purchases;
    }

    public function index()
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $documents = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_PURCHASE_RECEIPT)
            ->withCount('lines')
            ->orderByDesc('document_date')
            ->orderByDesc('id')
            ->paginate(25);

        return view('admin.inventory-erp.grns.index', compact('documents'));
    }

    public function create(Request $request)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $poId = $request->filled('po_id') ? (int) $request->get('po_id') : null;

        $openOrders = InvPurchaseOrder::forAccount($accountId)
            ->where('status', InvPurchaseOrder::STATUS_ORDERED)
            ->with('supplier:id,name')
            ->orderByDesc('id')
            ->get(['id', 'po_number', 'supplier_id', 'order_date']);

        $po = null;
        if ($poId) {
            $po = InvPurchaseOrder::forAccount($accountId)
                ->where('status', InvPurchaseOrder::STATUS_ORDERED)
                ->with(['lines.item', 'lines.store', 'supplier'])
                ->findOrFail($poId);
        }

        return view('admin.inventory-erp.grns.create', [
            'openOrders' => $openOrders,
            'po' => $po,
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
            'purchase_order_id' => 'required|integer',
            'document_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.purchase_order_line_id' => 'required|integer',
            'lines.*.store_id' => 'required|integer',
            'lines.*.quantity' => 'nullable|numeric|gte:0',
            'lines.*.unit_cost' => 'nullable|numeric|gte:0',
        ]);

        $po = InvPurchaseOrder::forAccount($accountId)->findOrFail((int) $data['purchase_order_id']);

        $receiveLines = [];
        foreach ($data['lines'] as $line) {
            $qty = (float) ($line['quantity'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $receiveLines[] = [
                'purchase_order_line_id' => (int) $line['purchase_order_line_id'],
                'store_id' => (int) $line['store_id'],
                'quantity' => $qty,
                'unit_cost' => $line['unit_cost'] ?? null,
            ];
        }

        if ($receiveLines === []) {
            flash('Enter quantity for at least one line.')->error()->important();

            return redirect()->back()->withInput();
        }

        try {
            $document = $this->purchases->receive(
                $po,
                $data['document_date'],
                $receiveLines,
                $data['notes'] ?? null,
                Auth::id()
            );
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();

            return redirect()->back()->withInput();
        }

        flash('GRN posted: '.$document->document_no)->success()->important();

        return redirect()->route('admin.inventory-erp.grns.show', $document->id);
    }

    public function show(int $id)
    {
        if (! Gate::allows('inv_purchase_manage') && ! Gate::allows('inv_report_manage')) {
            return abort(401);
        }

        $document = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->where('document_type', InvStockDocument::TYPE_PURCHASE_RECEIPT)
            ->with(['lines.item', 'lines.store'])
            ->findOrFail($id);

        $po = null;
        if ($document->reference_type === InvPurchaseOrder::REF_TYPE && $document->reference_id) {
            $po = InvPurchaseOrder::forAccount((int) Auth::user()->account_id)
                ->with('supplier')
                ->find($document->reference_id);
        }

        return view('admin.inventory-erp.grns.show', compact('document', 'po'));
    }
}
