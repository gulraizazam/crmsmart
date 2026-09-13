@extends('admin.layouts.master')
@section('title', 'Meta Leads')
@section('content')

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-leads-page" id="kt_content">
        @include('admin.partials.breadcrumb', ['module' => 'Leads', 'title' => 'Meta Leads'])

        <div class="d-flex flex-column-fluid">
            <div class="container">
                <div class="card card-custom mb-6">
                    <div class="card-header py-3">
                        <div class="card-title">
                            <h3 class="card-label">Meta Lead Ads</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">When someone fills a Facebook or Instagram lead form, Meta calls this CRM. We fetch the answers and create (or update) a lead on the Kanban.</p>
                        <ol class="mb-4">
                            <li>Create a Meta App (Business) at developers.facebook.com. Add the <strong>Webhooks</strong> product.</li>
                            <li>In Business Manager, claim the Facebook Page and link Instagram. Create Lead Ads forms that ask for <strong>full name</strong> and <strong>phone</strong>.</li>
                            <li>App permissions: <code>leads_retrieval</code>, <code>pages_manage_ads</code>, <code>pages_show_list</code>, <code>pages_read_engagement</code>.</li>
                            <li>Create a Page access token, exchange it for a long-lived token, and paste it below with the Page ID and App Secret.</li>
                            <li>In the app Webhooks settings, subscribe the Page to <code>leadgen</code> using this callback URL and the verify token saved here.</li>
                            <li>In Development mode only testers’ forms arrive. Switch the app Live after App Review.</li>
                        </ol>
                        <div class="form-group">
                            <label>Webhook URL</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="meta_webhook_url" value="{{ $webhookUrl }}" readonly>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-light" id="copy_webhook_url">Copy</button>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted mb-0">Optional form questions we map automatically: city, gender, service / treatment. Anything else uses the defaults below.</p>
                    </div>
                </div>

                <div class="card card-custom mb-6">
                    <div class="card-header py-3">
                        <div class="card-title">
                            <h3 class="card-label">Connection &amp; defaults</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="meta_lead_settings_form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Page ID</label>
                                        <input type="text" name="page_id" id="page_id" class="form-control" value="{{ $settings->page_id ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Page name</label>
                                        <input type="text" name="page_name" id="page_name" class="form-control" value="{{ $settings->page_name ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Page access token {{ $settings && $settings->hasAccessToken() ? '(saved — leave blank to keep)' : '' }}</label>
                                        <input type="password" name="access_token" id="access_token" class="form-control" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>App secret {{ $settings && $settings->hasAppSecret() ? '(saved — leave blank to keep)' : '' }}</label>
                                        <input type="password" name="app_secret" id="app_secret" class="form-control" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Verify token</label>
                                        <input type="text" name="verify_token" id="verify_token" class="form-control" value="{{ $verifyToken }}" placeholder="Paste this into the Meta webhook subscription">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Lead source</label>
                                        <select name="lead_source_id" id="lead_source_id" class="form-control">
                                            <option value="">Social media (default)</option>
                                            @foreach($sources as $id => $name)
                                                <option value="{{ $id }}" @if(($settings->lead_source_id ?? null) == $id) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Default status</label>
                                        <select name="lead_status_id" id="lead_status_id" class="form-control">
                                            <option value="">Open / default</option>
                                            @foreach($statuses as $id => $name)
                                                <option value="{{ $id }}" @if(($settings->lead_status_id ?? null) == $id) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Default city (required if the form has no city)</label>
                                        <select name="city_id" id="city_id" class="form-control">
                                            <option value="">Select</option>
                                            @foreach($cities as $id => $name)
                                                <option value="{{ $id }}" @if(($settings->city_id ?? null) == $id) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Default centre</label>
                                        <select name="location_id" id="location_id" class="form-control">
                                            <option value="">None</option>
                                            @foreach($locations as $id => $name)
                                                <option value="{{ $id }}" @if(($settings->location_id ?? null) == $id) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Default department</label>
                                        <select name="department_id" id="department_id" class="form-control">
                                            <option value="">None</option>
                                            @foreach($departments as $id => $name)
                                                <option value="{{ $id }}" @if(($settings->department_id ?? null) == $id) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Assign to</label>
                                        <select name="assigned_to" id="assigned_to" class="form-control">
                                            <option value="">None</option>
                                            @foreach($users as $id => $name)
                                                <option value="{{ $id }}" @if(($settings->assigned_to ?? null) == $id) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Default gender</label>
                                        <select name="gender" id="gender" class="form-control">
                                            <option value="">Male if missing</option>
                                            <option value="1" @if(($settings->gender ?? null) == 1) selected @endif>Male</option>
                                            <option value="2" @if(($settings->gender ?? null) == 2) selected @endif>Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group pt-8">
                                        <label class="checkbox">
                                            <input type="checkbox" name="active" id="meta_active" value="1" @if($settings->active ?? false) checked @endif>
                                            <span></span>
                                            Receive Meta leads
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save settings</button>
                            <button type="button" class="btn btn-light-primary" id="meta_catchup_btn">Fetch missed leads</button>
                        </form>
                    </div>
                </div>

                <div class="card card-custom">
                    <div class="card-header py-3">
                        <div class="card-title">
                            <h3 class="card-label">Recent Meta events</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>When</th>
                                        <th>Leadgen ID</th>
                                        <th>Status</th>
                                        <th>CRM lead</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody id="meta_events_table">
                                    @forelse($events as $event)
                                        <tr>
                                            <td>{{ $event->created_at }}</td>
                                            <td>{{ $event->leadgen_id }}</td>
                                            <td>{{ $event->status }}</td>
                                            <td>{{ $event->lead_id ?: '—' }}</td>
                                            <td>{{ $event->error ?: '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No events yet. Use Meta’s Lead Ads Testing Tool after the webhook is subscribed.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            (function () {
                function csrf() {
                    return { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
                }

                $('#copy_webhook_url').on('click', function () {
                    var input = document.getElementById('meta_webhook_url');
                    input.select();
                    document.execCommand('copy');
                    toastr.success('Webhook URL copied.');
                });

                $('#meta_lead_settings_form').on('submit', function (e) {
                    e.preventDefault();
                    $.ajax({
                        url: route('admin.meta_leads.update'),
                        type: 'PUT',
                        headers: csrf(),
                        data: {
                            page_id: $('#page_id').val(),
                            page_name: $('#page_name').val(),
                            access_token: $('#access_token').val(),
                            app_secret: $('#app_secret').val(),
                            verify_token: $('#verify_token').val(),
                            lead_source_id: $('#lead_source_id').val(),
                            lead_status_id: $('#lead_status_id').val(),
                            city_id: $('#city_id').val(),
                            location_id: $('#location_id').val(),
                            department_id: $('#department_id').val(),
                            assigned_to: $('#assigned_to').val(),
                            gender: $('#gender').val(),
                            active: $('#meta_active').is(':checked') ? 1 : 0
                        },
                        success: function (response) {
                            if (!response.status) {
                                toastr.error(response.message || 'Could not save settings.');
                                return;
                            }
                            if (response.data && response.data.settings && response.data.settings.verify_token) {
                                $('#verify_token').val(response.data.settings.verify_token);
                            }
                            $('#access_token, #app_secret').val('');
                            toastr.success(response.message);
                        },
                        error: function (xhr) {
                            toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not save settings.');
                        }
                    });
                });

                $('#meta_catchup_btn').on('click', function () {
                    var $btn = $(this);
                    $btn.prop('disabled', true);
                    $.ajax({
                        url: route('admin.meta_leads.catch_up'),
                        type: 'POST',
                        headers: csrf(),
                        success: function (response) {
                            $btn.prop('disabled', false);
                            if (!response.status) {
                                toastr.error(response.message || 'Catch-up failed.');
                                return;
                            }
                            toastr.success(response.message);
                            window.location.reload();
                        },
                        error: function (xhr) {
                            $btn.prop('disabled', false);
                            toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Catch-up failed.');
                        }
                    });
                });
            })();
        </script>
    @endpush
@endsection
