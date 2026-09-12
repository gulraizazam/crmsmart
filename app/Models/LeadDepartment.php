<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class LeadDepartment extends BaseModal
{
    use SoftDeletes;

    protected $table = 'lead_departments';

    protected $fillable = [
        'name',
        'sort_order',
        'active',
        'account_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    public function locations()
    {
        return $this->belongsToMany(Locations::class, 'lead_department_location', 'lead_department_id', 'location_id');
    }

    public function leads()
    {
        return $this->hasMany(Leads::class, 'department_id');
    }

    public function scopeForAccount($query, ?int $accountId = null)
    {
        return $query->where('account_id', $accountId ?? Auth::user()->account_id);
    }

    public static function getActiveForAccount(?int $accountId = null)
    {
        return self::forAccount($accountId)
            ->where('active', 1)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
