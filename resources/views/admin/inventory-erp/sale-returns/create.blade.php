@extends('admin.layouts.inventory-erp')
@section('title', 'New Sales Return')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Sales Return</h3></div>
                    <div class="card-toolbar"><a href="{{ route('admin.inventory-erp.sale-returns.index') }}" class="btn btn-light">Back</a></div>
                </div>
                <div class="card-body">
                    @include('admin.inventory-erp.partials.outbound-form', [
                        'action' => route('admin.inventory-erp.sale-returns.store'),
                        'confirm' => 'Post sales return? Stock will increase.',
                        'showCustomer' => false,
                        'showUnitCost' => true,
                        'items' => $items,
                        'stores' => $stores,
                        'balances' => [],
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
