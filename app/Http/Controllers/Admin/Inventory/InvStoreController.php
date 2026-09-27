<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockMovement;
use App\Models\Inventory\InvStore;
use App\Models\Locations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvStoreController extends Controller
{
    public function index(Request $request)
    {
        if (! Gate::allows('inv_store_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $query = InvStore::forAccount($accountId)
            ->with('location:id,name')
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('active')) {
            $query->where('active', (int) $request->get('active') === 1);
        }

        if ($request->filled('store_type')) {
            $query->where('store_type', $request->get('store_type'));
        }

        $stores = $query->paginate(25)->appends($request->query());

        return view('admin.inventory-erp.stores.index', compact('stores'));
    }

    public function create()
    {
        if (! Gate::allows('inv_store_manage')) {
            return abort(401);
        }

        return view('admin.inventory-erp.stores.form', [
            'store' => new InvStore([
                'store_type' => InvStore::TYPE_WAREHOUSE,
                'active' => true,
            ]),
            'locations' => $this->locations(),
        ]);
    }

    public function store(Request $request)
    {
        if (! Gate::allows('inv_store_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $data = $this->validated($request, $accountId);

        InvStore::create(array_merge($data, [
            'account_id' => $accountId,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));

        flash('Store created successfully.')->success()->important();

        return redirect()->route('admin.inventory-erp.stores.index');
    }

    public function edit(int $id)
    {
        if (! Gate::allows('inv_store_manage')) {
            return abort(401);
        }

        $store = InvStore::forAccount((int) Auth::user()->account_id)->findOrFail($id);

        return view('admin.inventory-erp.stores.form', [
            'store' => $store,
            'locations' => $this->locations(),
        ]);
    }

    public function update(Request $request, int $id)
    {
        if (! Gate::allows('inv_store_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $store = InvStore::forAccount($accountId)->findOrFail($id);
        $data = $this->validated($request, $accountId);

        $store->fill($data);
        $store->updated_by = Auth::id();
        $store->save();

        flash('Store updated successfully.')->success()->important();

        return redirect()->route('admin.inventory-erp.stores.index');
    }

    public function destroy(int $id)
    {
        if (! Gate::allows('inv_store_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $store = InvStore::forAccount($accountId)->findOrFail($id);

        $hasMovements = InvStockMovement::where('store_id', $store->id)->exists();
        $hasQty = InvStockBalance::where('store_id', $store->id)->where('quantity', '>', 0)->exists();

        if ($hasMovements || $hasQty) {
            flash('Store has stock history. Deactivate it instead of deleting.')->error()->important();

            return redirect()->back();
        }

        $store->delete();
        flash('Store deleted successfully.')->success()->important();

        return redirect()->route('admin.inventory-erp.stores.index');
    }

    private function validated(Request $request, int $accountId): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'store_type' => 'required|in:warehouse,centre_store,retail',
            'location_id' => [
                'nullable', 'integer',
                function ($attribute, $value, $fail) use ($accountId) {
                    if ($value && ! Locations::where('account_id', $accountId)->where('id', $value)->exists()) {
                        $fail('Selected centre is invalid.');
                    }
                },
            ],
            'active' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');
        $data['location_id'] = $data['location_id'] ?: null;

        return $data;
    }

    private function locations()
    {
        return Locations::where('account_id', Auth::user()->account_id)
            ->where('active', 1)
            ->orderBy('name')
            ->pluck('name', 'id');
    }
}
