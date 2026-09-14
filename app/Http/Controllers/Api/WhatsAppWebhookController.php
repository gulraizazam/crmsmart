<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsApp\WhatsAppCloudService;
use App\Services\WhatsApp\WhatsAppInboxService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        protected WhatsAppCloudService $cloud,
        protected WhatsAppInboxService $inbox
    ) {
    }

    public function handle(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->verify($request);
        }

        $raw = $request->getContent();
        if (! $this->cloud->signatureIsValid($raw, $request->header('X-Hub-Signature-256'))) {
            Log::warning('whatsapp.invalid_signature');
            return response('Invalid signature', 403);
        }

        try {
            $this->inbox->ingestWebhook($request->all());
        } catch (\Throwable $e) {
            Log::error('whatsapp.webhook_failed', ['error' => $e->getMessage()]);
        }

        return response('EVENT_RECEIVED', 200);
    }

    protected function verify(Request $request): Response
    {
        $hub = $request->query('hub');
        $mode = is_array($hub) ? ($hub['mode'] ?? '') : $request->query('hub_mode', $request->query('hub.mode', ''));
        $token = is_array($hub) ? ($hub['verify_token'] ?? '') : $request->query('hub_verify_token', $request->query('hub.verify_token', ''));
        $challengeValue = is_array($hub) ? ($hub['challenge'] ?? '') : $request->query('hub_challenge', $request->query('hub.challenge', ''));
        $challenge = $this->cloud->verifySubscription(
            (string) $mode,
            (string) $token,
            (string) $challengeValue
        );

        if ($challenge === null) {
            return response('Forbidden', 403);
        }

        return response($challenge, 200)->header('Content-Type', 'text/plain');
    }
}
