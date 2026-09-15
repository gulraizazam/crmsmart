<div class="sn-table-holder">
    <div class="sn-report-head">
        <div class="sn-title">
            <h1>Lead report — {{ $title }}</h1>
        </div>
    </div>
</div>
<div class="panel-body sn-table-body">
    <div class="bordered">
        <div class="sn-table-head">
            <div class="row">
                <div class="col-md-2">
                    <div style="font-family: Georgia, serif;"><img src="{{ asset('logoClarity.jpg') }}?v=4" alt="Smart Aesthetics" style="height: 70px; max-width: 260px; width: auto; display: inline-block;"></div>
                </div>
                <div class="col-md-6">&nbsp;</div>
                <div class="col-md-4">
                    <table class="dark-th-table table table-bordered">
                        <tr>
                            <th width="25%">Duration</th>
                            <td>From {{ $start_date }} to {{ $end_date }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <table class="table border">
                        <thead>
                            <tr>
                                <td class="bg-light">New leads</td>
                                <td class="bg-light" style="text-align:right;">{{ number_format($kpis['total']) }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light">Booked</td>
                                <td class="bg-light" style="text-align:right;">{{ number_format($kpis['booked']) }}</td>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="col-md-3 mb-3">
                    <table class="table border">
                        <thead>
                            <tr>
                                <td class="bg-light">Arrived</td>
                                <td class="bg-light" style="text-align:right;">{{ number_format($kpis['arrived']) }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light">Converted</td>
                                <td class="bg-light" style="text-align:right;">{{ number_format($kpis['converted']) }}</td>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="col-md-3 mb-3">
                    <table class="table border">
                        <thead>
                            <tr>
                                <td class="bg-light">Conversion rate</td>
                                <td class="bg-light" style="text-align:right;">{{ number_format($kpis['conversion_rate'], 1) }}%</td>
                            </tr>
                            <tr>
                                <td class="bg-light">Junk</td>
                                <td class="bg-light" style="text-align:right;">{{ number_format($kpis['junk']) }}</td>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="col-md-3 mb-3">
                    <table class="table border">
                        <thead>
                            <tr>
                                <td class="bg-light">Collection</td>
                                <td class="bg-light" style="text-align:right;">PKR {{ number_format($kpis['collection'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="bg-light">Avg. converted value</td>
                                <td class="bg-light" style="text-align:right;">PKR {{ number_format($kpis['avg_value'], 2) }}</td>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            @if (!empty($truncated))
                <p class="text-muted mb-3">Showing the first {{ number_format(\App\Services\Lead\LeadReportService::DETAIL_LIMIT) }} leads. Narrow the filters to see the rest.</p>
            @endif

            <div class="table-wrapper" id="topscroll">
                @if ($is_detail)
                    <table class="table" id="leads_report_table">
                        <thead>
                            <tr>
                                <th>Lead ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th>Agent</th>
                                <th>Centre</th>
                                <th>City</th>
                                <th>Created by</th>
                                <th>Department</th>
                                <th>Channel</th>
                                <th>Gender</th>
                                <th>Created at</th>
                                <th>Booked</th>
                                <th>Arrived</th>
                                <th>Converted</th>
                                <th>Collection</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row['id'] }}</td>
                                    <td>{{ $row['name'] }}</td>
                                    <td>{{ $row['phone'] }}</td>
                                    <td>{{ $row['source'] }}</td>
                                    <td>{{ $row['status'] }}</td>
                                    <td>{{ $row['agent'] }}</td>
                                    <td>{{ $row['centre'] }}</td>
                                    <td>{{ $row['city'] }}</td>
                                    <td>{{ $row['creator'] }}</td>
                                    <td>{{ $row['department'] }}</td>
                                    <td>{{ $row['channel'] }}</td>
                                    <td>{{ $row['gender'] }}</td>
                                    <td>{{ $row['created_at'] }}</td>
                                    <td>{{ $row['booked'] }}</td>
                                    <td>{{ $row['arrived'] }}</td>
                                    <td>{{ $row['converted'] }}</td>
                                    <td>PKR {{ number_format($row['collection'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="17" class="text-center">No leads found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="table" id="leads_report_table">
                        <thead>
                            <tr>
                                <th>{{ $group_label }}</th>
                                <th>New leads</th>
                                <th>Booked</th>
                                <th>Arrived</th>
                                <th>Converted</th>
                                <th>Conversion %</th>
                                <th>Junk</th>
                                <th>Collection</th>
                                <th>Avg. value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $sumTotal = 0;
                                $sumBooked = 0;
                                $sumArrived = 0;
                                $sumConverted = 0;
                                $sumJunk = 0;
                                $sumCollection = 0;
                            @endphp
                            @forelse ($rows as $row)
                                @php
                                    $sumTotal += $row['total'];
                                    $sumBooked += $row['booked'];
                                    $sumArrived += $row['arrived'];
                                    $sumConverted += $row['converted'];
                                    $sumJunk += $row['junk'];
                                    $sumCollection += $row['collection'];
                                @endphp
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td>{{ number_format($row['total']) }}</td>
                                    <td>{{ number_format($row['booked']) }}</td>
                                    <td>{{ number_format($row['arrived']) }}</td>
                                    <td>{{ number_format($row['converted']) }}</td>
                                    <td>{{ number_format($row['conversion_rate'], 1) }}%</td>
                                    <td>{{ number_format($row['junk']) }}</td>
                                    <td>PKR {{ number_format($row['collection'], 2) }}</td>
                                    <td>PKR {{ number_format($row['avg_value'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No leads found for this period.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($rows->count())
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th>{{ number_format($sumTotal) }}</th>
                                    <th>{{ number_format($sumBooked) }}</th>
                                    <th>{{ number_format($sumArrived) }}</th>
                                    <th>{{ number_format($sumConverted) }}</th>
                                    <th>{{ $sumTotal > 0 ? number_format(($sumConverted / $sumTotal) * 100, 1) : '0.0' }}%</th>
                                    <th>{{ number_format($sumJunk) }}</th>
                                    <th>PKR {{ number_format($sumCollection, 2) }}</th>
                                    <th>PKR {{ $sumConverted > 0 ? number_format($sumCollection / $sumConverted, 2) : '0.00' }}</th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                @endif
            </div>
        </div>
    </div>
    <div class="clear clearfix"></div>
</div>
