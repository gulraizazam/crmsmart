@extends('admin.layouts.master')
@section('title', 'Prescription #'.$prescription->id)
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
                            <h3 class="card-label">Prescription #{{ $prescription->id }}</h3>
                        </div>
                        <div class="card-toolbar">
                            @can('appointments_prescription_edit')
                                <a href="{{ route('admin.appointments.prescriptions.edit', $prescription->id) }}" class="btn btn-warning">Edit</a>
                            @endcan
                            <a href="{{ route('admin.appointments.prescriptions.print', $prescription->id) }}" target="_blank" class="btn btn-info ml-2">Print</a>
                            <a href="{{ route('admin.appointments.prescriptions.pdf', $prescription->id) }}" target="_blank" class="btn btn-light-info ml-2">PDF</a>
                            <a href="{{ route('admin.appointments.prescriptions.index', $appointment->id) }}" class="btn btn-dark ml-2">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @include('admin.appointments.prescriptions.partials.meta')

                        <div class="sneat-rx-summary">
                            <div>
                                <span>Date</span>
                                <strong>{{ optional($prescription->prescribed_at)->format('d M Y') }}</strong>
                            </div>
                            <div>
                                <span>Prescribed by</span>
                                <strong>{{ $prescription->doctor->name ?? '—' }}</strong>
                            </div>
                            @if($prescription->diagnosis)
                                <div class="sneat-rx-summary-wide">
                                    <span>Diagnosis</span>
                                    <strong>{{ $prescription->diagnosis }}</strong>
                                </div>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table sneat-rx-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Medicine</th>
                                        <th>Dose</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                        <th>Instructions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prescription->items as $item)
                                        <tr>
                                            <td data-label="#">{{ $loop->iteration }}</td>
                                            <td data-label="Medicine">{{ $item->medicine_name }}</td>
                                            <td data-label="Dose">{{ $item->dose ?: '—' }}</td>
                                            <td data-label="Frequency">{{ $item->frequency ?: '—' }}</td>
                                            <td data-label="Duration">{{ $item->duration ?: '—' }}</td>
                                            <td data-label="Instructions">{{ $item->instructions ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($prescription->notes)
                            <div class="sneat-rx-notes">
                                <span>Notes</span>
                                <p>{{ $prescription->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
