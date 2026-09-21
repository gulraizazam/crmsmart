<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointments;
use App\Models\AppointmentStatuses;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Auth;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AppointmentPrescriptionController extends Controller
{
    public function index($appointmentId)
    {
        $this->authorizeManage();
        $appointment = $this->findConsultancy($appointmentId);
        $this->requireArrivedOrConverted($appointment);
        $prescriptions = Prescription::with(['items', 'doctor'])
            ->where('account_id', Auth::user()->account_id)
            ->where('appointment_id', $appointment->id)
            ->orderByDesc('id')
            ->get();

        return view('admin.appointments.prescriptions.index', compact('appointment', 'prescriptions'));
    }

    public function create($appointmentId)
    {
        $this->authorizeCreate();
        $appointment = $this->findConsultancy($appointmentId);
        $this->requireArrivedOrConverted($appointment);

        return view('admin.appointments.prescriptions.form', [
            'appointment' => $appointment,
            'prescription' => null,
        ]);
    }

    public function store(Request $request, $appointmentId)
    {
        $this->authorizeCreate();
        $appointment = $this->findConsultancy($appointmentId);
        $this->requireArrivedOrConverted($appointment);
        $data = $this->validated($request);

        $prescription = DB::transaction(function () use ($data, $appointment) {
            $prescription = Prescription::create([
                'account_id' => Auth::user()->account_id,
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id ?: Auth::id(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'prescribed_at' => $data['prescribed_at'] ?: Carbon::today()->toDateString(),
                'diagnosis' => $data['diagnosis'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'issued',
            ]);
            $this->syncItems($prescription, $data['items']);

            return $prescription;
        });

        return redirect()
            ->route('admin.appointments.prescriptions.show', $prescription->id)
            ->with('success', 'Prescription saved.');
    }

    public function show($id)
    {
        $this->authorizeManage();
        $prescription = $this->findPrescription($id);
        $appointment = $this->findConsultancy($prescription->appointment_id);

        return view('admin.appointments.prescriptions.show', compact('appointment', 'prescription'));
    }

    public function edit($id)
    {
        $this->authorizeEdit();
        $prescription = $this->findPrescription($id);
        $appointment = $this->findConsultancy($prescription->appointment_id);

        return view('admin.appointments.prescriptions.form', compact('appointment', 'prescription'));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeEdit();
        $prescription = $this->findPrescription($id);
        $this->findConsultancy($prescription->appointment_id);
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $prescription) {
            $prescription->update([
                'updated_by' => Auth::id(),
                'prescribed_at' => $data['prescribed_at'] ?: $prescription->prescribed_at,
                'diagnosis' => $data['diagnosis'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
            $prescription->items()->delete();
            $this->syncItems($prescription, $data['items']);
        });

        return redirect()
            ->route('admin.appointments.prescriptions.show', $prescription->id)
            ->with('success', 'Prescription updated.');
    }

    public function destroy($id)
    {
        if (! Gate::allows('appointments_prescription_destroy')) {
            abort(401);
        }
        $prescription = $this->findPrescription($id);
        $appointmentId = $prescription->appointment_id;
        $prescription->delete();

        return redirect()
            ->route('admin.appointments.prescriptions.index', $appointmentId)
            ->with('success', 'Prescription deleted.');
    }

    public function printView($id)
    {
        $this->authorizeManage();
        $prescription = $this->findPrescription($id);
        $appointment = $this->findConsultancy($prescription->appointment_id);

        return view('admin.appointments.prescriptions.print', compact('appointment', 'prescription'));
    }

    public function exportPdf($id)
    {
        $this->authorizeManage();
        $prescription = $this->findPrescription($id);
        $appointment = $this->findConsultancy($prescription->appointment_id);
        $pdf = PDF::loadView('admin.appointments.prescriptions.print', [
            'appointment' => $appointment,
            'prescription' => $prescription,
            'forPdf' => true,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('prescription-'.$prescription->id.'.pdf');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'prescribed_at' => 'nullable|date',
            'diagnosis' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.medicine_name' => 'required|string|max:255',
            'items.*.dose' => 'nullable|string|max:100',
            'items.*.frequency' => 'nullable|string|max:100',
            'items.*.duration' => 'nullable|string|max:100',
            'items.*.instructions' => 'nullable|string|max:500',
        ], [
            'items.required' => 'Add at least one medicine.',
            'items.*.medicine_name.required' => 'Medicine name is required.',
        ]);
    }

    private function syncItems(Prescription $prescription, array $items): void
    {
        foreach (array_values($items) as $index => $item) {
            $name = trim($item['medicine_name'] ?? '');
            if ($name === '') {
                continue;
            }
            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'sort_no' => $index + 1,
                'medicine_name' => $name,
                'dose' => $item['dose'] ?? null,
                'frequency' => $item['frequency'] ?? null,
                'duration' => $item['duration'] ?? null,
                'instructions' => $item['instructions'] ?? null,
            ]);
        }
    }

    private function findConsultancy($appointmentId): Appointments
    {
        $appointment = Appointments::with(['patient', 'doctor', 'location', 'service', 'appointment_type', 'appointment_status'])
            ->where('id', $appointmentId)
            ->where('account_id', Auth::user()->account_id)
            ->firstOrFail();

        $consultancyTypeId = (int) config('constants.appointment_type_consultancy', 1);
        if ((int) $appointment->appointment_type_id !== $consultancyTypeId) {
            abort(404);
        }

        return $appointment;
    }

    private function requireArrivedOrConverted(Appointments $appointment): void
    {
        if (! AppointmentStatuses::appointmentAllowsPrescription($appointment)) {
            abort(403, 'Prescription is only available when the consultation is Arrived or Converted.');
        }
    }

    private function findPrescription($id): Prescription
    {
        return Prescription::with(['items', 'doctor', 'patient'])
            ->where('account_id', Auth::user()->account_id)
            ->findOrFail($id);
    }

    private function authorizeManage(): void
    {
        if (! Gate::allows('appointments_prescription_manage')) {
            abort(401);
        }
    }

    private function authorizeCreate(): void
    {
        if (! Gate::allows('appointments_prescription_create')) {
            abort(401);
        }
    }

    private function authorizeEdit(): void
    {
        if (! Gate::allows('appointments_prescription_edit')) {
            abort(401);
        }
    }
}
