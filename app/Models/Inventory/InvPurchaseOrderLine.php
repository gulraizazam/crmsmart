<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InvPurchaseOrderLine extends Model
{
    protected $table = 'inv_purchase_order_lines';

    protected $fillable = [
        'purchase_order_id', 'line_no', 'item_id', 'store_id',
        'quantity_ordered', 'quantity_received', 'unit_cost', 'notes',
    ];

    protected $casts = [
        'line_no' => 'integer',
        'quantity_ordered' => 'decimal:4',
        'quantity_received' => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(InvPurchaseOrder::class, 'purchase_order_id');
    }

    public function item()
    {
        return $this->belongsTo(InvItem::class, 'item_id');
    }

    public function store()
    {
        return $this->belongsTo(InvStore::class, 'store_id');
    }

    public function remainingQty(): float
    {
        return max(0, (float) $this->quantity_ordered - (float) $this->quantity_received);
    }
}
