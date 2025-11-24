<?php

namespace App\Jobs;

use App\Models\PhotoBatch;
use App\Services\AIService;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPhotoBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public PhotoBatch $batch,
        public string $provider = 'gemini'
    ) {
    }

    public function handle(): void
    {
        Log::info("Processing batch {$this->batch->id}");

        $photos = $this->batch->photos()->orderBy('order')->get();
        if ($photos->isEmpty()) {
            return;
        }

        // 1. Scan barcodes with Gemini
        $this->scanBarcodes($photos);

        // 2. Generate description
        $this->generateDescription($photos);

        // Update status
        $this->batch->update(['status' => 'processed', 'processed_at' => now()]);

        // 3. Auto-create Product
        $this->createProduct($photos);

        Log::info("Batch {$this->batch->id} processed successfully");
    }

    protected function createProduct($photos): void
    {
        // Refresh batch to get updated data
        $this->batch->refresh();

        // Create product with AI-generated data
        $product = \App\Models\Product::create([
            'photo_batch_id' => $this->batch->id,
            'title' => $this->batch->title,
            'description' => $this->batch->description,
            'price' => $this->batch->price,
            'brand' => $this->batch->brand,
            'category' => $this->batch->category,
            'size' => $this->batch->size,
            'color' => $this->batch->color,
            'material' => $this->batch->material ?? null,
            'condition' => $this->batch->condition ?? 'used',
            'status' => 'draft', // Draft until manual review
        ]);

        // Copy photos to product
        foreach ($photos as $index => $photo) {
            $product->photos()->create([
                'image_path' => $photo->image,
                'order' => $index,
            ]);
        }

        Log::info("Product {$product->id} created from batch {$this->batch->id}");
    }

    protected function scanBarcodes($photos): void
    {
        $aiService = app(AIService::class)->setProvider('gemini');
        $photoUrls = $photos->map(fn($p) => url('storage/' . $p->image))->toArray();

        $result = $aiService->scanBarcodes($photoUrls);

        if (!$result) {
            Log::warning("Barcode scan failed for batch {$this->batch->id}");
            return;
        }

        $firstPhoto = $photos->first();

        // Save regular barcodes
        if (!empty($result['barcodes'])) {
            foreach ($result['barcodes'] as $barcode) {
                $existing = $firstPhoto->barcodes()->where('data', $barcode['data'])->first();
                if (!$existing) {
                    $source = ($barcode['is_gg_label'] ?? false) ? 'gg-label' : 'gemini';
                    $firstPhoto->barcodes()->create([
                        'data' => $barcode['data'],
                        'symbology' => $barcode['symbology'] ?? 'UNKNOWN',
                        'source' => $source,
                    ]);
                }
            }
        }

        // Save GG labels
        if (!empty($result['gg_labels'])) {
            foreach ($result['gg_labels'] as $label) {
                $existing = $firstPhoto->barcodes()->where('data', $label['data'])->first();
                if (!$existing) {
                    $firstPhoto->barcodes()->create([
                        'data' => $label['data'],
                        'symbology' => $label['symbology'] ?? 'CODE39',
                        'source' => 'gg-label',
                    ]);
                }
            }
        }

        Log::info("Barcodes scanned for batch {$this->batch->id}");
    }

    protected function generateDescription($photos): void
    {
        $photoPaths = $photos->pluck('image')->toArray();
        $barcodes = collect($this->batch->getAllBarcodes())->pluck('data')->toArray();
        $ggLabels = $this->batch->getGgLabels();

        if ($this->provider === 'gemini') {
            $service = new GeminiService();
            $result = $service->generateProductDescription($photoPaths);

            if ($result) {
                $this->batch->update([
                    'title' => $result['title'] ?? null,
                    'description' => $result['description'] ?? null,
                    'brand' => $result['brand'] ?? null,
                    'category' => $result['category'] ?? null,
                    'color' => $result['color'] ?? null,
                    'size' => $result['size'] ?? null,
                    'condition' => $result['condition'] ?? 'used',
                    'price' => $result['price_estimate'] ?? null,
                    'ai_summary' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ]);

                // Save detected codes
                $this->saveDetectedCodes($result, $photos->first());
            }
        } else {
            // OpenAI
            $aiService = app(AIService::class)->setProvider('openai');
            $result = $aiService->generateSummaryFromPaths($photoPaths, $barcodes, $ggLabels);

            if ($result) {
                $this->batch->update([
                    'title' => $result['title'] ?? null,
                    'description' => $result['description'] ?? null,
                    'brand' => $result['brand'] ?? null,
                    'category' => $result['category'] ?? null,
                    'color' => $result['color'] ?? null,
                    'size' => $result['size'] ?? null,
                    'condition' => $result['condition'] ?? 'used',
                    'price' => $result['price_estimate'] ?? null,
                    'ai_summary' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ]);

                $this->saveDetectedCodes($result, $photos->first());
            }
        }

        Log::info("Description generated for batch {$this->batch->id}");
    }

    protected function saveDetectedCodes(array $result, $firstPhoto): void
    {
        if (!empty($result['internal_ids'])) {
            foreach ($result['internal_ids'] as $code) {
                if (!$firstPhoto->barcodes()->where('data', $code)->exists()) {
                    $firstPhoto->barcodes()->create([
                        'data' => $code,
                        'symbology' => 'MANUAL-AI',
                        'source' => 'gg-label'
                    ]);
                }
            }
        }

        if (!empty($result['barcodes'])) {
            foreach ($result['barcodes'] as $bc) {
                $bcData = is_array($bc) ? $bc['data'] ?? $bc : $bc;
                if (!$firstPhoto->barcodes()->where('data', $bcData)->exists()) {
                    $firstPhoto->barcodes()->create([
                        'data' => $bcData,
                        'symbology' => 'MANUAL-AI',
                        'source' => 'manual'
                    ]);
                }
            }
        }
    }
}
