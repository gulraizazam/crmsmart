@extends('admin.layouts.inventory-erp')
@section('title', 'Reorder Levels')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Reorder Levels</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.stock-controls.low-stock') }}" class="btn btn-light-primary">Low stock</a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.inventory-erp.stock-controls.store') }}" class="form-row inv-filters align-items-end mb-4">
                        @csrf
                        <div class="col-md-3">
                            <select name="item_id" class="form-control" required>
                                <option value="">Item *</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->sku }} — {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="store_id" class="form-control" required>
                                <option value="">Store *</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2"><input type="number" step="0.0001" min="0" name="reorder_level" class="form-control" placeholder="Level *" required></div>
                        <div class="col-md-2"><input type="number" step="0.0001" min="0" name="reorder_qty" class="form-control" placeholder="Reorder qty *" required></div>
                        <div class="col-md-2"><button class="btn btn-primary" type="submit">Save</button></div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr><th>Item</th><th>Store</th><th class="text-right">Level</th><th class="text-right">Reorder qty</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse($controls as $control)
                                    <tr>
                                        <td>{{ optional($control->item)->sku }} — {{ optional($control->item)->name }}</td>
                                        <td>{{ optional($control->store)->name }}</td>
                                        <td class="text-right">{{ number_format((float) $control->reorder_level, 4) }}</td>
                                        <td class="text-right">{{ number_format((float) $control->reorder_qty, 4) }}</td>
                                        <td class="text-right">
                                            <form method="post" action="{{ route('admin.inventory-erp.stock-controls.destroy', $control->id) }}" class="d-inline" onsubmit="return confirm('Remove?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-light-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No reorder levels set.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $controls->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
