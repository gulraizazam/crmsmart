@extends('admin.layouts.inventory-erp')
@section('title', 'New Purchase Return')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Purchase Return</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.purchase-returns.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($items->isEmpty() || $stores->isEmpty())
                        <div class="alert alert-warning mb-0">Need active items and stores first.</div>
                    @else
                        <form method="post" action="{{ route('admin.inventory-erp.purchase-returns.store') }}" onsubmit="return confirm('Post purchase return? Stock will decrease.');">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Document date *</label>
                                    <input type="date" name="document_date" class="form-control" value="{{ old('document_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Supplier (optional)</label>
                                    <select name="supplier_id" class="form-control">
                                        <option value="">— None —</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-5">
                                    <label>Notes</label>
                                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                                </div>
                            </div>

                            <div class="table-responsive inv-opening-lines">
                                <table class="table" id="prt-lines-table">
                                    <thead>
                                        <tr>
                                            <th style="min-width:220px;">Item *</th>
                                            <th style="min-width:160px;">Store *</th>
                                            <th style="min-width:110px;">Qty *</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="prt-line">
                                            <td>
                                                <select name="lines[0][item_id]" class="form-control" required>
                                                    <option value="">Select item</option>
                                                    @foreach($items as $item)
                                                        <option value="{{ $item->id }}">{{ $item->sku }} — {{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="lines[0][store_id]" class="form-control" required>
                                                    <option value="">Select store</option>
                                                    @foreach($stores as $store)
                                                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="number" step="0.0001" min="0.0001" name="lines[0][quantity]" class="form-control" required></td>
                                            <td><button type="button" class="btn btn-light-danger btn-remove-line" disabled>&times;</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-light-primary mb-4" id="btn-add-line"><i class="la la-plus"></i> Add line</button>
                            <div>
                                <button type="submit" class="btn btn-primary">Save &amp; Post</button>
                            </div>
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
    var table = document.getElementById('prt-lines-table');
    if (!table) return;
    var tbody = table.querySelector('tbody');
    var addBtn = document.getElementById('btn-add-line');
    var index = 1;
    function refresh() {
        var rows = tbody.querySelectorAll('.prt-line');
        rows.forEach(function (row) {
            row.querySelector('.btn-remove-line').disabled = rows.length === 1;
        });
    }
    addBtn.addEventListener('click', function () {
        var clone = tbody.querySelector('.prt-line').cloneNode(true);
        clone.querySelectorAll('select, input').forEach(function (el) {
            var name = el.getAttribute('name');
            if (name) el.setAttribute('name', name.replace(/lines\[\d+]/, 'lines[' + index + ']'));
            if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = '';
        });
        tbody.appendChild(clone);
        index += 1;
        refresh();
    });
    tbody.addEventListener('click', function (e) {
        if (!e.target.classList.contains('btn-remove-line')) return;
        if (tbody.querySelectorAll('.prt-line').length <= 1) return;
        e.target.closest('.prt-line').remove();
        refresh();
    });
})();
</script>
@endpush
@endsection
