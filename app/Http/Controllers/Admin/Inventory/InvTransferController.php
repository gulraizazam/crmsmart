<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvStore;
use App\Models\Inventory\InvTransfer;
use App\Services\Inventory\TransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvTransferController extends Controller
{
    private TransferService $transfers;

    public function __construct(TransferService $transfers)
    {
        $this->transfers = $transfers;
    }

    public function index(Request $request)
    {
        if (! Gate::allows('inv_transfer_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $query = InvTransfer::forAccount($accountId)
            ->with(['fromStore:id,name', 'toStore:id,name'])
            ->withCount('lines')
            ->orderByDesc('transfer_date')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('search')) {
            $query->where('transfer_no', 'like', '%'.$request->get('search').'%');
        }

        return view('admin.inventory-erp.transfers.index', [
            'transfers' => $query->paginate(25)->appends($request->query()),
        ]);
    }

    public function inTransit()
    {
        if (! Gate::allows('inv_transfer_manage') && ! Gate::allows('inv_report_manage')) {
            return abort(401);
        }

        $transfers = InvTransfer::forAccount((int) Auth::user()->account_id)
            ->where('status', InvTransfer::STATUS_IN_TRANSIT)
            ->with(['fromStore:id,name', 'toStore:id,name', 'lines.item'])
            ->orderByDesc('dispatched_at')
            ->get();

        return view('admin.inventory-erp.transfers.in-transit', compact('transfers'));
    }

    public function create()
    {
        if (! Gate::allows('inv_transfer_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;

        return view('admin.inventory-erp.transfers.create', [
            'stores' => InvStore::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'name']),
            'items' => InvItem::forAccount($accountId)->where('active', true)->orderBy('name')->get(['id', 'sku', 'name', 'uom']),
        ]);
    }

    public function store(Request $request)
    {
        if (! Gate::allows('inv_transfer_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $data = $request->validate([
            'from_store_id' => 'required|integer',
            'to_store_id' => 'required|integer|different:from_store_id',
            'transfer_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|integer',
            'lines.*.quantity' => 'required|numeric|gt:0',
        ]);

        $storeIds = InvStore::forAccount($accountId)
            ->whereIn('id', [$data['from_store_id'], $data['to_store_id']])
            ->pluck('id')->all();
        if (count($storeIds) < 2) {
            flash('Invalid store selection.')->error()->important();

            return redirect()->back()->withInput();
        }

        $itemIds = InvItem::forAccount($accountId)->whereIn('id', collect($data['lines'])->pluck('item_id'))->pluck('id')->all();
        $lines = [];
        foreach ($data['lines'] as $line) {
            if (! in_array((int) $line['item_id'], $itemIds, true)) {
                flash('One or more lines reference an invalid item.')->error()->important();

                return redirect()->back()->withInput();
            }
            $lines[] = [
                'item_id' => (int) $line['item_id'],
                'quantity' => $line['quantity'],
            ];
        }

        try {
            $transfer = $this->transfers->createDraft(
                $accountId,
                (int) $data['from_store_id'],
                (int) $data['to_store_id'],
                $data['transfer_date'],
                $lines,
                $data['notes'] ?? null,
                Auth::id()
            );
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();

            return redirect()->back()->withInput();
        }

        flash('Transfer created: '.$transfer->transfer_no)->success()->important();

        return redirect()->route('admin.inventory-erp.transfers.show', $transfer->id);
    }

    public function show(int $id)
    {
        if (! Gate::allows('inv_transfer_manage') && ! Gate::allows('inv_report_manage')) {
            return abort(401);
        }

        $transfer = InvTransfer::forAccount((int) Auth::user()->account_id)
            ->with(['fromStore', 'toStore', 'lines.item', 'dispatchDocument', 'stockDocuments'])
            ->findOrFail($id);

        return view('admin.inventory-erp.transfers.show', compact('transfer'));
    }

    public function approve(int $id)
    {
        if (! Gate::allows('inv_transfer_manage')) {
            return abort(401);
        }

        $transfer = InvTransfer::forAccount((int) Auth::user()->account_id)->findOrFail($id);

        try {
            $this->transfers->approve($transfer, Auth::id());
            flash('Transfer approved.')->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.transfers.show', $id);
    }

    public function dispatchTransfer(int $id)
    {
        if (! Gate::allows('inv_transfer_manage')) {
            return abort(401);
        }

        $transfer = InvTransfer::forAccount((int) Auth::user()->account_id)->findOrFail($id);

        try {
            $this->transfers->dispatch($transfer, null, Auth::id());
            flash('Transfer dispatched — stock left source store (in transit).')->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.transfers.show', $id);
    }

    public function receiveForm(int $id)
    {
        if (! Gate::allows('inv_transfer_manage')) {
            return abort(401);
        }

        $transfer = InvTransfer::forAccount((int) Auth::user()->account_id)
            ->where('status', InvTransfer::STATUS_IN_TRANSIT)
            ->with(['fromStore', 'toStore', 'lines.item'])
            ->findOrFail($id);

        return view('admin.inventory-erp.transfers.receive', compact('transfer'));
    }

    public function receive(Request $request, int $id)
    {
        if (! Gate::allows('inv_transfer_manage')) {
            return abort(401);
        }

        $transfer = InvTransfer::forAccount((int) Auth::user()->account_id)->findOrFail($id);
        $data = $request->validate([
            'document_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.transfer_line_id' => 'required|integer',
            'lines.*.quantity' => 'nullable|numeric|gte:0',
        ]);

        $receiveLines = [];
        foreach ($data['lines'] as $line) {
            $qty = (float) ($line['quantity'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            $receiveLines[] = [
                'transfer_line_id' => (int) $line['transfer_line_id'],
                'quantity' => $qty,
            ];
        }

        if ($receiveLines === []) {
            flash('Enter quantity for at least one line.')->error()->important();

            return redirect()->back()->withInput();
        }

        try {
            $doc = $this->transfers->receive(
                $transfer,
                $data['document_date'],
                $receiveLines,
                $data['notes'] ?? null,
                Auth::id()
            );
            flash('Transfer receipt posted: '.$doc->document_no)->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();

            return redirect()->back()->withInput();
        }

        return redirect()->route('admin.inventory-erp.transfers.show', $id);
    }

    public function cancel(int $id)
    {
        if (! Gate::allows('inv_transfer_manage')) {
            return abort(401);
        }

        $transfer = InvTransfer::forAccount((int) Auth::user()->account_id)->findOrFail($id);

        try {
            $this->transfers->cancel($transfer, Auth::id());
            flash('Transfer cancelled.')->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.transfers.show', $id);
    }
}
