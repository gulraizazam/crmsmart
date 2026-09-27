@extends('admin.layouts.inventory-erp')
@section('title', 'New Internal Issue')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Internal Issue</h3></div>
                    <div class="card-toolbar"><a href="{{ route('admin.inventory-erp.issues.index') }}" class="btn btn-light">Back</a></div>
                </div>
                <div class="card-body">
                    @include('admin.inventory-erp.partials.outbound-form', [
                        'action' => route('admin.inventory-erp.issues.store'),
                        'confirm' => 'Post issue? Stock will decrease.',
                        'showCustomer' => false,
                        'showUnitCost' => false,
                        'items' => $items,
                        'stores' => $stores,
                        'balances' => $balances,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
