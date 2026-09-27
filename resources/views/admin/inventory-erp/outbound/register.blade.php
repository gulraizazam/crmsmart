@extends('admin.layouts.inventory-erp')
@section('title', 'Outbound Register')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Outbound Register</h3></div>
                </div>
                <div class="card-body">
                    <form method="get" class="form-row inv-filters align-items-end">
                        <div class="col-md-2">
                            <select name="document_type" class="form-control">
                                <option value="">All types</option>
                                <option value="sale" {{ request('document_type')==='sale' ? 'selected' : '' }}>Sale</option>
                                <option value="issue" {{ request('document_type')==='issue' ? 'selected' : '' }}>Issue</option>
                                <option value="sale_return" {{ request('document_type')==='sale_return' ? 'selected' : '' }}>Sale return</option>
                            </select>
                        </div>
                        <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control"></div>
                        <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control"></div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a href="{{ route('admin.inventory-erp.outbound.register') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Lines</th>
                                    <th>Notes</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documents as $document)
                                    @php
                                        $show = $document->document_type === 'sale'
                                            ? 'admin.inventory-erp.sales.show'
                                            : ($document->document_type === 'issue'
                                                ? 'admin.inventory-erp.issues.show'
                                                : 'admin.inventory-erp.sale-returns.show');
                                        $label = $document->document_type === 'sale' ? 'Sale' : ($document->document_type === 'issue' ? 'Issue' : 'Return');
                                    @endphp
                                    <tr>
                                        <td class="inv-value">{{ $document->document_no }}</td>
                                        <td><span class="badge badge-soft">{{ $label }}</span></td>
                                        <td>{{ optional($document->document_date)->format('d M Y') }}</td>
                                        <td>{{ $document->status }}</td>
                                        <td>{{ $document->lines_count }}</td>
                                        <td class="inv-meta">{{ \Illuminate\Support\Str::limit($document->notes, 40) ?: '—' }}</td>
                                        <td class="text-right">
                                            <a href="{{ route($show, $document->id) }}" class="btn btn-sm btn-light-primary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No outbound documents yet.</td></tr>
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
