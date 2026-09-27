@extends('admin.layouts.inventory-erp')
@section('title', 'Purchase Register')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Purchase Register</h3></div>
                </div>
                <div class="card-body">
                    <form method="get" class="form-row inv-filters align-items-end">
                        <div class="col-md-2">
                            <select name="document_type" class="form-control">
                                <option value="">All types</option>
                                <option value="purchase_receipt" {{ request('document_type')==='purchase_receipt' ? 'selected' : '' }}>GRN</option>
                                <option value="purchase_return" {{ request('document_type')==='purchase_return' ? 'selected' : '' }}>Return</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control" placeholder="From">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control" placeholder="To">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary" type="submit">Filter</button>
                            <a href="{{ route('admin.inventory-erp.purchases.register') }}" class="btn btn-light">Reset</a>
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
                                    <tr>
                                        <td class="inv-value">{{ $document->document_no }}</td>
                                        <td><span class="badge badge-soft">{{ $document->document_type === 'purchase_receipt' ? 'GRN' : 'Return' }}</span></td>
                                        <td>{{ optional($document->document_date)->format('d M Y') }}</td>
                                        <td>{{ $document->status }}</td>
                                        <td>{{ $document->lines_count }}</td>
                                        <td class="inv-meta">{{ \Illuminate\Support\Str::limit($document->notes, 40) ?: '—' }}</td>
                                        <td class="text-right">
                                            @if($document->document_type === 'purchase_receipt')
                                                <a href="{{ route('admin.inventory-erp.grns.show', $document->id) }}" class="btn btn-sm btn-light-primary">View</a>
                                            @else
                                                <a href="{{ route('admin.inventory-erp.purchase-returns.show', $document->id) }}" class="btn btn-sm btn-light-primary">View</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No purchase documents yet.</td></tr>
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
