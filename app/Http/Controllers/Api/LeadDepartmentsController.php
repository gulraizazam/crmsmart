<?php

namespace App\Http\Controllers\Api;

use App\HelperModule\ApiHelper;
use App\Http\Controllers\Controller;
use App\Models\LeadDepartment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LeadDepartmentsController extends Controller
{
    public function index(): JsonResponse
    {
        if (! $this->canManage()) {
            return ApiHelper::apiResponse(config('constants.api_status.unauthorized'), 'You are not authorized to access this resource.');
        }

        $rows = LeadDepartment::forAccount()
            ->with('locations:id,name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (LeadDepartment $row) => $this->transform($row));

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'Record found.', true, [
            'departments' => $rows,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->canManage()) {
            return ApiHelper::apiResponse(config('constants.api_status.unauthorized'), 'You are not authorized to access this resource.');
        }

        $data = $this->validateDepartment($request);
        $department = LeadDepartment::create([
            'name' => $data['name'],
            'active' => $data['active'],
            'sort_order' => $data['sort_order'] ?? 0,
            'account_id' => Auth::user()->account_id,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
        $department->locations()->sync($data['location_ids'] ?? []);

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'Department created.', true, [
            'department' => $this->transform($department->fresh('locations')),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (! $this->canManage()) {
            return ApiHelper::apiResponse(config('constants.api_status.unauthorized'), 'You are not authorized to access this resource.');
        }

        $department = LeadDepartment::forAccount()->find($id);
        if (! $department) {
            return ApiHelper::apiResponse(config('constants.api_status.error'), 'Department not found.');
        }

        $data = $this->validateDepartment($request);
        $department->update([
            'name' => $data['name'],
            'active' => $data['active'],
            'sort_order' => $data['sort_order'] ?? $department->sort_order,
            'updated_by' => Auth::id(),
        ]);
        $department->locations()->sync($data['location_ids'] ?? []);

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'Department updated.', true, [
            'department' => $this->transform($department->fresh('locations')),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        if (! $this->canManage()) {
            return ApiHelper::apiResponse(config('constants.api_status.unauthorized'), 'You are not authorized to access this resource.');
        }

        $department = LeadDepartment::forAccount()->find($id);
        if (! $department) {
            return ApiHelper::apiResponse(config('constants.api_status.error'), 'Department not found.');
        }

        $department->locations()->detach();
        $department->delete();

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'Department deleted.');
    }

    protected function canManage(): bool
    {
        return Gate::allows('leads_manage') || Gate::allows('lead_sources_manage');
    }

    protected function validateDepartment(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'active' => 'nullable|in:0,1',
            'sort_order' => 'nullable|integer',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'integer|exists:locations,id',
        ]);
    }

    protected function transform(LeadDepartment $row): array
    {
        return [
            'id' => $row->id,
            'name' => $row->name,
            'active' => (int) $row->active,
            'sort_order' => (int) $row->sort_order,
            'location_ids' => $row->locations->pluck('id')->values(),
            'locations' => $row->locations->pluck('name')->values(),
        ];
    }
}
