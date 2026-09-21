/**
 * Sneat overview charts for the home dashboard.
 * Renders ApexCharts from server-provided window.dashboardOverviewCharts.
 */
(function () {
    'use strict';

    var charts = window.dashboardOverviewCharts || {};
    var money = new Intl.NumberFormat('en-PK', { maximumFractionDigits: 0 });
    var lineColors = ['#696cff', '#71dd37', '#03c3ec', '#ffab00'];

    function hasLabels(payload) {
        return payload && payload.labels && payload.labels.length > 0;
    }

    function emptyState(el, message) {
        el.innerHTML = '<div class="dash-chart-empty">' + (message || 'No data for this period') + '</div>';
    }

    function moneyLabel(value) {
        return 'PKR ' + money.format(value || 0);
    }

    /** Evenly spaced tick indexes that always include the first and last label. */
    function pickTickIndices(count, maxTicks) {
        if (count <= 0) {
            return [];
        }
        if (count <= maxTicks) {
            var all = [];
            for (var i = 0; i < count; i++) {
                all.push(i);
            }
            return all;
        }
        var indices = [];
        var steps = maxTicks - 1;
        for (var t = 0; t <= steps; t++) {
            indices.push(Math.round((t * (count - 1)) / steps));
        }
        return indices.filter(function (value, index, arr) {
            return index === 0 || value !== arr[index - 1];
        });
    }

    function baseOptions() {
        return {
            chart: {
                fontFamily: 'Public Sans, Segoe UI, sans-serif',
                toolbar: { show: false },
                parentHeightOffset: 0,
                zoom: { enabled: false }
            },
            colors: lineColors,
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            grid: {
                borderColor: '#d9dee3',
                strokeDashArray: 4,
                padding: { top: 8, right: 12, bottom: 0, left: 8 }
            },
            legend: {
                fontFamily: 'Public Sans, Segoe UI, sans-serif',
                fontSize: '13px',
                labels: { colors: '#566a7f' },
                markers: { width: 10, height: 10, radius: 10 }
            },
            tooltip: {
                theme: 'dark',
                style: { fontFamily: 'Public Sans, Segoe UI, sans-serif', fontSize: '13px' }
            },
            xaxis: {
                labels: {
                    rotate: 0,
                    rotateAlways: true,
                    style: { colors: '#a1acb8', fontSize: '12px', fontFamily: 'Public Sans, Segoe UI, sans-serif' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: '#a1acb8', fontSize: '12px', fontFamily: 'Public Sans, Segoe UI, sans-serif' }
                }
            }
        };
    }

    function renderLine(id, payload, moneyAxis) {
        var el = document.querySelector('#' + id);
        if (!el) return;
        if (!hasLabels(payload) || !payload.datasets || !payload.datasets.length) {
            emptyState(el);
            return;
        }
        var labels = payload.labels;
        var tickIndex = {};
        pickTickIndices(labels.length, 12).forEach(function (index) {
            tickIndex[index] = true;
        });
        var options = Object.assign(baseOptions(), {
            chart: Object.assign(baseOptions().chart, { type: 'line', height: el.clientHeight || 320, animations: { enabled: true } }),
            series: payload.datasets.map(function (set) {
                return { name: set.label, data: set.data };
            }),
            xaxis: Object.assign(baseOptions().xaxis, {
                categories: labels,
                labels: Object.assign({}, baseOptions().xaxis.labels, {
                    hideOverlappingLabels: false,
                    formatter: function (value, _timestamp, opts) {
                        var index = opts && typeof opts.i === 'number' ? opts.i : labels.indexOf(value);
                        return tickIndex[index] ? value : '';
                    }
                })
            }),
            yaxis: Object.assign(baseOptions().yaxis, {
                min: 0,
                labels: {
                    style: baseOptions().yaxis.labels.style,
                    formatter: moneyAxis ? function (v) { return money.format(v); } : function (v) { return Math.round(v); }
                }
            }),
            markers: { size: 4, strokeWidth: 2, hover: { size: 6 } },
            tooltip: Object.assign(baseOptions().tooltip, {
                y: { formatter: moneyAxis ? moneyLabel : function (v) { return v; } }
            })
        });
        new ApexCharts(el, options).render();
    }

    function renderBar(id, payload, opts) {
        var el = document.querySelector('#' + id);
        if (!el) return;
        if (!hasLabels(payload)) {
            emptyState(el);
            return;
        }
        opts = opts || {};
        var horizontal = !!opts.horizontal;
        var stacked = !!opts.stacked;
        var moneyAxis = !!opts.money;
        var categoryCount = (payload.labels || []).length || 1;
        var series;
        if (payload.datasets && payload.datasets.length) {
            series = payload.datasets.map(function (set) {
                return { name: set.label, data: set.data };
            });
        } else {
            series = [{ name: opts.seriesName || 'Value', data: payload.values || [] }];
        }

        var barHeightPct = categoryCount <= 1 ? '26%' : categoryCount <= 3 ? '38%' : categoryCount <= 6 ? '50%' : '58%';
        var columnWidthPct = categoryCount <= 2 ? '28%' : categoryCount <= 4 ? '40%' : '52%';
        var chartHeight = horizontal
            ? Math.max(240, Math.min(el.clientHeight || 360, categoryCount * 54 + 110))
            : (el.clientHeight || 360);

        var options = Object.assign(baseOptions(), {
            chart: Object.assign(baseOptions().chart, {
                type: 'bar',
                height: chartHeight,
                stacked: stacked
            }),
            colors: payload.colors && payload.colors.length ? payload.colors : lineColors,
            series: series,
            plotOptions: {
                bar: {
                    horizontal: horizontal,
                    borderRadius: 6,
                    columnWidth: columnWidthPct,
                    barHeight: barHeightPct,
                    distributed: !stacked && !(payload.datasets && payload.datasets.length > 1),
                    dataLabels: {
                        position: horizontal ? 'top' : 'top'
                    }
                }
            },
            dataLabels: {
                enabled: !stacked,
                offsetX: horizontal ? 6 : 0,
                offsetY: horizontal ? 0 : -6,
                style: {
                    fontSize: '11px',
                    fontWeight: 600,
                    fontFamily: 'Public Sans, Segoe UI, sans-serif',
                    colors: ['#566a7f']
                },
                background: {
                    enabled: false
                },
                formatter: function (val) {
                    if (val == null || val === '') {
                        return '';
                    }
                    return moneyAxis ? money.format(val) : Math.round(Number(val) || 0);
                }
            },
            xaxis: Object.assign(baseOptions().xaxis, {
                categories: payload.labels,
                labels: Object.assign({}, baseOptions().xaxis.labels, {
                    rotate: 0,
                    rotateAlways: true,
                    trim: true,
                    hideOverlappingLabels: true
                })
            }),
            yaxis: Object.assign(baseOptions().yaxis, {
                labels: {
                    style: baseOptions().yaxis.labels.style,
                    formatter: moneyAxis && !horizontal ? function (v) { return money.format(v); } : undefined
                }
            }),
            legend: Object.assign(baseOptions().legend, { show: stacked || (payload.datasets && payload.datasets.length > 1) }),
            tooltip: Object.assign(baseOptions().tooltip, {
                y: { formatter: moneyAxis ? moneyLabel : function (v) { return v; } }
            })
        });
        if (horizontal) {
            var longest = 0;
            (payload.labels || []).forEach(function (label) {
                longest = Math.max(longest, String(label || '').length);
            });
            var labelWidth = Math.min(200, Math.max(110, Math.round(longest * 7.5)));
            options.grid = Object.assign({}, baseOptions().grid, {
                padding: { top: 8, right: 28, bottom: 4, left: 16 }
            });
            options.yaxis = Object.assign({}, baseOptions().yaxis, {
                labels: {
                    show: true,
                    align: 'left',
                    minWidth: labelWidth,
                    maxWidth: labelWidth,
                    offsetX: 0,
                    trim: false,
                    style: baseOptions().yaxis.labels.style
                }
            });
            options.xaxis.labels.trim = false;
        }
        if (moneyAxis && horizontal) {
            options.xaxis.labels.formatter = function (v) { return money.format(v); };
        }
        if (stacked) {
            options.dataLabels.enabled = true;
            options.dataLabels.formatter = function (val) {
                if (!val) {
                    return '';
                }
                return Math.round(Number(val) || 0);
            };
        }
        new ApexCharts(el, options).render();
    }

    function renderCircle(id, payload, type, moneyAxis) {
        var el = document.querySelector('#' + id);
        if (!el) return;
        if (!hasLabels(payload)) {
            emptyState(el);
            return;
        }

        var rawValues = (payload.values || []).map(function (v) { return Number(v) || 0; });
        var total = rawValues.reduce(function (sum, value) { return sum + value; }, 0) || 1;

        if (type === 'radialBar') {
            var radialOptions = Object.assign(baseOptions(), {
                chart: Object.assign(baseOptions().chart, { type: 'radialBar', height: el.clientHeight || 320 }),
                labels: payload.labels,
                series: rawValues.map(function (value) {
                    return Math.round((value / total) * 1000) / 10;
                }),
                colors: payload.colors && payload.colors.length ? payload.colors : lineColors,
                legend: Object.assign(baseOptions().legend, {
                    show: true,
                    position: 'bottom',
                    formatter: function (seriesName, opts) {
                        var value = rawValues[opts.seriesIndex] || 0;
                        var formatted = moneyAxis ? money.format(value) : String(Math.round(value));
                        return seriesName + ': ' + formatted;
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
                                    return Math.round(Number(val) || 0) + '%';
                                }
                            },
                            total: {
                                show: true,
                                label: 'Total',
                                formatter: function () {
                                    return moneyAxis ? money.format(total) : String(Math.round(total));
                                }
                            }
                        }
                    }
                },
                stroke: { lineCap: 'round' },
                tooltip: Object.assign(baseOptions().tooltip, {
                    y: {
                        formatter: function (_val, opts) {
                            var value = rawValues[opts.seriesIndex] || 0;
                            return moneyAxis ? moneyLabel(value) : value;
                        }
                    }
                })
            });
            new ApexCharts(el, radialOptions).render();
            return;
        }

        var options = Object.assign(baseOptions(), {
            chart: Object.assign(baseOptions().chart, { type: type, height: el.clientHeight || 320 }),
            labels: payload.labels,
            series: rawValues,
            colors: payload.colors && payload.colors.length ? payload.colors : lineColors,
            stroke: { width: type === 'polarArea' ? 1 : 0 },
            legend: Object.assign(baseOptions().legend, {
                position: 'bottom',
                formatter: function (seriesName, opts) {
                    var value = opts.w.globals.series[opts.seriesIndex];
                    var formatted = moneyAxis ? money.format(value) : String(Math.round(Number(value) || 0));
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
                enabled: true,
                style: { fontSize: '12px', fontFamily: 'Public Sans, Segoe UI, sans-serif' },
                formatter: function (_percent, opts) {
                    var value = opts.w.config.series[opts.seriesIndex];
                    if (moneyAxis) {
                        return money.format(value);
                    }
                    return Math.round(Number(value) || 0);
                }
            },
            tooltip: Object.assign(baseOptions().tooltip, {
                y: { formatter: moneyAxis ? moneyLabel : function (v) { return v; } }
            })
        });
        new ApexCharts(el, options).render();
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof ApexCharts === 'undefined') {
            return;
        }
        renderLine('chartCollectionRevenue', charts.collection_revenue_trend, true);
        renderCircle('chartCollectionMode', charts.collection_by_mode, 'donut', true);
        renderBar('chartCollectionCentre', charts.collection_by_centre, { money: true });
        renderBar('chartRevenueCentre', charts.revenue_by_centre, { money: true });
        renderLine('chartAppointmentTrend', charts.appointment_trend, false);
        renderCircle('chartAppointmentType', charts.appointments_by_type, 'polarArea');
        renderBar('chartAppointmentStatus', charts.appointments_by_status, { horizontal: true });
        renderBar('chartAppointmentCentre', charts.appointments_by_centre, { stacked: true });
        renderBar('chartRevenueCategory', charts.revenue_by_category, { horizontal: true, money: true });
        renderBar('chartRevenueService', charts.revenue_by_service, { horizontal: true, money: true });
        renderBar('chartLeadSource', charts.leads_by_source, { horizontal: true });
        renderCircle('chartLeadStatus', charts.leads_by_status, 'pie');
        renderCircle('chartPatientGender', charts.patients_by_gender, 'radialBar');
        renderLine('chartPatientTrend', charts.patients_trend, false);
        renderBar('chartDoctors', charts.doctors_by_appointments, { horizontal: true });
        renderBar('chartExpenses', charts.expenses_by_category, { horizontal: true, money: true });
        renderBar('chartPools', charts.pool_balances, { money: true });
    });
})();
