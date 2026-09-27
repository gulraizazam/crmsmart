@extends('admin.layouts.inventory-erp')
@section('title', 'Goods Receipts')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Goods Receipts (GRN)</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.grns.create') }}" class="btn btn-primary"><i class="la la-plus"></i> Receive</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Lines</th>
                                    <th>Notes</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $document)
                                    <tr>
                                        <td class="inv-value">{{ $document->document_no }}</td>
                                        <td>{{ optional($document->document_date)->format('d M Y') }}</td>
                                        <td><span class="badge badge-soft">{{ $document->status }}</span></td>
                                        <td>{{ $document->lines_count }}</td>
                                        <td class="inv-meta">{{ \Illuminate\Support\Str::limit($document->notes, 40) ?: '—' }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.inventory-erp.grns.show', $document->id) }}" class="btn btn-sm btn-light-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No GRNs yet.</td></tr>
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
