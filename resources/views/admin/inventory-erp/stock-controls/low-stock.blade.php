@extends('admin.layouts.inventory-erp')
@section('title', 'Low Stock')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Low Stock</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.stock-controls.index') }}" class="btn btn-light">Reorder levels</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>SKU</th><th>Item</th><th>Store</th>
                                    <th class="text-right">On hand</th><th class="text-right">Level</th>
                                    <th class="text-right">Suggest qty</th><th>UOM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td class="inv-value">{{ $row->sku }}</td>
                                        <td>{{ $row->item_name }}</td>
                                        <td>{{ $row->store_name }}</td>
                                        <td class="text-right">{{ number_format((float) $row->quantity, 4) }}</td>
                                        <td class="text-right">{{ number_format((float) $row->reorder_level, 4) }}</td>
                                        <td class="text-right inv-value">{{ number_format((float) $row->reorder_qty, 4) }}</td>
                                        <td>{{ $row->uom }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">Nothing below reorder level.</td></tr>
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
