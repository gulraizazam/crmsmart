@extends('admin.layouts.inventory-erp')
@section('title', 'Receive GRN')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Receive against PO</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.grns.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($openOrders->isEmpty())
                        <div class="alert alert-warning mb-0">No ordered purchase orders available for receiving.</div>
                    @elseif(!$po)
                        <form method="get" action="{{ route('admin.inventory-erp.grns.create') }}" class="form-row inv-filters align-items-end">
                            <div class="col-md-6">
                                <label>Purchase order *</label>
                                <select name="po_id" class="form-control" required>
                                    <option value="">Select PO</option>
                                    @foreach($openOrders as $order)
                                        <option value="{{ $order->id }}">{{ $order->po_number }} — {{ optional($order->supplier)->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-primary" type="submit">Continue</button>
                            </div>
                        </form>
                    @else
                        <p class="inv-meta mb-3">
                            PO <span class="inv-value">{{ $po->po_number }}</span>
                            · {{ optional($po->supplier)->name }}
                        </p>
                        <form method="post" action="{{ route('admin.inventory-erp.grns.store') }}" onsubmit="return confirm('Post GRN now? Stock will increase.');">
                            @csrf
                            <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Document date *</label>
                                    <input type="date" name="document_date" class="form-control" value="{{ old('document_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="form-group col-md-9">
                                    <label>Notes</label>
                                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-right">Ordered</th>
                                            <th class="text-right">Received</th>
                                            <th class="text-right">Remaining</th>
                                            <th>Store *</th>
                                            <th>Qty now</th>
                                            <th>Unit cost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($po->lines as $i => $line)
                                            @php $remaining = $line->remainingQty(); @endphp
                                            <tr>
                                                <td>
                                                    {{ optional($line->item)->sku }} — {{ optional($line->item)->name }}
                                                    <input type="hidden" name="lines[{{ $i }}][purchase_order_line_id]" value="{{ $line->id }}">
                                                </td>
                                                <td class="text-right">{{ number_format((float) $line->quantity_ordered, 4) }}</td>
                                                <td class="text-right">{{ number_format((float) $line->quantity_received, 4) }}</td>
                                                <td class="text-right inv-value">{{ number_format($remaining, 4) }}</td>
                                                <td>
                                                    <select name="lines[{{ $i }}][store_id]" class="form-control" {{ $remaining <= 0 ? 'disabled' : 'required' }}>
                                                        @foreach($stores as $store)
                                                            <option value="{{ $store->id }}" {{ (string) $store->id === (string) $line->store_id ? 'selected' : '' }}>{{ $store->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if($remaining <= 0)
                                                        <input type="hidden" name="lines[{{ $i }}][store_id]" value="{{ $line->store_id }}">
                                                    @endif
                                                </td>
                                                <td>
                                                    <input type="number" step="0.0001" min="0" max="{{ $remaining }}"
                                                           name="lines[{{ $i }}][quantity]" class="form-control"
                                                           value="{{ $remaining > 0 ? $remaining : 0 }}"
                                                           {{ $remaining <= 0 ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.0001" min="0"
                                                           name="lines[{{ $i }}][unit_cost]" class="form-control"
                                                           value="{{ number_format((float) $line->unit_cost, 4, '.', '') }}"
                                                           {{ $remaining <= 0 ? 'disabled' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-primary">Save &amp; Post GRN</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
