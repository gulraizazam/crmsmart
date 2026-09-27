@extends('admin.layouts.inventory-erp')
@section('title', 'Transfers')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Transfers</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.transfers.in-transit') }}" class="btn btn-light-primary">In transit</a>
                        <a href="{{ route('admin.inventory-erp.transfers.create') }}" class="btn btn-primary"><i class="la la-plus"></i> New Transfer</a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="get" class="form-row inv-filters align-items-end">
                        <div class="col-md-3">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Transfer no">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">All status</option>
                                @foreach(['draft','approved','in_transit','completed','cancelled'] as $st)
                                    <option value="{{ $st }}" {{ request('status')===$st ? 'selected' : '' }}>{{ str_replace('_',' ', $st) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a href="{{ route('admin.inventory-erp.transfers.index') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Transfer</th>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Status</th>
                                    <th>Lines</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transfers as $transfer)
                                    <tr>
                                        <td class="inv-value">{{ $transfer->transfer_no }}</td>
                                        <td>{{ optional($transfer->transfer_date)->format('d M Y') }}</td>
                                        <td>{{ optional($transfer->fromStore)->name }}</td>
                                        <td>{{ optional($transfer->toStore)->name }}</td>
                                        <td><span class="badge badge-soft">{{ str_replace('_',' ', $transfer->status) }}</span></td>
                                        <td>{{ $transfer->lines_count }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.inventory-erp.transfers.show', $transfer->id) }}" class="btn btn-sm btn-light-primary">View</a>
                                            @if($transfer->status === 'in_transit')
                                                <a href="{{ route('admin.inventory-erp.transfers.receive', $transfer->id) }}" class="btn btn-sm btn-primary">Receive</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No transfers yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $transfers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
