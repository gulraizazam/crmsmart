@extends('admin.layouts.inventory-erp')
@section('title', 'COGS Report')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">COGS by Period</h3></div>
                </div>
                <div class="card-body">
                    <form method="get" class="form-row inv-filters align-items-end">
                        <div class="col-md-2"><input type="date" name="from" value="{{ $from }}" class="form-control" required></div>
                        <div class="col-md-2"><input type="date" name="to" value="{{ $to }}" class="form-control" required></div>
                        <div class="col-md-2">
                            <select name="store_id" class="form-control">
                                <option value="">All stores</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string) request('store_id') === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="location_id" class="form-control">
                                <option value="">All centres</option>
                                @foreach($locations as $id => $name)
                                    <option value="{{ $id }}" {{ (string) request('location_id') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Run</button>
                            <a href="{{ route('admin.inventory-erp.finance.cogs') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>

                    <p class="inv-meta mb-3">Total COGS (sales + issues): <span class="inv-value">{{ number_format($total, 2) }}</span></p>

                    <h5 class="mb-2">By centre</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr><th>Centre</th><th class="text-right">Qty</th><th class="text-right">COGS</th></tr>
                            </thead>
                            <tbody>
                                @forelse($byCentre as $row)
                                    <tr>
                                        <td>{{ $row->centre_name }}</td>
                                        <td class="text-right">{{ number_format((float) $row->quantity, 4) }}</td>
                                        <td class="text-right inv-value">{{ number_format((float) $row->cogs, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted">No COGS in range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mb-2">Detail</h5>
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Date</th><th>Doc</th><th>Type</th><th>Item</th><th>Store</th><th>Centre</th>
                                    <th class="text-right">Qty</th><th class="text-right">Unit cost</th><th class="text-right">COGS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>{{ optional($row->moved_at)->format('d M Y') }}</td>
                                        <td class="inv-value">{{ $row->document_no }}</td>
                                        <td>{{ $row->document_type }}</td>
                                        <td>{{ $row->sku }} — {{ $row->item_name }}</td>
                                        <td>{{ $row->store_name }}</td>
                                        <td>{{ $row->centre_name ?: '—' }}</td>
                                        <td class="text-right">{{ number_format((float) $row->quantity, 4) }}</td>
                                        <td class="text-right">{{ number_format((float) $row->unit_cost, 4) }}</td>
                                        <td class="text-right inv-value">{{ number_format((float) $row->cogs, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted">No lines in range.</td></tr>
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
