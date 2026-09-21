@extends('admin.layouts.master')
@section('title', $prescription ? 'Edit prescription' : 'Write prescription')
@section('content')
    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=11" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-prescription.css') }}?v=2" rel="stylesheet" type="text/css" />
    @endpush
    @php
        $items = old('items');
        if (!$items) {
            $items = $prescription ? $prescription->items->map(function ($item) {
                return [
                    'medicine_name' => $item->medicine_name,
                    'dose' => $item->dose,
                    'frequency' => $item->frequency,
                    'duration' => $item->duration,
                    'instructions' => $item->instructions,
                ];
            })->toArray() : [['medicine_name' => '', 'dose' => '', 'frequency' => '', 'duration' => '', 'instructions' => '']];
        }
        $frequencies = ['Once daily (OD)', 'Twice daily (BD)', 'Thrice daily (TDS)', 'Four times daily (QID)', 'At bedtime (HS)', 'As needed (PRN)'];
        $action = $prescription
            ? route('admin.appointments.prescriptions.update', $prescription->id)
            : route('admin.appointments.prescriptions.store', $appointment->id);
    @endphp
    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-rx-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">{{ $prescription ? 'Edit prescription' : 'Write prescription' }}</h3>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{ route('admin.appointments.prescriptions.index', $appointment->id) }}" class="btn btn-dark sneat-rx-back">
                                <i class="la la-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        @include('admin.appointments.prescriptions.partials.meta')

                        <form method="POST" action="{{ $action }}" id="prescription-form">
                            @csrf
                            @if($prescription)
                                @method('PUT')
                            @endif

                            <div class="sneat-rx-form-grid">
                                <div class="sneat-rx-field">
                                    <label>Date</label>
                                    <input type="date" name="prescribed_at" class="form-control"
                                           value="{{ old('prescribed_at', $prescription ? optional($prescription->prescribed_at)->format('Y-m-d') : now()->format('Y-m-d')) }}">
                                </div>
                                <div class="sneat-rx-field sneat-rx-field-wide">
                                    <label>Diagnosis / complaint</label>
                                    <input type="text" name="diagnosis" class="form-control" maxlength="1000"
                                           value="{{ old('diagnosis', optional($prescription)->diagnosis) }}" placeholder="Optional" autocomplete="off">
                                </div>
                            </div>

                            <div class="sneat-rx-items-head">
                                <h4>Medicines</h4>
                                <button type="button" class="btn btn-primary" id="add-rx-item">
                                    <i class="la la-plus"></i> Add medicine
                                </button>
                            </div>

                            <div id="rx-items">
                                @foreach($items as $index => $item)
                                    @include('admin.appointments.prescriptions.partials.item', ['index' => $index, 'item' => $item, 'frequencies' => $frequencies])
                                @endforeach
                            </div>

                            <div class="sneat-rx-field mt-3">
                                <label>Notes</label>
                                <textarea name="notes" rows="3" class="form-control" maxlength="2000" placeholder="Optional advice for the patient">{{ old('notes', optional($prescription)->notes) }}</textarea>
                            </div>

                            <div class="sneat-rx-footer">
                                <a href="{{ route('admin.appointments.prescriptions.index', $appointment->id) }}" class="btn btn-light sneat-rx-cancel">Cancel</a>
                                <button type="submit" class="btn btn-primary sneat-rx-save">{{ $prescription ? 'Update prescription' : 'Save prescription' }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <template id="rx-item-template">
        @include('admin.appointments.prescriptions.partials.item', ['index' => '__INDEX__', 'item' => [], 'frequencies' => $frequencies])
    </template>
    @push('js')
        <script src="{{ asset('assets/js/pages/appointment/prescription.js') }}?v=2"></script>
    @endpush
@endsection
