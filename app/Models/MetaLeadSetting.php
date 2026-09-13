<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class MetaLeadSetting extends Model
{
    protected $table = 'meta_lead_settings';

    protected $fillable = [
        'account_id',
        'page_id',
        'page_name',
        'access_token',
        'verify_token',
        'app_secret',
        'lead_source_id',
        'lead_status_id',
        'city_id',
        'location_id',
        'department_id',
        'assigned_to',
        'gender',
        'active',
        'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'app_secret',
    ];

    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getAccessTokenAttribute($value): ?string
    {
        return $this->decryptValue($value);
    }

    public function setAppSecretAttribute($value): void
    {
        $this->attributes['app_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getAppSecretAttribute($value): ?string
    {
        return $this->decryptValue($value);
    }

    public function hasAccessToken(): bool
    {
        return ! empty($this->attributes['access_token']);
    }

    public function hasAppSecret(): bool
    {
        return ! empty($this->attributes['app_secret']);
    }

    public static function forAccount(int $accountId): ?self
    {
        return self::where('account_id', $accountId)->first();
    }

    public static function forPage(string $pageId): ?self
    {
        return self::where('page_id', $pageId)->where('active', 1)->first();
    }

    protected function decryptValue($value): ?string
    {
        if (! $value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
