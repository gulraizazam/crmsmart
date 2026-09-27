<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvItem extends Model
{
    use SoftDeletes;

    public const TYPE_TRADABLE = 'tradable';
    public const TYPE_CONSUMABLE = 'consumable';

    protected $table = 'inv_items';

    protected $fillable = [
        'account_id', 'sku', 'name', 'item_type', 'uom', 'active', 'track_batch',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'track_batch' => 'boolean',
    ];

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function balances()
    {
        return $this->hasMany(InvStockBalance::class, 'item_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
