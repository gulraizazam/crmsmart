<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InvAdjustmentReason extends Model
{
    protected $table = 'inv_adjustment_reasons';

    protected $fillable = [
        'account_id', 'code', 'name', 'direction', 'active', 'sort_order',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function allowsDirection(string $direction): bool
    {
        return $this->direction === 'both' || $this->direction === $direction;
    }
}
