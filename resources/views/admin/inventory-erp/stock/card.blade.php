@extends('admin.layouts.inventory-erp')
@section('title', 'Stock Card')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Stock Card</h3></div>
                </div>
                <div class="card-body">
                    <form method="get" class="form-row inv-filters align-items-end">
                        <div class="col-md-4">
                            <label>Item *</label>
                            <select name="item_id" class="form-control" required>
                                <option value="">Select item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ (string) $itemId === (string) $item->id ? 'selected' : '' }}>{{ $item->sku }} — {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Store *</label>
                            <select name="store_id" class="form-control" required>
                                <option value="">Select store</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string) $storeId === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Show</button>
                        </div>
                    </form>

                    @if($itemId && $storeId)
                        <p class="inv-meta mb-3">
                            Current balance:
                            <span class="inv-value">{{ number_format((float) optional($balance)->quantity, 4) }}</span>
                            · Avg cost:
                            <span class="inv-value">{{ number_format((float) optional($balance)->avg_cost, 4) }}</span>
                            · Value:
                            <span class="inv-value">{{ number_format((float) optional($balance)->quantity * (float) optional($balance)->avg_cost, 2) }}</span>
                        </p>

                        <div class="table-responsive">
                            <table class="table table-head-custom table-vertical-center">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Document</th>
                                        <th>Type</th>
                                        <th>Direction</th>
                                        <th class="text-right">Qty</th>
                                        <th class="text-right">Unit cost</th>
                                        <th class="text-right">Balance after</th>
                                        <th class="text-right">Avg after</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($movements as $move)
                                        <tr>
                                            <td>{{ optional($move->moved_at)->format('d M Y H:i') }}</td>
                                            <td class="inv-value">{{ optional($move->document)->document_no }}</td>
                                            <td>{{ optional($move->document)->document_type }}</td>
                                            <td>
                                                @if($move->direction === 'in')
                                                    <span class="badge badge-soft-success">IN</span>
                                                @else
                                                    <span class="badge badge-soft-muted">OUT</span>
                                                @endif
                                            </td>
                                            <td class="text-right">{{ number_format((float) $move->quantity, 4) }}</td>
                                            <td class="text-right">{{ number_format((float) $move->unit_cost, 4) }}</td>
                                            <td class="text-right">{{ number_format((float) $move->balance_qty_after, 4) }}</td>
                                            <td class="text-right">{{ number_format((float) $move->avg_cost_after, 4) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center text-muted">No movements for this item/store.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($movements instanceof \Illuminate\Pagination\AbstractPaginator)
                            {{ $movements->links() }}
                        @endif
                    @else
                        <p class="text-muted mb-0">Select an item and store to view the ledger.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
