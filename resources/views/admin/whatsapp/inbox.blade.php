@extends('admin.layouts.master')
@section('title', 'WhatsApp')
@section('body_class', 'wa-inbox-open')

@push('css')
    <link href="{{ asset('assets/css/whatsapp-inbox.css') }}?v=1" rel="stylesheet" type="text/css" />
@endpush

@section('content')

    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="wa-inbox" id="wa-inbox">
            <aside class="wa-inbox__sidebar">
                <div class="wa-inbox__side-head">
                    <div class="wa-inbox__brand">
                        <i class="lab la-whatsapp"></i>
                        <span>WhatsApp</span>
                    </div>
                    <div>
                        <button type="button" class="wa-inbox__icon-btn" id="wa-new-chat" title="New chat">
                            <i class="la la-comment"></i>
                        </button>
                        @if($canSettings)
                            <a href="{{ route('admin.whatsapp.settings') }}" class="wa-inbox__icon-btn" title="Settings">
                                <i class="la la-cog"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="wa-inbox__search">
                    <i class="la la-search"></i>
                    <input type="search" id="wa-search" placeholder="Search or start a new chat" autocomplete="off">
                </div>
                <div class="wa-inbox__banner {{ empty($status['connected']) ? 'is-on' : '' }}" id="wa-connect-banner">
                    WhatsApp Cloud API is not connected yet.
                    @if($canSettings)
                        <a href="{{ route('admin.whatsapp.settings') }}">Open settings</a>
                    @endif
                    to send and receive real messages.
                </div>
                <div class="wa-inbox__list" id="wa-chat-list"></div>

                <div class="wa-newchat" id="wa-newchat">
                    <div class="wa-newchat__head">
                        <button type="button" id="wa-newchat-close" aria-label="Close"><i class="la la-arrow-left"></i></button>
                        <strong>New chat</strong>
                    </div>
                    <div class="wa-inbox__search">
                        <i class="la la-search"></i>
                        <input type="search" id="wa-patient-q" placeholder="Search patients" autocomplete="off">
                    </div>
                    <div class="wa-inbox__list" id="wa-patient-results">
                        <div class="text-muted p-4">Type a patient name or phone.</div>
                    </div>
                </div>
            </aside>

            <section class="wa-inbox__thread">
                <div class="wa-inbox__placeholder" id="wa-placeholder">
                    <i class="lab la-whatsapp"></i>
                    <h3>WhatsApp for CRM</h3>
                    <p>Select a chat, or start a new one with a patient. Messages stay inside this screen.</p>
                </div>

                <div id="wa-thread-live" class="d-none flex-column" style="flex:1;min-height:0;">
                    <div class="wa-inbox__thread-head">
                        <button type="button" class="wa-inbox__icon-btn wa-inbox__back" id="wa-back"><i class="la la-arrow-left"></i></button>
                        <div class="wa-avatar" id="wa-thread-avatar">WA</div>
                        <div class="wa-inbox__thread-meta">
                            <div class="wa-inbox__thread-name" id="wa-thread-name">Chat</div>
                            <div class="wa-inbox__thread-sub" id="wa-thread-sub"></div>
                        </div>
                        <a href="#" target="_blank" class="wa-inbox__icon-btn d-none" id="wa-patient-link" title="Open patient">
                            <i class="la la-user"></i>
                        </a>
                    </div>
                    <div class="wa-inbox__messages" id="wa-messages"></div>
                    <div class="wa-session-note" id="wa-session-note">
                        WhatsApp only allows free-form replies within 24 hours of the patient’s last message. Send an approved template to start or restart the chat.
                    </div>
                    <div class="wa-template-bar" id="wa-template-bar">
                        <select id="wa-template-name"></select>
                        <input type="text" id="wa-template-params" placeholder="Template variables, comma separated">
                        <button type="button" class="btn btn-sm btn-success" id="wa-template-send">Send template</button>
                    </div>
                    <form class="wa-inbox__composer" id="wa-composer">
                        <textarea id="wa-body" rows="1" placeholder="Type a message" {{ $canSend && !empty($status['connected']) ? '' : 'disabled' }}></textarea>
                        <button type="submit" class="wa-send" id="wa-send" {{ $canSend && !empty($status['connected']) ? '' : 'disabled' }} title="Send">
                            <i class="la la-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('js')
    <script>
        window.waConnected = {{ !empty($status['connected']) ? 'true' : 'false' }};
        window.waCanSend = {{ $canSend ? 'true' : 'false' }};
    </script>
    <script src="{{ asset('assets/js/pages/whatsapp/inbox.js') }}?v=1"></script>
@endpush
