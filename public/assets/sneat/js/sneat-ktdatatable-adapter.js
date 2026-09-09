/**
 * Replaces Metronic KTDatatable with DataTables.net, Sneat-styled.
 * Keeps the existing KTDatatable call shape (columns.template, search(), reload())
 * so list pages and /api datatable endpoints stay unchanged.
 */
(function ($, window) {
    'use strict';

    if (typeof $ === 'undefined') {
        return;
    }

    var SPINNER =
        '<div class="sneat-dt-spinner" role="status" aria-live="polite">' +
        '<div class="sneat-dt-wave" aria-hidden="true">' +
        '<span></span><span></span><span></span><span></span><span></span>' +
        '</div>' +
        '<span>Loading...</span>' +
        '</div>';

    function csrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function columnField(col) {
        return col.field || col.data || null;
    }

    function isAutoWidth(width) {
        return width === undefined || width === null || width === 'auto';
    }

    function SneatDatatable($el, options) {
        this.$el = $el;
        this.options = options || {};
        this.queryExtras = {};
        this.dt = null;
        this._draw = 1;
        this._failHandlers = [];
        this._init();
    }

    SneatDatatable.prototype._readConfig = function () {
        var data = this.options.data || {};
        var source = data.source || {};
        var read = source.read || {};

        if (typeof source === 'string') {
            read = { url: source };
        }

        return {
            type: data.type || 'remote',
            url: read.url || '',
            method: (read.method || 'POST').toUpperCase(),
            headers: read.headers || { 'X-CSRF-TOKEN': csrfToken() },
            map: read.map,
            params: read.params,
            pageSize: data.pageSize || 30,
            localSource: Array.isArray(source) ? source : (data.type === 'local' ? source : null),
            paging: this.options.pagination !== false,
            sortable: this.options.sortable !== false,
            scrollY: this.options.layout && this.options.layout.height ? this.options.layout.height : null,
            scrollX: !!(this.options.layout && this.options.layout.scroll),
        };
    };

    SneatDatatable.prototype._buildTable = function () {
        var columns = this.options.columns || [];
        var heads = columns.map(function (col) {
            var align = col.textAlign ? ' style="text-align:' + col.textAlign + '"' : '';
            return '<th' + align + '>' + (col.title || col.field || '') + '</th>';
        }).join('');

        this.$el
            .removeClass('datatable datatable-bordered datatable-head-custom')
            .addClass('sneat-datatable')
            .empty()
            .html(
                '<div class="sneat-dt-wrap table-responsive">' +
                '<table class="table dataTable">' +
                '<thead><tr>' + heads + '</tr></thead>' +
                '<tbody></tbody>' +
                '</table></div>'
            );

        this.$table = this.$el.find('table');
    };

    SneatDatatable.prototype._dtColumns = function () {
        var tableSortable = this._readConfig().sortable;
        return (this.options.columns || []).map(function (col) {
            var def = {
                data: columnField(col),
                defaultContent: '',
                orderable: col.sortable === true && tableSortable,
                searchable: col.searchable !== false,
            };

            if (col.selector) {
                def.orderable = false;
                def.searchable = false;
            }

            if (!isAutoWidth(col.width) && typeof col.width === 'number') {
                def.width = col.width + 'px';
            } else if (!isAutoWidth(col.width) && typeof col.width === 'string' && col.width.indexOf('px') !== -1) {
                def.width = col.width;
            }

            if (col.textAlign) {
                def.className = (def.className ? def.className + ' ' : '') + 'text-' + col.textAlign;
            }

            if (col.overflow === 'visible') {
                def.className = (def.className ? def.className + ' ' : '') + 'text-nowrap';
            }

            if (typeof col.template === 'function') {
                def.render = function (data, type, row) {
                    if (type !== 'display' && type !== 'filter') {
                        return data;
                    }
                    var html = col.template(row, data);
                    return html === undefined || html === null ? '' : html;
                };
            }

            return def;
        });
    };

    SneatDatatable.prototype._ajaxPayload = function (d, cfg) {
        var perPage = d.length > 0 ? d.length : 30;
        var page = d.length > 0 ? Math.floor(d.start / d.length) + 1 : 1;
        var payload = {
            pagination: {
                page: page,
                pages: 7,
                perpage: perPage,
                total: 0,
            },
            query: $.extend(true, {}, this.queryExtras),
        };

        if (d.order && d.order.length && this.options.columns) {
            var orderCol = this.options.columns[d.order[0].column];
            if (orderCol && columnField(orderCol)) {
                payload.sort = {
                    field: columnField(orderCol),
                    sort: d.order[0].dir || 'asc',
                };
            }
        }

        if (typeof cfg.params === 'function') {
            $.extend(true, payload, cfg.params(payload));
        } else if (cfg.params && typeof cfg.params === 'object') {
            $.extend(true, payload, cfg.params);
        }

        d.pagination = payload.pagination;
        d.query = payload.query;
        if (payload.sort) {
            d.sort = payload.sort;
        } else {
            delete d.sort;
        }

        return d;
    };

    SneatDatatable.prototype._init = function () {
        var self = this;
        var cfg = this._readConfig();

        this._buildTable();

        if (typeof $.fn.DataTable === 'undefined') {
            window.console && console.error('Sneat datatable: DataTables is not loaded.');
            return;
        }

        $.fn.dataTable.ext.errMode = 'none';

        var dtOptions = {
            processing: true,
            serverSide: cfg.type === 'remote',
            searching: false,
            ordering: cfg.sortable,
            paging: cfg.paging,
            pageLength: cfg.pageSize,
            lengthMenu: [10, 25, 30, 50, 100],
            autoWidth: false,
            deferRender: true,
            order: [],
            columns: this._dtColumns(),
            language: {
                processing: SPINNER,
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                infoEmpty: 'Showing 0 to 0 of 0 entries',
                infoFiltered: '',
                zeroRecords: 'No matching records found',
                emptyTable: 'No data available',
                paginate: {
                    previous: '‹',
                    next: '›',
                    first: '«',
                    last: '»',
                },
            },
            dom:
                "<'row sneat-dt-toolbar align-items-center'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-right'i>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row sneat-dt-footer align-items-center'<'col-sm-12'p>>",
            drawCallback: function () {
                self.$el.find('[data-toggle="dropdown"]').dropdown && self.$el.find('[data-toggle="dropdown"]');
            },
        };

        if (cfg.scrollY) {
            dtOptions.scrollY = (typeof cfg.scrollY === 'number' ? cfg.scrollY + 'px' : cfg.scrollY);
            dtOptions.scrollCollapse = true;
        }
        if (cfg.scrollX) {
            dtOptions.scrollX = true;
        }

        if (cfg.type === 'remote') {
            dtOptions.ajax = {
                url: cfg.url,
                type: cfg.method,
                headers: cfg.headers,
                data: function (d) {
                    self._draw = d.draw;
                    return self._ajaxPayload(d, cfg);
                },
                dataSrc: function (json) {
                    json.draw = self._draw;
                    var total = 0;
                    if (json && json.meta && typeof json.meta.total !== 'undefined') {
                        total = parseInt(json.meta.total, 10) || 0;
                    } else if (json && Array.isArray(json.data)) {
                        total = json.data.length;
                    }
                    json.recordsTotal = total;
                    json.recordsFiltered = total;

                    var rows = json;
                    if (typeof cfg.map === 'function') {
                        rows = cfg.map(json);
                    } else if (json && typeof json.data !== 'undefined') {
                        rows = json.data;
                    }

                    return Array.isArray(rows) ? rows : [];
                },
                error: function (xhr) {
                    self._emitFail(xhr);
                },
            };
        } else {
            dtOptions.data = Array.isArray(cfg.localSource) ? cfg.localSource : [];
        }

        this.dt = this.$table.DataTable(dtOptions);
        this.$el.data('sneat-dt', this);
    };

    SneatDatatable.prototype._emitFail = function (xhr) {
        this._failHandlers.forEach(function (fn) {
            try {
                fn({}, xhr);
            } catch (e) {
                /* keep other handlers running */
            }
        });
        this.$el.trigger('datatable-on-ajax-fail', [xhr]);
    };

    SneatDatatable.prototype.reload = function () {
        if (this.dt) {
            this.dt.ajax.reload(null, false);
        }
        return this;
    };

    SneatDatatable.prototype.search = function (value, key) {
        var queryKey = key || 'generalSearch';
        this.queryExtras[queryKey] = value;
        if (this.dt) {
            var keepPage = !!(value && typeof value === 'object' && value.datatable_reload === 'reload');
            this.dt.ajax.reload(null, !keepPage ? true : false);
        }
        return this;
    };

    SneatDatatable.prototype.destroy = function () {
        if (this.dt) {
            this.dt.destroy(true);
            this.dt = null;
        }
        this.$el.removeData('sneat-dt').empty();
        return this;
    };

    SneatDatatable.prototype.on = function (event, handler) {
        if (event === 'datatable-on-ajax-fail' && typeof handler === 'function') {
            this._failHandlers.push(handler);
        } else if (typeof handler === 'function') {
            this.$el.on(event, handler);
        }
        return this;
    };

    SneatDatatable.prototype.setDataSourceParam = function (key, value) {
        this.queryExtras[key] = value;
        return this;
    };

    function plugin(options) {
        var $el = this.first();
        if (!$el.length) {
            return this;
        }

        if (typeof options === 'string') {
            var existing = $el.data('sneat-dt');
            if (!existing) {
                return this;
            }
            if (options === 'reload') {
                existing.reload();
            } else if (options === 'destroy') {
                existing.destroy();
            }
            return $el;
        }

        var previous = $el.data('sneat-dt');
        if (previous) {
            previous.destroy();
        }

        return new SneatDatatable($el, options || {});
    }

    $.fn.KTDatatable = plugin;
    $.fn.KTDatatable.defaults = $.fn.KTDatatable.defaults || {};
})(jQuery, window);
