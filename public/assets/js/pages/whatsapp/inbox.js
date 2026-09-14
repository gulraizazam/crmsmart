'use strict';

(function () {
    var apiBase = '/api/whatsapp/';
    var EMOJIS = '😀😁😂🤣😊😍😘😅😉😎🙂🤔😴😭😡👍👎🙏👏🔥❤️💯🎉✅❌'.split('');
    var state = {
        conversations: [],
        activeId: null,
        lastMessageId: 0,
        lastSyncedAt: null,
        sending: false,
        pendingFile: null,
        msgReq: 0,
        templates: [],
        pollTimer: null
    };

    $(document).ready(function () {
        bindEvents();
        renderEmojiPanel();
        loadConversations();
        loadTemplates();
        state.pollTimer = setInterval(function () {
            loadConversations(true);
            if (state.activeId) {
                loadMessages(state.activeId, true);
            }
        }, 4000);
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
        });

        $('#wa-send').on('click', function (e) {
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

        $('#wa-emoji-btn').on('click', function (e) {
            e.stopPropagation();
            $('#wa-attach-menu').removeClass('is-on');
            $('#wa-emoji-panel').toggleClass('is-on');
        });

        $('#wa-attach-btn').on('click', function (e) {
            e.stopPropagation();
            if ($(this).prop('disabled')) return;
            $('#wa-emoji-panel').removeClass('is-on');
            $('#wa-attach-menu').toggleClass('is-on');
        });

        $('#wa-pick-media').on('click', function () {
            pickFile('image/*,video/*');
        });

        $('#wa-pick-doc').on('click', function () {
            pickFile('.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar');
        });

        $('#wa-file').on('change', function () {
            var file = this.files && this.files[0];
            this.value = '';
            if (!file) return;
            if (file.size > 16 * 1024 * 1024) {
                toastr.error('File must be 16 MB or smaller.');
                return;
            }
            state.pendingFile = file;
            renderFilePreview();
            $('#wa-attach-menu').removeClass('is-on');
        });

        $('#wa-file-preview').on('click', '.wa-file-preview__clear', function () {
            clearPendingFile();
        });

        $('#wa-emoji-panel').on('click', 'button', function () {
            insertEmoji($(this).text());
        });

        $(document).on('click', function (e) {
            if (!$(e.target).closest('#wa-attach-menu, #wa-attach-btn').length) {
                $('#wa-attach-menu').removeClass('is-on');
            }
            if (!$(e.target).closest('#wa-emoji-panel, #wa-emoji-btn').length) {
                $('#wa-emoji-panel').removeClass('is-on');
            }
        });
    }

    function pickFile(accept) {
        $('#wa-file').attr('accept', accept);
        $('#wa-file').trigger('click');
        $('#wa-attach-menu').removeClass('is-on');
    }

    function renderEmojiPanel() {
        var $panel = $('#wa-emoji-panel').empty();
        EMOJIS.forEach(function (emoji) {
            $panel.append($('<button/>', { type: 'button', text: emoji }));
        });
    }

    function insertEmoji(emoji) {
        var el = document.getElementById('wa-body');
        if (!el || el.disabled) return;
        var start = el.selectionStart || el.value.length;
        var end = el.selectionEnd || el.value.length;
        el.value = el.value.slice(0, start) + emoji + el.value.slice(end);
        el.selectionStart = el.selectionEnd = start + emoji.length;
        $(el).trigger('input').focus();
        $('#wa-emoji-panel').removeClass('is-on');
    }

    function renderFilePreview() {
        var $box = $('#wa-file-preview').empty();
        if (!state.pendingFile) {
            $box.removeClass('is-on');
            return;
        }
        var file = state.pendingFile;
        var inner = '<div class="wa-file-preview__card">';
        if (file.type && file.type.indexOf('image/') === 0) {
            inner += '<img src="' + URL.createObjectURL(file) + '" alt="">';
        } else {
            inner += '<i class="la la-file"></i>';
        }
        inner += '<div><strong>' + escapeHtml(file.name) + '</strong><div>' + formatBytes(file.size) + '</div></div>';
        inner += '<button type="button" class="wa-file-preview__clear" title="Remove">&times;</button></div>';
        $box.html(inner).addClass('is-on');
    }

    function clearPendingFile() {
        state.pendingFile = null;
        renderFilePreview();
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
        state.lastSyncedAt = null;
        $('.wa-inbox').addClass('is-chat');
        $('#wa-placeholder').addClass('d-none');
        $('#wa-thread-live').removeClass('d-none').addClass('d-flex');
        $('.wa-chat-item').removeClass('is-active');
        $('.wa-chat-item[data-id="' + id + '"]').addClass('is-active');
        clearPendingFile();
        var cached = state.conversations.find(function (row) { return row.id === state.activeId; });
        if (cached) {
            renderHeader(cached);
            updateComposer(cached);
        }
        loadMessages(id, false);
    }

    function loadMessages(id, silent) {
        var reqId = ++state.msgReq;
        var data = {};
        if (silent) {
            data.after_id = state.lastMessageId;
            if (state.lastSyncedAt) {
                data.updated_since = state.lastSyncedAt;
            }
        }
        $.ajax({
            url: apiBase + 'conversations/' + id + '/messages',
            type: 'GET',
            data: data,
            success: function (res) {
                if (parseInt(id, 10) !== state.activeId || reqId !== state.msgReq) {
                    return;
                }
                if (!res.status) {
                    if (!silent) toastr.error(res.message || 'Could not load messages.');
                    return;
                }
                var conv = res.data.conversation;
                var messages = res.data.messages || [];
                if (res.data.synced_at) {
                    state.lastSyncedAt = res.data.synced_at;
                }
                if (!silent) {
                    $('#wa-messages').empty().removeData('last-day');
                    state.lastMessageId = 0;
                }
                renderHeader(conv);
                upsertMessages(messages, !silent);
                updateComposer(conv);
                if (!silent) {
                    loadConversations(true);
                }
            },
            error: function () {
                if (!silent && parseInt(id, 10) === state.activeId) {
                    toastr.error('Could not load messages.');
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

    function upsertMessages(messages, replace) {
        var $box = $('#wa-messages');
        var lastDay = $box.data('last-day') || '';
        var appended = false;
        if (replace) {
            lastDay = '';
            $box.removeData('last-day');
        }
        messages.forEach(function (msg) {
            var $existing = $box.find('.wa-bubble-row[data-id="' + msg.id + '"]');
            if ($existing.length) {
                $existing.find('.wa-ticks').replaceWith(ticks(msg));
                if (msg.id > state.lastMessageId) {
                    state.lastMessageId = msg.id;
                }
                return;
            }
            var day = dayLabel(msg.created_at);
            if (day !== lastDay) {
                $box.append('<div class="wa-day"><span>' + day + '</span></div>');
                lastDay = day;
            }
            $box.append(bubbleHtml(msg));
            appended = true;
            if (msg.id > state.lastMessageId) {
                state.lastMessageId = msg.id;
            }
        });
        $box.data('last-day', lastDay);
        if (replace || appended) {
            $box.scrollTop($box.prop('scrollHeight'));
        }
    }

    function bubbleHtml(msg) {
        var dir = msg.direction === 'outbound' ? 'is-out' : 'is-in';
        return (
            '<div class="wa-bubble-row ' + dir + '" data-id="' + msg.id + '">' +
                '<div class="wa-bubble">' +
                    bubbleBody(msg) +
                    '<div class="wa-bubble__meta">' +
                        '<span>' + formatClock(msg.created_at) + '</span>' +
                        ticks(msg) +
                    '</div>' +
                '</div>' +
            '</div>'
        );
    }

    function bubbleBody(msg) {
        var html = '';
        var caption = msg.body || '';
        if (msg.has_media) {
            var mime = (msg.mime || '').toLowerCase();
            var type = (msg.type || '').toLowerCase();
            var url = msg.media_url;
            if (type === 'image' || mime.indexOf('image/') === 0) {
                html += '<a class="wa-media-thumb" href="' + escapeHtml(url) + '" target="_blank" rel="noopener">' +
                    '<img src="' + escapeHtml(url) + '" alt=""></a>';
            } else if (type === 'video' || mime.indexOf('video/') === 0) {
                html += '<video class="wa-media-video" controls preload="metadata" src="' + escapeHtml(url) + '"></video>';
            } else if (type === 'audio' || mime.indexOf('audio/') === 0) {
                html += '<audio class="wa-media-audio" controls preload="metadata" src="' + escapeHtml(url) + '"></audio>';
            } else {
                html += '<a class="wa-doc" href="' + escapeHtml(url) + '" target="_blank" rel="noopener">' +
                    '<i class="la la-file"></i>' +
                    '<span>' + escapeHtml(msg.file_name || caption || 'Document') + '</span>' +
                    '</a>';
                if (caption && caption === (msg.file_name || '')) {
                    caption = '';
                }
            }
            if (caption && (caption.charAt(0) === '[' || caption === (msg.file_name || ''))) {
                if (type !== 'document') {
                    caption = caption.charAt(0) === '[' ? '' : caption;
                }
            }
        }
        if (caption) {
            html += '<div class="wa-bubble__text">' + escapeHtml(caption) + '</div>';
        } else if (!html) {
            html += '<div class="wa-bubble__text"></div>';
        }
        return html;
    }

    function ticks(msg) {
        if (msg.direction !== 'outbound') return '';
        if (msg.status === 'failed') {
            return '<span class="wa-ticks is-failed" title="' + escapeHtml(msg.error || 'Failed') + '">!</span>';
        }
        if (msg.status === 'read') {
            return '<span class="wa-ticks is-read" title="Read">✓✓</span>';
        }
        if (msg.status === 'delivered') {
            return '<span class="wa-ticks is-delivered" title="Delivered">✓✓</span>';
        }
        return '<span class="wa-ticks is-sent" title="Sent">✓</span>';
    }

    function updateComposer(conv) {
        var canSend = window.waCanSend;
        var connected = window.waConnected;
        var sessionOpen = conv && conv.session_open;
        var locked = !canSend || !connected || !sessionOpen;
        $('#wa-session-note').toggleClass('is-on', connected && !sessionOpen);
        $('#wa-template-bar').toggleClass('is-on', connected && !sessionOpen && state.templates.length > 0);
        $('#wa-body, #wa-send, #wa-attach-btn, #wa-emoji-btn').prop('disabled', locked);
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
        if ($('#wa-send').prop('disabled')) return;
        var body = $.trim($('#wa-body').val());
        var file = state.pendingFile;
        if (!body && !file) return;

        state.sending = true;
        $('#wa-send').prop('disabled', true);
        $('#wa-body').val('').trigger('input');
        var sendingFile = file;
        clearPendingFile();

        var ajax = {
            url: apiBase + 'conversations/' + state.activeId + '/messages',
            type: 'POST',
            headers: csrf(),
            success: onSendSuccess,
            error: function (xhr) {
                if (!sendingFile) {
                    $('#wa-body').val(body).trigger('input');
                } else {
                    state.pendingFile = sendingFile;
                    renderFilePreview();
                }
                onSendError(xhr);
            },
            complete: function () {
                state.sending = false;
                var conv = state.conversations.find(function (row) { return row.id === state.activeId; });
                if (conv) {
                    updateComposer(conv);
                } else {
                    $('#wa-send').prop('disabled', false);
                }
            }
        };

        if (sendingFile) {
            var form = new FormData();
            form.append('file', sendingFile);
            if (body) form.append('body', body);
            ajax.data = form;
            ajax.processData = false;
            ajax.contentType = false;
        } else {
            ajax.data = { type: 'text', body: body };
        }

        $.ajax(ajax);
    }

    function onSendSuccess(res) {
        if (!res.status) {
            toastr.error(res.message || 'Could not send.');
            return;
        }
        upsertMessages([res.data.message], false);
        if (res.data.conversation) {
            renderHeader(res.data.conversation);
            updateComposer(res.data.conversation);
        }
        loadConversations(true);
    }

    function onSendError(xhr) {
        toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not send.');
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
                upsertMessages([res.data.message], false);
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

    function formatBytes(n) {
        if (n < 1024) return n + ' B';
        if (n < 1024 * 1024) return (n / 1024).toFixed(1) + ' KB';
        return (n / (1024 * 1024)).toFixed(1) + ' MB';
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
