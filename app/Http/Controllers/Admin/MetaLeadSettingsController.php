<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cities;
use App\Models\LeadDepartment;
use App\Models\LeadSources;
use App\Models\LeadStatuses;
use App\Models\Locations;
use App\Models\MetaLeadEvent;
use App\Models\MetaLeadSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class MetaLeadSettingsController extends Controller
{
    public function index()
    {
        if (! Gate::allows('leads_manage') && ! Gate::allows('lead_sources_manage')) {
            return abort(401);
        }

        $accountId = Auth::user()->account_id;
        $settings = MetaLeadSetting::forAccount($accountId);
        $events = MetaLeadEvent::query()
            ->where('account_id', $accountId)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $cities = Cities::where(['account_id' => $accountId, 'active' => 1])->orderBy('name')->pluck('name', 'id');
        $locations = Locations::where(['account_id' => $accountId, 'active' => 1])->orderBy('name')->pluck('name', 'id');
        $departments = LeadDepartment::forAccount($accountId)->where('active', 1)->orderBy('name')->pluck('name', 'id');
        $sources = LeadSources::where(['account_id' => $accountId, 'active' => 1])->orderBy('name')->pluck('name', 'id');
        $statuses = LeadStatuses::where(['account_id' => $accountId, 'active' => 1])
            ->where(function ($q) {
                $q->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->orderBy('sort_no')
            ->pluck('name', 'id');
        $users = User::where(['account_id' => $accountId, 'active' => 1])
            ->where('user_type_id', '!=', 3)
            ->orderBy('name')
            ->pluck('name', 'id');
        $webhookUrl = url('/api/meta/leads/webhook');
        $verifyToken = ($settings && $settings->verify_token) ? $settings->verify_token : Str::random(32);

        return view('admin.meta_leads.index', compact(
            'settings',
            'events',
            'cities',
            'locations',
            'departments',
            'sources',
            'statuses',
            'users',
            'webhookUrl',
            'verifyToken'
        ));
    }
}
