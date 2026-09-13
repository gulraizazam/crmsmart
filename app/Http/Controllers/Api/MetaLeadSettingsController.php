<?php

namespace App\Http\Controllers\Api;

use App\HelperModule\ApiHelper;
use App\Http\Controllers\Controller;
use App\Models\MetaLeadEvent;
use App\Models\MetaLeadSetting;
use App\Services\Meta\MetaLeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class MetaLeadSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        if (! $this->canManage()) {
            return ApiHelper::apiResponse(config('constants.api_status.unauthorized'), 'You are not authorized to access this resource.');
        }

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'Record found.', true, [
            'settings' => $this->transform(MetaLeadSetting::forAccount(Auth::user()->account_id)),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (! $this->canManage()) {
            return ApiHelper::apiResponse(config('constants.api_status.unauthorized'), 'You are not authorized to access this resource.');
        }

        $data = $request->validate([
            'page_id' => 'nullable|string|max:64',
            'page_name' => 'nullable|string|max:150',
            'access_token' => 'nullable|string',
            'verify_token' => 'nullable|string|max:150',
            'app_secret' => 'nullable|string',
            'lead_source_id' => 'nullable|integer',
            'lead_status_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'assigned_to' => 'nullable|integer',
            'gender' => 'nullable|in:1,2',
            'active' => 'nullable|in:0,1',
        ]);

        $accountId = Auth::user()->account_id;
        $settings = MetaLeadSetting::firstOrNew(['account_id' => $accountId]);
        if (empty($data['access_token'])) {
            unset($data['access_token']);
        }
        if (empty($data['app_secret'])) {
            unset($data['app_secret']);
        }
        if (empty($data['verify_token'])) {
            $data['verify_token'] = $settings->verify_token ?: Str::random(32);
        }
        $data['active'] = (int) ($data['active'] ?? 0);
        $data['updated_by'] = Auth::id();
        $settings->fill($data);
        $settings->account_id = $accountId;
        $settings->save();

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'Meta Leads settings saved.', true, [
            'settings' => $this->transform($settings->fresh()),
        ]);
    }

    public function events(): JsonResponse
    {
        if (! $this->canManage()) {
            return ApiHelper::apiResponse(config('constants.api_status.unauthorized'), 'You are not authorized to access this resource.');
        }

        $events = MetaLeadEvent::query()
            ->where('account_id', Auth::user()->account_id)
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn (MetaLeadEvent $event) => [
                'id' => $event->id,
                'leadgen_id' => $event->leadgen_id,
                'page_id' => $event->page_id,
                'form_id' => $event->form_id,
                'lead_id' => $event->lead_id,
                'status' => $event->status,
                'error' => $event->error,
                'created_at' => optional($event->created_at)->format('Y-m-d H:i'),
            ]);

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'Record found.', true, [
            'events' => $events,
        ]);
    }

    public function catchUp(MetaLeadService $metaLeadService): JsonResponse
    {
        if (! $this->canManage()) {
            return ApiHelper::apiResponse(config('constants.api_status.unauthorized'), 'You are not authorized to access this resource.');
        }

        $stats = $metaLeadService->catchUp(Auth::user()->account_id);

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'Catch-up finished.', true, $stats);
    }

    protected function canManage(): bool
    {
        return Gate::allows('leads_manage') || Gate::allows('lead_sources_manage');
    }

    protected function transform(?MetaLeadSetting $settings): array
    {
        if (! $settings) {
            return [
                'page_id' => '',
                'page_name' => '',
                'verify_token' => '',
                'has_access_token' => false,
                'has_app_secret' => false,
                'lead_source_id' => '',
                'lead_status_id' => '',
                'city_id' => '',
                'location_id' => '',
                'department_id' => '',
                'assigned_to' => '',
                'gender' => '',
                'active' => 0,
            ];
        }

        return [
            'page_id' => $settings->page_id,
            'page_name' => $settings->page_name,
            'verify_token' => $settings->verify_token,
            'has_access_token' => $settings->hasAccessToken(),
            'has_app_secret' => $settings->hasAppSecret(),
            'lead_source_id' => $settings->lead_source_id,
            'lead_status_id' => $settings->lead_status_id,
            'city_id' => $settings->city_id,
            'location_id' => $settings->location_id,
            'department_id' => $settings->department_id,
            'assigned_to' => $settings->assigned_to,
            'gender' => $settings->gender,
            'active' => (int) $settings->active,
        ];
    }
}
