<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppConversation extends Model
{
    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'account_id',
        'patient_id',
        'phone',
        'contact_name',
        'last_message_preview',
        'last_message_direction',
        'last_message_at',
        'last_inbound_at',
        'unread_count',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'last_inbound_at' => 'datetime',
        'unread_count' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id');
    }

    public function sessionIsOpen(): bool
    {
        if (! $this->last_inbound_at) {
            return false;
        }

        return $this->last_inbound_at->gt(now()->subHours(24));
    }
}
