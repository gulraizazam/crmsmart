@extends('admin.layouts.inventory-erp')
@section('title', $document->document_no)
@section('content')
@push('css')
    <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=12" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/sneat-inventory-erp.css') }}?v=1" rel="stylesheet" type="text/css" />
@endpush
<div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-inv-page" id="kt_content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid sneat-page">
            <div class="card card-custom sneat-page-card">
                @include('admin.inventory-erp.partials.outbound-show', [
                    'reverseRoute' => 'admin.inventory-erp.issues.reverse',
                    'backRoute' => 'admin.inventory-erp.issues.index',
                ])
            </div>
        </div>
    </div>
</div>
@endsection
