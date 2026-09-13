<?php

namespace App\Services\Meta;

use App\Models\Cities;
use App\Models\Locations;
use App\Models\MetaLeadEvent;
use App\Models\MetaLeadSetting;
use App\Models\Services;
use App\Services\Lead\LeadService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaLeadService
{
    protected string $graphVersion = 'v21.0';

    public function __construct(protected LeadService $leadService)
    {
    }

    public function verifySubscription(string $mode, string $token, string $challenge): ?string
    {
        if ($mode !== 'subscribe' || $token === '') {
            return null;
        }

        $matches = MetaLeadSetting::query()
            ->where('active', 1)
            ->get()
            ->first(fn (MetaLeadSetting $row) => hash_equals((string) $row->verify_token, $token));

        return $matches ? $challenge : null;
    }

    public function signatureIsValid(string $rawBody, ?string $header, ?MetaLeadSetting $setting = null): bool
    {
        if (! $header || ! str_starts_with($header, 'sha256=')) {
            return false;
        }

        $provided = substr($header, 7);
        $settings = $setting ? collect([$setting]) : MetaLeadSetting::query()->where('active', 1)->get();

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

    public function recordWebhook(array $payload): array
    {
        $ids = [];
        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') !== 'leadgen') {
                    continue;
                }
                $value = $change['value'] ?? [];
                $leadgenId = (string) ($value['leadgen_id'] ?? '');
                $pageId = (string) ($value['page_id'] ?? $entry['id'] ?? '');
                if ($leadgenId === '' || $pageId === '') {
                    continue;
                }

                $setting = MetaLeadSetting::forPage($pageId);
                $event = MetaLeadEvent::firstOrCreate(
                    ['leadgen_id' => $leadgenId],
                    [
                        'account_id' => $setting?->account_id,
                        'page_id' => $pageId,
                        'form_id' => $value['form_id'] ?? null,
                        'status' => $setting ? MetaLeadEvent::STATUS_RECEIVED : MetaLeadEvent::STATUS_IGNORED,
                        'error' => $setting ? null : 'Page is not connected or Meta Leads is inactive.',
                        'payload' => $value,
                    ]
                );
                $ids[] = $event->id;
            }
        }

        return $ids;
    }

    public function processEvent(int $eventId): MetaLeadEvent
    {
        $event = MetaLeadEvent::findOrFail($eventId);
        if (in_array($event->status, [MetaLeadEvent::STATUS_CREATED, MetaLeadEvent::STATUS_UPDATED, MetaLeadEvent::STATUS_DUPLICATE], true)) {
            return $event;
        }

        $setting = $event->page_id
            ? MetaLeadSetting::forPage($event->page_id)
            : MetaLeadSetting::forAccount((int) $event->account_id);

        if (! $setting || ! $setting->active) {
            $event->update([
                'status' => MetaLeadEvent::STATUS_IGNORED,
                'error' => 'No active Meta Lead settings for this Page.',
            ]);
            return $event->fresh();
        }

        try {
            $graphLead = $this->fetchLead($event->leadgen_id, $setting->access_token);
            $mapped = $this->mapFieldData($graphLead['field_data'] ?? [], $setting);
            $mapped['meta_lead_id'] = $event->leadgen_id;
            $mapped['form_id'] = $graphLead['form_id'] ?? $event->form_id;

            $result = $this->leadService->ingestExternalLead($mapped, (int) $setting->account_id, $setting);
            $event->update([
                'account_id' => $setting->account_id,
                'form_id' => $mapped['form_id'] ?? $event->form_id,
                'lead_id' => $result['lead']->id ?? $event->lead_id,
                'status' => $result['status'],
                'error' => $result['message'] ?? null,
                'payload' => array_merge($event->payload ?? [], ['graph' => $graphLead, 'mapped' => $mapped]),
            ]);
        } catch (\Throwable $e) {
            Log::error('meta.lead.process_failed', [
                'event_id' => $event->id,
                'leadgen_id' => $event->leadgen_id,
                'message' => $e->getMessage(),
            ]);
            $event->update([
                'status' => MetaLeadEvent::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
        }

        return $event->fresh();
    }

    public function catchUp(?int $accountId = null): array
    {
        $query = MetaLeadSetting::query()->where('active', 1)->whereNotNull('page_id')->whereNotNull('access_token');
        if ($accountId) {
            $query->where('account_id', $accountId);
        }

        $stats = ['forms' => 0, 'seen' => 0, 'queued' => 0];
        foreach ($query->get() as $setting) {
            $forms = $this->graphGet($setting->page_id . '/leadgen_forms', $setting->access_token, ['limit' => 50]);
            foreach ($forms['data'] ?? [] as $form) {
                $stats['forms']++;
                $leads = $this->graphGet($form['id'] . '/leads', $setting->access_token, ['limit' => 50]);
                foreach ($leads['data'] ?? [] as $lead) {
                    $stats['seen']++;
                    $leadgenId = (string) ($lead['id'] ?? '');
                    if ($leadgenId === '') {
                        continue;
                    }
                    $event = MetaLeadEvent::firstOrCreate(
                        ['leadgen_id' => $leadgenId],
                        [
                            'account_id' => $setting->account_id,
                            'page_id' => $setting->page_id,
                            'form_id' => $form['id'] ?? null,
                            'status' => MetaLeadEvent::STATUS_RECEIVED,
                            'payload' => $lead,
                        ]
                    );
                    if (in_array($event->status, [MetaLeadEvent::STATUS_RECEIVED, MetaLeadEvent::STATUS_FAILED], true)) {
                        $this->processEvent($event->id);
                        $stats['queued']++;
                    }
                }
            }
        }

        return $stats;
    }

    public function fetchLead(string $leadgenId, string $token): array
    {
        $response = $this->graphGet($leadgenId, $token);
        if (! empty($response['error']['message'])) {
            throw new \RuntimeException('Meta Graph: ' . $response['error']['message']);
        }
        if (empty($response['id'])) {
            throw new \RuntimeException('Meta Graph did not return a lead.');
        }

        return $response;
    }

    public function mapFieldData(array $fieldData, MetaLeadSetting $setting): array
    {
        $fields = [];
        foreach ($fieldData as $row) {
            $name = strtolower(trim(str_replace([' ', '-'], '_', (string) ($row['name'] ?? ''))));
            $value = is_array($row['values'] ?? null) ? trim((string) ($row['values'][0] ?? '')) : trim((string) ($row['values'] ?? ''));
            if ($name !== '') {
                $fields[$name] = $value;
            }
        }

        $first = $fields['first_name'] ?? $fields['firstname'] ?? '';
        $last = $fields['last_name'] ?? $fields['lastname'] ?? '';
        $name = $fields['full_name'] ?? $fields['fullname'] ?? $fields['name'] ?? trim($first . ' ' . $last);
        $phone = $fields['phone_number'] ?? $fields['phone'] ?? $fields['mobile'] ?? $fields['mobile_number'] ?? '';
        $email = $fields['email'] ?? $fields['email_address'] ?? null;
        $cityName = $fields['city'] ?? $fields['city_name'] ?? null;
        $genderRaw = $fields['gender'] ?? null;
        $serviceName = $fields['service'] ?? $fields['treatment'] ?? $fields['interested_in'] ?? null;

        $cityId = $this->matchCity($cityName, (int) $setting->account_id) ?: $setting->city_id;
        $service = $this->matchService($serviceName, (int) $setting->account_id);

        return [
            'name' => $name !== '' ? $name : 'Meta Lead',
            'phone' => $phone,
            'email' => $email,
            'city_id' => $cityId,
            'gender' => $this->parseGender($genderRaw) ?: $setting->gender,
            'location_id' => $setting->location_id,
            'department_id' => $setting->department_id,
            'assigned_to' => $setting->assigned_to,
            'lead_source_id' => $setting->lead_source_id ?: config('constants.lead_source_social_media'),
            'lead_status_id' => $setting->lead_status_id,
            'service_id' => $service['parent'] ?? null,
            'child_service_id' => $service['child'] ?? null,
        ];
    }

    protected function matchCity(?string $name, int $accountId): ?int
    {
        if (! $name) {
            return null;
        }
        $city = Cities::query()
            ->where('account_id', $accountId)
            ->where('active', 1)
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
            ->first();

        return $city?->id;
    }

    protected function matchService(?string $name, int $accountId): array
    {
        if (! $name) {
            return [];
        }
        $service = Services::query()
            ->where('account_id', $accountId)
            ->where('active', 1)
            ->whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
            ->first();
        if (! $service) {
            return [];
        }
        if ((int) $service->parent_id > 0) {
            return ['parent' => (int) $service->parent_id, 'child' => (int) $service->id];
        }

        return ['parent' => (int) $service->id, 'child' => null];
    }

    protected function parseGender(?string $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $value = strtolower(trim($value));
        if (in_array($value, ['1', 'male', 'm', 'man'], true)) {
            return 1;
        }
        if (in_array($value, ['2', 'female', 'f', 'woman'], true)) {
            return 2;
        }

        return null;
    }

    protected function graphGet(string $path, string $token, array $query = []): array
    {
        $url = 'https://graph.facebook.com/' . $this->graphVersion . '/' . ltrim($path, '/');
        $response = Http::timeout(20)->get($url, array_merge($query, ['access_token' => $token]));
        $json = $response->json();

        return is_array($json) ? $json : [];
    }
}
