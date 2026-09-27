@extends('admin.layouts.inventory-erp')
@section('title', 'Purchase Orders')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Purchase Orders</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.purchase-orders.create') }}" class="btn btn-primary"><i class="la la-plus"></i> New PO</a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" class="form-row inv-filters align-items-end">
                        <div class="col-md-3">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="PO number">
                        </div>
                        <div class="col-md-3">
                            <select name="supplier_id" class="form-control">
                                <option value="">All suppliers</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ (string) request('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">All status</option>
                                @foreach(['draft','ordered','closed','cancelled'] as $st)
                                    <option value="{{ $st }}" {{ request('status')===$st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a href="{{ route('admin.inventory-erp.purchase-orders.index') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>PO</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Status</th>
                                    <th>Lines</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    <tr>
                                        <td class="inv-value">{{ $order->po_number }}</td>
                                        <td>{{ optional($order->order_date)->format('d M Y') }}</td>
                                        <td>{{ optional($order->supplier)->name }}</td>
                                        <td><span class="badge badge-soft">{{ $order->status }}</span></td>
                                        <td>{{ $order->lines_count }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.inventory-erp.purchase-orders.show', $order->id) }}" class="btn btn-sm btn-light-primary">View</a>
                                            @if($order->status === 'ordered')
                                                <a href="{{ route('admin.inventory-erp.grns.create', ['po_id' => $order->id]) }}" class="btn btn-sm btn-primary">Receive</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No purchase orders yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
