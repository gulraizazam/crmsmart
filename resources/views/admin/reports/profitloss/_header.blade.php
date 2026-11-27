<table style="width: 100%; border-collapse: collapse; margin-bottom: 16px; font-size: 12px;">
    <tr>
        <td style="vertical-align: top;">
            <img src="{{ asset('logoClarity.jpg') }}?v=4" alt="Smart Aesthetics" style="height: 60px; width: auto;">
        </td>
        <td style="vertical-align: top; text-align: right;">
            <div style="font-size: 18px; font-weight: bold; color: #0B2545; margin-bottom: 6px;">Profit &amp; Loss Report</div>
            <div>Duration: <b>{{ $start_date }}</b> to <b>{{ $end_date }}</b></div>
            <div>Centres: <b>{{ $report['centres'] }}</b></div>
            <div>Date: <b>{{ \Carbon\Carbon::now()->format('Y-m-d') }}</b></div>
        </td>
    </tr>
</table>
