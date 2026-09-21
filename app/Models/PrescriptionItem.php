<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'sort_no',
        'medicine_name',
        'dose',
        'frequency',
        'duration',
        'instructions',
    ];

    protected $table = 'prescription_items';

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
