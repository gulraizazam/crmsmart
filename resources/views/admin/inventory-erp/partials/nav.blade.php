@php
    $invTabs = [
        ['route' => 'admin.inventory-erp.items.index', 'label' => 'Items', 'perm' => 'inv_item_manage'],
        ['route' => 'admin.inventory-erp.stores.index', 'label' => 'Stores', 'perm' => 'inv_store_manage'],
        ['route' => 'admin.inventory-erp.openings.index', 'label' => 'Opening Stock', 'perms' => ['inv_adjust_manage', 'inv_move_manage']],
        ['route' => 'admin.inventory-erp.suppliers.index', 'label' => 'Suppliers', 'perm' => 'inv_purchase_manage'],
        ['route' => 'admin.inventory-erp.purchase-orders.index', 'label' => 'Purchase Orders', 'perm' => 'inv_purchase_manage'],
        ['route' => 'admin.inventory-erp.grns.index', 'label' => 'GRN', 'perm' => 'inv_purchase_manage'],
        ['route' => 'admin.inventory-erp.purchase-returns.index', 'label' => 'Returns', 'perm' => 'inv_purchase_manage'],
        ['route' => 'admin.inventory-erp.purchases.register', 'label' => 'Register', 'perms' => ['inv_purchase_manage', 'inv_report_manage']],
        ['route' => 'admin.inventory-erp.transfers.index', 'label' => 'Transfers', 'perm' => 'inv_transfer_manage'],
        ['route' => 'admin.inventory-erp.transfers.in-transit', 'label' => 'In Transit', 'perms' => ['inv_transfer_manage', 'inv_report_manage']],
        ['route' => 'admin.inventory-erp.issues.index', 'label' => 'Issues', 'perm' => 'inv_move_manage'],
        ['route' => 'admin.inventory-erp.sales.index', 'label' => 'Sales', 'perm' => 'inv_move_manage'],
        ['route' => 'admin.inventory-erp.sale-returns.index', 'label' => 'Sale Returns', 'perm' => 'inv_move_manage'],
        ['route' => 'admin.inventory-erp.outbound.register', 'label' => 'Outbound', 'perms' => ['inv_move_manage', 'inv_report_manage']],
        ['route' => 'admin.inventory-erp.adjustments.index', 'label' => 'Adjustments', 'perm' => 'inv_adjust_manage'],
        ['route' => 'admin.inventory-erp.cycle-counts.create', 'label' => 'Cycle Count', 'perm' => 'inv_adjust_manage'],
        ['route' => 'admin.inventory-erp.stock-controls.index', 'label' => 'Reorder', 'perm' => 'inv_adjust_manage'],
        ['route' => 'admin.inventory-erp.stock-controls.low-stock', 'label' => 'Low Stock', 'perms' => ['inv_adjust_manage', 'inv_report_manage']],
        ['route' => 'admin.inventory-erp.stock.balances', 'label' => 'Balances', 'perms' => ['inv_report_manage', 'inv_erp_manage']],
        ['route' => 'admin.inventory-erp.stock.card', 'label' => 'Stock Card', 'perms' => ['inv_report_manage', 'inv_erp_manage']],
        ['route' => 'admin.inventory-erp.finance.valuation', 'label' => 'Valuation', 'perms' => ['inv_report_manage', 'inv_erp_manage']],
        ['route' => 'admin.inventory-erp.finance.cogs', 'label' => 'COGS', 'perms' => ['inv_report_manage', 'inv_erp_manage']],
        ['route' => 'admin.inventory-erp.finance.periods', 'label' => 'Period Lock', 'perm' => 'inv_erp_manage'],
    ];
@endphp
<ul class="nav nav-pills mb-4 flex-wrap" style="gap:0.35rem;">
    @foreach ($invTabs as $tab)
        @php
            $allowed = isset($tab['perm'])
                ? (Gate::allows($tab['perm']) || Gate::allows('inv_erp_manage'))
                : collect($tab['perms'])->contains(function ($p) { return Gate::allows($p); });
            $isActive = request()->routeIs($tab['route']) || request()->routeIs(str_replace('.index', '.*', $tab['route'])) || request()->routeIs(str_replace('.create', '.*', $tab['route']));
            if ($tab['route'] === 'admin.inventory-erp.transfers.in-transit') {
                $isActive = request()->routeIs('admin.inventory-erp.transfers.in-transit');
            } elseif ($tab['route'] === 'admin.inventory-erp.transfers.index') {
                $isActive = request()->routeIs('admin.inventory-erp.transfers.*')
                    && ! request()->routeIs('admin.inventory-erp.transfers.in-transit');
            } elseif ($tab['route'] === 'admin.inventory-erp.stock-controls.low-stock') {
                $isActive = request()->routeIs('admin.inventory-erp.stock-controls.low-stock');
            } elseif ($tab['route'] === 'admin.inventory-erp.stock-controls.index') {
                $isActive = request()->routeIs('admin.inventory-erp.stock-controls.*')
                    && ! request()->routeIs('admin.inventory-erp.stock-controls.low-stock');
            } elseif ($tab['route'] === 'admin.inventory-erp.cycle-counts.create') {
                $isActive = request()->routeIs('admin.inventory-erp.cycle-counts.*');
            }
        @endphp
        @if($allowed)
            <li class="nav-item">
                <a class="nav-link {{ $isActive ? 'active' : '' }}"
                   href="{{ route($tab['route']) }}">{{ $tab['label'] }}</a>
            </li>
        @endif
    @endforeach
</ul>
