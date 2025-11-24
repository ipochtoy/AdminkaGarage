<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductListing extends Model
{
    public const PLATFORM_POCHTOY = 'pochtoy';
    public const PLATFORM_EBAY = 'ebay';
    public const PLATFORM_SHOPIFY = 'shopify';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DELETED = 'deleted';
    public const STATUS_SOLD = 'sold';

    protected $fillable = [
        'photo_batch_id',
        'platform',
        'external_id',
        'external_url',
        'status',
        'error_message',
        'platform_data',
        'listed_price',
        'currency',
        'published_at',
        'sold_at',
        // eBay specific
        'ebay_category_id',
        'ebay_category_name',
        'ebay_item_specifics',
        'ebay_condition_id',
        'ebay_listing_format',
        'ebay_quantity',
        // Shopify specific
        'shopify_product_type',
        'shopify_collection_id',
        'shopify_tags',
        'shopify_options',
        // Pochtoy specific
        'pochtoy_trackings',
        // Overrides
        'override_title',
        'override_description',
    ];

    protected $casts = [
        'platform_data' => 'array',
        'listed_price' => 'decimal:2',
        'published_at' => 'datetime',
        'sold_at' => 'datetime',
        'ebay_item_specifics' => 'array',
        'shopify_tags' => 'array',
        'shopify_options' => 'array',
        'pochtoy_trackings' => 'array',
    ];

    public function photoBatch(): BelongsTo
    {
        return $this->belongsTo(PhotoBatch::class);
    }

    public static function platforms(): array
    {
        return [
            self::PLATFORM_POCHTOY => 'Бой гаража',
            self::PLATFORM_EBAY => 'eBay',
            self::PLATFORM_SHOPIFY => 'Shopify',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Ожидает',
            self::STATUS_PUBLISHED => 'Опубликовано',
            self::STATUS_FAILED => 'Ошибка',
            self::STATUS_DELETED => 'Удалено',
            self::STATUS_SOLD => 'Продано',
        ];
    }

    public function getPlatformNameAttribute(): string
    {
        return self::platforms()[$this->platform] ?? $this->platform;
    }

    public function getStatusNameAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function markAsPublished(string $externalId = null, string $externalUrl = null, array $platformData = []): void
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'external_id' => $externalId,
            'external_url' => $externalUrl,
            'platform_data' => $platformData,
            'published_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsDeleted(): void
    {
        $this->update([
            'status' => self::STATUS_DELETED,
        ]);
    }

    public function markAsSold(): void
    {
        $this->update([
            'status' => self::STATUS_SOLD,
            'sold_at' => now(),
        ]);
    }
}
