<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadDepartment;
use App\Models\Locations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LeadDepartmentsController extends Controller
{
    public function index()
    {
        if (! Gate::allows('leads_manage') && ! Gate::allows('lead_sources_manage')) {
            return abort(401);
        }

        $accountId = Auth::user()->account_id;
        $departments = LeadDepartment::forAccount($accountId)
            ->with('locations:id,name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $locations = Locations::where([
            'account_id' => $accountId,
            'active' => 1,
        ])->orderBy('name')->pluck('name', 'id');

        return view('admin.lead_departments.index', compact('departments', 'locations'));
    }
}
