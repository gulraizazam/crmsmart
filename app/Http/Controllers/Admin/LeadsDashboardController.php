<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\LeadsDashboardService;
use App\Support\DashboardPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LeadsDashboardController extends Controller
{
    /** @var LeadsDashboardService */
    private $dashboard;

    public function __construct(LeadsDashboardService $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    /**
     * Leads analytics dashboard.
     */
    public function index(Request $request)
    {
        if (! Gate::allows('leads_manage')) {
            return abort(401);
        }

        $period = DashboardPeriod::fromRequest($request->query('period', $request->query('type')));
        $data = $this->dashboard->build($period);

        return view('admin.leads.dashboard', $data);
    }
}
