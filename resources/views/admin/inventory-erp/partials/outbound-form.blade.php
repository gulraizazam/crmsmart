@if($items->isEmpty() || $stores->isEmpty())
    <div class="alert alert-warning mb-0">Need active items and stores first.</div>
@else
    <form method="post" action="{{ $action }}" onsubmit="return confirm(@json($confirm));">
        @csrf
        <div class="form-row">
            <div class="form-group col-md-3">
                <label>Document date *</label>
                <input type="date" name="document_date" class="form-control" value="{{ old('document_date', date('Y-m-d')) }}" required>
            </div>
            @if(!empty($showCustomer))
                <div class="form-group col-md-3">
                    <label>Customer ref</label>
                    <input type="text" name="customer_ref" class="form-control" value="{{ old('customer_ref') }}" placeholder="Optional">
                </div>
            @endif
            <div class="form-group col-md-{{ !empty($showCustomer) ? 6 : 9 }}">
                <label>Notes</label>
                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
            </div>
        </div>

        <div class="table-responsive inv-opening-lines">
            <table class="table" id="ob-lines-table">
                <thead>
                    <tr>
                        <th style="min-width:220px;">Item *</th>
                        <th style="min-width:160px;">Store *</th>
                        <th style="min-width:100px;">Qty *</th>
                        @if(!empty($showUnitCost))
                            <th style="min-width:110px;">Unit cost</th>
                        @endif
                        @if(!empty($balances))
                            <th style="min-width:90px;">Avail</th>
                        @endif
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="ob-line">
                        <td>
                            <select name="lines[0][item_id]" class="form-control ob-item" required>
                                <option value="">Select item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->sku }} — {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select name="lines[0][store_id]" class="form-control ob-store" required>
                                <option value="">Select store</option>
                                @foreach($stores as $store)
                                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" step="0.0001" min="0.0001" name="lines[0][quantity]" class="form-control" required></td>
                        @if(!empty($showUnitCost))
                            <td><input type="number" step="0.0001" min="0" name="lines[0][unit_cost]" class="form-control" placeholder="Auto"></td>
                        @endif
                        @if(!empty($balances))
                            <td class="ob-avail inv-meta">—</td>
                        @endif
                        <td><button type="button" class="btn btn-light-danger btn-remove-line" disabled>&times;</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-light-primary mb-4" id="btn-add-line"><i class="la la-plus"></i> Add line</button>
        <div><button type="submit" class="btn btn-primary">Save &amp; Post</button></div>
    </form>
@endif

@push('js')
<script>
(function () {
    var table = document.getElementById('ob-lines-table');
    if (!table) return;
    var tbody = table.querySelector('tbody');
    var addBtn = document.getElementById('btn-add-line');
    var index = 1;
    var balances = @json($balances ?? []);

    function refresh() {
        tbody.querySelectorAll('.ob-line').forEach(function (row) {
            row.querySelector('.btn-remove-line').disabled = tbody.querySelectorAll('.ob-line').length === 1;
            updateAvail(row);
        });
    }
    function updateAvail(row) {
        var cell = row.querySelector('.ob-avail');
        if (!cell) return;
        var item = row.querySelector('.ob-item');
        var store = row.querySelector('.ob-store');
        if (!item || !store || !item.value || !store.value) {
            cell.textContent = '—';
            return;
        }
        var key = item.value + ':' + store.value;
        var qty = balances[key];
        cell.textContent = (qty === undefined || qty === null) ? '0' : Number(qty).toFixed(4);
    }
    tbody.addEventListener('change', function (e) {
        if (e.target.classList.contains('ob-item') || e.target.classList.contains('ob-store')) {
            updateAvail(e.target.closest('.ob-line'));
        }
    });
    addBtn.addEventListener('click', function () {
        var clone = tbody.querySelector('.ob-line').cloneNode(true);
        clone.querySelectorAll('select, input').forEach(function (el) {
            var name = el.getAttribute('name');
            if (name) el.setAttribute('name', name.replace(/lines\[\d+]/, 'lines[' + index + ']'));
            if (el.tagName === 'SELECT') el.selectedIndex = 0; else el.value = '';
        });
        var avail = clone.querySelector('.ob-avail');
        if (avail) avail.textContent = '—';
        tbody.appendChild(clone);
        index += 1;
        refresh();
    });
    tbody.addEventListener('click', function (e) {
        if (!e.target.classList.contains('btn-remove-line')) return;
        if (tbody.querySelectorAll('.ob-line').length <= 1) return;
        e.target.closest('.ob-line').remove();
        refresh();
    });
})();
</script>
@endpush
