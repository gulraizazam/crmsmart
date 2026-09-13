<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaLeadEvent extends Model
{
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CREATED = 'created';
    public const STATUS_UPDATED = 'updated';
    public const STATUS_DUPLICATE = 'duplicate';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_FAILED = 'failed';
    public const STATUS_IGNORED = 'ignored';

    protected $table = 'meta_lead_events';

    protected $fillable = [
        'account_id',
        'leadgen_id',
        'page_id',
        'form_id',
        'lead_id',
        'status',
        'error',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo(Leads::class, 'lead_id');
    }
}
