/**
 * Sneat overview charts for the home dashboard.
 * Renders server-provided window.dashboardOverviewCharts through DashChartKit, which also adds the
 * three-dots "Chart type" menu to every chart card.
 */
(function () {
    'use strict';

    var charts = window.dashboardOverviewCharts || {};

    document.addEventListener('DOMContentLoaded', function () {
        var kit = window.DashChartKit;
        if (typeof ApexCharts === 'undefined' || !kit) {
            return;
        }
        // type = default view; pieBy = how a multi-series chart is split when shown as pie / donut.
        kit.mount('chartCollectionRevenue', charts.collection_revenue_trend, { type: 'line', money: true, pieBy: 'series' });
        kit.mount('chartCollectionMode', charts.collection_by_mode, { type: 'donut', money: true, seriesName: 'Collection' });
        kit.mount('chartCollectionCentre', charts.collection_by_centre, { type: 'bar', money: true, seriesName: 'Collection' });
        kit.mount('chartRevenueCentre', charts.revenue_by_centre, { type: 'bar', money: true, seriesName: 'Revenue' });
        kit.mount('chartAppointmentTrend', charts.appointment_trend, { type: 'line', pieBy: 'series' });
        kit.mount('chartAppointmentType', charts.appointments_by_type, { type: 'polarArea', seriesName: 'Appointments' });
        kit.mount('chartAppointmentStatus', charts.appointments_by_status, { type: 'horizontal', seriesName: 'Consultancies' });
        kit.mount('chartAppointmentCentre', charts.appointments_by_centre, { type: 'stacked', pieBy: 'labels' });
        kit.mount('chartRevenueCategory', charts.revenue_by_category, { type: 'pie', money: true, seriesName: 'Revenue' });
        kit.mount('chartRevenueService', charts.revenue_by_service, { type: 'pie', money: true, seriesName: 'Revenue' });
        kit.mount('chartLeadSource', charts.leads_by_source, { type: 'pie', seriesName: 'Leads' });
        kit.mount('chartLeadStatus', charts.leads_by_status, { type: 'pie', seriesName: 'Leads' });
        kit.mount('chartPatientGender', charts.patients_by_gender, { type: 'radialBar', seriesName: 'Patients' });
        kit.mount('chartPatientTrend', charts.patients_trend, { type: 'line', pieBy: 'series' });
        kit.mount('chartDoctors', charts.doctors_by_appointments, { type: 'horizontal', seriesName: 'Appointments' });
        kit.mount('chartExpenses', charts.expenses_by_category, { type: 'horizontal', money: true, seriesName: 'Expenses' });
        kit.mount('chartPools', charts.pool_balances, { type: 'bar', money: true, seriesName: 'Balance' });
    });
})();
