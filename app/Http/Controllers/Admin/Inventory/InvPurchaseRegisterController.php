<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InvStockDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvPurchaseRegisterController extends Controller
{
    public function index(Request $request)
    {
        if (! Gate::allows('inv_purchase_manage') && ! Gate::allows('inv_report_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $query = InvStockDocument::forAccount($accountId)
            ->whereIn('document_type', [
                InvStockDocument::TYPE_PURCHASE_RECEIPT,
                InvStockDocument::TYPE_PURCHASE_RETURN,
            ])
            ->withCount('lines')
            ->orderByDesc('document_date')
            ->orderByDesc('id');

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->get('document_type'));
        }
        if ($request->filled('from')) {
            $query->whereDate('document_date', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('document_date', '<=', $request->get('to'));
        }

        return view('admin.inventory-erp.purchases.register', [
            'documents' => $query->paginate(50)->appends($request->query()),
        ]);
    }
}
