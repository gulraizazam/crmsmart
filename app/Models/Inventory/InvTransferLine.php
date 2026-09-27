<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InvTransferLine extends Model
{
    protected $table = 'inv_transfer_lines';

    protected $fillable = [
        'transfer_id', 'line_no', 'item_id',
        'quantity_requested', 'quantity_dispatched', 'quantity_received',
        'unit_cost', 'notes',
    ];

    protected $casts = [
        'line_no' => 'integer',
        'quantity_requested' => 'decimal:4',
        'quantity_dispatched' => 'decimal:4',
        'quantity_received' => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    public function transfer()
    {
        return $this->belongsTo(InvTransfer::class, 'transfer_id');
    }

    public function item()
    {
        return $this->belongsTo(InvItem::class, 'item_id');
    }

    public function inTransitQty(): float
    {
        return max(0, (float) $this->quantity_dispatched - (float) $this->quantity_received);
    }

    public function remainingToReceive(): float
    {
        return $this->inTransitQty();
    }
}
