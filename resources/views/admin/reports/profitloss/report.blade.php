<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>Profit &amp; Loss Report</h1>
        </div>
        <div class="sn-buttons">
            <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="printReport('excel');">
                <i class="fa fa-file-excel-o"></i><span>Excel</span>
            </a>
            <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="printReport('pdf');">
                <i class="fa fa-file-pdf-o"></i><span>PDF</span>
            </a>
            <a class="btn sn-white-btn btn-default" href="javascript:;" onclick="printReport('print');">
                <i class="fa fa-print"></i><span>Print</span>
            </a>
        </div>
    </div>
</div>
<div class="panel-body sn-table-body">
    <div class="bordered">
        <div class="sn-table-head">
            <div class="row">
                <div class="col-md-2">
                    <img src="{{ asset('logoClarity.jpg') }}?v=4" alt="Smart Aesthetics" style="height: 70px; max-width: 260px; width: auto;">
                </div>
                <div class="col-md-5">&nbsp;</div>
                <div class="col-md-5">
                    <table class="dark-th-table table table-bordered">
                        <tr>
                            <th width="25%">Duration</th>
                            <td>From {{ $start_date }} to {{ $end_date }}</td>
                        </tr>
                        <tr>
                            <th>Centres</th>
                            <td>{{ $report['centres'] }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="table-wrapper" style="overflow-x: auto;">
                @include('admin.reports.profitloss._table')
            </div>
        </div>
    </div>
    <div class="clear clearfix"></div>
</div>
