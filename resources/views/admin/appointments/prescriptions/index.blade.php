@extends('admin.layouts.master')
@section('title', 'E-Prescription')
@section('content')
    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=11" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-prescription.css') }}?v=2" rel="stylesheet" type="text/css" />
    @endpush
    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-rx-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">E-Prescription</h3>
                        </div>
                        <div class="card-toolbar">
                            @can('appointments_prescription_create')
                                <a href="{{ route('admin.appointments.prescriptions.create', $appointment->id) }}" class="btn btn-primary">
                                    <i class="la la-plus"></i> Write prescription
                                </a>
                            @endcan
                            <a href="{{ route('admin.consultancy.index') }}" class="btn btn-dark ml-2">
                                <i class="la la-arrow-left"></i> Back to consultancies
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @include('admin.appointments.prescriptions.partials.meta')

                        @if($prescriptions->isEmpty())
                            <div class="sneat-rx-empty">
                                <p>No prescription has been written for this consultation yet.</p>
                                @can('appointments_prescription_create')
                                    <a href="{{ route('admin.appointments.prescriptions.create', $appointment->id) }}" class="btn btn-primary">Write prescription</a>
                                @endcan
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table sneat-rx-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Doctor</th>
                                            <th>Medicines</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($prescriptions as $rx)
                                            <tr>
                                                <td data-label="#">{{ $rx->id }}</td>
                                                <td data-label="Date">{{ optional($rx->prescribed_at)->format('d M Y') }}</td>
                                                <td data-label="Doctor">{{ $rx->doctor->name ?? '—' }}</td>
                                                <td data-label="Medicines">{{ $rx->items->pluck('medicine_name')->filter()->join(', ') }}</td>
                                                <td class="sneat-rx-actions" data-label="Actions">
                                                    <a href="{{ route('admin.appointments.prescriptions.show', $rx->id) }}" class="btn btn-sm btn-light-primary">View</a>
                                                    @can('appointments_prescription_edit')
                                                        <a href="{{ route('admin.appointments.prescriptions.edit', $rx->id) }}" class="btn btn-sm btn-light-warning">Edit</a>
                                                    @endcan
                                                    <a href="{{ route('admin.appointments.prescriptions.print', $rx->id) }}" target="_blank" class="btn btn-sm btn-light-info">Print</a>
                                                    @can('appointments_prescription_destroy')
                                                        <form method="POST" action="{{ route('admin.appointments.prescriptions.destroy', $rx->id) }}" class="d-inline" onsubmit="return confirm('Delete this prescription?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-light-danger">Delete</button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
