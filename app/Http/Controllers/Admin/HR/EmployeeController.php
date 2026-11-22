<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use App\Services\HR\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected readonly EmployeeService $service,
    ) {}

    public function index(): View
    {
        $accountId = Auth::user()->account_id;

        $designations = Designation::active()
            ->where('account_id', $accountId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $locations = \App\Models\Locations::where('account_id', $accountId)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.hr.employees.index', compact('designations', 'locations'));
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $accountId = Auth::user()->account_id;
            $datatableData = $this->service->getDatatableData($request->all(), $accountId);

            [$orderBy, $order] = getSortBy($request);
            [$displayLength, $displayStart, $pages, $page] = getPaginationElement($request, $datatableData['total']);

            $sortColumn = match ($orderBy) {
                'name' => 'users.name',
                'email' => 'users.email',
                'designation' => 'designations.name',
                'active' => 'users.active',
                'hire_date' => 'employee_details.hire_date',
                default => 'users.name',
            };

            $records = $datatableData['query']
                ->limit($displayLength)
                ->offset($displayStart)
                ->orderBy($sortColumn, $order ?: 'asc')
                ->get();

            $data = $records->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'designation' => $user->designation_name ?? '—',
                'hire_date' => $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->format('d M Y') : '—',
                'active' => $user->active,
            ]);

            return response()->json([
                'data' => $data,
                'permissions' => [
                    'view' => Gate::allows('hr_employees_view'),
                    'edit' => Gate::allows('hr_employees_manage'),
                ],
                'active_filters' => $datatableData['active_filters'],
                'filter_values' => [
                    'status' => config('constants.status'),
                ],
                'meta' => [
                    'field' => $orderBy,
                    'page' => $page,
                    'pages' => $pages,
                    'perpage' => $displayLength,
                    'total' => $datatableData['total'],
                    'sort' => $order,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'EmployeeController@datatable');
        }
    }

    public function show(User $user): View
    {
        $employee = $this->service->getEmployeeProfile($user->id);
        $accountId = Auth::user()->account_id;

        $documents = $employee->employeeDocuments;

        $departments = Department::active()
            ->where('account_id', $accountId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $designations = Designation::active()
            ->where('account_id', $accountId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $managers = User::where('account_id', $accountId)
            ->whereIn('user_type_id', [2, 5])
            ->where('active', 1)
            ->where('hr_managed', 1)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $locations = \App\Models\Locations::where('account_id', $accountId)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.hr.employees.show', compact(
            'employee',
            'documents',
            'departments',
            'designations',
            'managers',
            'locations'
        ));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_if((int) $user->account_id !== (int) Auth::user()->account_id, 404);

        try {
            $accountId = Auth::user()->account_id;
            $validated = $request->validate([
                // Personal info (users table)
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
                'cnic' => ['nullable', 'string', 'max:50'],
                'dob' => ['nullable', 'date'],
                'gender' => ['nullable', 'in:1,2'],
                'address' => ['nullable', 'string', 'max:500'],
                // HR details (employee_details table)
                'department_id' => ['nullable', "exists:departments,id,account_id,{$accountId}"],
                'designation_id' => ['nullable', "exists:designations,id,account_id,{$accountId}"],
                'reporting_manager_id' => ['nullable', "exists:users,id,account_id,{$accountId}"],
                'hire_date' => ['nullable', 'date'],
                'employment_type' => ['nullable', 'in:full_time,part_time,contract'],
                'shift_hours' => ['nullable', 'numeric', 'gt:0', 'lte:24'],
                'salary' => ['nullable', 'numeric', 'min:0'],
                'bank_name' => ['nullable', 'string', 'max:255'],
                'bank_account_number' => ['nullable', 'string', 'max:255'],
                'tax_id' => ['nullable', 'string', 'max:255'],
                'emergency_contact_name' => ['nullable', 'string', 'max:255'],
                'emergency_contact_phone' => ['nullable', 'string', 'max:255'],
                'emergency_contact_relation' => ['nullable', 'string', 'max:255'],
                'location_ids' => ['nullable', 'array'],
                'location_ids.*' => ['integer', "exists:locations,id,account_id,{$accountId}"],
            ]);

            // HR address is stored on employee_details.address, not users.address.
            $profileFields = ['name', 'email', 'phone', 'cnic', 'dob', 'gender'];
            $user->update(collect($validated)->only($profileFields)->toArray());

            // Update HR details
            $hrFields = collect($validated)->except([...$profileFields, 'location_ids'])->toArray();
            $this->service->updateEmployeeDetails(
                $user,
                $hrFields,
                $accountId
            );

            // Sync locations/centres
            if (array_key_exists('location_ids', $validated)) {
                $this->service->syncLocations($user, $validated['location_ids'] ?? [], $accountId);
            }

            return $this->successResponse('Employee details updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422, $e->errors());
        } catch (\Exception $e) {
            return $this->handleException($e, 'EmployeeController@update');
        }
    }
}
