<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramPost extends Model
{
    protected $fillable = [
        'telegram_channel_id',
        'photo_batch_id',
        'title',
        'description',
        'price',
        'currency',
        'buy_link',
        'images',
        'status',
        'scheduled_at',
        'sent_at',
        'telegram_message_id',
        'error_message',
        'is_sold',
        'sold_at',
    ];

    protected $casts = [
        'images' => 'array',
        'price' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'sold_at' => 'datetime',
        'is_sold' => 'boolean',
    ];

    /**
     * Канал, в который публикуется пост
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(TelegramChannel::class, 'telegram_channel_id');
    }

    /**
     * Связанный товар (опционально)
     */
    public function photoBatch(): BelongsTo
    {
        return $this->belongsTo(PhotoBatch::class);
    }

    /**
     * Посты со статусом draft
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Посты, готовые к отправке по расписанию
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Отправленные посты
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Непроданные товары
     */
    public function scopeNotSold($query)
    {
        return $query->where('is_sold', false);
    }

    /**
     * Можно ли отправить пост
     */
    public function canBeSent(): bool
    {
        return in_array($this->status, ['draft', 'failed']);
    }

    /**
     * Можно ли редактировать пост в Telegram
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'sent' && $this->telegram_message_id;
    }

    /**
     * URL первой картинки для превью
     */
    public function getPreviewImageUrlAttribute(): ?string
    {
        $images = $this->images ?? [];
        if (empty($images)) {
            return null;
        }
        return asset('storage/' . $images[0]);
    }

    /**
     * Форматированная цена
     */
    public function getFormattedPriceAttribute(): string
    {
        if (!$this->price) {
            return '';
        }

        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'RUB' => '₽',
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency . ' ';
        return $symbol . number_format($this->price, 2);
    }
}
