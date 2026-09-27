@extends('admin.layouts.inventory-erp')
@section('title', 'Adjustment Reasons')
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
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Adjustment Reasons</h3></div>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.inventory-erp.adjustment-reasons.store') }}" class="form-row inv-filters align-items-end mb-4">
                        @csrf
                        <div class="col-md-2"><input type="text" name="code" class="form-control" placeholder="Code *" required></div>
                        <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Name *" required></div>
                        <div class="col-md-2">
                            <select name="direction" class="form-control" required>
                                <option value="both">Both</option>
                                <option value="in">IN only</option>
                                <option value="out">OUT only</option>
                            </select>
                        </div>
                        <div class="col-md-2"><button class="btn btn-primary" type="submit">Add</button></div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr><th>Code</th><th>Name</th><th>Direction</th><th>Active</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($reasons as $reason)
                                    <tr>
                                        <td class="inv-value">{{ $reason->code }}</td>
                                        <td colspan="4">
                                            <form method="post" action="{{ route('admin.inventory-erp.adjustment-reasons.update', $reason->id) }}" class="form-row align-items-center">
                                                @csrf
                                                @method('PUT')
                                                <div class="col-md-4"><input type="text" name="name" class="form-control" value="{{ $reason->name }}" required></div>
                                                <div class="col-md-2">
                                                    <select name="direction" class="form-control">
                                                        @foreach(['both','in','out'] as $d)
                                                            <option value="{{ $d }}" {{ $reason->direction===$d ? 'selected' : '' }}>{{ $d }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="checkbox">
                                                        <input type="checkbox" name="active" value="1" {{ $reason->active ? 'checked' : '' }}>
                                                        <span></span> Active
                                                    </label>
                                                </div>
                                                <div class="col-md-2"><button class="btn btn-sm btn-light-primary" type="submit">Save</button></div>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
