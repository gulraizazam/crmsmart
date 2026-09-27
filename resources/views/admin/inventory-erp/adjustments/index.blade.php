@extends('admin.layouts.inventory-erp')
@section('title', 'Stock Adjustments')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Adjustments</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.cycle-counts.create') }}" class="btn btn-light-primary">Cycle count</a>
                        <a href="{{ route('admin.inventory-erp.adjustments.create') }}" class="btn btn-primary"><i class="la la-plus"></i> New</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Document</th><th>Date</th><th>Reason</th><th>Status</th><th>Approved</th><th>Lines</th><th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $document)
                                    <tr>
                                        <td class="inv-value">{{ $document->document_no }}</td>
                                        <td>{{ optional($document->document_date)->format('d M Y') }}</td>
                                        <td>{{ optional($document->adjustmentReason)->name ?: '—' }}</td>
                                        <td><span class="badge badge-soft">{{ $document->status }}</span></td>
                                        <td>{{ $document->approved_at ? 'Yes' : 'No' }}</td>
                                        <td>{{ $document->lines_count }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.inventory-erp.adjustments.show', $document->id) }}" class="btn btn-sm btn-light-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No adjustments yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $documents->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
