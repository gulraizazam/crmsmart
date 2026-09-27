@php
    $invActive = function (...$patterns) {
        foreach ($patterns as $pattern) {
            if (request()->routeIs($pattern)) {
                return 'active';
            }
        }
        return '';
    };
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme inv-erp-sidebar">
    <div class="app-brand">
        <a href="{{ route('admin.inventory-erp.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo">
                <i class="la la-boxes" style="font-size:1.35rem;"></i>
            </span>
            <span class="app-brand-text">Inventory ERP</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-close d-xl-none" id="layout-menu-close" aria-label="Close menu">&times;</a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <li class="menu-item {{ $invActive('admin.inventory-erp.dashboard') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.dashboard') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-dashboard"></i></span>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <li class="menu-header"><span class="menu-header-text">Master Data</span></li>
        @can('inv_item_manage')
        <li class="menu-item {{ $invActive('admin.inventory-erp.items.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.items.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-cube"></i></span>
                <span class="menu-text">Items</span>
            </a>
        </li>
        @endcan
        @can('inv_store_manage')
        <li class="menu-item {{ $invActive('admin.inventory-erp.stores.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.stores.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-building"></i></span>
                <span class="menu-text">Stores</span>
            </a>
        </li>
        @endcan
        @if(Gate::allows('inv_adjust_manage') || Gate::allows('inv_move_manage'))
        <li class="menu-item {{ $invActive('admin.inventory-erp.openings.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.openings.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-inbox"></i></span>
                <span class="menu-text">Opening Stock</span>
            </a>
        </li>
        @endif

        @can('inv_purchase_manage')
        <li class="menu-header"><span class="menu-header-text">Purchasing</span></li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.suppliers.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.suppliers.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-truck"></i></span>
                <span class="menu-text">Suppliers</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.purchase-orders.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.purchase-orders.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-file-text"></i></span>
                <span class="menu-text">Purchase Orders</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.grns.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.grns.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-download"></i></span>
                <span class="menu-text">GRN</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.purchase-returns.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.purchase-returns.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-undo"></i></span>
                <span class="menu-text">Purchase Returns</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.purchases.register') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.purchases.register') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-list-alt"></i></span>
                <span class="menu-text">Purchase Register</span>
            </a>
        </li>
        @endcan

        @can('inv_transfer_manage')
        <li class="menu-header"><span class="menu-header-text">Transfers</span></li>
        <li class="menu-item {{ request()->routeIs('admin.inventory-erp.transfers.*') && ! request()->routeIs('admin.inventory-erp.transfers.in-transit') ? 'active' : '' }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.transfers.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-exchange"></i></span>
                <span class="menu-text">Transfers</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.transfers.in-transit') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.transfers.in-transit') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-ship"></i></span>
                <span class="menu-text">In Transit</span>
            </a>
        </li>
        @endcan

        @can('inv_move_manage')
        <li class="menu-header"><span class="menu-header-text">Outbound</span></li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.issues.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.issues.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-share"></i></span>
                <span class="menu-text">Internal Issues</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.sales.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.sales.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-shopping-cart"></i></span>
                <span class="menu-text">Retail Sales</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.sale-returns.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.sale-returns.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-reply"></i></span>
                <span class="menu-text">Sales Returns</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.outbound.register') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.outbound.register') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-book"></i></span>
                <span class="menu-text">Outbound Register</span>
            </a>
        </li>
        @endcan

        @can('inv_adjust_manage')
        <li class="menu-header"><span class="menu-header-text">Stock Control</span></li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.adjustments.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.adjustments.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-sliders"></i></span>
                <span class="menu-text">Adjustments</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.cycle-counts.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.cycle-counts.create') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-check-square"></i></span>
                <span class="menu-text">Cycle Count</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.adjustment-reasons.*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.adjustment-reasons.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-tags"></i></span>
                <span class="menu-text">Adj. Reasons</span>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.inventory-erp.stock-controls.*') && ! request()->routeIs('admin.inventory-erp.stock-controls.low-stock') ? 'active' : '' }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.stock-controls.index') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-level-down"></i></span>
                <span class="menu-text">Reorder Levels</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.stock-controls.low-stock') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.stock-controls.low-stock') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-warning"></i></span>
                <span class="menu-text">Low Stock</span>
            </a>
        </li>
        @endcan

        @if(Gate::allows('inv_report_manage') || Gate::allows('inv_erp_manage'))
        <li class="menu-header"><span class="menu-header-text">Reports &amp; Finance</span></li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.stock.balances') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.stock.balances') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-database"></i></span>
                <span class="menu-text">Balances</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.stock.card') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.stock.card') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-id-card"></i></span>
                <span class="menu-text">Stock Card</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.finance.valuation') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.finance.valuation') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-money"></i></span>
                <span class="menu-text">Valuation</span>
            </a>
        </li>
        <li class="menu-item {{ $invActive('admin.inventory-erp.finance.cogs') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.finance.cogs') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-pie-chart"></i></span>
                <span class="menu-text">COGS</span>
            </a>
        </li>
        @endif
        @can('inv_erp_manage')
        <li class="menu-item {{ $invActive('admin.inventory-erp.finance.periods*') }}" aria-haspopup="true">
            <a href="{{ route('admin.inventory-erp.finance.periods') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-lock"></i></span>
                <span class="menu-text">Period Locks</span>
            </a>
        </li>
        @endcan

        <li class="menu-header"><span class="menu-header-text">CRM</span></li>
        <li class="menu-item" aria-haspopup="true">
            <a href="{{ route('admin.home') }}" class="menu-link">
                <span class="svg-icon menu-icon"><i class="la la-arrow-left"></i></span>
                <span class="menu-text">Back to CRM</span>
            </a>
        </li>
    </ul>
</aside>
