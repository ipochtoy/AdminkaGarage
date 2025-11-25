<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Photo extends Model
{
    protected $fillable = [
        'photo_batch_id',
        'file_id',
        'message_id',
        'image',
        'uploaded_at',
        'is_main',
        'is_public',
        'ebay_selected',
        'order',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'is_public' => 'boolean',
        'ebay_selected' => 'boolean',
        'uploaded_at' => 'datetime',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PhotoBatch::class, 'photo_batch_id');
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(BarcodeResult::class);
    }

    public function processingTasks(): HasMany
    {
        return $this->hasMany(ProcessingTask::class);
    }

    protected static function booted()
    {
        static::saving(function ($photo) {
            if ($photo->is_main) {
                Photo::where('photo_batch_id', $photo->photo_batch_id)
                    ->where('is_main', true)
                    ->where('id', '!=', $photo->id)
                    ->update(['is_main' => false]);
            }
        });
    }
}
