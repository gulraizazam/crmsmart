<?php

namespace App\Models\Inventory;

use App\Models\Locations;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvStore extends Model
{
    use SoftDeletes;

    public const TYPE_WAREHOUSE = 'warehouse';
    public const TYPE_CENTRE_STORE = 'centre_store';
    public const TYPE_RETAIL = 'retail';

    protected $table = 'inv_stores';

    protected $fillable = [
        'account_id', 'name', 'store_type', 'location_id', 'active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function location()
    {
        return $this->belongsTo(Locations::class, 'location_id');
    }

    public function balances()
    {
        return $this->hasMany(InvStockBalance::class, 'store_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
