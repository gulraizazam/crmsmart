@extends('admin.layouts.inventory-erp')
@section('title', 'Cycle Count')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Cycle Count</h3></div>
                    <div class="card-toolbar"><a href="{{ route('admin.inventory-erp.adjustments.index') }}" class="btn btn-light">Back</a></div>
                </div>
                <div class="card-body">
                    <form method="get" action="{{ route('admin.inventory-erp.cycle-counts.create') }}" class="form-row inv-filters align-items-end mb-4">
                        <div class="col-md-4">
                            <label>Store *</label>
                            <select name="store_id" class="form-control" required>
                                <option value="">Select store</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}" {{ (string) $storeId === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2"><button class="btn btn-primary" type="submit">Load</button></div>
                    </form>

                    @if($storeId)
                        <form method="post" action="{{ route('admin.inventory-erp.cycle-counts.store') }}" onsubmit="return confirm('Create adjustment draft for variances?');">
                            @csrf
                            <input type="hidden" name="store_id" value="{{ $storeId }}">
                            <div class="form-group col-md-3 px-0">
                                <label>Document date *</label>
                                <input type="date" name="document_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-right">System qty</th>
                                            <th>Counted qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rows as $i => $row)
                                            <tr>
                                                <td>
                                                    {{ $row->item->sku }} — {{ $row->item->name }}
                                                    <input type="hidden" name="counts[{{ $i }}][item_id]" value="{{ $row->item->id }}">
                                                </td>
                                                <td class="text-right inv-value">{{ number_format($row->system_qty, 4) }}</td>
                                                <td>
                                                    <input type="number" step="0.0001" min="0" name="counts[{{ $i }}][counted_qty]" class="form-control" placeholder="Leave blank to skip">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button type="submit" class="btn btn-primary">Create variance draft</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
