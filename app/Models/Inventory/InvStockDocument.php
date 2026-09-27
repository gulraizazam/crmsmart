<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvStockDocument extends Model
{
    use SoftDeletes;

    public const TYPE_OPENING = 'opening';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_PURCHASE_RECEIPT = 'purchase_receipt';
    public const TYPE_PURCHASE_RETURN = 'purchase_return';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_SALE = 'sale';
    public const TYPE_ISSUE = 'issue';
    public const TYPE_SALE_RETURN = 'sale_return';
    public const TYPE_REVERSAL = 'reversal';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_POSTED = 'posted';
    public const STATUS_REVERSED = 'reversed';

    protected $table = 'inv_stock_documents';

    protected $fillable = [
        'account_id', 'document_no', 'document_type', 'status', 'document_date',
        'notes', 'adjustment_reason_id', 'reference_type', 'reference_id', 'reversal_of_id',
        'posted_at', 'posted_by', 'reversed_at', 'reversed_by',
        'created_by', 'updated_by', 'approved_at', 'approved_by',
    ];

    protected $casts = [
        'document_date' => 'date',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function lines()
    {
        return $this->hasMany(InvStockDocumentLine::class, 'document_id')->orderBy('line_no');
    }

    public function movements()
    {
        return $this->hasMany(InvStockMovement::class, 'document_id');
    }

    public function adjustmentReason()
    {
        return $this->belongsTo(InvAdjustmentReason::class, 'adjustment_reason_id');
    }

    public function reversalOf()
    {
        return $this->belongsTo(self::class, 'reversal_of_id');
    }

    public function postedByUser()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPosted(): bool
    {
        return $this->status === self::STATUS_POSTED;
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function absoluteValue(): float
    {
        return (float) $this->lines->sum(function (InvStockDocumentLine $line) {
            return abs((float) $line->quantity * (float) ($line->unit_cost ?? 0));
        });
    }
}
