<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotoBatch extends Model
{
    protected $fillable = [
        'correlation_id',
        'chat_id',
        'message_ids',
        'uploaded_at',
        'processed_at',
        'status',
        'pochtoy_status',
        'pochtoy_error',
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
        // eBay & Shopify fields
        'ebay_title',
        'ebay_description',
        'ebay_condition',
        'ebay_brand',
        'ebay_size',
        'ebay_color',
        'ebay_price',
        'ebay_category',
        'ebay_tags',
    ];

    protected $casts = [
        'message_ids' => 'array',
        'locations' => 'array',
        'ebay_tags' => 'array',
        'price' => 'decimal:2',
        'ebay_price' => 'decimal:2',
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
}
