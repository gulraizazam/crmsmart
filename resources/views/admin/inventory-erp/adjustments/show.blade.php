@extends('admin.layouts.inventory-erp')
@section('title', $document->document_no)
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">{{ $document->document_no }}</h3></div>
                    <div class="card-toolbar">
                        @if($document->isDraft() && !$document->isApproved() && $canApprove)
                            <form method="post" action="{{ route('admin.inventory-erp.adjustments.approve', $document->id) }}" class="d-inline" onsubmit="return confirm('Approve this adjustment?');">
                                @csrf
                                <button class="btn btn-primary" type="submit">Approve</button>
                            </form>
                        @endif
                        @if($document->isDraft() && $document->isApproved() && Gate::allows('inv_adjust_manage'))
                            <form method="post" action="{{ route('admin.inventory-erp.adjustments.post', $document->id) }}" class="d-inline" onsubmit="return confirm('Post to stock now?');">
                                @csrf
                                <button class="btn btn-primary" type="submit">Post</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.inventory-erp.adjustments.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="inv-meta mb-3">
                        Reason: <span class="inv-value">{{ optional($document->adjustmentReason)->name }}</span>
                        · Status: {{ $document->status }}
                        · Approved: {{ $document->isApproved() ? 'Yes' : 'No' }}
                        · Value: <span class="inv-value">{{ number_format($document->absoluteValue(), 2) }}</span>
                        @if($needsElevated)
                            · <span class="text-danger">Above threshold ({{ number_format($threshold, 2) }}) — needs elevated approval</span>
                        @endif
                        @if($document->notes) · {{ $document->notes }} @endif
                    </p>
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>#</th><th>Item</th><th>Store</th><th>Dir</th>
                                    <th class="text-right">Qty</th><th class="text-right">Unit cost</th><th class="text-right">Value</th><th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($document->lines as $line)
                                    <tr>
                                        <td>{{ $line->line_no }}</td>
                                        <td>{{ optional($line->item)->sku }} — {{ optional($line->item)->name }}</td>
                                        <td>{{ optional($line->store)->name }}</td>
                                        <td>{{ strtoupper($line->direction) }}</td>
                                        <td class="text-right">{{ number_format((float) $line->quantity, 4) }}</td>
                                        <td class="text-right">{{ number_format((float) $line->unit_cost, 4) }}</td>
                                        <td class="text-right inv-value">{{ number_format((float) $line->quantity * (float) $line->unit_cost, 2) }}</td>
                                        <td class="inv-meta">{{ $line->notes ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
