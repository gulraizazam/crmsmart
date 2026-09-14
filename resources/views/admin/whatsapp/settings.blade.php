@extends('admin.layouts.master')
@section('title', 'WhatsApp Settings')
@section('content')

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page" id="kt_content">
        @include('admin.partials.breadcrumb', ['module' => 'WhatsApp', 'title' => 'Settings'])

        <div class="d-flex flex-column-fluid">
            <div class="container">
                <div class="card card-custom mb-6">
                    <div class="card-header py-3">
                        <div class="card-title">
                            <h3 class="card-label">WhatsApp Cloud API</h3>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{ route('admin.whatsapp.index') }}" class="btn btn-light">Back to inbox</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">This inbox talks to WhatsApp through Meta’s official Cloud API. Staff chat from the CRM. Patients still use the normal WhatsApp app on their phone. WhatsApp Web is not embedded and cannot be.</p>
                        <ol class="mb-4">
                            <li>Create a Meta App and add the <strong>WhatsApp</strong> product at developers.facebook.com.</li>
                            <li>In WhatsApp &gt; API Setup, copy the <strong>Phone number ID</strong> and <strong>WhatsApp Business Account ID</strong>.</li>
                            <li>Generate a permanent access token with <code>whatsapp_business_messaging</code> and <code>whatsapp_business_management</code>.</li>
                            <li>Subscribe the app webhook to <code>messages</code> using the callback URL and verify token below.</li>
                            <li>Paste the App Secret so inbound webhooks can be verified.</li>
                        </ol>
                        <div class="form-group">
                            <label>Webhook URL</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="wa_webhook_url" value="{{ $webhookUrl }}" readonly>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-light" id="copy_wa_webhook_url">Copy</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-custom mb-6">
                    <div class="card-header py-3">
                        <div class="card-title">
                            <h3 class="card-label">Connection</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="wa_settings_form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone number ID</label>
                                        <input type="text" name="phone_number_id" id="phone_number_id" class="form-control" value="{{ $settings->phone_number_id ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>WhatsApp Business Account ID</label>
                                        <input type="text" name="waba_id" id="waba_id" class="form-control" value="{{ $settings->waba_id ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Display phone</label>
                                        <input type="text" name="display_phone" id="display_phone" class="form-control" value="{{ $settings->display_phone ?? '' }}" placeholder="+92 3xx xxxxxxx">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Verify token</label>
                                        <input type="text" name="verify_token" id="verify_token" class="form-control" value="{{ $verifyToken }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Access token {{ $settings && $settings->hasAccessToken() ? '(saved — leave blank to keep)' : '' }}</label>
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
                                    <div class="form-group d-flex align-items-center" style="padding-top: 28px;">
                                        <label class="checkbox mb-0">
                                            <input type="checkbox" id="wa_active" {{ !empty($settings->active) ? 'checked' : '' }}>
                                            <span></span>
                                            Enable this connection
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
        <script>
            (function () {
                function csrf() {
                    return { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
                }

                $('#copy_wa_webhook_url').on('click', function () {
                    var input = document.getElementById('wa_webhook_url');
                    input.select();
                    document.execCommand('copy');
                    toastr.success('Webhook URL copied.');
                });

                $('#wa_settings_form').on('submit', function (e) {
                    e.preventDefault();
                    $.ajax({
                        url: '/api/whatsapp/settings',
                        type: 'POST',
                        headers: csrf(),
                        data: {
                            phone_number_id: $('#phone_number_id').val(),
                            waba_id: $('#waba_id').val(),
                            display_phone: $('#display_phone').val(),
                            access_token: $('#access_token').val(),
                            app_secret: $('#app_secret').val(),
                            verify_token: $('#verify_token').val(),
                            active: $('#wa_active').is(':checked') ? 1 : 0
                        },
                        success: function (response) {
                            if (!response.status) {
                                toastr.error(response.message || 'Could not save settings.');
                                return;
                            }
                            $('#access_token, #app_secret').val('');
                            toastr.success(response.message);
                        },
                        error: function (xhr) {
                            toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not save settings.');
                        }
                    });
                });
            })();
        </script>
@endpush
