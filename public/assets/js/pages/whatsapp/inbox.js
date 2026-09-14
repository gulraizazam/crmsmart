'use strict';

(function () {
    var apiBase = '/api/whatsapp/';
    var state = {
        conversations: [],
        activeId: null,
        lastMessageId: 0,
        sending: false,
        templates: [],
        pollTimer: null
    };

    $(document).ready(function () {
        bindEvents();
        loadConversations();
        loadTemplates();
        state.pollTimer = setInterval(function () {
            loadConversations(true);
            if (state.activeId) {
                loadMessages(state.activeId, true);
            }
        }, 6000);
    });

    function csrf() {
        return { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
    }

    function bindEvents() {
        $('#wa-search').on('input', debounce(function () {
            loadConversations();
        }, 300));

        $('#wa-new-chat').on('click', function () {
            $('#wa-newchat').addClass('is-on');
            $('#wa-patient-q').val('').focus();
            $('#wa-patient-results').empty();
        });

        $('#wa-newchat-close').on('click', function () {
            $('#wa-newchat').removeClass('is-on');
        });

        $('#wa-patient-q').on('input', debounce(function () {
            searchPatients($(this).val());
        }, 250));

        $('#wa-patient-results').on('click', '.wa-patient-hit', function () {
            startChat($(this).data('id'));
        });

        $('#wa-chat-list').on('click', '.wa-chat-item', function () {
            openChat($(this).data('id'));
        });

        $('#wa-back').on('click', function () {
            state.activeId = null;
            $('.wa-inbox').removeClass('is-chat');
            $('.wa-chat-item').removeClass('is-active');
            showPlaceholder();
        });

        $('#wa-composer').on('submit', function (e) {
            e.preventDefault();
            sendCurrent();
        });

        $('#wa-body').on('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendCurrent();
            }
        });

        $('#wa-body').on('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        $('#wa-template-send').on('click', sendTemplate);
    }

    function loadConversations(silent) {
        $.ajax({
            url: apiBase + 'conversations',
            type: 'GET',
            data: { q: $('#wa-search').val() },
            success: function (res) {
                if (!res.status) {
                    if (!silent) toastr.error(res.message || 'Could not load chats.');
                    return;
                }
                state.conversations = res.data.conversations || [];
                renderConversations();
            },
            error: function () {
                if (!silent) toastr.error('Could not load chats.');
            }
        });
    }

    function loadTemplates() {
        $.get(apiBase + 'templates', function (res) {
            if (!res.status) return;
            state.templates = res.data.templates || [];
            var $sel = $('#wa-template-name').empty();
            $sel.append('<option value="">Choose a template</option>');
            state.templates.forEach(function (tpl) {
                $sel.append($('<option/>', {
                    value: tpl.name,
                    text: tpl.name + (tpl.language ? ' (' + tpl.language + ')' : ''),
                    'data-language': tpl.language,
                    'data-params': tpl.param_count
                }));
            });
        });
    }

    function renderConversations() {
        var $list = $('#wa-chat-list').empty();
        if (!state.conversations.length) {
            $list.append(
                '<div class="wa-inbox__empty" style="min-height:220px">' +
                '<p>No chats yet. Start one with a patient.</p></div>'
            );
            return;
        }
        state.conversations.forEach(function (row) {
            var active = row.id === state.activeId ? ' is-active' : '';
            var unread = row.unread ? '<span class="wa-unread">' + row.unread + '</span>' : '';
            $list.append(
                '<div class="wa-chat-item' + active + '" data-id="' + row.id + '">' +
                    '<div class="wa-avatar">' + escapeHtml(row.initials || 'WA') + '</div>' +
                    '<div class="wa-chat-item__body">' +
                        '<div class="wa-chat-item__top">' +
                            '<div class="wa-chat-item__name">' + escapeHtml(row.name) + '</div>' +
                            '<div class="wa-chat-item__time">' + formatListTime(row.last_message_at) + '</div>' +
                        '</div>' +
                        '<div class="wa-chat-item__bottom">' +
                            '<div class="wa-chat-item__preview">' + escapeHtml(row.last_message || '') + '</div>' +
                            unread +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        });
    }

    function openChat(id) {
        state.activeId = parseInt(id, 10);
        state.lastMessageId = 0;
        $('.wa-inbox').addClass('is-chat');
        $('#wa-placeholder').addClass('d-none');
        $('#wa-thread-live').removeClass('d-none').addClass('d-flex');
        $('.wa-chat-item').removeClass('is-active');
        $('.wa-chat-item[data-id="' + id + '"]').addClass('is-active');
        loadMessages(id, false);
    }

    function loadMessages(id, silent) {
        $.ajax({
            url: apiBase + 'conversations/' + id + '/messages',
            type: 'GET',
            data: silent ? { after_id: state.lastMessageId } : {},
            success: function (res) {
                if (!res.status) return;
                var conv = res.data.conversation;
                var messages = res.data.messages || [];
                if (!silent) {
                    $('#wa-messages').empty();
                    state.lastMessageId = 0;
                }
                renderHeader(conv);
                if (!silent || messages.length) {
                    renderMessages(messages, !silent);
                }
                updateComposer(conv);
                if (!silent) {
                    loadConversations(true);
                }
            }
        });
    }

    function renderHeader(conv) {
        $('#wa-thread-name').text(conv.name || conv.display_phone);
        $('#wa-thread-sub').text(conv.display_phone || conv.phone || '');
        $('#wa-thread-avatar').text(conv.initials || 'WA');
        if (conv.patient_url) {
            $('#wa-patient-link').attr('href', conv.patient_url).removeClass('d-none');
        } else {
            $('#wa-patient-link').addClass('d-none');
        }
    }

    function renderMessages(messages, replace) {
        var $box = $('#wa-messages');
        var lastDay = $box.data('last-day') || '';
        if (replace) {
            lastDay = '';
            $box.removeData('last-day');
        }
        messages.forEach(function (msg) {
            var day = dayLabel(msg.created_at);
            if (day !== lastDay) {
                $box.append('<div class="wa-day"><span>' + day + '</span></div>');
                lastDay = day;
            }
            var dir = msg.direction === 'outbound' ? 'is-out' : 'is-in';
            $box.append(
                '<div class="wa-bubble-row ' + dir + '" data-id="' + msg.id + '">' +
                    '<div class="wa-bubble">' +
                        '<div class="wa-bubble__text">' + escapeHtml(msg.body || '') + '</div>' +
                        '<div class="wa-bubble__meta">' +
                            '<span>' + formatClock(msg.created_at) + '</span>' +
                            ticks(msg) +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
            if (msg.id > state.lastMessageId) {
                state.lastMessageId = msg.id;
            }
        });
        $box.data('last-day', lastDay);
        $box.scrollTop($box.prop('scrollHeight'));
    }

    function ticks(msg) {
        if (msg.direction !== 'outbound') return '';
        if (msg.status === 'failed') {
            return '<span class="wa-tick is-failed" title="' + escapeHtml(msg.error || 'Failed') + '">!</span>';
        }
        if (msg.status === 'read') {
            return '<span class="wa-tick is-read">✓✓</span>';
        }
        if (msg.status === 'delivered' || msg.status === 'sent') {
            return '<span class="wa-tick">✓✓</span>';
        }
        return '<span class="wa-tick">✓</span>';
    }

    function updateComposer(conv) {
        var canSend = window.waCanSend;
        var connected = window.waConnected;
        var sessionOpen = conv && conv.session_open;
        $('#wa-session-note').toggleClass('is-on', connected && !sessionOpen);
        $('#wa-template-bar').toggleClass('is-on', connected && !sessionOpen && state.templates.length > 0);
        $('#wa-body, #wa-send').prop('disabled', !canSend || !connected || !sessionOpen);
        if (!connected) {
            $('#wa-body').attr('placeholder', 'Connect WhatsApp in Settings to send messages');
        } else if (!sessionOpen) {
            $('#wa-body').attr('placeholder', 'Waiting for the patient to reply, or send a template');
        } else {
            $('#wa-body').attr('placeholder', 'Type a message');
        }
    }

    function sendCurrent() {
        if (state.sending || !state.activeId) return;
        if ($('#wa-template-bar').hasClass('is-on')) {
            sendTemplate();
            return;
        }
        var body = $.trim($('#wa-body').val());
        if (!body) return;
        state.sending = true;
        $.ajax({
            url: apiBase + 'conversations/' + state.activeId + '/messages',
            type: 'POST',
            headers: csrf(),
            data: { type: 'text', body: body },
            success: function (res) {
                state.sending = false;
                if (!res.status) {
                    toastr.error(res.message || 'Could not send.');
                    return;
                }
                $('#wa-body').val('').trigger('input');
                renderMessages([res.data.message], false);
                loadConversations(true);
            },
            error: function (xhr) {
                state.sending = false;
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not send.');
            }
        });
    }

    function sendTemplate() {
        if (state.sending || !state.activeId) return;
        var $opt = $('#wa-template-name option:selected');
        var name = $opt.val();
        if (!name) {
            toastr.error('Choose a WhatsApp template first.');
            return;
        }
        var params = [];
        var extra = $.trim($('#wa-template-params').val());
        if (extra) {
            params = extra.split(',').map(function (s) { return $.trim(s); }).filter(Boolean);
        }
        state.sending = true;
        $.ajax({
            url: apiBase + 'conversations/' + state.activeId + '/messages',
            type: 'POST',
            headers: csrf(),
            data: {
                type: 'template',
                template_name: name,
                template_language: $opt.data('language') || 'en',
                template_params: params
            },
            success: function (res) {
                state.sending = false;
                if (!res.status) {
                    toastr.error(res.message || 'Could not send template.');
                    return;
                }
                renderMessages([res.data.message], false);
                loadConversations(true);
            },
            error: function (xhr) {
                state.sending = false;
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not send template.');
            }
        });
    }

    function searchPatients(q) {
        if (!q || q.length < 2) {
            $('#wa-patient-results').html('<div class="text-muted p-4">Type a patient name or phone.</div>');
            return;
        }
        $.get(apiBase + 'patients', { q: q }, function (res) {
            var rows = (res.data && res.data.patients) || [];
            var $box = $('#wa-patient-results').empty();
            if (!rows.length) {
                $box.html('<div class="text-muted p-4">No patients found.</div>');
                return;
            }
            rows.forEach(function (p) {
                $box.append(
                    '<div class="wa-patient-hit" data-id="' + p.id + '">' +
                        '<div class="wa-avatar">' + escapeHtml(p.initials) + '</div>' +
                        '<div><div class="wa-chat-item__name">' + escapeHtml(p.name) + '</div>' +
                        '<div class="wa-chat-item__preview">' + escapeHtml(p.phone || 'No phone') + '</div></div>' +
                    '</div>'
                );
            });
        });
    }

    function startChat(patientId) {
        $.ajax({
            url: apiBase + 'conversations/start',
            type: 'POST',
            headers: csrf(),
            data: { patient_id: patientId },
            success: function (res) {
                if (!res.status) {
                    toastr.error(res.message || 'Could not start chat.');
                    return;
                }
                $('#wa-newchat').removeClass('is-on');
                loadConversations();
                openChat(res.data.conversation.id);
            },
            error: function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not start chat.');
            }
        });
    }

    function showPlaceholder() {
        $('#wa-thread-live').addClass('d-none').removeClass('d-flex');
        $('#wa-placeholder').removeClass('d-none');
    }

    function formatListTime(iso) {
        if (!iso) return '';
        var d = new Date(iso);
        if (isNaN(d.getTime())) return '';
        var now = new Date();
        if (d.toDateString() === now.toDateString()) {
            return formatClock(iso);
        }
        return ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + String(d.getFullYear()).slice(-2);
    }

    function formatClock(iso) {
        if (!iso) return '';
        var d = new Date(iso);
        if (isNaN(d.getTime())) return '';
        var h = d.getHours();
        var m = ('0' + d.getMinutes()).slice(-2);
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        if (!h) h = 12;
        return h + ':' + m + ' ' + ampm;
    }

    function dayLabel(iso) {
        var d = new Date(iso);
        var now = new Date();
        if (d.toDateString() === now.toDateString()) return 'Today';
        var y = new Date();
        y.setDate(now.getDate() - 1);
        if (d.toDateString() === y.toDateString()) return 'Yesterday';
        return d.toLocaleDateString();
    }

    function escapeHtml(str) {
        return String(str || '').replace(/[&<>"']/g, function (ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    function debounce(fn, wait) {
        var t;
        return function () {
            var args = arguments;
            var ctx = this;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }
})();
