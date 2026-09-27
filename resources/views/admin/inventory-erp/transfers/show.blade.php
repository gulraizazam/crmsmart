@extends('admin.layouts.inventory-erp')
@section('title', 'Transfer ' . $transfer->transfer_no)
@section('content')
@push('css')
    <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=12" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/sneat-inventory-erp.css') }}?v=1" rel="stylesheet" type="text/css" />
@endpush

<div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-inv-page" id="kt_content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid sneat-page">

            <div class="card card-custom sneat-page-card">
                <div class="card-header">
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">{{ $transfer->transfer_no }}</h3></div>
                    <div class="card-toolbar">
                        @if($transfer->isDraft())
                            <form method="post" action="{{ route('admin.inventory-erp.transfers.approve', $transfer->id) }}" class="d-inline" onsubmit="return confirm('Approve this transfer?');">
                                @csrf
                                <button class="btn btn-primary" type="submit">Approve</button>
                            </form>
                        @endif
                        @if($transfer->canDispatch())
                            <form method="post" action="{{ route('admin.inventory-erp.transfers.dispatch', $transfer->id) }}" class="d-inline" onsubmit="return confirm('Dispatch now? Stock will leave the source store.');">
                                @csrf
                                <button class="btn btn-primary" type="submit">Dispatch</button>
                            </form>
                        @endif
                        @if($transfer->canReceive())
                            <a href="{{ route('admin.inventory-erp.transfers.receive', $transfer->id) }}" class="btn btn-primary">Receive</a>
                        @endif
                        @if(in_array($transfer->status, ['draft','approved','in_transit'], true))
                            <form method="post" action="{{ route('admin.inventory-erp.transfers.cancel', $transfer->id) }}" class="d-inline" onsubmit="return confirm('Cancel this transfer?');">
                                @csrf
                                <button class="btn btn-light-danger" type="submit">Cancel</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.inventory-erp.transfers.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="inv-meta mb-3">
                        From: <span class="inv-value">{{ optional($transfer->fromStore)->name }}</span>
                        → To: <span class="inv-value">{{ optional($transfer->toStore)->name }}</span>
                        · Date: {{ optional($transfer->transfer_date)->format('d M Y') }}
                        · Status: <span class="badge badge-soft">{{ str_replace('_',' ', $transfer->status) }}</span>
                        @if($transfer->notes) · {{ $transfer->notes }} @endif
                    </p>

                    <div class="table-responsive mb-4">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th class="text-right">Requested</th>
                                    <th class="text-right">Dispatched</th>
                                    <th class="text-right">Received</th>
                                    <th class="text-right">In transit</th>
                                    <th class="text-right">Unit cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfer->lines as $line)
                                    <tr>
                                        <td>{{ $line->line_no }}</td>
                                        <td>{{ optional($line->item)->sku }} — {{ optional($line->item)->name }}</td>
                                        <td class="text-right">{{ number_format((float) $line->quantity_requested, 4) }}</td>
                                        <td class="text-right">{{ number_format((float) $line->quantity_dispatched, 4) }}</td>
                                        <td class="text-right">{{ number_format((float) $line->quantity_received, 4) }}</td>
                                        <td class="text-right inv-value">{{ number_format($line->inTransitQty(), 4) }}</td>
                                        <td class="text-right">{{ $line->unit_cost !== null ? number_format((float) $line->unit_cost, 4) : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($transfer->stockDocuments->isNotEmpty())
                        <h5 class="mb-3">Stock documents</h5>
                        <ul class="mb-0">
                            @foreach($transfer->stockDocuments as $doc)
                                <li>
                                    {{ $doc->document_no }} — {{ optional($doc->document_date)->format('d M Y') }}
                                    ({{ $doc->status }}) {{ $doc->notes }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
