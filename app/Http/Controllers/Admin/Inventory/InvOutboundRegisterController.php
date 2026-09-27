<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InvStockDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvOutboundRegisterController extends Controller
{
    public function index(Request $request)
    {
        if (! Gate::allows('inv_move_manage') && ! Gate::allows('inv_report_manage')) {
            abort(401);
        }

        $types = [
            InvStockDocument::TYPE_SALE,
            InvStockDocument::TYPE_ISSUE,
            InvStockDocument::TYPE_SALE_RETURN,
        ];

        $query = InvStockDocument::forAccount((int) Auth::user()->account_id)
            ->whereIn('document_type', $types)
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

        return view('admin.inventory-erp.outbound.register', [
            'documents' => $query->paginate(50)->appends($request->query()),
        ]);
    }
}
