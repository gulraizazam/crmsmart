@extends('admin.layouts.inventory-erp')
@section('title', 'In Transit Stock')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">In Transit</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.transfers.index') }}" class="btn btn-light">All transfers</a>
                    </div>
                </div>
                <div class="card-body">
                    @forelse($transfers as $transfer)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="inv-value">{{ $transfer->transfer_no }}</span>
                                    <span class="inv-meta">
                                        · {{ optional($transfer->fromStore)->name }} → {{ optional($transfer->toStore)->name }}
                                        · Dispatched {{ optional($transfer->dispatched_at)->format('d M Y H:i') }}
                                    </span>
                                </div>
                                <a href="{{ route('admin.inventory-erp.transfers.receive', $transfer->id) }}" class="btn btn-sm btn-primary">Receive</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-head-custom table-vertical-center mb-0">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-right">In transit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transfer->lines as $line)
                                            @if($line->inTransitQty() > 0)
                                                <tr>
                                                    <td>{{ optional($line->item)->sku }} — {{ optional($line->item)->name }}</td>
                                                    <td class="text-right inv-value">{{ number_format($line->inTransitQty(), 4) }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Nothing in transit.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
