<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InvPurchaseOrder;
use App\Models\Inventory\InvSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvSupplierController extends Controller
{
    public function index(Request $request)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $query = InvSupplier::forAccount($accountId)->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('active')) {
            $query->where('active', (int) $request->get('active') === 1);
        }

        $suppliers = $query->paginate(25)->appends($request->query());

        return view('admin.inventory-erp.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        return view('admin.inventory-erp.suppliers.form', [
            'supplier' => new InvSupplier(['active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $data = $this->validated($request);

        InvSupplier::create(array_merge($data, [
            'account_id' => (int) Auth::user()->account_id,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]));

        flash('Supplier created successfully.')->success()->important();

        return redirect()->route('admin.inventory-erp.suppliers.index');
    }

    public function edit(int $id)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $supplier = InvSupplier::forAccount((int) Auth::user()->account_id)->findOrFail($id);

        return view('admin.inventory-erp.suppliers.form', compact('supplier'));
    }

    public function update(Request $request, int $id)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $supplier = InvSupplier::forAccount((int) Auth::user()->account_id)->findOrFail($id);
        $data = $this->validated($request);

        $supplier->fill($data);
        $supplier->updated_by = Auth::id();
        $supplier->save();

        flash('Supplier updated successfully.')->success()->important();

        return redirect()->route('admin.inventory-erp.suppliers.index');
    }

    public function destroy(int $id)
    {
        if (! Gate::allows('inv_purchase_manage')) {
            return abort(401);
        }

        $supplier = InvSupplier::forAccount((int) Auth::user()->account_id)->findOrFail($id);

        if (InvPurchaseOrder::where('supplier_id', $supplier->id)->exists()) {
            flash('Supplier has purchase orders. Deactivate instead of deleting.')->error()->important();

            return redirect()->back();
        }

        $supplier->delete();
        flash('Supplier deleted successfully.')->success()->important();

        return redirect()->route('admin.inventory-erp.suppliers.index');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:120',
            'address' => 'nullable|string|max:500',
            'active' => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active');

        return $data;
    }
}
