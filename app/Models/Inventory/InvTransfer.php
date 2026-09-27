<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvTransfer extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const REF_TYPE = 'transfer';

    protected $table = 'inv_transfers';

    protected $fillable = [
        'account_id', 'transfer_no', 'from_store_id', 'to_store_id', 'status', 'transfer_date',
        'notes', 'approved_at', 'approved_by', 'dispatched_at', 'dispatched_by',
        'received_at', 'received_by', 'dispatch_document_id', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'approved_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function fromStore()
    {
        return $this->belongsTo(InvStore::class, 'from_store_id');
    }

    public function toStore()
    {
        return $this->belongsTo(InvStore::class, 'to_store_id');
    }

    public function lines()
    {
        return $this->hasMany(InvTransferLine::class, 'transfer_id')->orderBy('line_no');
    }

    public function dispatchDocument()
    {
        return $this->belongsTo(InvStockDocument::class, 'dispatch_document_id');
    }

    public function stockDocuments()
    {
        return $this->hasMany(InvStockDocument::class, 'reference_id')
            ->where('reference_type', self::REF_TYPE);
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isInTransit(): bool
    {
        return $this->status === self::STATUS_IN_TRANSIT;
    }

    public function canDispatch(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canReceive(): bool
    {
        return $this->status === self::STATUS_IN_TRANSIT;
    }

    public function inTransitQty(): float
    {
        return (float) $this->lines->sum(function (InvTransferLine $line) {
            return $line->inTransitQty();
        });
    }
}
