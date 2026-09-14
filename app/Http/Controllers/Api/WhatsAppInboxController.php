<?php

namespace App\Http\Controllers\Api;

use App\HelperModule\ApiHelper;
use App\Http\Controllers\Controller;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppSetting;
use App\Services\WhatsApp\WhatsAppInboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Throwable;

class WhatsAppInboxController extends Controller
{
    public function __construct(protected WhatsAppInboxService $inbox)
    {
    }

    public function status(): JsonResponse
    {
        if (! $this->canInbox()) {
            return $this->denied();
        }

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'OK', true, [
            'status' => $this->inbox->connectionStatus((int) Auth::user()->account_id),
            'can_send' => Gate::allows('whatsapp_send'),
            'can_settings' => Gate::allows('whatsapp_settings'),
        ]);
    }

    public function conversations(Request $request): JsonResponse
    {
        if (! $this->canInbox()) {
            return $this->denied();
        }

        $rows = $this->inbox->listConversations((int) Auth::user()->account_id, $request->query('q'));

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'OK', true, [
            'conversations' => $rows,
        ]);
    }

    public function messages(Request $request, int $id): JsonResponse
    {
        if (! $this->canInbox()) {
            return $this->denied();
        }

        $conversation = $this->conversation($id);
        if (! $conversation) {
            return ApiHelper::apiResponse(404, 'Conversation not found.', false);
        }

        $this->inbox->markRead($conversation);
        $messages = $this->inbox->messages($conversation, $request->integer('after_id') ?: null);

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'OK', true, [
            'conversation' => $this->inbox->transformConversation($conversation->fresh('patient:id,name,phone,image_src')),
            'messages' => $messages,
        ]);
    }

    public function send(Request $request, int $id): JsonResponse
    {
        if (! $this->canInbox() || ! Gate::allows('whatsapp_send')) {
            return $this->denied();
        }

        $data = $request->validate([
            'type' => 'nullable|in:text,template',
            'body' => 'nullable|string|max:4096',
            'template_name' => 'nullable|string|max:512',
            'template_language' => 'nullable|string|max:16',
            'template_params' => 'nullable|array',
            'template_params.*' => 'nullable|string|max:255',
        ]);

        $conversation = $this->conversation($id);
        if (! $conversation) {
            return ApiHelper::apiResponse(404, 'Conversation not found.', false);
        }

        try {
            $type = $data['type'] ?? 'text';
            if ($type === 'template') {
                $message = $this->inbox->sendTemplate(
                    $conversation,
                    (string) ($data['template_name'] ?? ''),
                    (string) ($data['template_language'] ?? 'en'),
                    array_values($data['template_params'] ?? []),
                    Auth::id()
                );
            } else {
                $body = trim((string) ($data['body'] ?? ''));
                if ($body === '') {
                    return ApiHelper::apiResponse(422, 'Message cannot be empty.', false);
                }
                $message = $this->inbox->sendText($conversation, $body, Auth::id());
            }
        } catch (Throwable $e) {
            return ApiHelper::apiResponse(422, $e->getMessage(), false);
        }

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'Sent.', true, [
            'message' => $this->inbox->transformMessage($message),
            'conversation' => $this->inbox->transformConversation($conversation->fresh('patient:id,name,phone,image_src')),
        ]);
    }

    public function start(Request $request): JsonResponse
    {
        if (! $this->canInbox()) {
            return $this->denied();
        }

        $data = $request->validate([
            'patient_id' => 'required|integer',
        ]);

        try {
            $conversation = $this->inbox->startConversation((int) Auth::user()->account_id, (int) $data['patient_id']);
        } catch (Throwable $e) {
            return ApiHelper::apiResponse(422, $e->getMessage(), false);
        }

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'Conversation ready.', true, [
            'conversation' => $this->inbox->transformConversation($conversation),
        ]);
    }

    public function patients(Request $request): JsonResponse
    {
        if (! $this->canInbox()) {
            return $this->denied();
        }

        $rows = $this->inbox->searchPatients((int) Auth::user()->account_id, (string) $request->query('q', ''));

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'OK', true, [
            'patients' => $rows,
        ]);
    }

    public function templates(): JsonResponse
    {
        if (! $this->canInbox()) {
            return $this->denied();
        }

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'OK', true, [
            'templates' => $this->inbox->templates((int) Auth::user()->account_id),
        ]);
    }

    public function showSettings(): JsonResponse
    {
        if (! Gate::allows('whatsapp_settings')) {
            return $this->denied();
        }

        $settings = WhatsAppSetting::firstOrNew(['account_id' => Auth::user()->account_id]);
        $verifyToken = $settings->verify_token ?: Str::random(32);

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'OK', true, [
            'settings' => $this->inbox->settingsPayload($settings, $verifyToken),
        ]);
    }

    public function saveSettings(Request $request): JsonResponse
    {
        if (! Gate::allows('whatsapp_settings')) {
            return $this->denied();
        }

        $data = $request->validate([
            'phone_number_id' => 'nullable|string|max:64',
            'waba_id' => 'nullable|string|max:64',
            'display_phone' => 'nullable|string|max:32',
            'access_token' => 'nullable|string',
            'verify_token' => 'nullable|string|max:150',
            'app_secret' => 'nullable|string',
            'active' => 'nullable|in:0,1',
        ]);

        $accountId = (int) Auth::user()->account_id;
        $settings = WhatsAppSetting::firstOrNew(['account_id' => $accountId]);
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

        return ApiHelper::apiResponse(config('constants.api_status.success'), 'WhatsApp settings saved.', true, [
            'settings' => $this->inbox->settingsPayload($settings->fresh(), $settings->verify_token),
        ]);
    }

    protected function conversation(int $id): ?WhatsAppConversation
    {
        return WhatsAppConversation::query()
            ->with('patient:id,name,phone,image_src')
            ->where('account_id', Auth::user()->account_id)
            ->where('id', $id)
            ->first();
    }

    protected function canInbox(): bool
    {
        return Gate::allows('whatsapp_manage');
    }

    protected function denied(): JsonResponse
    {
        return ApiHelper::apiResponse(config('constants.api_status.unauthorized'), 'You are not authorized to access this resource.', false);
    }
}
