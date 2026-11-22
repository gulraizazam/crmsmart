<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BaseModel extends Model
{
    // Scope route-model binding to the signed-in user's account to block cross-tenant IDs in URLs.
    public function resolveRouteBinding($value, $field = null)
    {
        $query = $this->resolveRouteBindingQuery($this, $value, $field);

        if (Auth::check() && in_array('account_id', $this->getFillable(), true)) {
            $query->where($this->getTable() . '.account_id', Auth::user()->account_id);
        }

        return $query->first();
    }
}
