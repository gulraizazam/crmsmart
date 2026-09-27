<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InvPeriodLock extends Model
{
    protected $table = 'inv_period_locks';

    protected $fillable = [
        'account_id', 'period_year', 'period_month',
        'locked_at', 'locked_by', 'unlocked_at', 'unlocked_by', 'notes',
    ];

    protected $casts = [
        'period_year' => 'integer',
        'period_month' => 'integer',
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null && $this->unlocked_at === null;
    }

    public function lockedByUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function label(): string
    {
        return sprintf('%04d-%02d', $this->period_year, $this->period_month);
    }
}
