@extends('admin.layouts.inventory-erp')
@section('title', 'PO ' . $po->po_number)
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
                    <div class="card-title sneat-page-title-wrap">
                        <h3 class="card-label">{{ $po->po_number }}</h3>
                    </div>
                    <div class="card-toolbar">
                        @if($po->isDraft())
                            <form method="post" action="{{ route('admin.inventory-erp.purchase-orders.approve', $po->id) }}" class="d-inline" onsubmit="return confirm('Approve this PO for receiving?');">
                                @csrf
                                <button class="btn btn-primary" type="submit">Approve &amp; Order</button>
                            </form>
                        @endif
                        @if($po->canReceive())
                            <a href="{{ route('admin.inventory-erp.grns.create', ['po_id' => $po->id]) }}" class="btn btn-primary">Receive GRN</a>
                        @endif
                        @if(in_array($po->status, ['draft','ordered'], true))
                            <form method="post" action="{{ route('admin.inventory-erp.purchase-orders.cancel', $po->id) }}" class="d-inline" onsubmit="return confirm('Cancel this PO?');">
                                @csrf
                                <button class="btn btn-light-danger" type="submit">Cancel</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.inventory-erp.purchase-orders.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="inv-meta mb-3">
                        Supplier: <span class="inv-value">{{ optional($po->supplier)->name }}</span>
                        · Date: {{ optional($po->order_date)->format('d M Y') }}
                        · Status: <span class="badge badge-soft">{{ $po->status }}</span>
                        @if($po->notes) · {{ $po->notes }} @endif
                    </p>

                    <div class="table-responsive mb-4">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Store</th>
                                    <th class="text-right">Ordered</th>
                                    <th class="text-right">Received</th>
                                    <th class="text-right">Remaining</th>
                                    <th class="text-right">Unit cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($po->lines as $line)
                                    <tr>
                                        <td>{{ $line->line_no }}</td>
                                        <td>{{ optional($line->item)->sku }} — {{ optional($line->item)->name }}</td>
                                        <td>{{ optional($line->store)->name }}</td>
                                        <td class="text-right">{{ number_format((float) $line->quantity_ordered, 4) }}</td>
                                        <td class="text-right">{{ number_format((float) $line->quantity_received, 4) }}</td>
                                        <td class="text-right inv-value">{{ number_format($line->remainingQty(), 4) }}</td>
                                        <td class="text-right">{{ number_format((float) $line->unit_cost, 4) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($po->receipts->isNotEmpty())
                        <h5 class="mb-3">Receipts</h5>
                        <ul class="mb-0">
                            @foreach($po->receipts as $receipt)
                                <li>
                                    <a href="{{ route('admin.inventory-erp.grns.show', $receipt->id) }}">{{ $receipt->document_no }}</a>
                                    — {{ optional($receipt->document_date)->format('d M Y') }}
                                    ({{ $receipt->status }})
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
