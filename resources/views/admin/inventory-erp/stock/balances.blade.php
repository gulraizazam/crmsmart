@extends('admin.layouts.inventory-erp')
@section('title', 'Stock Balances')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Stock Balances</h3></div>
                </div>
                <div class="card-body">
                    <form method="get" class="form-row inv-filters align-items-end">
                        <div class="col-md-3">
                            <select name="item_id" class="form-control">
                                <option value="">All items</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}" {{ (string) request('item_id') === (string) $item->id ? 'selected' : '' }}>{{ $item->sku }} — {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="store_id" class="form-control">
                                <option value="">All stores</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="checkbox mt-2">
                                <input type="checkbox" name="non_zero" value="1" {{ request('non_zero')==='1' ? 'checked' : '' }}>
                                <span></span> Non-zero only
                            </label>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a href="{{ route('admin.inventory-erp.stock.balances') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>SKU</th>
                                    <th>Item</th>
                                    <th>Store</th>
                                    <th class="text-right">Qty</th>
                                    <th>UOM</th>
                                    <th class="text-right">Avg cost</th>
                                    <th class="text-right">Value</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($balances as $row)
                                    <tr>
                                        <td class="inv-value">{{ $row->item_sku }}</td>
                                        <td>{{ $row->item_name }}</td>
                                        <td>{{ $row->store_name }}</td>
                                        <td class="text-right">{{ number_format((float) $row->quantity, 4) }}</td>
                                        <td>{{ $row->item_uom }}</td>
                                        <td class="text-right">{{ number_format((float) $row->avg_cost, 4) }}</td>
                                        <td class="text-right inv-value">{{ number_format((float) $row->quantity * (float) $row->avg_cost, 2) }}</td>
                                        <td class="text-right">
                                            <a class="btn btn-sm btn-light-primary"
                                               href="{{ route('admin.inventory-erp.stock.card', ['item_id' => $row->item_id, 'store_id' => $row->store_id]) }}">Card</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">No balances yet. Post opening stock first.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $balances->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
