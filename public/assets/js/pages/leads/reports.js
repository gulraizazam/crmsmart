/**
 * Lead reports: date range + AJAX load into DataTables.
 */
(function () {
    'use strict';

    $(function () {
        $('#date_range').daterangepicker({
            ranges: {
                Today: [moment(), moment()],
                Yesterday: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
                'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            },
            startDate: moment().subtract(29, 'days'),
            endDate: moment()
        });

        $('#load_lead_report').on('click', function () {
            loadLeadReport($(this));
        });
    });

    function loadLeadReport(button) {
        if (typeof button.prop('disabled') !== 'undefined' && button.prop('disabled') === true) {
            return false;
        }
        if (typeof showSpinner === 'function') {
            showSpinner();
        }
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: route('admin.reports.load_leads_report'),
            type: 'POST',
            data: {
                report_type: $('#report_type').val(),
                date_range: $('#date_range').val(),
                city_id: $('#city_id').val(),
                location_id: $('#location_id').val(),
                lead_source_id: $('#lead_source_id').val(),
                lead_status_id: $('#lead_status_id').val(),
                assigned_to: $('#assigned_to').val(),
                created_by: $('#created_by').val(),
                department_id: $('#department_id').val(),
                gender: $('#gender').val()
            },
            success: function (response) {
                $('#content').html(response);
                if ($.fn.DataTable && $('#leads_report_table').length) {
                    $('#leads_report_table').DataTable({
                        dom: 'Bfrtip',
                        buttons: ['excelHtml5', 'csvHtml5', 'pdfHtml5'],
                        ordering: true,
                        pageLength: 50,
                        scrollX: true
                    });
                }
                if (typeof hideSpinner === 'function') {
                    hideSpinner();
                }
            },
            error: function () {
                if (typeof hideSpinner === 'function') {
                    hideSpinner();
                }
            }
        });
    }
})();
