@extends('admin.layouts.inventory-erp')
@section('title', 'Inventory Dashboard')
@section('content')
@push('css')
    <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=12" rel="stylesheet" type="text/css" />
@endpush

<div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-inv-page" id="kt_content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid sneat-page">
            <div class="inv-dash-hero mb-4">
                <div>
                    <h2 class="inv-dash-title mb-1">Inventory ERP</h2>
                    <p class="inv-dash-sub mb-0">Stock, purchasing, transfers, and costing in one place.</p>
                </div>
                <a href="{{ route('admin.home') }}" class="btn btn-light-primary btn-sm">
                    <i class="la la-arrow-left"></i> Back to CRM
                </a>
            </div>

            <div class="row inv-dash-kpis">
                <div class="col-6 col-md-4 col-xl-2 mb-3">
                    <div class="inv-kpi">
                        <div class="inv-kpi-label">Active Items</div>
                        <div class="inv-kpi-value">{{ number_format($stats['items']) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2 mb-3">
                    <div class="inv-kpi">
                        <div class="inv-kpi-label">Stores</div>
                        <div class="inv-kpi-value">{{ number_format($stats['stores']) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2 mb-3">
                    <div class="inv-kpi">
                        <div class="inv-kpi-label">On-hand Value</div>
                        <div class="inv-kpi-value">{{ number_format($stats['on_hand_value'], 0) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2 mb-3">
                    <div class="inv-kpi">
                        <div class="inv-kpi-label">In Transit</div>
                        <div class="inv-kpi-value">{{ number_format($stats['in_transit']) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2 mb-3">
                    <div class="inv-kpi {{ $stats['low_stock'] > 0 ? 'inv-kpi-warn' : '' }}">
                        <div class="inv-kpi-label">Low Stock</div>
                        <div class="inv-kpi-value">{{ number_format($stats['low_stock']) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2 mb-3">
                    <div class="inv-kpi">
                        <div class="inv-kpi-label">Posted Today</div>
                        <div class="inv-kpi-value">{{ number_format($stats['docs_today']) }}</div>
                    </div>
                </div>
            </div>

            <div class="card card-custom sneat-page-card mb-4">
                <div class="card-header">
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Quick actions</h3></div>
                </div>
                <div class="card-body">
                    <div class="inv-quick-grid">
                        @foreach($quickLinks as $link)
                            <a href="{{ route($link['route']) }}" class="inv-quick-tile">
                                <i class="la {{ $link['icon'] }}"></i>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="inv-module-card">
                        <h4>Purchasing</h4>
                        <p>{{ number_format($stats['open_pos']) }} open purchase order(s)</p>
                        @can('inv_purchase_manage')
                        <a href="{{ route('admin.inventory-erp.purchase-orders.index') }}">View POs →</a>
                        @endcan
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="inv-module-card">
                        <h4>Transfers</h4>
                        <p>{{ number_format($stats['in_transit']) }} shipment(s) in transit</p>
                        @can('inv_transfer_manage')
                        <a href="{{ route('admin.inventory-erp.transfers.in-transit') }}">In transit →</a>
                        @endcan
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="inv-module-card">
                        <h4>Stock alerts</h4>
                        <p>{{ number_format($stats['low_stock']) }} item/store below reorder</p>
                        @can('inv_adjust_manage')
                        <a href="{{ route('admin.inventory-erp.stock-controls.low-stock') }}">Low stock →</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
