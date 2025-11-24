<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PhotoBatch extends Model
{
    protected $fillable = [
        'correlation_id',
        'chat_id',
        'message_ids',
        'uploaded_at',
        'processed_at',
        'status',
        'title',
        'description',
        'price',
        'condition',
        'category',
        'brand',
        'size',
        'color',
        'sku',
        'quantity',
        'ai_summary',
        'locations',
    ];

    protected $casts = [
        'message_ids' => 'array',
        'locations' => 'array',
        'price' => 'decimal:2',
        'uploaded_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function getGgLabels(): array
    {
        $ggLabels = [];
        foreach ($this->photos as $photo) {
            foreach ($photo->barcodes()->where('source', 'gg-label')->get() as $barcode) {
                if (!in_array($barcode->data, $ggLabels)) {
                    $ggLabels[] = $barcode->data;
                }
            }
            // Also check Q-codes as GG
            foreach ($photo->barcodes()->where('symbology', 'CODE39')->where('data', 'like', 'Q%')->get() as $barcode) {
                if (!in_array($barcode->data, $ggLabels)) {
                    $ggLabels[] = $barcode->data;
                }
            }
        }
        return $ggLabels;
    }

    public function getAllBarcodes(): array
    {
        $barcodes = [];
        foreach ($this->photos as $photo) {
            foreach ($photo->barcodes()
                ->where('source', '!=', 'gg-label')
                ->where(function ($q) {
                    $q->where('symbology', '!=', 'CODE39')
                      ->orWhere('data', 'not like', 'Q%');
                })->get() as $barcode) {
                $barcodes[] = $barcode;
            }
        }
        return $barcodes;
    }

    public function listings(): HasMany
    {
        return $this->hasMany(ProductListing::class);
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }

    public function getListingFor(string $platform): ?ProductListing
    {
        return $this->listings()->where('platform', $platform)->first();
    }

    public function isListedOn(string $platform): bool
    {
        $listing = $this->getListingFor($platform);
        return $listing && $listing->isPublished();
    }

    public function getListingStatus(string $platform): ?string
    {
        $listing = $this->getListingFor($platform);
        return $listing?->status;
    }

    public function getPublishedPlatforms(): array
    {
        return $this->listings()
            ->where('status', ProductListing::STATUS_PUBLISHED)
            ->pluck('platform')
            ->toArray();
    }
}
