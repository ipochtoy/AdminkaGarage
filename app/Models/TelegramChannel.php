<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramChannel extends Model
{
    protected $fillable = [
        'name',
        'bot_token',
        'chat_id',
        'link_template',
        'site_name',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Посты в этом канале
     */
    public function posts(): HasMany
    {
        return $this->hasMany(TelegramPost::class);
    }

    /**
     * Только активные каналы
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Сортировка по порядку
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Генерация ссылки на товар
     */
    public function generateBuyLink(PhotoBatch $batch): string
    {
        return str_replace(
            ['{id}', '{correlation_id}', '{sku}'],
            [$batch->id, $batch->correlation_id ?? '', $batch->sku ?? ''],
            $this->link_template
        );
    }
}
