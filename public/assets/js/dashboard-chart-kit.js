/**
 * Dashboard chart kit.
 *
 * Shared ApexCharts renderers for the home dashboard plus the three-dots "Chart type" menu that lets a
 * viewer redraw any chart as bar, horizontal bar, line, area, pie, donut or stacked bar.
 *
 * - Overview charts register with DashChartKit.mount(id, payload, config).
 * - Legacy charts (centre-wise arrival, doctor-wise conversion) build through DashChartKit.create(el, options),
 *   which returns a handle with render()/destroy() so the existing reload code keeps working.
 *
 * The chosen type is remembered per user in this browser (localStorage); "Reset to default" forgets it.
 */
(function (window, document) {
    'use strict';

    var FONT = 'Public Sans, Segoe UI, sans-serif';
    var PALETTE = [
        '#696cff', '#71dd37', '#03c3ec', '#ffab00', '#ff3e1d',
        '#8592a3', '#233d4d', '#a8aaae', '#28c76f', '#00cfe8',
        '#ea5455', '#7367f0', '#ff9f43', '#00bad1'
    ];
    /** Above this many bars (categories x series) value labels turn into clutter, so they are hidden. */
    var BAR_LABEL_LIMIT = 30;
    /** Above this many slices the slice labels overlap, so only the legend and tooltip carry values. */
    var SLICE_LABEL_LIMIT = 12;
    var money = new Intl.NumberFormat('en-PK', { maximumFractionDigits: 0 });
    var compactMoney = new Intl.NumberFormat('en', { notation: 'compact', maximumFractionDigits: 1 });

    var TYPES = [
        { key: 'bar', label: 'Bar', icon: '<path d="M5 20v-7M10 20V5M15 20v-9M20 20V8"/>' },
        { key: 'horizontal', label: 'Horizontal', icon: '<path d="M4 6h15M4 12h9M4 18h12"/>' },
        { key: 'line', label: 'Line', icon: '<path d="M3 17l5-5 4 3 9-9"/>' },
        { key: 'area', label: 'Area', icon: '<path d="M3 19v-3l5-6 4 3 9-8v14z"/>' },
        { key: 'pie', label: 'Pie', icon: '<path d="M20.5 15.5A9 9 0 1 1 8.5 3.7"/><path d="M21 12a9 9 0 0 0-9-9v9z"/>' },
        { key: 'donut', label: 'Donut', icon: '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/>' },
        { key: 'stacked', label: 'Stacked bar', icon: '<rect x="4" y="9" width="6" height="11" rx="1"/><path d="M4 14h6"/><rect x="14" y="4" width="6" height="16" rx="1"/><path d="M14 11h6"/>' }
    ];
    var DOTS_ICON = '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" focusable="false">'
        + '<circle cx="12" cy="5" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="12" cy="19" r="1.75"/></svg>';
    var RESET_ICON = '<path d="M3 12a9 9 0 1 0 2.6-6.4L3 8"/><path d="M3 3v5h5"/>';

    var entries = {};
    var prefScope = null;

    /* ------------------------------------------------------------------ helpers */

    function num(value) {
        var n = Number(value);
        return isFinite(n) ? n : 0;
    }

    function total(list) {
        return list.reduce(function (sum, value) { return sum + num(value); }, 0);
    }

    function isType(value) {
        return TYPES.some(function (type) { return type.key === value; });
    }

    function svg(paths) {
        return '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"'
            + ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' + paths + '</svg>';
    }

    function moneyLabel(value) {
        return 'PKR ' + money.format(value || 0);
    }

    function plainValue(value) {
        return value;
    }

    function roundValue(value) {
        return Math.round(num(value));
    }

    /**
     * Axis names without a leading phrase that every label shares (e.g. "Smart Aesthetics "),
     * so the part that tells centres apart stays readable. Tooltips keep the full name.
     */
    function axisNames(labels) {
        var texts = labels.map(function (label) { return label == null ? '' : String(label); });
        if (texts.length < 2) {
            return texts;
        }
        var words = texts[0].split(' ');
        var prefix = '';
        for (var i = 1; i < words.length; i++) {
            var candidate = words.slice(0, i).join(' ') + ' ';
            var shared = texts.every(function (text) {
                return text.indexOf(candidate) === 0 && text.length > candidate.length;
            });
            if (!shared) {
                break;
            }
            prefix = candidate;
        }
        if (prefix.length < 8) {
            return texts;
        }
        return texts.map(function (text) { return text.substring(prefix.length); });
    }

    /** Smallest round axis maximum >= target that splits into `steps` round intervals (whole numbers at least). */
    function niceMax(target, steps) {
        var raw = target / steps;
        var magnitude = Math.pow(10, Math.floor(Math.log10(raw)));
        var factors = [1, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10];
        var step = magnitude * 10;
        for (var i = 0; i < factors.length; i++) {
            var candidate = factors[i] * magnitude;
            // Below 10 only whole steps, so count axes never show 1.5 or 2.5.
            if (candidate >= raw && (candidate >= 10 || candidate % 1 === 0)) {
                step = candidate;
                break;
            }
        }
        return Math.max(1, step) * steps;
    }

    function shorten(value, max) {
        var text = value == null ? '' : String(value);
        return text.length > max ? text.substring(0, max - 1) + '…' : text;
    }

    /** Deep copy of plain option objects that keeps formatter functions intact. */
    function cloneOptions(value) {
        if (Array.isArray(value)) {
            return value.map(cloneOptions);
        }
        if (value && typeof value === 'object' && Object.getPrototypeOf(value) === Object.prototype) {
            var out = {};
            Object.keys(value).forEach(function (key) {
                out[key] = cloneOptions(value[key]);
            });
            return out;
        }
        return value;
    }

    function hasData(payload) {
        return !!(payload && payload.labels && payload.labels.length > 0
            && ((payload.values && payload.values.length) || (payload.datasets && payload.datasets.length)));
    }

    function emptyState(el, message) {
        el.innerHTML = '<div class="dash-chart-empty">' + (message || 'No data for this period') + '</div>';
    }

    /** Series list for axis charts, whether the payload is {values} or {datasets}. */
    function datasetsOf(payload, fmt) {
        if (payload.datasets && payload.datasets.length) {
            return payload.datasets.map(function (set) {
                return { label: set.label, data: (set.data || []).map(num) };
            });
        }
        return [{ label: fmt.seriesName, data: (payload.values || []).map(num) }];
    }

    /**
     * Share payload for pie / donut charts.
     * Several series are shown as one slice per series (e.g. collection vs revenue) unless the chart asks for
     * one slice per label (e.g. appointments per centre, consultancies + treatments added together).
     */
    function circlePayload(payload, fmt) {
        if (payload.values) {
            return payload;
        }
        var sets = datasetsOf(payload, fmt);
        var slices;
        if (sets.length > 1 && fmt.pieBy !== 'labels') {
            var seriesColorList = seriesColors(fmt);
            slices = sets.map(function (set, index) {
                return { label: set.label, value: total(set.data), color: seriesColorList[index % seriesColorList.length] };
            });
        } else {
            slices = (payload.labels || []).map(function (label, index) {
                return {
                    label: label,
                    value: sets.reduce(function (sum, set) { return sum + (set.data[index] || 0); }, 0),
                    color: payload.colors && payload.colors.length ? payload.colors[index % payload.colors.length] : null
                };
            });
        }
        // Zero slices are invisible but still crowd the legend.
        var shown = slices.filter(function (slice) { return slice.value !== 0; });
        if (shown.length) {
            slices = shown;
        }
        return {
            labels: slices.map(function (slice) { return slice.label; }),
            values: slices.map(function (slice) { return slice.value; }),
            colors: slices[0] && slices[0].color ? slices.map(function (slice) { return slice.color; }) : null
        };
    }

    function seriesColors(fmt) {
        return fmt.colors && fmt.colors.length ? fmt.colors : PALETTE;
    }

    function sliceColors(payload) {
        return payload.colors && payload.colors.length ? payload.colors : PALETTE;
    }

    /** Evenly spaced tick indexes that always include the first and last label; null when all fit. */
    function tickLookup(count, maxTicks) {
        if (count <= maxTicks) {
            return null;
        }
        var lookup = {};
        var steps = maxTicks - 1;
        for (var t = 0; t <= steps; t++) {
            lookup[Math.round((t * (count - 1)) / steps)] = true;
        }
        return lookup;
    }

    /**
     * X-axis label settings for category charts (line, area, vertical bars): a shared prefix is dropped,
     * long names are tilted, and ticks are thinned so they fit the chart width.
     */
    function categoryAxis(labels, fmt) {
        var names = axisNames(labels);
        var tilt = labels.length > 2 && names.some(function (name) { return name.length > 10; });
        var width = fmt.width || 600;
        var maxTicks = tilt ? Math.floor(width / 26) : Math.min(12, Math.floor(width / 56));
        var ticks = tickLookup(labels.length, Math.max(4, maxTicks));
        return {
            tilt: tilt,
            labels: {
                rotate: tilt ? -45 : 0,
                rotateAlways: true,
                trim: false,
                hideOverlappingLabels: false,
                maxHeight: tilt ? 110 : undefined,
                formatter: function (value, _timestamp, opts) {
                    var index = opts && typeof opts.i === 'number' ? opts.i : labels.indexOf(value);
                    if (ticks && !ticks[index]) {
                        return '';
                    }
                    var name = index >= 0 && names[index] != null ? names[index] : value;
                    return tilt ? shorten(name, 20) : name;
                }
            }
        };
    }

    /** Tooltip title always shows the full category, even when its axis tick is thinned out. */
    function categoryAt(labels) {
        return function (value, opts) {
            var index = opts && typeof opts.dataPointIndex === 'number' ? opts.dataPointIndex : -1;
            return index >= 0 && labels[index] != null ? labels[index] : value;
        };
    }

    function baseHeight(el) {
        return parseInt(el.style.minHeight, 10) || el.clientHeight || 320;
    }

    /* ------------------------------------------------------------------ renderers */

    function baseOptions() {
        return {
            chart: {
                fontFamily: FONT,
                toolbar: { show: false },
                parentHeightOffset: 0,
                zoom: { enabled: false }
            },
            colors: PALETTE,
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            grid: {
                borderColor: '#d9dee3',
                strokeDashArray: 4,
                padding: { top: 8, right: 12, bottom: 0, left: 8 }
            },
            legend: {
                fontFamily: FONT,
                fontSize: '13px',
                labels: { colors: '#566a7f' },
                markers: { width: 10, height: 10, radius: 10 }
            },
            tooltip: {
                theme: 'dark',
                style: { fontFamily: FONT, fontSize: '13px' }
            },
            xaxis: {
                labels: {
                    rotate: 0,
                    rotateAlways: true,
                    style: { colors: '#a1acb8', fontSize: '12px', fontFamily: FONT }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: '#a1acb8', fontSize: '12px', fontFamily: FONT }
                }
            }
        };
    }

    function lineOptions(payload, fmt, area) {
        var labels = payload.labels;
        var sets = datasetsOf(payload, fmt);
        var axis = categoryAxis(labels, fmt);
        var base = baseOptions();
        var options = Object.assign(base, {
            chart: Object.assign(base.chart, { type: area ? 'area' : 'line', height: fmt.height, animations: { enabled: true } }),
            colors: seriesColors(fmt),
            series: sets.map(function (set) {
                return { name: set.label, data: set.data };
            }),
            xaxis: Object.assign(base.xaxis, {
                categories: labels,
                labels: Object.assign({}, base.xaxis.labels, axis.labels)
            }),
            yaxis: Object.assign(base.yaxis, {
                min: 0,
                forceNiceScale: true,
                labels: {
                    style: base.yaxis.labels.style,
                    formatter: fmt.money ? function (v) { return money.format(v); } : roundValue
                }
            }),
            markers: area ? { size: 0, hover: { size: 5 } } : { size: 4, strokeWidth: 2, hover: { size: 6 } },
            tooltip: Object.assign(base.tooltip, {
                x: { formatter: categoryAt(labels) },
                y: { formatter: fmt.money ? moneyLabel : plainValue }
            })
        });
        if (axis.tilt) {
            // The first point sits on the left edge, so its tilted name needs room to the left.
            options.grid = Object.assign({}, base.grid, { padding: { top: 8, right: 12, bottom: 0, left: 40 } });
        }
        if (area) {
            options.stroke = { curve: 'smooth', width: 2 };
            options.fill = {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] }
            };
        }
        return options;
    }

    function barOptions(payload, fmt, mode) {
        var horizontal = !!mode.horizontal;
        var stacked = !!mode.stacked;
        var moneyAxis = !!fmt.money;
        var labels = payload.labels || [];
        var sets = datasetsOf(payload, fmt);
        var multi = sets.length > 1;
        var categoryCount = labels.length || 1;
        var labelColors = payload.colors && payload.colors.length ? payload.colors : null;
        var distributed = !stacked && !multi && !!labelColors;
        // Stacked segments share one column, so only the number of columns matters for crowding.
        var showValues = stacked ? categoryCount <= 20 : categoryCount * sets.length <= BAR_LABEL_LIMIT;
        var manyRows = horizontal && categoryCount > 14;
        var names = axisNames(labels);
        var longest = names.reduce(function (max, name) {
            return Math.max(max, name.length);
        }, 0);
        var formatValue = function (val) {
            if (!num(val)) {
                return '';
            }
            return moneyAxis ? money.format(val) : String(roundValue(val));
        };
        // Apex centres a horizontal bar's label on the bar end; shift it by half the widest label so it
        // starts just past the bar instead of covering the rounded tip (~6.6px per 11px glyph).
        var widestValue = sets.reduce(function (max, set) {
            return set.data.reduce(function (inner, val) { return Math.max(inner, formatValue(val).length); }, max);
        }, 0);
        var valueWidth = Math.round(widestValue * 6.6);
        var nameOf = function (value, index) {
            if (index < 0) {
                index = labels.indexOf(value);
            }
            return index >= 0 && names[index] != null ? names[index] : value;
        };

        var barHeightPct = categoryCount <= 1 ? '26%' : categoryCount <= 3 ? '38%' : categoryCount <= 6 ? '50%' : '58%';
        var columnWidthPct = categoryCount <= 2 ? '28%' : categoryCount <= 4 ? '40%' : '52%';
        var chartHeight = fmt.height;
        if (horizontal) {
            chartHeight = manyRows
                ? Math.min(Math.max(fmt.height, categoryCount * 14 + 110), 640)
                : Math.max(240, Math.min(fmt.height, categoryCount * 54 + 110));
        }

        var base = baseOptions();
        var options = Object.assign(base, {
            chart: Object.assign(base.chart, {
                type: 'bar',
                height: chartHeight,
                stacked: stacked
            }),
            colors: distributed ? labelColors : seriesColors(fmt),
            series: sets.map(function (set) {
                return { name: set.label, data: set.data };
            }),
            plotOptions: {
                bar: {
                    horizontal: horizontal,
                    borderRadius: 6,
                    columnWidth: columnWidthPct,
                    barHeight: barHeightPct,
                    distributed: distributed,
                    dataLabels: { position: 'top' }
                }
            },
            dataLabels: {
                enabled: !stacked && showValues,
                // Values sit just past the end of each bar (above it when vertical) rather than on its tip.
                textAnchor: horizontal ? 'start' : 'middle',
                offsetX: horizontal ? Math.round(valueWidth / 2) + 6 : 0,
                offsetY: horizontal ? 0 : -20,
                style: {
                    fontSize: '11px',
                    fontWeight: 600,
                    fontFamily: FONT,
                    colors: ['#566a7f']
                },
                background: { enabled: false },
                formatter: formatValue
            },
            xaxis: Object.assign(base.xaxis, {
                categories: labels,
                // Vertical bars: centre / service names are tilted rather than cut down to "Sma…".
                labels: Object.assign({}, base.xaxis.labels, horizontal ? { trim: false } : categoryAxis(labels, fmt).labels)
            }),
            yaxis: Object.assign(base.yaxis, {
                labels: {
                    style: base.yaxis.labels.style,
                    formatter: moneyAxis && !horizontal ? function (v) { return money.format(v); } : undefined
                }
            }),
            legend: Object.assign(base.legend, { show: stacked || multi }),
            tooltip: Object.assign(base.tooltip, {
                x: { formatter: categoryAt(labels) },
                y: { formatter: moneyAxis ? moneyLabel : plainValue }
            })
        });

        if (horizontal) {
            // Room for the longest name plus a gap, so names never run into the bars.
            var labelWidth = Math.min(220, Math.max(110, Math.round(longest * 7) + 28));
            var rowTicks = manyRows ? tickLookup(categoryCount, Math.max(8, Math.floor((chartHeight - 110) / 18))) : null;
            options.grid = Object.assign({}, base.grid, {
                padding: { top: 8, right: showValues ? Math.max(28, valueWidth + 16) : 28, bottom: 4, left: 16 }
            });
            options.yaxis = Object.assign({}, base.yaxis, {
                labels: {
                    show: true,
                    align: 'right',
                    minWidth: labelWidth,
                    maxWidth: labelWidth,
                    offsetX: 0,
                    trim: false,
                    style: base.yaxis.labels.style,
                    formatter: function (value) {
                        var index = labels.indexOf(value);
                        if (rowTicks && index >= 0 && !rowTicks[index]) {
                            return '';
                        }
                        return nameOf(value, index);
                    }
                }
            });
            if (showValues) {
                // Headroom past the longest bar, otherwise Apex tucks its value label back inside the bar.
                var largest = sets.reduce(function (max, set) {
                    return set.data.reduce(function (inner, val) { return Math.max(inner, val); }, max);
                }, 0);
                if (largest > 0) {
                    // Horizontal bars take the value range from yaxis but the tick count from xaxis.
                    options.yaxis.max = niceMax(largest * 1.18, 4);
                    options.yaxis.min = 0;
                    options.xaxis.tickAmount = 4;
                }
            }
            if (moneyAxis) {
                // Compact ticks (300K, 1.2M) so the value axis never overlaps in narrow cards.
                options.xaxis.labels.formatter = function (v) { return compactMoney.format(num(v)); };
            }
        }
        if (stacked) {
            // Segment totals sit inside each coloured segment.
            options.dataLabels.enabled = showValues;
            options.dataLabels.textAnchor = 'middle';
            options.dataLabels.offsetX = 0;
            options.dataLabels.offsetY = 0;
            options.dataLabels.style = Object.assign({}, options.dataLabels.style, { colors: ['#fff'] });
            options.plotOptions.bar.dataLabels = { position: 'center' };
        }
        return options;
    }

    function circleOptions(payload, type, fmt) {
        var moneyAxis = !!fmt.money;
        var values = (payload.values || []).map(num);
        var base = baseOptions();
        var options = Object.assign(base, {
            chart: Object.assign(base.chart, { type: type, height: fmt.height }),
            labels: payload.labels,
            series: values,
            colors: sliceColors(payload),
            stroke: { width: type === 'polarArea' ? 1 : 0 },
            legend: Object.assign(base.legend, {
                position: 'bottom',
                formatter: function (seriesName, opts) {
                    var value = opts.w.globals.series[opts.seriesIndex];
                    var formatted = moneyAxis ? money.format(value) : String(roundValue(value));
                    return seriesName + ': ' + formatted;
                }
            }),
            plotOptions: {
                pie: {
                    donut: { size: type === 'donut' ? '68%' : '0%' }
                },
                polarArea: {
                    rings: { strokeWidth: 0 },
                    spokes: { connectorColors: '#d9dee3' }
                }
            },
            dataLabels: {
                enabled: values.length <= SLICE_LABEL_LIMIT,
                style: { fontSize: '12px', fontFamily: FONT },
                formatter: function (_percent, opts) {
                    var value = opts.w.config.series[opts.seriesIndex];
                    return moneyAxis ? money.format(value) : roundValue(value);
                }
            },
            tooltip: Object.assign(base.tooltip, {
                y: { formatter: moneyAxis ? moneyLabel : roundValue }
            })
        });
        if (type === 'polarArea') {
            options.yaxis = {
                show: false,
                labels: { show: false },
                axisTicks: { show: false },
                axisBorder: { show: false }
            };
        }
        return options;
    }

    function radialOptions(payload, fmt) {
        var moneyAxis = !!fmt.money;
        var rawValues = (payload.values || []).map(num);
        var sum = total(rawValues) || 1;
        var base = baseOptions();
        return Object.assign(base, {
            chart: Object.assign(base.chart, { type: 'radialBar', height: fmt.height }),
            labels: payload.labels,
            series: rawValues.map(function (value) {
                return Math.round((value / sum) * 1000) / 10;
            }),
            colors: sliceColors(payload),
            legend: Object.assign(base.legend, {
                show: true,
                position: 'bottom',
                formatter: function (seriesName, opts) {
                    var value = rawValues[opts.seriesIndex] || 0;
                    return seriesName + ': ' + (moneyAxis ? money.format(value) : String(Math.round(value)));
                }
            }),
            plotOptions: {
                radialBar: {
                    hollow: { size: '28%' },
                    track: { background: '#f0f2f5' },
                    dataLabels: {
                        name: { fontSize: '12px', color: '#566a7f' },
                        value: {
                            fontSize: '14px',
                            fontWeight: 600,
                            color: '#566a7f',
                            formatter: function (val) {
                                return roundValue(val) + '%';
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            formatter: function () {
                                return moneyAxis ? money.format(sum) : String(Math.round(sum));
                            }
                        }
                    }
                }
            },
            stroke: { lineCap: 'round' },
            tooltip: Object.assign(base.tooltip, {
                y: {
                    formatter: function (_val, opts) {
                        var value = rawValues[opts.seriesIndex] || 0;
                        return moneyAxis ? moneyLabel(value) : value;
                    }
                }
            })
        });
    }

    function buildOptions(type, payload, fmt) {
        switch (type) {
            case 'line':
                return lineOptions(payload, fmt, false);
            case 'area':
                return lineOptions(payload, fmt, true);
            case 'bar':
                return barOptions(payload, fmt, {});
            case 'horizontal':
                return barOptions(payload, fmt, { horizontal: true });
            case 'stacked':
                return barOptions(payload, fmt, { stacked: true });
            case 'radialBar':
                return radialOptions(circlePayload(payload, fmt), fmt);
            default:
                // pie, donut, polarArea
                return circleOptions(circlePayload(payload, fmt), type, fmt);
        }
    }

    /* ------------------------------------------------------------------ preferences */

    function storageKey() {
        if (prefScope === null) {
            var holder = document.querySelector('[data-chart-pref-scope]');
            prefScope = holder ? holder.getAttribute('data-chart-pref-scope') || 'default' : 'default';
        }
        return 'crm.dashboardChartTypes.' + prefScope;
    }

    function readPrefs() {
        try {
            var parsed = JSON.parse(window.localStorage.getItem(storageKey()) || '{}');
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (e) {
            return {};
        }
    }

    function prefFor(id) {
        var type = readPrefs()[id];
        return isType(type) ? type : null;
    }

    function writePref(id, type) {
        try {
            var prefs = readPrefs();
            if (type) {
                prefs[id] = type;
            } else {
                delete prefs[id];
            }
            window.localStorage.setItem(storageKey(), JSON.stringify(prefs));
        } catch (e) {
            // Storage blocked (private window etc.): the choice still applies until the page reloads.
        }
    }

    /* ------------------------------------------------------------------ drawing */

    function destroyInstance(entry) {
        if (entry.instance) {
            try {
                entry.instance.destroy();
            } catch (e) {
                // The element was already wiped by the caller; nothing left to clean up.
            }
            entry.instance = null;
        }
    }

    function draw(entry, type) {
        destroyInstance(entry);
        entry.el.innerHTML = '';
        entry.fmt.width = entry.el.clientWidth || 600;
        var useDefault = !isType(type) || type === entry.defaultType;
        entry.current = useDefault ? entry.defaultType : type;
        var options = useDefault ? entry.defaultOptions() : buildOptions(type, entry.payload, entry.fmt);
        entry.instance = new ApexCharts(entry.el, options);
        syncMenu(entry);
        return entry.instance.render();
    }

    /* ------------------------------------------------------------------ menu */

    function menuSlot(id) {
        return document.querySelector('.dash-chart-menu[data-chart-menu-for="' + id + '"]');
    }

    function buildMenu(slot, id) {
        var panelId = 'dash-chart-menu-' + id;
        var options = TYPES.map(function (type) {
            return '<button type="button" class="dash-chart-menu__option" role="menuitemradio" aria-checked="false"'
                + ' data-chart-type="' + type.key + '">' + svg(type.icon) + '<span>' + type.label + '</span></button>';
        }).join('');
        slot.innerHTML = '<button type="button" class="dash-chart-menu__toggle" aria-haspopup="true" aria-expanded="false"'
            + ' aria-controls="' + panelId + '" aria-label="Chart options" title="Chart options">' + DOTS_ICON + '</button>'
            + '<div class="dash-chart-menu__panel" id="' + panelId + '" role="menu" aria-label="Chart type" hidden>'
            + '<p class="dash-chart-menu__heading">Chart type</p>'
            + '<div class="dash-chart-menu__grid">' + options + '</div>'
            + '<div class="dash-chart-menu__footer">'
            + '<button type="button" class="dash-chart-menu__reset" role="menuitem" data-chart-reset>'
            + svg(RESET_ICON) + '<span>Reset to default</span></button>'
            + '</div></div>';
    }

    function showMenu(id, visible) {
        var slot = menuSlot(id);
        if (!slot) {
            return;
        }
        if (visible && !slot.firstChild) {
            buildMenu(slot, id);
        }
        if (!visible) {
            closeMenu(slot, false);
        }
        slot.hidden = !visible;
    }

    function syncMenu(entry) {
        var slot = menuSlot(entry.id);
        if (!slot || !slot.firstChild) {
            return;
        }
        Array.prototype.forEach.call(slot.querySelectorAll('[data-chart-type]'), function (button) {
            var active = button.getAttribute('data-chart-type') === entry.current;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-checked', active ? 'true' : 'false');
        });
        var reset = slot.querySelector('[data-chart-reset]');
        if (reset) {
            reset.disabled = entry.current === entry.defaultType;
        }
    }

    function openMenu(slot) {
        Array.prototype.forEach.call(document.querySelectorAll('.dash-chart-menu.is-open'), function (other) {
            if (other !== slot) {
                closeMenu(other, false);
            }
        });
        slot.classList.add('is-open');
        slot.querySelector('.dash-chart-menu__toggle').setAttribute('aria-expanded', 'true');
        slot.querySelector('.dash-chart-menu__panel').hidden = false;
        var focusTarget = slot.querySelector('.dash-chart-menu__option.is-active') || slot.querySelector('.dash-chart-menu__option');
        if (focusTarget) {
            focusTarget.focus();
        }
    }

    function closeMenu(slot, returnFocus) {
        if (!slot.classList.contains('is-open')) {
            return;
        }
        slot.classList.remove('is-open');
        var toggle = slot.querySelector('.dash-chart-menu__toggle');
        toggle.setAttribute('aria-expanded', 'false');
        slot.querySelector('.dash-chart-menu__panel').hidden = true;
        if (returnFocus) {
            toggle.focus();
        }
    }

    function choose(slot, type) {
        var id = slot.getAttribute('data-chart-menu-for');
        var entry = entries[id];
        var defaultType = entry ? entry.defaultType : null;
        writePref(id, isType(type) && type !== defaultType ? type : null);
        closeMenu(slot, true);
        // A legacy chart that is reloading picks the saved type up on its next render().
        if (entry && entry.live) {
            draw(entry, type);
        }
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        var slot = target && target.closest ? target.closest('.dash-chart-menu') : null;
        if (!slot) {
            Array.prototype.forEach.call(document.querySelectorAll('.dash-chart-menu.is-open'), function (open) {
                closeMenu(open, false);
            });
            return;
        }
        if (target.closest('.dash-chart-menu__toggle')) {
            if (slot.classList.contains('is-open')) {
                closeMenu(slot, false);
            } else {
                openMenu(slot);
            }
            return;
        }
        var option = target.closest('[data-chart-type]');
        if (option) {
            choose(slot, option.getAttribute('data-chart-type'));
            return;
        }
        var reset = target.closest('[data-chart-reset]');
        if (reset && !reset.disabled) {
            choose(slot, null);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }
        Array.prototype.forEach.call(document.querySelectorAll('.dash-chart-menu.is-open'), function (open) {
            closeMenu(open, true);
        });
    });

    /* ------------------------------------------------------------------ public API */

    /**
     * Render an overview chart from a server payload and attach its chart-type menu.
     *
     * @param {string} id      element id of the chart container
     * @param {object} payload {labels, values, colors?} or {labels, datasets: [{label, data}]}
     * @param {object} config  {type: default chart type, money?: bool, seriesName?: string, pieBy?: 'series'|'labels'}
     */
    function mount(id, payload, config) {
        var el = document.getElementById(id);
        if (!el) {
            return;
        }
        config = config || {};
        if (!hasData(payload)) {
            emptyState(el);
            showMenu(id, false);
            return;
        }
        var fmt = {
            height: baseHeight(el),
            money: !!config.money,
            seriesName: config.seriesName || 'Value',
            pieBy: config.pieBy || 'series',
            colors: null
        };
        var entry = entries[id] = {
            id: id,
            el: el,
            payload: payload,
            fmt: fmt,
            defaultType: config.type,
            live: true,
            defaultOptions: function () {
                return buildOptions(config.type, payload, fmt);
            }
        };
        showMenu(id, true);
        draw(entry, prefFor(id));
    }

    function payloadFromApex(options) {
        var series = options.series || [];
        if (series.length && typeof series[0] !== 'object') {
            return { labels: (options.labels || []).slice(), values: series.map(num) };
        }
        return {
            labels: ((options.xaxis && options.xaxis.categories) || []).slice(),
            datasets: series.map(function (set) {
                return { label: set.name, data: (set.data || []).map(num) };
            })
        };
    }

    function typeFromApex(options) {
        var chart = options.chart || {};
        var type = chart.type || 'line';
        if (type === 'bar') {
            if (chart.stacked) {
                return 'stacked';
            }
            return options.plotOptions && options.plotOptions.bar && options.plotOptions.bar.horizontal ? 'horizontal' : 'bar';
        }
        return type;
    }

    /**
     * Drop-in for `new ApexCharts(el, options)` used by the legacy dashboard widgets.
     * The default type renders the caller's own options untouched; other types are rebuilt from its data.
     */
    function create(el, options) {
        if (!el || !el.id) {
            return new ApexCharts(el, options);
        }
        var id = el.id;
        var previous = entries[id];
        if (previous) {
            previous.live = false;
            destroyInstance(previous);
        }
        var original = cloneOptions(options);
        var payload = payloadFromApex(original);
        var entry = entries[id] = {
            id: id,
            el: el,
            payload: payload,
            fmt: {
                height: num(original.chart && original.chart.height) || baseHeight(el),
                money: false,
                seriesName: 'Value',
                pieBy: 'series',
                colors: original.colors
            },
            defaultType: typeFromApex(original),
            live: false,
            defaultOptions: function () {
                return cloneOptions(original);
            }
        };
        showMenu(id, hasData(payload));

        return {
            render: function () {
                entry.live = true;
                return draw(entry, prefFor(id));
            },
            destroy: function () {
                entry.live = false;
                destroyInstance(entry);
            }
        };
    }

    window.DashChartKit = {
        mount: mount,
        create: create,
        types: TYPES.map(function (type) { return type.key; })
    };
})(window, document);
