<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvPurchaseOrder extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ORDERED = 'ordered';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    public const REF_TYPE = 'purchase_order';

    protected $table = 'inv_purchase_orders';

    protected $fillable = [
        'account_id', 'po_number', 'supplier_id', 'status', 'order_date', 'expected_date',
        'notes', 'approved_at', 'approved_by', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function supplier()
    {
        return $this->belongsTo(InvSupplier::class, 'supplier_id');
    }

    public function lines()
    {
        return $this->hasMany(InvPurchaseOrderLine::class, 'purchase_order_id')->orderBy('line_no');
    }

    public function receipts()
    {
        return $this->hasMany(InvStockDocument::class, 'reference_id')
            ->where('reference_type', self::REF_TYPE)
            ->where('document_type', InvStockDocument::TYPE_PURCHASE_RECEIPT);
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isOrdered(): bool
    {
        return $this->status === self::STATUS_ORDERED;
    }

    public function canReceive(): bool
    {
        return $this->status === self::STATUS_ORDERED;
    }
}
