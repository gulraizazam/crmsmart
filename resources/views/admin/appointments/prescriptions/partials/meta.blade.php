@php
    $patientName = $appointment->name ?: ($appointment->patient->name ?? 'N/A');
    $doctorName = $appointment->doctor->name ?? 'N/A';
    $centreName = $appointment->location->name ?? 'N/A';
    $serviceName = $appointment->service->name ?? 'N/A';
    $scheduled = $appointment->scheduled_date
        ? \Carbon\Carbon::parse($appointment->scheduled_date)->format('M j, Y') . ' at ' . \Carbon\Carbon::parse($appointment->scheduled_time)->format('h:i A')
        : '—';
@endphp
<div class="sneat-rx-meta">
    <div>
        <span>Patient</span>
        <strong>{{ $patientName }}</strong>
    </div>
    <div>
        <span>Doctor</span>
        <strong>{{ $doctorName }}</strong>
    </div>
    <div>
        <span>Consultation</span>
        <strong>{{ $scheduled }}</strong>
    </div>
    <div>
        <span>Centre</span>
        <strong>{{ $centreName }}</strong>
    </div>
    <div>
        <span>Service</span>
        <strong>{{ $serviceName }}</strong>
    </div>
</div>
