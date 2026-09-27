@extends('admin.layouts.inventory-erp')
@section('title', $store->exists ? 'Edit Store' : 'Add Store')
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
                        <h3 class="card-label">{{ $store->exists ? 'Edit Store' : 'Add Store' }}</h3>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.stores.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body inv-form">
                    <form method="post" action="{{ $store->exists ? route('admin.inventory-erp.stores.update', $store->id) : route('admin.inventory-erp.stores.store') }}">
                        @csrf
                        @if($store->exists) @method('PUT') @endif

                        <div class="form-row">
                            <div class="form-group col-md-5">
                                <label>Name *</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $store->name) }}" required>
                                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label>Type *</label>
                                <select name="store_type" class="form-control" required>
                                    <option value="warehouse" {{ old('store_type', $store->store_type)==='warehouse' ? 'selected' : '' }}>Warehouse</option>
                                    <option value="centre_store" {{ old('store_type', $store->store_type)==='centre_store' ? 'selected' : '' }}>Centre store</option>
                                    <option value="retail" {{ old('store_type', $store->store_type)==='retail' ? 'selected' : '' }}>Retail</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Centre (optional)</label>
                                <select name="location_id" class="form-control">
                                    <option value="">— None —</option>
                                    @foreach($locations as $id => $name)
                                        <option value="{{ $id }}" {{ (string) old('location_id', $store->location_id) === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox">
                                <input type="checkbox" name="active" value="1" {{ old('active', $store->active ?? true) ? 'checked' : '' }}>
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
