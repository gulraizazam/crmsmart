<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InvStockDocumentLine extends Model
{
    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    protected $table = 'inv_stock_document_lines';

    protected $fillable = [
        'document_id', 'line_no', 'item_id', 'store_id', 'direction',
        'quantity', 'unit_cost', 'notes', 'purchase_order_line_id', 'transfer_line_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'line_no' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(InvStockDocument::class, 'document_id');
    }

    public function item()
    {
        return $this->belongsTo(InvItem::class, 'item_id');
    }

    public function store()
    {
        return $this->belongsTo(InvStore::class, 'store_id');
    }

    public function purchaseOrderLine()
    {
        return $this->belongsTo(InvPurchaseOrderLine::class, 'purchase_order_line_id');
    }

    public function transferLine()
    {
        return $this->belongsTo(InvTransferLine::class, 'transfer_line_id');
    }
}
