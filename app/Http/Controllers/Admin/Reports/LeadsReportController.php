<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Helpers\ACL;
use App\Http\Controllers\Controller;
use App\Models\Cities;
use App\Models\LeadDepartment;
use App\Models\LeadSources;
use App\Models\LeadStatuses;
use App\Models\Locations;
use App\Models\User;
use App\Services\Lead\LeadReportService;
use App\Services\Lead\LeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LeadsReportController extends Controller
{
    /** @var LeadReportService */
    private $reports;

    /** @var LeadService */
    private $leads;

    public function __construct(LeadReportService $reports, LeadService $leads)
    {
        $this->reports = $reports;
        $this->leads = $leads;
    }

    /**
     * Lead reports filter page.
     */
    public function index()
    {
        $this->authorizeReports();

        $accountId = Auth::user()->account_id;
        $reportTypes = LeadReportService::reportTypes();
        $locations = Locations::getActiveSorted(ACL::getUserCentres());
        $cities = Cities::getActiveSortedFeatured(ACL::getUserCities() ?: [0]);
        $sources = LeadSources::getActiveSorted();
        $statuses = LeadStatuses::getLeadStatuses();
        $departments = LeadDepartment::getActiveForAccount($accountId)->pluck('name', 'id');
        $agents = $this->leads->getCsrUsers($accountId);
        $centres = ACL::getUserCentres();
        $centreIds = collect($centres)->map(function ($id) {
            return (int) $id;
        })->filter()->values()->all();
        $creators = User::getAllActiveEmployeeRecords($accountId, $centreIds ?: false)->pluck('name', 'id');

        return view('admin.reports.leads.index', compact(
            'reportTypes',
            'locations',
            'cities',
            'sources',
            'statuses',
            'departments',
            'agents',
            'creators'
        ));
    }

    /**
     * Load report HTML fragment.
     */
    public function load(Request $request)
    {
        $this->authorizeReports();

        ini_set('memory_limit', '-1');
        set_time_limit(0);

        $data = $this->reports->build($request);

        return view('admin.reports.leads.result', $data);
    }

    /**
     * @return void
     */
    private function authorizeReports()
    {
        if (! Gate::allows('leads_reports_manage') && ! Gate::allows('leads_manage')) {
            abort(401);
        }
    }
}
