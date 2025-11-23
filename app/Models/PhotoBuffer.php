<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoBuffer extends Model
{
    protected $fillable = [
        'file_id',
        'message_id',
        'chat_id',
        'image',
        'uploaded_at',
        'taken_at',
        'gg_label',
        'barcode',
        'group_id',
        'group_order',
        'processed',
        'sent_to_bot',
    ];

    protected $casts = [
        'processed' => 'boolean',
        'sent_to_bot' => 'boolean',
        'uploaded_at' => 'datetime',
        'taken_at' => 'datetime',
    ];
}
