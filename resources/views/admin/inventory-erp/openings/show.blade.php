@extends('admin.layouts.inventory-erp')
@section('title', 'Opening ' . $document->document_no)
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
                        <h3 class="card-label">{{ $document->document_no }}</h3>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.openings.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="inv-meta mb-3">
                        Date: {{ optional($document->document_date)->format('d M Y') }}
                        · Status: {{ $document->status }}
                        @if($document->notes) · {{ $document->notes }} @endif
                    </p>
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Store</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Unit cost</th>
                                    <th class="text-right">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($document->lines as $line)
                                    <tr>
                                        <td>{{ $line->line_no }}</td>
                                        <td>{{ optional($line->item)->sku }} — {{ optional($line->item)->name }}</td>
                                        <td>{{ optional($line->store)->name }}</td>
                                        <td class="text-right">{{ number_format((float) $line->quantity, 4) }}</td>
                                        <td class="text-right">{{ number_format((float) $line->unit_cost, 4) }}</td>
                                        <td class="text-right inv-value">{{ number_format((float) $line->quantity * (float) $line->unit_cost, 2) }}</td>
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
