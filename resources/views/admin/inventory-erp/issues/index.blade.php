@extends('admin.layouts.inventory-erp')
@section('title', 'Internal Issues')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Internal Issues</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory-erp.issues.create') }}" class="btn btn-primary"><i class="la la-plus"></i> New Issue</a>
                    </div>
                </div>
                <div class="card-body">
                    @include('admin.inventory-erp.partials.doc-table', [
                        'documents' => $documents,
                        'empty' => 'No issues yet.',
                        'showRoute' => 'admin.inventory-erp.issues.show',
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
