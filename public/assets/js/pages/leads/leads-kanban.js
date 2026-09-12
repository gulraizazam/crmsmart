var LeadsKanban = {
    pageSize: 20,
    filtersReady: false,
    columns: {},
    boardXhr: null,
    columnXhrs: {},
    drag: null,
    justDragged: false,

    init: function () {
        if (!$('#leads-kanban').length) {
            return;
        }
        if (typeof applyFilters === 'function') {
            applyFilters(null);
        }
        $(document).off('click.leadsKanbanReset', '#reset-filters').on('click.leadsKanbanReset', '#reset-filters', function (event) {
            event.preventDefault();
            if (typeof resetLeadKanbanFilters === 'function') {
                resetLeadKanbanFilters();
                return;
            }
            LeadsKanban.reload('filter_cancel');
        });
        this.reload();
    },

    currentType: function () {
        return (typeof lead_type !== 'undefined' && lead_type) ? lead_type : '';
    },

    payload: function (action, extra) {
        var filters = typeof collectLeadFilters === 'function'
            ? collectLeadFilters(action || '')
            : {};
        if (!action) {
            delete filters.filter;
        }
        var body = {
            type: this.currentType(),
            limit: this.pageSize,
            query: { search: filters }
        };
        return $.extend(body, extra || {});
    },

    abortPending: function () {
        if (this.boardXhr && this.boardXhr.readyState !== 4) {
            this.boardXhr.abort();
        }
        Object.keys(this.columnXhrs).forEach(function (id) {
            var xhr = LeadsKanban.columnXhrs[id];
            if (xhr && xhr.readyState !== 4) {
                xhr.abort();
            }
        });
        this.columnXhrs = {};
    },

    closeCardMenus: function () {
        $('body > .sneat-lead-action-menu-open').each(function () {
            var $menu = $(this);
            var $origin = $menu.data('origin');
            $menu.removeClass('sneat-lead-action-menu-open show').removeAttr('style');
            if ($origin && $origin.length) {
                $origin.append($menu);
            } else {
                $menu.remove();
            }
        });
        $('#leads-kanban .action-dots.show').removeClass('show');
    },

    reload: function (action) {
        var self = this;
        this.abortPending();
        this.closeCardMenus();
        var $board = $('#leads-kanban');
        $board.html('<div class="sneat-leads-kanban-boot">Loading leads…</div>');

        this.boardXhr = $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: route('admin.leads.kanban'),
            type: 'POST',
            data: this.payload(action || 'filter'),
            success: function (response) {
                if (!response || !response.status) {
                    $board.html('<div class="sneat-leads-kanban-boot sneat-leads-kanban-error">Could not load leads.</div>');
                    return;
                }
                var data = response.data || {};
                if (data.filter_values && data.filter_values.csr_users) {
                    window.csrUsers = data.filter_values.csr_users;
                }
                if (typeof permissions === 'undefined') {
                    window.permissions = data.permissions || {};
                } else {
                    permissions = data.permissions || permissions;
                }
                if (!self.filtersReady && typeof setFilters === 'function') {
                    var filterValues = data.filter_values || {};
                    if (!filterValues.lead_statuses || !Object.keys(filterValues.lead_statuses).length) {
                        filterValues.lead_statuses = {};
                        (data.columns || []).forEach(function (column) {
                            if (column && column.id) {
                                filterValues.lead_statuses[column.id] = column.name;
                            }
                        });
                    }
                    setFilters(filterValues, data.active_filters || {});
                    self.filtersReady = true;
                    if (window.SneatFilterPicker && typeof window.SneatFilterPicker.syncAll === 'function') {
                        window.SneatFilterPicker.syncAll();
                    }
                }
                self.renderBoard(data.columns || []);
            },
            error: function (xhr) {
                if (xhr.statusText === 'abort') {
                    return;
                }
                $board.html('<div class="sneat-leads-kanban-boot sneat-leads-kanban-error">Could not load leads.</div>');
            }
        });
    },

    renderBoard: function (columns) {
        var self = this;
        this.columns = {};
        var $board = $('#leads-kanban').empty();

        if (!columns.length) {
            $board.html('<div class="sneat-leads-kanban-boot">No lead statuses found.</div>');
            return;
        }

        columns.forEach(function (column) {
            self.columns[column.id] = {
                id: column.id,
                total: column.total || 0,
                offset: column.offset || (column.leads || []).length,
                hasMore: !!column.has_more,
                loading: false,
                locked: !!(column.is_arrived || column.is_converted)
            };
            $board.append(self.columnHtml(column));
            self.bindColumnScroll(column.id);
        });
    },

    columnTone: function (column) {
        if (column.is_converted) {
            return 'success';
        }
        if (column.is_arrived) {
            return 'info';
        }
        if (column.is_default) {
            return 'primary';
        }
        return 'warning';
    },

    columnHtml: function (column) {
        var tone = this.columnTone(column);
        var cards = (column.leads || []).map(function (lead) {
            return this.cardHtml(lead, column);
        }.bind(this)).join('');
        var empty = !cards
            ? '<div class="sneat-lead-card-empty">No leads</div>'
            : '';
        var more = column.has_more
            ? '<div class="sneat-leads-kanban-more">Scroll for more</div>'
            : '';
        var locked = (column.is_arrived || column.is_converted) ? '1' : '0';

        return '<section class="sneat-leads-kanban-col sneat-leads-kanban-col--' + tone + '" data-status-id="' + column.id + '" data-locked="' + locked + '">' +
            '<header class="sneat-leads-kanban-head">' +
                '<h4>' + this.escape(column.name) + '</h4>' +
                '<span class="sneat-leads-kanban-count" data-count>' + (column.total || 0) + '</span>' +
            '</header>' +
            '<div class="sneat-leads-kanban-list" data-list>' +
                cards + empty + more +
            '</div>' +
        '</section>';
    },

    canDrag: function (column) {
        var allowed = !!(window.permissions || permissions || {}).update_status;
        if (!allowed) {
            return false;
        }
        if (column && (column.is_arrived || column.is_converted || column.locked)) {
            return false;
        }
        return true;
    },

    cardHtml: function (lead, column) {
        var displayUrl = route('admin.leads.detail', { id: lead.lead_id });
        var initials = typeof sneatPatientInitials === 'function' ? sneatPatientInitials(lead.name) : '?';
        var tone = typeof sneatPatientAvatarTone === 'function' ? sneatPatientAvatarTone(lead.lead_id) : 'primary';
        var service = this.activeService(lead);
        var city = lead.city_id || 'No city';
        var centre = lead.location || 'No centre';
        var actionsHtml = typeof actions === 'function' ? actions(lead) : '';
        var childBadge = lead.child_status
            ? '<span class="sneat-lead-chip sneat-lead-chip--child">' + this.escape(lead.child_status) + '</span>'
            : '';
        var parentStatusId = (column && column.id) || lead.parent_status_id || '';
        var canDrag = this.canDrag(column || this.columns[parentStatusId] || {});
        var dragAttrs = canDrag
            ? ' draggable="true" class="sneat-lead-card is-draggable"'
            : ' class="sneat-lead-card"';

        return '<article' + dragAttrs + ' data-lead-id="' + lead.lead_id + '" data-parent-status-id="' + parentStatusId + '" data-view="' + displayUrl + '">' +
            '<div class="sneat-lead-card-top">' +
                '<div class="sneat-patient-cell">' +
                    '<span class="sneat-patient-avatar sneat-patient-avatar--' + tone + '" aria-hidden="true">' + initials + '</span>' +
                    '<div class="sneat-patient-copy">' +
                        '<a class="sneat-patient-name js-lead-view" href="javascript:void(0);" draggable="false">' + this.escape(lead.name || 'N/A') + '</a>' +
                        '<div class="sneat-patient-meta">' +
                            '<span class="sneat-patient-id">' + lead.lead_id + '</span>' +
                            '<span class="sneat-patient-sep" aria-hidden="true"></span>' +
                            (typeof phoneClip === 'function' ? phoneClip(lead) : this.escape(lead.phone || '')) +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="sneat-lead-card-actions" draggable="false">' + actionsHtml + '</div>' +
            '</div>' +
            '<div class="sneat-lead-card-meta">' +
                '<div class="sneat-lead-card-line">' + this.escape(city) + ' · ' + this.escape(centre) + '</div>' +
                (lead.department ? '<div class="sneat-lead-card-line">' + this.escape(lead.department) + '</div>' : '') +
                '<div class="sneat-lead-card-line sneat-lead-card-service">' + this.escape(service) + '</div>' +
                (lead.assigned_to_name ? '<div class="sneat-lead-card-line">Assigned: ' + this.escape(lead.assigned_to_name) + '</div>' : '') +
                (childBadge ? '<div class="sneat-lead-card-line">' + childBadge + '</div>' : '') +
                '<div class="sneat-lead-card-foot">' +
                    '<span>' + this.escape(lead.created_by || 'N/A') + '</span>' +
                    '<span>' + this.escape(lead.created_at || '') + '</span>' +
                '</div>' +
            '</div>' +
        '</article>';
    },

    activeService: function (lead) {
        if (lead.service_active) {
            var first = String(lead.service_active).split(',')[0];
            if (first) {
                return first;
            }
        }
        if (lead.service_id) {
            var any = String(lead.service_id).split(',')[0];
            if (any) {
                return any;
            }
        }
        return 'No service';
    },

    bindColumnScroll: function (statusId) {
        var self = this;
        var $list = this.columnEl(statusId).find('[data-list]');
        $list.off('scroll.leadsKanban').on('scroll.leadsKanban', function () {
            var el = this;
            if (el.scrollTop + el.clientHeight >= el.scrollHeight - 80) {
                self.loadMore(statusId);
            }
        });
    },

    columnEl: function (statusId) {
        return $('#leads-kanban').find('[data-status-id="' + statusId + '"]');
    },

    loadMore: function (statusId) {
        var self = this;
        var state = this.columns[statusId];
        if (!state || state.loading || !state.hasMore) {
            return;
        }

        state.loading = true;
        var $list = this.columnEl(statusId).find('[data-list]');
        $list.find('.sneat-leads-kanban-more').html('<span class="spinner-border spinner-border-sm" role="status"></span> Loading…');

        this.columnXhrs[statusId] = $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: route('admin.leads.kanban.column'),
            type: 'POST',
            data: this.payload('filter', {
                status_id: statusId,
                offset: state.offset
            }),
            success: function (response) {
                state.loading = false;
                if (!response || !response.status) {
                    $list.find('.sneat-leads-kanban-more').text('Could not load more');
                    return;
                }
                var data = response.data || {};
                state.offset = data.offset || state.offset;
                state.hasMore = !!data.has_more;
                state.total = data.total || state.total;
                self.columnEl(statusId).find('[data-count]').text(state.total);

                $list.find('.sneat-lead-card-empty, .sneat-leads-kanban-more').remove();
                (data.leads || []).forEach(function (lead) {
                    $list.append(self.cardHtml(lead, self.columns[statusId]));
                });
                if (state.hasMore) {
                    $list.append('<div class="sneat-leads-kanban-more">Scroll for more</div>');
                }
            },
            error: function (xhr) {
                state.loading = false;
                if (xhr.statusText === 'abort') {
                    return;
                }
                $list.find('.sneat-leads-kanban-more').text('Could not load more');
            }
        });
    },

    escape: function (value) {
        return $('<div>').text(value == null ? '' : value).html();
    },

    adjustCount: function (statusId, delta) {
        var state = this.columns[statusId];
        if (!state) {
            return;
        }
        state.total = Math.max(0, (state.total || 0) + delta);
        state.offset = Math.max(0, (state.offset || 0) + delta);
        this.columnEl(statusId).find('[data-count]').text(state.total);
    },

    moveCardToColumn: function ($card, fromId, toId) {
        var $target = this.columnEl(toId).find('[data-list]');
        var $source = this.columnEl(fromId).find('[data-list]');
        $target.find('.sneat-lead-card-empty').remove();
        $target.prepend($card);
        $card.attr('data-parent-status-id', toId);
        $card.find('.sneat-lead-chip--child').closest('.sneat-lead-card-line').remove();
        var targetLocked = this.columnEl(toId).data('locked') == 1;
        if (targetLocked) {
            $card.removeAttr('draggable').removeClass('is-draggable');
        } else if ((window.permissions || permissions || {}).update_status) {
            $card.attr('draggable', 'true').addClass('is-draggable');
        }
        if (!$source.find('.sneat-lead-card').length && !$source.find('.sneat-lead-card-empty').length) {
            $source.prepend('<div class="sneat-lead-card-empty">No leads</div>');
        }
        this.adjustCount(fromId, -1);
        this.adjustCount(toId, 1);
    },

    changeStatus: function (leadId, fromId, toId, $card) {
        var self = this;
        if ($card.data('moving')) {
            return;
        }
        $card.data('moving', true).addClass('is-drop-busy');

        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            url: route('admin.leads.storeleadstatus'),
            type: 'PUT',
            data: {
                id: leadId,
                lead_status_parent_id: toId
            },
            success: function (response) {
                $card.removeData('moving').removeClass('is-drop-busy');
                if (!response || !response.status) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response && response.message ? response.message : 'Could not update status.');
                    }
                    return;
                }
                self.moveCardToColumn($card, fromId, toId);
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Status updated successfully!');
                }
            },
            error: function (xhr) {
                $card.removeData('moving').removeClass('is-drop-busy');
                if (typeof errorMessage === 'function') {
                    errorMessage(xhr);
                } else if (typeof toastr !== 'undefined') {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not update status.');
                }
            }
        });
    }
};

