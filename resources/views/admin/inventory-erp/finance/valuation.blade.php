@extends('admin.layouts.inventory-erp')
@section('title', 'Inventory Valuation')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Inventory Valuation</h3></div>
                </div>
                <div class="card-body">
                    <form method="get" class="form-row inv-filters align-items-end">
                        <div class="col-md-3">
                            <select name="store_id" class="form-control">
                                <option value="">All stores</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="location_id" class="form-control">
                                <option value="">All centres</option>
                                @foreach($locations as $id => $name)
                                    <option value="{{ $id }}" {{ (string) request('location_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a href="{{ route('admin.inventory-erp.finance.valuation') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>

                    <p class="inv-meta mb-3">Total value: <span class="inv-value">{{ number_format($total, 2) }}</span></p>

                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>SKU</th><th>Item</th><th>Store</th><th>Centre</th>
                                    <th class="text-right">Qty</th><th>UOM</th>
                                    <th class="text-right">Avg cost</th><th class="text-right">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="inv-value">{{ $row->sku }}</td>
                                        <td>{{ $row->item_name }}</td>
                                        <td>{{ $row->store_name }}</td>
                                        <td>{{ $row->centre_name ?: '—' }}</td>
                                        <td class="text-right">{{ number_format((float) $row->quantity, 4) }}</td>
                                        <td>{{ $row->uom }}</td>
                                        <td class="text-right">{{ number_format((float) $row->avg_cost, 4) }}</td>
                                        <td class="text-right inv-value">{{ number_format((float) $row->value, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">No stock balances.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
