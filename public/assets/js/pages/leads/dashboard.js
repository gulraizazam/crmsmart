/**
 * ApexCharts renderer for the leads dashboard.
 * Reads window.leadsDashboardCharts from the Blade payload.
 */
(function () {
    'use strict';

    var charts = window.leadsDashboardCharts || {};
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

    function percentLabel(value) {
        return (Number(value) || 0).toFixed(1) + '%';
    }

    function valueFormatter(opts) {
        if (opts && opts.money) {
            return moneyLabel;
        }
        if (opts && opts.percent) {
            return percentLabel;
        }
        return function (v) { return v; };
    }

    function axisFormatter(opts, horizontal) {
        if (opts && opts.money && !horizontal) {
            return function (v) { return money.format(v); };
        }
        if (opts && opts.percent && !horizontal) {
            return function (v) { return Math.round(v) + '%'; };
        }
        return undefined;
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
        var options = Object.assign(baseOptions(), {
            chart: Object.assign(baseOptions().chart, {
                type: 'area',
                height: el.clientHeight || 320,
                animations: { enabled: true }
            }),
            series: payload.datasets.map(function (set) {
                return { name: set.label, data: set.data };
            }),
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] }
            },
            xaxis: Object.assign(baseOptions().xaxis, {
                categories: payload.labels,
                tickAmount: Math.min(12, payload.labels.length)
            }),
            yaxis: Object.assign(baseOptions().yaxis, {
                min: 0,
                labels: {
                    style: baseOptions().yaxis.labels.style,
                    formatter: moneyAxis ? function (v) { return money.format(v); } : function (v) { return Math.round(v); }
                }
            }),
            markers: { size: 3, strokeWidth: 2, hover: { size: 5 } },
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
        var series;
        if (payload.datasets && payload.datasets.length) {
            series = payload.datasets.map(function (set) {
                return { name: set.label, data: set.data };
            });
        } else {
            series = [{ name: opts.seriesName || 'Value', data: payload.values || [] }];
        }
        var options = Object.assign(baseOptions(), {
            chart: Object.assign(baseOptions().chart, {
                type: 'bar',
                height: el.clientHeight || 360,
                stacked: stacked
            }),
            colors: payload.colors && payload.colors.length ? payload.colors : lineColors,
            series: series,
            plotOptions: {
                bar: {
                    horizontal: horizontal,
                    borderRadius: 6,
                    columnWidth: '55%',
                    barHeight: '70%',
                    distributed: !stacked && !(payload.datasets && payload.datasets.length > 1)
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
                    formatter: axisFormatter(opts, horizontal)
                }
            }),
            legend: Object.assign(baseOptions().legend, {
                show: stacked || (payload.datasets && payload.datasets.length > 1)
            }),
            tooltip: Object.assign(baseOptions().tooltip, {
                y: { formatter: valueFormatter(opts) }
            })
        });
        if (opts.money && horizontal) {
            options.xaxis.labels.formatter = function (v) { return money.format(v); };
        }
        if (opts.percent && horizontal) {
            options.xaxis.labels.formatter = function (v) { return percentLabel(v); };
        }
        new ApexCharts(el, options).render();
    }

    function renderFunnel(id, payload) {
        var el = document.querySelector('#' + id);
        if (!el) return;
        if (!hasLabels(payload)) {
            emptyState(el);
            return;
        }
        var options = Object.assign(baseOptions(), {
            chart: Object.assign(baseOptions().chart, {
                type: 'bar',
                height: el.clientHeight || 320
            }),
            colors: payload.colors && payload.colors.length ? payload.colors : lineColors,
            series: [{ name: 'Leads', data: payload.values || [] }],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    distributed: true,
                    barHeight: '78%',
                    isFunnel: true
                }
            },
            xaxis: Object.assign(baseOptions().xaxis, { categories: payload.labels }),
            legend: Object.assign(baseOptions().legend, { show: false }),
            dataLabels: {
                enabled: true,
                formatter: function (val) { return val; },
                style: { fontSize: '12px', fontFamily: 'Public Sans, Segoe UI, sans-serif' }
            },
            tooltip: Object.assign(baseOptions().tooltip, {
                y: { formatter: function (v) { return v; } }
            })
        });
        new ApexCharts(el, options).render();
    }

    function renderCircle(id, payload, type, moneyAxis) {
        var el = document.querySelector('#' + id);
        if (!el) return;
        if (!hasLabels(payload)) {
            emptyState(el);
            return;
        }
        var options = Object.assign(baseOptions(), {
            chart: Object.assign(baseOptions().chart, { type: type, height: el.clientHeight || 320 }),
            labels: payload.labels,
            series: (payload.values || []).map(function (v) { return Number(v) || 0; }),
            colors: payload.colors && payload.colors.length ? payload.colors : lineColors,
            stroke: { width: type === 'polarArea' ? 1 : 0 },
            legend: Object.assign(baseOptions().legend, { position: 'bottom' }),
            plotOptions: {
                pie: {
                    donut: { size: type === 'donut' ? '68%' : '0%' }
                }
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '12px', fontFamily: 'Public Sans, Segoe UI, sans-serif' }
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

        renderLine('chartLeadsTrend', charts.leads_trend, false);
        renderFunnel('chartConversionFunnel', charts.conversion_funnel);
        renderCircle('chartLeadStatus', charts.leads_by_status, 'donut');
        renderCircle('chartAssignmentMix', charts.assignment_mix, 'donut');
        renderCircle('chartChannelMix', charts.channel_mix, 'donut');

        renderBar('chartLeadSource', charts.leads_by_source, { horizontal: true, seriesName: 'Leads' });
        renderBar('chartCollectionBySource', charts.collection_by_source, { horizontal: true, money: true, seriesName: 'Collection' });
        renderBar('chartConversionRateBySource', charts.conversion_rate_by_source, { horizontal: true, percent: true, seriesName: 'Conversion' });

        renderBar('chartLeadsByAgent', charts.leads_by_agent, { horizontal: true, seriesName: 'Leads' });
        renderBar('chartConversionRateByAgent', charts.conversion_rate_by_agent, { horizontal: true, percent: true, seriesName: 'Conversion' });
        renderBar('chartLeadsByCreator', charts.leads_by_creator, { horizontal: true, seriesName: 'Leads' });
        renderBar('chartLeadsByCentre', charts.leads_by_centre, { seriesName: 'Leads' });

        renderCircle('chartLeadsByDepartment', charts.leads_by_department, 'donut');
        renderCircle('chartLeadsByGender', charts.leads_by_gender, 'pie');
        renderBar('chartLeadsByCity', charts.leads_by_city, { horizontal: true, seriesName: 'Leads' });
        renderBar('chartLeadsByService', charts.leads_by_service, { horizontal: true, seriesName: 'Leads' });
        renderBar('chartLeadsByWeekday', charts.leads_by_weekday, { seriesName: 'Leads' });
        renderLine('chartLeadsByHour', charts.leads_by_hour, false);
    });
})();
