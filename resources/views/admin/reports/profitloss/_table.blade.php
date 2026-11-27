@php
    $columns = array_column($report['months'], null, 'key');
    if ($report['show_total']) {
        $columns['total'] = ['label' => 'Total', 'range' => null];
    }
    $colspan = count($columns) + 1;
    $compact = $compact ?? false;
    $pad = $compact ? '5px 7px' : '8px 12px';
    $cell = "padding: {$pad}; border-bottom: 1px solid #E5E7EB;";
    $num = $cell . ' text-align: right; white-space: nowrap;';
    $section = "padding: {$pad}; background: #EEF2F7; color: #0B2545; font-weight: bold;";
    $totalRow = 'background: #E5E9F0; font-weight: bold;';
    $head = "padding: {$pad}; text-align: right;" . ($compact ? '' : ' white-space: nowrap;');
@endphp
<table class="pl-table" style="width: 100%; border-collapse: collapse; font-size: {{ $compact ? '10px' : '13px' }}; color: #1F2937;">
    <thead>
        <tr style="background: #0B2545; color: #FFFFFF;">
            <th style="padding: {{ $pad }}; text-align: left;">PARTICULARS</th>
            @foreach($columns as $column)
                <th style="{{ $head }}">
                    {{ strtoupper($column['label']) }} (PKR)
                    @if($column['range'])
                        <div style="font-size: 11px; font-weight: normal; opacity: 0.8;">{{ $column['range'] }}</div>
                    @endif
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="{{ $colspan }}" style="{{ $section }}">Revenue</td></tr>
        @foreach($report['revenue'] as $row)
            <tr>
                <td style="{{ $cell }}">{{ $row['label'] }}</td>
                @foreach(array_keys($columns) as $key)
                    <td style="{{ $num }}">{{ \App\Reports\ProfitLoss::money($row['values'][$key]) }}</td>
                @endforeach
            </tr>
        @endforeach
        <tr style="{{ $totalRow }}">
            <td style="{{ $cell }}">TOTAL REVENUE</td>
            @foreach(array_keys($columns) as $key)
                <td style="{{ $num }}">{{ \App\Reports\ProfitLoss::money($report['total_revenue'][$key]) }}</td>
            @endforeach
        </tr>

        <tr><td colspan="{{ $colspan }}" style="{{ $section }}">Expenses</td></tr>
        @forelse($report['expenses'] as $row)
            <tr>
                <td style="{{ $cell }}">{{ $row['label'] }}</td>
                @foreach(array_keys($columns) as $key)
                    <td style="{{ $num }}">{{ \App\Reports\ProfitLoss::money($row['values'][$key]) }}</td>
                @endforeach
            </tr>
        @empty
            <tr><td colspan="{{ $colspan }}" style="{{ $cell }} text-align: center;">No expense categories found.</td></tr>
        @endforelse
        <tr style="{{ $totalRow }}">
            <td style="{{ $cell }}">TOTAL EXPENSES</td>
            @foreach(array_keys($columns) as $key)
                <td style="{{ $num }}">{{ \App\Reports\ProfitLoss::money($report['total_expenses'][$key]) }}</td>
            @endforeach
        </tr>

        <tr style="font-weight: bold;">
            <td style="{{ $cell }} background: #E5E9F0; color: #0B2545;">NET PROFIT / (LOSS)</td>
            @foreach(array_keys($columns) as $key)
                @php($value = $report['net'][$key])
                <td style="{{ $num }} background: {{ $value < 0 ? '#FDECEC' : '#ECFDF3' }}; color: {{ $value < 0 ? '#B91C1C' : '#047857' }};">
                    {{ \App\Reports\ProfitLoss::money($value) }}
                </td>
            @endforeach
        </tr>
    </tbody>
</table>

<div style="margin-top: 16px; font-size: {{ $compact ? '9px' : '12px' }}; color: #4B5563; line-height: 1.5;">
    <div style="background: #0B2545; color: #FFFFFF; padding: 6px 12px; font-weight: bold;">Notes</div>
    <div style="padding: 8px 12px; border: 1px solid #E5E7EB; border-top: none;">
        &bull; <b>Treatment Sales</b> count treatments when they are consumed (invoiced), not when the plan is paid for.<br>
        &bull; <b>Plan Settlements</b> are unrefunded plan balances kept when a plan is settled.
        <b>Product Sales</b> are completed sale orders, net of refunds.<br>
        &bull; <b>Expenses</b> are counted by expense date and include pending ones; rejected and voided expenses are excluded.<br>
        &bull; @if($report['include_general'])
            General (company-wide) expenses are included.
        @else
            General (company-wide) expenses are <b>not</b> included because specific centres are selected.
        @endif
    </div>
</div>