window.reloadLeadsKanban = function (action) {
    LeadsKanban.reload(action);
};

$(document).on('show.bs.dropdown', '#leads-kanban .action-dots', function () {
    var $dropdown = $(this);
    var $toggle = $dropdown.children('[data-toggle="dropdown"]');
    var $menu = $dropdown.children('.dropdown-menu');
    if (!$menu.length || !$toggle.length) {
        return;
    }
    LeadsKanban.closeCardMenus();
    var offset = $toggle.offset();
    $menu.data('origin', $dropdown);
    $menu.addClass('sneat-lead-action-menu-open show');
    $('body').append($menu);
    var menuWidth = $menu.outerWidth() || 180;
    var left = Math.min(
        Math.max(8, offset.left + $toggle.outerWidth() - menuWidth),
        $(window).width() - menuWidth - 8
    );
    $menu.css({
        display: 'block',
        position: 'absolute',
        top: offset.top + $toggle.outerHeight() + 4,
        left: left,
        zIndex: 2000
    });
});

$(document).on('hidden.bs.dropdown', '#leads-kanban .action-dots', function () {
    var $dropdown = $(this);
    setTimeout(function () {
        var $menu = $('body > .sneat-lead-action-menu-open').filter(function () {
            return $(this).data('origin') && $(this).data('origin')[0] === $dropdown[0];
        });
        if (!$menu.length) {
            $menu = $dropdown.children('.dropdown-menu');
        }
        $menu.removeClass('sneat-lead-action-menu-open').removeAttr('style');
        $dropdown.append($menu);
    }, 0);
});

