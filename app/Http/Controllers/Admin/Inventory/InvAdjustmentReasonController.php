<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InvAdjustmentReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class InvAdjustmentReasonController extends Controller
{
    public function index()
    {
        $this->authorizeAdjust();

        $reasons = InvAdjustmentReason::forAccount((int) Auth::user()->account_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.inventory-erp.adjustment-reasons.index', compact('reasons'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdjust();
        $accountId = (int) Auth::user()->account_id;

        $data = $request->validate([
            'code' => [
                'required', 'string', 'max:40', 'alpha_dash',
                Rule::unique('inv_adjustment_reasons', 'code')->where(function ($q) use ($accountId) {
                    return $q->where('account_id', $accountId);
                }),
            ],
            'name' => 'required|string|max:255',
            'direction' => 'required|in:in,out,both',
        ]);

        InvAdjustmentReason::create([
            'account_id' => $accountId,
            'code' => strtolower($data['code']),
            'name' => $data['name'],
            'direction' => $data['direction'],
            'active' => true,
            'sort_order' => (int) InvAdjustmentReason::forAccount($accountId)->max('sort_order') + 1,
        ]);

        flash('Reason added.')->success()->important();

        return redirect()->route('admin.inventory-erp.adjustment-reasons.index');
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAdjust();
        $accountId = (int) Auth::user()->account_id;
        $reason = InvAdjustmentReason::forAccount($accountId)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'direction' => 'required|in:in,out,both',
            'active' => 'nullable|boolean',
        ]);

        $reason->name = $data['name'];
        $reason->direction = $data['direction'];
        $reason->active = $request->boolean('active');
        $reason->save();

        flash('Reason updated.')->success()->important();

        return redirect()->route('admin.inventory-erp.adjustment-reasons.index');
    }

    private function authorizeAdjust(): void
    {
        if (! Gate::allows('inv_adjust_manage')) {
            abort(401);
        }
    }
}
