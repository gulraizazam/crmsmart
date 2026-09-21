<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'appointment_id',
        'patient_id',
        'doctor_id',
        'created_by',
        'updated_by',
        'prescribed_at',
        'diagnosis',
        'notes',
        'status',
    ];

    protected $casts = [
        'prescribed_at' => 'date',
    ];

    protected $table = 'prescriptions';

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class)->orderBy('sort_no')->orderBy('id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointments::class, 'appointment_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id')->withTrashed();
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id')->withTrashed();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
