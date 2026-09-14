<?php

namespace App\Services\WhatsApp;

use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudService
{
    protected string $graphVersion = 'v21.0';

    public function verifySubscription(string $mode, string $token, string $challenge): ?string
    {
        if ($mode !== 'subscribe' || $token === '') {
            return null;
        }

        $matches = WhatsAppSetting::query()
            ->where('active', 1)
            ->get()
            ->first(fn (WhatsAppSetting $row) => hash_equals((string) $row->verify_token, $token));

        return $matches ? $challenge : null;
    }

    public function signatureIsValid(string $rawBody, ?string $header, ?WhatsAppSetting $setting = null): bool
    {
        if (! $header || ! str_starts_with($header, 'sha256=')) {
            return false;
        }

        $provided = substr($header, 7);
        $settings = $setting ? collect([$setting]) : WhatsAppSetting::query()->where('active', 1)->get();

        foreach ($settings as $row) {
            $secret = $row->app_secret;
            if (! $secret) {
                continue;
            }
            $expected = hash_hmac('sha256', $rawBody, $secret);
            if (hash_equals($expected, $provided)) {
                return true;
            }
        }

        return false;
    }

    public function sendText(WhatsAppSetting $setting, string $to, string $body): array
    {
        return $this->postMessage($setting, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $body,
            ],
        ]);
    }

    public function sendTemplate(WhatsAppSetting $setting, string $to, string $name, string $language, array $bodyParams = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $name,
                'language' => ['code' => $language],
            ],
        ];

        if ($bodyParams !== []) {
            $payload['template']['components'] = [[
                'type' => 'body',
                'parameters' => array_map(fn ($text) => [
                    'type' => 'text',
                    'text' => (string) $text,
                ], $bodyParams),
            ]];
        }

        return $this->postMessage($setting, $payload);
    }

    public function sendMedia(WhatsAppSetting $setting, string $to, string $type, string $mediaId, ?string $caption = null, ?string $filename = null): array
    {
        $content = ['id' => $mediaId];
        if ($caption && in_array($type, ['image', 'video', 'document'], true)) {
            $content['caption'] = $caption;
        }
        if ($filename && $type === 'document') {
            $content['filename'] = $filename;
        }

        return $this->postMessage($setting, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => $type,
            $type => $content,
        ]);
    }

    public function uploadMedia(WhatsAppSetting $setting, string $absolutePath, string $mime, string $filename): array
    {
        if (! $setting->isConnected()) {
            return ['ok' => false, 'id' => null, 'error' => 'WhatsApp is not connected.'];
        }

        $response = Http::withToken($setting->access_token)
            ->timeout(60)
            ->attach('file', file_get_contents($absolutePath), $filename, ['Content-Type' => $mime])
            ->post($this->graphUrl($setting->phone_number_id.'/media'), [
                'messaging_product' => 'whatsapp',
                'type' => $mime,
            ]);

        $json = $response->json() ?? [];
        if (! $response->successful() || empty($json['id'])) {
            $error = $json['error']['message'] ?? ('WhatsApp media upload failed '.$response->status());
            Log::warning('whatsapp.media_upload_failed', ['error' => $json]);

            return ['ok' => false, 'id' => null, 'error' => $error];
        }

        return ['ok' => true, 'id' => (string) $json['id'], 'error' => null];
    }

    public function downloadMedia(WhatsAppSetting $setting, string $mediaId): ?array
    {
        if (! $setting->isConnected() || $mediaId === '') {
            return null;
        }

        $meta = Http::withToken($setting->access_token)
            ->timeout(30)
            ->get($this->graphUrl($mediaId));
        if (! $meta->successful()) {
            return null;
        }

        $url = (string) ($meta->json('url') ?? '');
        if ($url === '') {
            return null;
        }

        $bin = Http::withToken($setting->access_token)
            ->timeout(60)
            ->withHeaders(['User-Agent' => 'crmsmart-whatsapp'])
            ->get($url);
        if (! $bin->successful()) {
            return null;
        }

        return [
            'bytes' => $bin->body(),
            'mime' => (string) ($meta->json('mime_type') ?? $bin->header('Content-Type') ?? 'application/octet-stream'),
            'size' => (int) ($meta->json('file_size') ?? strlen($bin->body())),
        ];
    }

    public function markRead(WhatsAppSetting $setting, string $waMessageId): void
    {
        if (! $setting->isConnected() || $waMessageId === '') {
            return;
        }

        Http::withToken($setting->access_token)
            ->timeout(15)
            ->post($this->graphUrl($setting->phone_number_id.'/messages'), [
                'messaging_product' => 'whatsapp',
                'status' => 'read',
                'message_id' => $waMessageId,
            ]);
    }

    public function listTemplates(WhatsAppSetting $setting): array
    {
        if (! $setting->waba_id || ! $setting->access_token) {
            return [];
        }

        $response = Http::withToken($setting->access_token)
            ->timeout(20)
            ->get($this->graphUrl($setting->waba_id.'/message_templates'), [
                'limit' => 100,
                'fields' => 'name,language,status,category,components',
            ]);

        if (! $response->successful()) {
            Log::warning('whatsapp.templates_failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            return [];
        }

        $templates = [];
        foreach ($response->json('data', []) as $row) {
            if (strtoupper((string) ($row['status'] ?? '')) !== 'APPROVED') {
                continue;
            }
            $templates[] = [
                'name' => $row['name'] ?? '',
                'language' => $row['language'] ?? 'en',
                'category' => $row['category'] ?? '',
                'body' => $this->templateBodyPreview($row['components'] ?? []),
                'param_count' => $this->templateBodyParamCount($row['components'] ?? []),
            ];
        }

        return $templates;
    }

    protected function postMessage(WhatsAppSetting $setting, array $payload): array
    {
        if (! $setting->isConnected()) {
            return [
                'ok' => false,
                'message_id' => null,
                'error' => 'WhatsApp is not connected. Add Cloud API credentials in Settings.',
            ];
        }

        $response = Http::withToken($setting->access_token)
            ->timeout(20)
            ->post($this->graphUrl($setting->phone_number_id.'/messages'), $payload);

        $json = $response->json() ?? [];
        if (! $response->successful()) {
            $error = $json['error']['message'] ?? ('WhatsApp API error '.$response->status());
            Log::warning('whatsapp.send_failed', ['error' => $json]);

            return [
                'ok' => false,
                'message_id' => null,
                'error' => $error,
            ];
        }

        return [
            'ok' => true,
            'message_id' => $json['messages'][0]['id'] ?? null,
            'error' => null,
        ];
    }

    protected function graphUrl(string $path): string
    {
        return 'https://graph.facebook.com/'.$this->graphVersion.'/'.ltrim($path, '/');
    }

    protected function templateBodyPreview(array $components): string
    {
        foreach ($components as $component) {
            if (strtolower((string) ($component['type'] ?? '')) === 'body') {
                return (string) ($component['text'] ?? '');
            }
        }

        return '';
    }

    protected function templateBodyParamCount(array $components): int
    {
        $text = $this->templateBodyPreview($components);
        preg_match_all('/\{\{\d+\}\}/', $text, $matches);

        return count($matches[0] ?? []);
    }
}
