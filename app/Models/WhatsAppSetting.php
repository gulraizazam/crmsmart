<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class WhatsAppSetting extends Model
{
    protected $table = 'whatsapp_settings';

    protected $fillable = [
        'account_id',
        'phone_number_id',
        'waba_id',
        'display_phone',
        'access_token',
        'verify_token',
        'app_secret',
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

    public function isConnected(): bool
    {
        return $this->active
            && $this->hasAccessToken()
            && ! empty($this->phone_number_id);
    }

    public static function forAccount(int $accountId): ?self
    {
        return self::where('account_id', $accountId)->first();
    }

    public static function forPhoneNumberId(string $phoneNumberId): ?self
    {
        return self::where('phone_number_id', $phoneNumberId)->where('active', 1)->first();
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
