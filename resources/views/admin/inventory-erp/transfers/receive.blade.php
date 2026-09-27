@extends('admin.layouts.inventory-erp')
@section('title', 'Receive ' . $transfer->transfer_no)
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Receive {{ $transfer->transfer_no }}</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.transfers.show', $transfer->id) }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="inv-meta mb-3">
                        {{ optional($transfer->fromStore)->name }} → {{ optional($transfer->toStore)->name }}
                    </p>
                    <form method="post" action="{{ route('admin.inventory-erp.transfers.receive.store', $transfer->id) }}" onsubmit="return confirm('Post receipt? Stock will arrive at destination.');">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Document date *</label>
                                <input type="date" name="document_date" class="form-control" value="{{ old('document_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="form-group col-md-9">
                                <label>Notes</label>
                                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-right">Dispatched</th>
                                        <th class="text-right">Received</th>
                                        <th class="text-right">In transit</th>
                                        <th>Qty now</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transfer->lines as $i => $line)
                                        @php $remaining = $line->remainingToReceive(); @endphp
                                        <tr>
                                            <td>
                                                {{ optional($line->item)->sku }} — {{ optional($line->item)->name }}
                                                <input type="hidden" name="lines[{{ $i }}][transfer_line_id]" value="{{ $line->id }}">
                                            </td>
                                            <td class="text-right">{{ number_format((float) $line->quantity_dispatched, 4) }}</td>
                                            <td class="text-right">{{ number_format((float) $line->quantity_received, 4) }}</td>
                                            <td class="text-right inv-value">{{ number_format($remaining, 4) }}</td>
                                            <td>
                                                <input type="number" step="0.0001" min="0" max="{{ $remaining }}"
                                                       name="lines[{{ $i }}][quantity]" class="form-control"
                                                       value="{{ $remaining > 0 ? $remaining : 0 }}"
                                                       {{ $remaining <= 0 ? 'disabled' : '' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-primary">Save &amp; Post receipt</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
