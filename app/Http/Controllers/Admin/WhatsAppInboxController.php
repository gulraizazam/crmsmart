<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppSetting;
use App\Services\WhatsApp\WhatsAppInboxService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class WhatsAppInboxController extends Controller
{
    public function index()
    {
        if (! Gate::allows('whatsapp_manage')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $status = app(WhatsAppInboxService::class)->connectionStatus($accountId);
        $canSend = Gate::allows('whatsapp_send');
        $canSettings = Gate::allows('whatsapp_settings');

        return view('admin.whatsapp.inbox', compact('status', 'canSend', 'canSettings'));
    }

    public function settings()
    {
        if (! Gate::allows('whatsapp_settings')) {
            return abort(401);
        }

        $accountId = (int) Auth::user()->account_id;
        $settings = WhatsAppSetting::forAccount($accountId);
        $verifyToken = ($settings && $settings->verify_token) ? $settings->verify_token : Str::random(32);
        $webhookUrl = url('/api/whatsapp/webhook');

        return view('admin.whatsapp.settings', compact('settings', 'verifyToken', 'webhookUrl'));
    }
}
