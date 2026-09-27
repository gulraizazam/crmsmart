<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InvStockBalance extends Model
{
    protected $table = 'inv_stock_balances';

    protected $fillable = [
        'account_id', 'item_id', 'store_id', 'quantity', 'avg_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'avg_cost' => 'decimal:4',
    ];

    public function item()
    {
        return $this->belongsTo(InvItem::class, 'item_id');
    }

    public function store()
    {
        return $this->belongsTo(InvStore::class, 'store_id');
    }
}
