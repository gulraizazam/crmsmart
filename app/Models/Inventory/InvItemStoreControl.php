<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InvItemStoreControl extends Model
{
    protected $table = 'inv_item_store_controls';

    protected $fillable = [
        'account_id', 'item_id', 'store_id', 'reorder_level', 'reorder_qty',
    ];

    protected $casts = [
        'reorder_level' => 'decimal:4',
        'reorder_qty' => 'decimal:4',
    ];

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function item()
    {
        return $this->belongsTo(InvItem::class, 'item_id');
    }

    public function store()
    {
        return $this->belongsTo(InvStore::class, 'store_id');
    }
}