$(document).on('click', '#leads-kanban .sneat-lead-card, #leads-kanban .js-lead-view', function (event) {
    if (LeadsKanban.justDragged) {
        LeadsKanban.justDragged = false;
        return;
    }
    if ($(event.target).closest('.sneat-lead-card-actions, .dropdown-menu, .clipboard, .sneat-lead-action-menu-open').length) {
        return;
    }
    var $card = $(this).closest('.sneat-lead-card');
    var url = $card.data('view');
    if (url && typeof viewLead === 'function') {
        viewLead(url);
    }
});

$(document).on('dragstart', '#leads-kanban .sneat-lead-card[draggable="true"]', function (event) {
    if ($(event.target).closest('.sneat-lead-card-actions, .dropdown-menu, .clipboard, a').length && !$(event.target).hasClass('sneat-lead-card')) {
        event.preventDefault();
        return;
    }
    var $card = $(this);
    var native = event.originalEvent;
    LeadsKanban.drag = {
        leadId: $card.data('lead-id'),
        fromId: String($card.data('parent-status-id')),
        $card: $card
    };
    $card.addClass('is-dragging');
    if (native && native.dataTransfer) {
        native.dataTransfer.effectAllowed = 'move';
        native.dataTransfer.setData('text/plain', String($card.data('lead-id')));
    }
});

