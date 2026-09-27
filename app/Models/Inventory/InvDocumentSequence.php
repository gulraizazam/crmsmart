<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class InvDocumentSequence extends Model
{
    protected $table = 'inv_document_sequences';

    protected $fillable = [
        'account_id', 'document_type', 'year', 'prefix', 'last_number',
    ];

    protected $casts = [
        'year' => 'integer',
        'last_number' => 'integer',
    ];
}
