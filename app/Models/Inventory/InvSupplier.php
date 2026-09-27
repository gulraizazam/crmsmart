<?php

namespace App\Models\Inventory;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvSupplier extends Model
{
    use SoftDeletes;

    protected $table = 'inv_suppliers';

    protected $fillable = [
        'account_id', 'name', 'phone', 'email', 'address', 'active',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(InvPurchaseOrder::class, 'supplier_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
