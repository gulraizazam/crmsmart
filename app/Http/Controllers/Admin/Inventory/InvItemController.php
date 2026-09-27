<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InvItem;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class InvItemController extends Controller
{
    public function index(Request $request)
    {
        if (! Gate::allows('inv_item_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $query = InvItem::forAccount($accountId)->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('active')) {
            $query->where('active', (int) $request->get('active') === 1);
        }

        if ($request->filled('item_type')) {
            $query->where('item_type', $request->get('item_type'));
        }

        $items = $query->paginate(25)->appends($request->query());

        return view('admin.inventory-erp.items.index', compact('items'));
    }

    public function create()
    {
        if (! Gate::allows('inv_item_manage')) {
            return abort(401);
        }

        return view('admin.inventory-erp.items.form', [
            'item' => new InvItem([
                'item_type' => InvItem::TYPE_TRADABLE,
                'uom' => 'pcs',
                'active' => true,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        if (! Gate::allows('inv_item_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $data = $this->validated($request, $accountId);

        InvItem::create(array_merge($data, [
            'account_id' => $accountId,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));

        flash('Item created successfully.')->success()->important();

        return redirect()->route('admin.inventory-erp.items.index');
    }

    public function edit(int $id)
    {
        if (! Gate::allows('inv_item_manage')) {
            return abort(401);
        }

        $item = InvItem::forAccount((int) Auth::user()->account_id)->findOrFail($id);

        return view('admin.inventory-erp.items.form', compact('item'));
    }

    public function update(Request $request, int $id)
    {
        if (! Gate::allows('inv_item_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $item = InvItem::forAccount($accountId)->findOrFail($id);
        $data = $this->validated($request, $accountId, $item->id);

        $item->fill($data);
        $item->updated_by = Auth::id();
        $item->save();

        flash('Item updated successfully.')->success()->important();

        return redirect()->route('admin.inventory-erp.items.index');
    }

    public function destroy(int $id)
    {
        if (! Gate::allows('inv_item_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $item = InvItem::forAccount($accountId)->findOrFail($id);

        $hasMovements = InvStockMovement::where('item_id', $item->id)->exists();
        $hasQty = InvStockBalance::where('item_id', $item->id)->where('quantity', '>', 0)->exists();

        if ($hasMovements || $hasQty) {
            flash('Item has stock history. Deactivate it instead of deleting.')->error()->important();

            return redirect()->back();
        }

        $item->delete();
        flash('Item deleted successfully.')->success()->important();

        return redirect()->route('admin.inventory-erp.items.index');
    }

    private function validated(Request $request, int $accountId, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'sku' => [
                'required', 'string', 'max:64',
                Rule::unique('inv_items', 'sku')
                    ->where(fn ($q) => $q->where('account_id', $accountId)->whereNull('deleted_at'))
                    ->ignore($ignoreId),
            ],
            'name' => 'required|string|max:255',
            'item_type' => 'required|in:tradable,consumable',
            'uom' => 'required|string|max:20',
            'active' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');
        $data['sku'] = strtoupper(trim($data['sku']));

        return $data;
    }
}
