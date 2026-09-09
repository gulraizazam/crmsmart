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
            chart: Object.assign(baseOptions().chart, { type: 'line', height: el.clientHeight || 320, animations: { enabled: true } }),
            series: payload.datasets.map(function (set) {
                return { name: set.label, data: set.data };
            }),
            xaxis: Object.assign(baseOptions().xaxis, { categories: payload.labels, tickAmount: Math.min(12, payload.labels.length) }),
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
                    formatter: moneyAxis && !horizontal ? function (v) { return money.format(v); } : undefined
                }
            }),
            legend: Object.assign(baseOptions().legend, { show: stacked || (payload.datasets && payload.datasets.length > 1) }),
            tooltip: Object.assign(baseOptions().tooltip, {
                y: { formatter: moneyAxis ? moneyLabel : function (v) { return v; } }
            })
        });
        if (moneyAxis && horizontal) {
            options.xaxis.labels.formatter = function (v) { return money.format(v); };
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
            dataLabels: { enabled: true, style: { fontSize: '12px', fontFamily: 'Public Sans, Segoe UI, sans-serif' } },
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
        renderCircle('chartAppointmentStatus', charts.appointments_by_status, 'donut');
        renderBar('chartAppointmentCentre', charts.appointments_by_centre, { stacked: true });
        renderBar('chartRevenueCategory', charts.revenue_by_category, { horizontal: true, money: true });
        renderBar('chartRevenueService', charts.revenue_by_service, { horizontal: true, money: true });
        renderBar('chartLeadSource', charts.leads_by_source, { horizontal: true });
        renderCircle('chartLeadStatus', charts.leads_by_status, 'pie');
        renderCircle('chartPatientGender', charts.patients_by_gender, 'donut');
        renderLine('chartPatientTrend', charts.patients_trend, false);
        renderBar('chartDoctors', charts.doctors_by_appointments, { horizontal: true });
        renderBar('chartExpenses', charts.expenses_by_category, { horizontal: true, money: true });
        renderBar('chartPools', charts.pool_balances, { money: true });
    });
})();
