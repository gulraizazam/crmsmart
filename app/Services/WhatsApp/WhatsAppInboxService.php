<?php

namespace App\Services\WhatsApp;

use App\Models\Patients;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppInboxService
{
    public function __construct(protected WhatsAppCloudService $cloud)
    {
    }

    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '0') && strlen($digits) >= 10) {
            $digits = '92'.substr($digits, 1);
        } elseif (strlen($digits) === 10 && ! str_starts_with($digits, '92')) {
            $digits = '92'.$digits;
        }

        return $digits;
    }

    public function connectionStatus(int $accountId): array
    {
        $settings = WhatsAppSetting::forAccount($accountId);

        return [
            'connected' => $settings?->isConnected() ?? false,
            'active' => (bool) ($settings?->active ?? false),
            'display_phone' => $settings?->display_phone,
            'phone_number_id' => $settings?->phone_number_id,
            'has_token' => $settings?->hasAccessToken() ?? false,
            'webhook_url' => url('/api/whatsapp/webhook'),
        ];
    }

    public function listConversations(int $accountId, ?string $search = null): Collection
    {
        $query = WhatsAppConversation::query()
            ->with(['patient:id,name,phone,image_src'])
            ->where('account_id', $accountId)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('contact_name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereHas('patient', function ($patient) use ($search) {
                        $patient->where('name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    });
            });
        }

        return $query->limit(200)->get()->map(fn (WhatsAppConversation $row) => $this->transformConversation($row));
    }

    public function messages(WhatsAppConversation $conversation, ?int $afterId = null): Collection
    {
        $query = $conversation->messages()->orderBy('id');
        if ($afterId) {
            $query->where('id', '>', $afterId);
        } else {
            $query->limit(200);
        }

        return $query->get()->map(fn (WhatsAppMessage $row) => $this->transformMessage($row));
    }

    public function markRead(WhatsAppConversation $conversation): void
    {
        if ($conversation->unread_count > 0) {
            $conversation->unread_count = 0;
            $conversation->save();
        }
    }

    public function searchPatients(int $accountId, string $search): Collection
    {
        $search = trim($search);
        if (strlen($search) < 2) {
            return collect();
        }

        return Patients::query()
            ->select(['id', 'name', 'phone', 'image_src'])
            ->where('account_id', $accountId)
            ->where('user_type_id', 3)
            ->where('active', 1)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function (Patients $patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                    'normalized_phone' => self::normalizePhone($patient->phone),
                    'initials' => $this->initials($patient->name),
                ];
            });
    }

    public function startConversation(int $accountId, int $patientId): WhatsAppConversation
    {
        $patient = Patients::query()
            ->where('account_id', $accountId)
            ->where('user_type_id', 3)
            ->where('id', $patientId)
            ->firstOrFail();

        $phone = self::normalizePhone($patient->phone);
        if ($phone === '') {
            throw new \InvalidArgumentException('This patient does not have a WhatsApp number.');
        }

        $conversation = WhatsAppConversation::firstOrNew([
            'account_id' => $accountId,
            'phone' => $phone,
        ]);
        $conversation->patient_id = $patient->id;
        $conversation->contact_name = $patient->name;
        if (! $conversation->exists) {
            $conversation->last_message_at = now();
        }
        $conversation->save();

        return $conversation->load('patient:id,name,phone,image_src');
    }

    public function sendText(WhatsAppConversation $conversation, string $body, ?int $userId = null): WhatsAppMessage
    {
        $setting = $this->requireConnected($conversation->account_id);
        if (! $conversation->sessionIsOpen()) {
            throw new \RuntimeException('Free-form messages can only be sent within 24 hours of the patient’s last reply. Send an approved template instead.');
        }

        $message = $this->storeOutbound($conversation, 'text', $body, $userId);
        $result = $this->cloud->sendText($setting, $conversation->phone, $body);
        $this->applySendResult($message, $conversation, $result);

        return $message->fresh();
    }

    public function sendTemplate(WhatsAppConversation $conversation, string $name, string $language, array $params = [], ?int $userId = null): WhatsAppMessage
    {
        $setting = $this->requireConnected($conversation->account_id);
        $preview = 'Template: '.$name;
        if ($params !== []) {
            $preview .= ' ('.implode(', ', $params).')';
        }

        $message = $this->storeOutbound($conversation, 'template', $preview, $userId, [
            'template' => $name,
            'language' => $language,
            'params' => $params,
        ]);
        $result = $this->cloud->sendTemplate($setting, $conversation->phone, $name, $language, $params);
        $this->applySendResult($message, $conversation, $result);

        return $message->fresh();
    }

    public function templates(int $accountId): array
    {
        $setting = WhatsAppSetting::forAccount($accountId);
        if (! $setting || ! $setting->isConnected()) {
            return [];
        }

        return $this->cloud->listTemplates($setting);
    }

    public function ingestWebhook(array $payload): void
    {
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') !== 'messages') {
                    continue;
                }
                $value = $change['value'] ?? [];
                $phoneNumberId = (string) ($value['metadata']['phone_number_id'] ?? '');
                $setting = $phoneNumberId !== '' ? WhatsAppSetting::forPhoneNumberId($phoneNumberId) : null;
                if (! $setting) {
                    Log::info('whatsapp.webhook_unknown_number', ['phone_number_id' => $phoneNumberId]);
                    continue;
                }

                $contacts = collect($value['contacts'] ?? [])->keyBy('wa_id');

                foreach ($value['messages'] ?? [] as $incoming) {
                    $this->ingestIncoming($setting, $incoming, $contacts);
                }
                foreach ($value['statuses'] ?? [] as $status) {
                    $this->ingestStatus($status);
                }
            }
        }
    }

    public function transformConversation(WhatsAppConversation $row): array
    {
        $name = $row->patient->name ?? $row->contact_name ?? $row->phone;

        return [
            'id' => $row->id,
            'phone' => $row->phone,
            'display_phone' => $this->displayPhone($row->phone),
            'name' => $name,
            'initials' => $this->initials($name),
            'patient_id' => $row->patient_id,
            'patient_url' => $row->patient_id ? route('admin.patients.preview', $row->patient_id) : null,
            'last_message' => $row->last_message_preview,
            'last_direction' => $row->last_message_direction,
            'last_message_at' => optional($row->last_message_at)->toIso8601String(),
            'last_inbound_at' => optional($row->last_inbound_at)->toIso8601String(),
            'unread' => (int) $row->unread_count,
            'session_open' => $row->sessionIsOpen(),
        ];
    }

    public function transformMessage(WhatsAppMessage $row): array
    {
        return [
            'id' => $row->id,
            'direction' => $row->direction,
            'type' => $row->type,
            'body' => $row->body,
            'status' => $row->status,
            'error' => $row->error_message,
            'created_at' => optional($row->created_at)->toIso8601String(),
        ];
    }

    public function settingsPayload(WhatsAppSetting $settings, string $verifyToken): array
    {
        return [
            'phone_number_id' => $settings->phone_number_id,
            'waba_id' => $settings->waba_id,
            'display_phone' => $settings->display_phone,
            'verify_token' => $settings->verify_token ?: $verifyToken,
            'active' => (bool) $settings->active,
            'has_token' => $settings->hasAccessToken(),
            'has_app_secret' => $settings->hasAppSecret(),
            'connected' => $settings->isConnected(),
            'webhook_url' => url('/api/whatsapp/webhook'),
        ];
    }

    protected function ingestIncoming(WhatsAppSetting $setting, array $incoming, Collection $contacts): void
    {
        $waId = (string) ($incoming['from'] ?? '');
        $waMessageId = (string) ($incoming['id'] ?? '');
        if ($waId === '' || $waMessageId === '') {
            return;
        }
        if (WhatsAppMessage::where('wa_message_id', $waMessageId)->exists()) {
            return;
        }

        $phone = self::normalizePhone($waId);
        $profileName = $contacts[$waId]['profile']['name'] ?? null;
        $conversation = $this->findOrCreateConversation($setting->account_id, $phone, $profileName);
        $extracted = $this->extractIncomingBody($incoming);

        $message = WhatsAppMessage::create([
            'conversation_id' => $conversation->id,
            'account_id' => $setting->account_id,
            'direction' => 'inbound',
            'type' => $extracted['type'],
            'body' => $extracted['body'],
            'wa_message_id' => $waMessageId,
            'status' => 'delivered',
            'payload' => $incoming,
        ]);

        $conversation->contact_name = $profileName ?: $conversation->contact_name;
        $conversation->last_message_preview = $message->body;
        $conversation->last_message_direction = 'inbound';
        $conversation->last_message_at = now();
        $conversation->last_inbound_at = now();
        $conversation->unread_count = (int) $conversation->unread_count + 1;
        if (! $conversation->patient_id) {
            $conversation->patient_id = $this->matchPatientId($setting->account_id, $phone);
        }
        $conversation->save();
    }

    protected function ingestStatus(array $status): void
    {
        $waMessageId = (string) ($status['id'] ?? '');
        if ($waMessageId === '') {
            return;
        }

        $message = WhatsAppMessage::where('wa_message_id', $waMessageId)->first();
        if (! $message) {
            return;
        }

        $state = strtolower((string) ($status['status'] ?? ''));
        if (in_array($state, ['sent', 'delivered', 'read', 'failed'], true)) {
            $message->status = $state;
        }
        if (! empty($status['errors'][0]['title'])) {
            $message->error_message = $status['errors'][0]['title'];
            $message->status = 'failed';
        }
        $message->save();
    }

    protected function findOrCreateConversation(int $accountId, string $phone, ?string $name): WhatsAppConversation
    {
        $conversation = WhatsAppConversation::firstOrNew([
            'account_id' => $accountId,
            'phone' => $phone,
        ]);
        if (! $conversation->exists) {
            $conversation->patient_id = $this->matchPatientId($accountId, $phone);
            $conversation->contact_name = $name;
        } elseif ($name && ! $conversation->contact_name) {
            $conversation->contact_name = $name;
        }
        $conversation->save();

        return $conversation;
    }

    protected function matchPatientId(int $accountId, string $phone): ?int
    {
        $last10 = substr($phone, -10);
        if ($last10 === '') {
            return null;
        }

        $patient = Patients::query()
            ->where('account_id', $accountId)
            ->where('user_type_id', 3)
            ->where('phone', 'like', '%'.$last10)
            ->orderByDesc('id')
            ->first();

        return $patient?->id;
    }

    protected function extractIncomingBody(array $incoming): array
    {
        $type = (string) ($incoming['type'] ?? 'text');
        if ($type === 'text') {
            return ['type' => 'text', 'body' => (string) ($incoming['text']['body'] ?? '')];
        }
        if ($type === 'button') {
            return ['type' => 'button', 'body' => (string) ($incoming['button']['text'] ?? '[Button]')];
        }
        if ($type === 'interactive') {
            $body = $incoming['interactive']['button_reply']['title']
                ?? $incoming['interactive']['list_reply']['title']
                ?? '[Interactive message]';

            return ['type' => 'interactive', 'body' => (string) $body];
        }
        if (in_array($type, ['image', 'video', 'document', 'audio', 'sticker'], true)) {
            $caption = $incoming[$type]['caption'] ?? null;

            return ['type' => $type, 'body' => $caption ?: '['.Str::title($type).']'];
        }
        if ($type === 'location') {
            return ['type' => 'location', 'body' => '[Location]'];
        }

        return ['type' => $type, 'body' => '['.Str::title($type).']'];
    }

    protected function storeOutbound(WhatsAppConversation $conversation, string $type, string $body, ?int $userId, array $payload = []): WhatsAppMessage
    {
        $message = WhatsAppMessage::create([
            'conversation_id' => $conversation->id,
            'account_id' => $conversation->account_id,
            'direction' => 'outbound',
            'type' => $type,
            'body' => $body,
            'status' => 'pending',
            'sent_by' => $userId ?: Auth::id(),
            'payload' => $payload ?: null,
        ]);

        $conversation->last_message_preview = $body;
        $conversation->last_message_direction = 'outbound';
        $conversation->last_message_at = now();
        $conversation->save();

        return $message;
    }

    protected function applySendResult(WhatsAppMessage $message, WhatsAppConversation $conversation, array $result): void
    {
        if ($result['ok']) {
            $message->status = 'sent';
            $message->wa_message_id = $result['message_id'];
            $message->error_message = null;
        } else {
            $message->status = 'failed';
            $message->error_message = $result['error'];
        }
        $message->save();

        if (! $result['ok']) {
            $conversation->last_message_preview = $message->body;
            $conversation->save();
        }
    }

    protected function requireConnected(int $accountId): WhatsAppSetting
    {
        $setting = WhatsAppSetting::forAccount($accountId);
        if (! $setting || ! $setting->isConnected()) {
            throw new \RuntimeException('WhatsApp is not connected. Add Cloud API credentials in Settings.');
        }

        return $setting;
    }

    protected function initials(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return 'WA';
        }
        $parts = preg_split('/\s+/', $name) ?: [];
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $letters ?: 'WA';
    }

    protected function displayPhone(string $phone): string
    {
        if (str_starts_with($phone, '92') && strlen($phone) === 12) {
            return '+92 '.substr($phone, 2, 3).' '.substr($phone, 5);
        }

        return $phone ? '+'.$phone : '';
    }
}