$(document).on('dragend', '#leads-kanban .sneat-lead-card', function () {
    $(this).removeClass('is-dragging');
    $('#leads-kanban .sneat-leads-kanban-col').removeClass('is-drop-target');
    LeadsKanban.justDragged = true;
    setTimeout(function () {
        LeadsKanban.justDragged = false;
        LeadsKanban.drag = null;
    }, 150);
});

$(document).on('dragover', '#leads-kanban .sneat-leads-kanban-col', function (event) {
    event.preventDefault();
    if (event.originalEvent && event.originalEvent.dataTransfer) {
        event.originalEvent.dataTransfer.dropEffect = 'move';
    }
    $('#leads-kanban .sneat-leads-kanban-col').removeClass('is-drop-target');
    $(this).addClass('is-drop-target');
});

$(document).on('dragleave', '#leads-kanban .sneat-leads-kanban-col', function (event) {
    var related = event.relatedTarget;
    if (related && $.contains(this, related)) {
        return;
    }
    $(this).removeClass('is-drop-target');
});

$(document).on('drop', '#leads-kanban .sneat-leads-kanban-col', function (event) {
    event.preventDefault();
    $('#leads-kanban .sneat-leads-kanban-col').removeClass('is-drop-target');
    var drag = LeadsKanban.drag;
    if (!drag || !drag.leadId) {
        return;
    }
    var toId = String($(this).data('status-id'));
    if (!toId || toId === String(drag.fromId)) {
        return;
    }
    LeadsKanban.changeStatus(drag.leadId, drag.fromId, toId, drag.$card);
});

jQuery(document).ready(function () {
    LeadsKanban.init();
});
