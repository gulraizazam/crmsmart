<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadFollowUp extends Model
{
    use SoftDeletes;

    protected $table = 'lead_follow_ups';

    protected $fillable = [
        'lead_id',
        'created_by',
        'account_id',
        'scheduled_at',
        'note',
        'dismissed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Leads::class, 'lead_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDueForUser($query, int $userId)
    {
        return $query
            ->where('created_by', $userId)
            ->whereNull('dismissed_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at');
    }

    public function isDismissed(): bool
    {
        return $this->dismissed_at !== null;
    }

    public function dismiss(): void
    {
        if ($this->dismissed_at === null) {
            $this->update(['dismissed_at' => now()]);
        }
    }
}
