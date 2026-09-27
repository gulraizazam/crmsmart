<div class="card-header">
    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">{{ $document->document_no }}</h3></div>
    <div class="card-toolbar">
        @if($document->status === 'posted' && Gate::allows('inv_move_manage'))
            <form method="post" action="{{ route($reverseRoute, $document->id) }}" class="d-inline" onsubmit="return confirm('Reverse this document?');">
                @csrf
                <button class="btn btn-light-danger" type="submit">Reverse</button>
            </form>
        @endif
        <a href="{{ route($backRoute) }}" class="btn btn-light">Back</a>
    </div>
</div>
<div class="card-body">
    <p class="inv-meta mb-3">
        Date: {{ optional($document->document_date)->format('d M Y') }}
        · Status: {{ $document->status }}
        @isset($cogs)
            · COGS: <span class="inv-value">{{ number_format((float) $cogs, 2) }}</span>
        @endisset
        @if($document->notes) · {{ $document->notes }} @endif
    </p>
    <div class="table-responsive">
        <table class="table table-head-custom table-vertical-center">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Store</th>
                    <th>Dir</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit cost</th>
                    <th class="text-right">Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($document->lines as $line)
                    <tr>
                        <td>{{ $line->line_no }}</td>
                        <td>{{ optional($line->item)->sku }} — {{ optional($line->item)->name }}</td>
                        <td>{{ optional($line->store)->name }}</td>
                        <td>{{ strtoupper($line->direction) }}</td>
                        <td class="text-right">{{ number_format((float) $line->quantity, 4) }}</td>
                        <td class="text-right">{{ number_format((float) $line->unit_cost, 4) }}</td>
                        <td class="text-right inv-value">{{ number_format((float) $line->quantity * (float) $line->unit_cost, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
