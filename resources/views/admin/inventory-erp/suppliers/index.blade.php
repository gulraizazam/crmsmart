@extends('admin.layouts.inventory-erp')
@section('title', 'Suppliers')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Suppliers</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.suppliers.create') }}" class="btn btn-primary"><i class="la la-plus"></i> Add Supplier</a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" class="form-row inv-filters align-items-end">
                        <div class="col-md-4">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search name / phone / email">
                        </div>
                        <div class="col-md-2">
                            <select name="active" class="form-control">
                                <option value="">All status</option>
                                <option value="1" {{ request('active')==='1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ request('active')==='0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a href="{{ route('admin.inventory-erp.suppliers.index') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suppliers as $supplier)
                                    <tr>
                                        <td class="inv-value">{{ $supplier->name }}</td>
                                        <td>{{ $supplier->phone ?: '—' }}</td>
                                        <td>{{ $supplier->email ?: '—' }}</td>
                                        <td>
                                            @if($supplier->active)
                                                <span class="badge badge-soft-success">Active</span>
                                            @else
                                                <span class="badge badge-soft-muted">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.inventory-erp.suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-light-primary">Edit</a>
                                            <form action="{{ route('admin.inventory-erp.suppliers.destroy', $supplier->id) }}" method="post" class="d-inline" onsubmit="return confirm('Delete this supplier?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-light-danger" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No suppliers yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $suppliers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
