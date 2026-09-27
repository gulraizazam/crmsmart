@extends('admin.layouts.inventory-erp')
@section('title', 'Post Opening Stock')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Post Opening Stock</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.openings.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
                <div class="card-body">
                    @if($items->isEmpty() || $stores->isEmpty())
                        <div class="alert alert-warning mb-0">
                            Create at least one active item and one active store before posting opening stock.
                        </div>
                    @else
                        <form method="post" action="{{ route('admin.inventory-erp.openings.store') }}" id="inv-opening-form">
                            @csrf
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Document date *</label>
                                    <input type="date" name="document_date" class="form-control" value="{{ old('document_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="form-group col-md-9">
                                    <label>Notes</label>
                                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Optional">
                                </div>
                            </div>

                            <div class="table-responsive inv-opening-lines">
                                <table class="table" id="opening-lines-table">
                                    <thead>
                                        <tr>
                                            <th style="min-width:240px;">Item *</th>
                                            <th style="min-width:180px;">Store *</th>
                                            <th style="min-width:110px;">Qty *</th>
                                            <th style="min-width:120px;">Unit cost *</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="opening-line">
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
                                            <td><input type="number" step="0.0001" min="0" name="lines[0][unit_cost]" class="form-control" required></td>
                                            <td><button type="button" class="btn btn-light-danger btn-remove-line" disabled>&times;</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <button type="button" class="btn btn-light-primary mb-4" id="btn-add-line"><i class="la la-plus"></i> Add line</button>
                            <div>
                                <button type="submit" class="btn btn-primary" onclick="return confirm('Post opening stock now? This will update balances.');">Save &amp; Post</button>
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
    var table = document.getElementById('opening-lines-table');
    if (!table) return;
    var tbody = table.querySelector('tbody');
    var addBtn = document.getElementById('btn-add-line');
    var index = 1;

    function refreshRemoveButtons() {
        var rows = tbody.querySelectorAll('.opening-line');
        rows.forEach(function (row) {
            var btn = row.querySelector('.btn-remove-line');
            btn.disabled = rows.length === 1;
        });
    }

    addBtn.addEventListener('click', function () {
        var first = tbody.querySelector('.opening-line');
        var clone = first.cloneNode(true);
        clone.querySelectorAll('select, input').forEach(function (el) {
            var name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/lines\[\d+]/, 'lines[' + index + ']'));
            }
            if (el.tagName === 'SELECT') {
                el.selectedIndex = 0;
            } else {
                el.value = '';
            }
        });
        tbody.appendChild(clone);
        index += 1;
        refreshRemoveButtons();
    });

    tbody.addEventListener('click', function (e) {
        if (!e.target.classList.contains('btn-remove-line')) return;
        var rows = tbody.querySelectorAll('.opening-line');
        if (rows.length <= 1) return;
        e.target.closest('.opening-line').remove();
        refreshRemoveButtons();
    });
})();
</script>
@endpush
@endsection
