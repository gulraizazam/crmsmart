@extends('admin.layouts.inventory-erp')
@section('title', 'New Adjustment')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">New Adjustment</h3></div>
                    <div class="card-toolbar"><a href="{{ route('admin.inventory-erp.adjustments.index') }}" class="btn btn-light">Back</a></div>
                </div>
                <div class="card-body">
                    <p class="inv-meta mb-3">Approval threshold: <span class="inv-value">{{ number_format($threshold, 2) }}</span> (elevated approval required above this value).</p>
                    @if($reasons->isEmpty() || $items->isEmpty() || $stores->isEmpty())
                        <div class="alert alert-warning mb-0">Need reasons, items, and stores first.</div>
                    @else
                        <form method="post" action="{{ route('admin.inventory-erp.adjustments.store') }}">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Reason *</label>
                                    <select name="adjustment_reason_id" class="form-control" required>
                                        <option value="">Select</option>
                                        @foreach($reasons as $reason)
                                            <option value="{{ $reason->id }}">{{ $reason->name }} ({{ $reason->direction }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Date *</label>
                                    <input type="date" name="document_date" class="form-control" value="{{ old('document_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="form-group col-md-5">
                                    <label>Notes</label>
                                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                                </div>
                            </div>
                            <div class="table-responsive inv-opening-lines">
                                <table class="table" id="adj-lines-table">
                                    <thead>
                                        <tr>
                                            <th>Item *</th><th>Store *</th><th>Dir *</th><th>Qty *</th><th>Unit cost (IN)</th><th>Avail</th><th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="adj-line">
                                            <td>
                                                <select name="lines[0][item_id]" class="form-control adj-item" required>
                                                    <option value="">Select</option>
                                                    @foreach($items as $item)
                                                        <option value="{{ $item->id }}">{{ $item->sku }} — {{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="lines[0][store_id]" class="form-control adj-store" required>
                                                    <option value="">Select</option>
                                                    @foreach($stores as $store)
                                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="lines[0][direction]" class="form-control" required>
                                                    <option value="out">OUT</option>
                                                    <option value="in">IN</option>
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.0001" min="0.0001" name="lines[0][quantity]" class="form-control" required></td>
                                            <td><input type="number" step="0.0001" min="0" name="lines[0][unit_cost]" class="form-control" placeholder="Required for IN"></td>
                                            <td class="adj-avail inv-meta">—</td>
                                            <td><button type="button" class="btn btn-light-danger btn-remove-line" disabled>&times;</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-light-primary mb-4" id="btn-add-line"><i class="la la-plus"></i> Add line</button>
                            <div><button type="submit" class="btn btn-primary">Save draft</button></div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
<script>
(function () {
    var table = document.getElementById('adj-lines-table');
    if (!table) return;
    var tbody = table.querySelector('tbody');
    var index = 1;
    var balances = @json($balances);
    function refresh() {
        tbody.querySelectorAll('.adj-line').forEach(function (row) {
            row.querySelector('.btn-remove-line').disabled = tbody.querySelectorAll('.adj-line').length === 1;
            updateAvail(row);
        });
    }
    function updateAvail(row) {
        var cell = row.querySelector('.adj-avail');
        var item = row.querySelector('.adj-item');
        var store = row.querySelector('.adj-store');
        if (!item.value || !store.value) { cell.textContent = '—'; return; }
        var bal = balances[item.value + ':' + store.value];
        cell.textContent = bal ? Number(bal.qty).toFixed(4) : '0';
    }
    tbody.addEventListener('change', function (e) {
        if (e.target.classList.contains('adj-item') || e.target.classList.contains('adj-store')) {
            updateAvail(e.target.closest('.adj-line'));
        }
    });
    document.getElementById('btn-add-line').addEventListener('click', function () {
        var clone = tbody.querySelector('.adj-line').cloneNode(true);
        clone.querySelectorAll('select, input').forEach(function (el) {
            var name = el.getAttribute('name');
            if (name) el.setAttribute('name', name.replace(/lines\[\d+]/, 'lines[' + index + ']'));
            if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = '';
        });
        clone.querySelector('.adj-avail').textContent = '—';
        tbody.appendChild(clone);
        index += 1;
        refresh();
    });
    tbody.addEventListener('click', function (e) {
        if (!e.target.classList.contains('btn-remove-line')) return;
        if (tbody.querySelectorAll('.adj-line').length <= 1) return;
        e.target.closest('.adj-line').remove();
        refresh();
    });
})();
</script>
@endpush
@endsection
