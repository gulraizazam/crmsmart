@extends('admin.layouts.inventory-erp')
@section('title', $supplier->exists ? 'Edit Supplier' : 'Add Supplier')
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
                        <h3 class="card-label">{{ $supplier->exists ? 'Edit Supplier' : 'Add Supplier' }}</h3>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.suppliers.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body inv-form">
                    <form method="post" action="{{ $supplier->exists ? route('admin.inventory-erp.suppliers.update', $supplier->id) : route('admin.inventory-erp.suppliers.store') }}">
                        @csrf
                        @if($supplier->exists) @method('PUT') @endif

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Name *</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required>
                                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="form-group col-md-3">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $supplier->address) }}">
                        </div>
                        <div class="form-group">
                            <label class="checkbox">
                                <input type="checkbox" name="active" value="1" {{ old('active', $supplier->active ?? true) ? 'checked' : '' }}>
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
