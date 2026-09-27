<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Exceptions\InventoryException;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvPeriodLock;
use App\Models\Inventory\InvStore;
use App\Models\Locations;
use App\Services\Inventory\FinanceReportService;
use App\Services\Inventory\PeriodLockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class InvFinanceController extends Controller
{
    private FinanceReportService $reports;
    private PeriodLockService $periodLocks;

    public function __construct(FinanceReportService $reports, PeriodLockService $periodLocks)
    {
        $this->reports = $reports;
        $this->periodLocks = $periodLocks;
    }

    public function valuation(Request $request)
    {
        $this->authorizeReport();
        $accountId = (int) Auth::user()->account_id;

        $storeId = $request->filled('store_id') ? (int) $request->get('store_id') : null;
        $locationId = $request->filled('location_id') ? (int) $request->get('location_id') : null;

        $rows = $this->reports->valuation($accountId, $storeId, $locationId);
        $total = (float) $rows->sum('value');

        return view('admin.inventory-erp.finance.valuation', [
            'rows' => $rows,
            'total' => $total,
            'stores' => InvStore::forAccount($accountId)->orderBy('name')->get(['id', 'name']),
            'locations' => Locations::where('account_id', $accountId)->where('active', 1)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function cogs(Request $request)
    {
        $this->authorizeReport();
        $accountId = (int) Auth::user()->account_id;

        $from = $request->get('from', date('Y-m-01'));
        $to = $request->get('to', date('Y-m-d'));
        $storeId = $request->filled('store_id') ? (int) $request->get('store_id') : null;
        $locationId = $request->filled('location_id') ? (int) $request->get('location_id') : null;

        $rows = $this->reports->cogs($accountId, $from, $to, $locationId, $storeId);
        $byCentre = $this->reports->cogsByCentre($accountId, $from, $to);
        $total = (float) $rows->sum('cogs');

        return view('admin.inventory-erp.finance.cogs', [
            'rows' => $rows,
            'byCentre' => $byCentre,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'stores' => InvStore::forAccount($accountId)->orderBy('name')->get(['id', 'name']),
            'locations' => Locations::where('account_id', $accountId)->where('active', 1)->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function periods()
    {
        $this->authorizeErp();
        $accountId = (int) Auth::user()->account_id;

        $locks = InvPeriodLock::forAccount($accountId)
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->paginate(24);

        return view('admin.inventory-erp.finance.periods', compact('locks'));
    }

    public function lockPeriod(Request $request)
    {
        $this->authorizeErp();
        $accountId = (int) Auth::user()->account_id;

        $data = $request->validate([
            'period_year' => 'required|integer|min:2000|max:2100',
            'period_month' => 'required|integer|min:1|max:12',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $lock = $this->periodLocks->lock(
                $accountId,
                (int) $data['period_year'],
                (int) $data['period_month'],
                Auth::id(),
                $data['notes'] ?? null
            );
            flash('Period '.$lock->label().' locked.')->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.finance.periods');
    }

    public function unlockPeriod(Request $request, int $id)
    {
        $this->authorizeErp();
        $accountId = (int) Auth::user()->account_id;

        $lock = InvPeriodLock::forAccount($accountId)->findOrFail($id);
        $data = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->periodLocks->unlock(
                $accountId,
                (int) $lock->period_year,
                (int) $lock->period_month,
                Auth::id(),
                $data['notes'] ?? 'Unlocked'
            );
            flash('Period '.$lock->label().' unlocked.')->success()->important();
        } catch (InventoryException $e) {
            flash($e->getMessage())->error()->important();
        }

        return redirect()->route('admin.inventory-erp.finance.periods');
    }

    private function authorizeReport(): void
    {
        if (! Gate::allows('inv_report_manage') && ! Gate::allows('inv_erp_manage')) {
            abort(401);
        }
    }

    private function authorizeErp(): void
    {
        if (! Gate::allows('inv_erp_manage')) {
            abort(401);
        }
    }
}
