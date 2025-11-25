<?php

namespace App\Console\Commands;

use App\Models\PhotoBatch;
use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessEbayListingCommand extends Command
{
    protected $signature = 'process:ebay-listing {batch_id}';

    protected $description = 'Generate eBay listing for a photo batch';

    public function handle()
    {
        $batchId = $this->argument('batch_id');

        $batch = PhotoBatch::find($batchId);

        if (!$batch) {
            $this->error("Batch {$batchId} not found");
            Log::error("eBay listing generation failed: Batch {$batchId} not found");
            return 1;
        }

        $photos = $batch->photos;
        if ($photos->isEmpty()) {
            $this->error("No photos in batch {$batchId}");
            Log::error("eBay listing generation failed: No photos in batch {$batchId}");
            return 1;
        }

        $this->info("Generating eBay listing for batch {$batchId}...");
        Log::info("Starting eBay listing generation for batch {$batchId}");

        $service = new GeminiService();
        $photoPaths = $photos->pluck('image')->toArray();

        // Pass context from existing product data
        $productData = [
            'title' => $batch->title,
            'brand' => $batch->brand,
            'category' => $batch->category,
            'condition' => $batch->condition,
        ];

        $result = $service->generateEbayListing($photoPaths, $productData);

        if ($result) {
            // Save to database
            $batch->update([
                'ebay_title' => $result['title'] ?? null,
                'ebay_description' => $result['description'] ?? null,
                'ebay_brand' => $result['brand'] ?? null,
                'ebay_category' => $result['category'] ?? '11450',
                'ebay_condition' => $result['condition'] ?? 'Pre-owned - Good',
                'ebay_size' => $result['size'] ?? null,
                'ebay_color' => $result['color'] ?? null,
                'ebay_price' => $result['price_usd'] ?? 0,
                'ebay_tags' => $result['tags'] ?? [],
            ]);

            $this->info("eBay listing generated successfully for batch {$batchId}!");
            Log::info("eBay listing generated successfully for batch {$batchId}", [
                'title' => $result['title'] ?? null,
                'price' => $result['price_usd'] ?? 0,
            ]);
            return 0;
        } else {
            $this->error("Failed to generate eBay listing for batch {$batchId}");
            Log::error("eBay listing generation failed for batch {$batchId}");
            return 1;
        }
    }
}
