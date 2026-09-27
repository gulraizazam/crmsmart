@extends('admin.layouts.inventory-erp')
@section('title', $item->exists ? 'Edit Item' : 'Add Item')
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
                        <h3 class="card-label">{{ $item->exists ? 'Edit Item' : 'Add Item' }}</h3>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.items.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body inv-form">
                    <form method="post" action="{{ $item->exists ? route('admin.inventory-erp.items.update', $item->id) : route('admin.inventory-erp.items.store') }}">
                        @csrf
                        @if($item->exists) @method('PUT') @endif

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>SKU *</label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku', $item->sku) }}" required>
                                @error('sku') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-5">
                                <label>Name *</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $item->name) }}" required>
                                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-2">
                                <label>Type *</label>
                                <select name="item_type" class="form-control" required>
                                    <option value="tradable" {{ old('item_type', $item->item_type)==='tradable' ? 'selected' : '' }}>Tradable</option>
                                    <option value="consumable" {{ old('item_type', $item->item_type)==='consumable' ? 'selected' : '' }}>Consumable</option>
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label>UOM *</label>
                                <input type="text" name="uom" class="form-control" value="{{ old('uom', $item->uom ?: 'pcs') }}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox">
                                <input type="checkbox" name="active" value="1" {{ old('active', $item->active ?? true) ? 'checked' : '' }}>
                                <span></span> Active
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
