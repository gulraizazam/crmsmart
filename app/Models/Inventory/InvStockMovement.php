<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class InvStockMovement extends Model
{
    protected $table = 'inv_stock_movements';

    protected $fillable = [
        'account_id', 'document_id', 'document_line_id', 'item_id', 'store_id',
        'direction', 'quantity', 'unit_cost', 'balance_qty_after', 'avg_cost_after',
        'moved_at', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'balance_qty_after' => 'decimal:4',
        'avg_cost_after' => 'decimal:4',
        'moved_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(InvStockDocument::class, 'document_id');
    }

    public function line()
    {
        return $this->belongsTo(InvStockDocumentLine::class, 'document_line_id');
    }

    public function item()
    {
        return $this->belongsTo(InvItem::class, 'item_id');
    }

    public function store()
    {
        return $this->belongsTo(InvStore::class, 'store_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
