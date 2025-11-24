<?php

namespace App\Services\Marketplaces;

use App\Models\PhotoBatch;
use App\Models\ProductListing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PochtoyMarketplaceService implements MarketplaceServiceInterface
{
    protected string $apiUrl;
    protected string $apiToken;

    public function __construct()
    {
        $this->apiUrl = config('services.pochtoy.api_url', 'https://pochtoy-test.pochtoy3.ru/api/garage-tg/store');
        $this->apiToken = config('services.pochtoy.api_token', '');
    }

    public function getPlatform(): string
    {
        return ProductListing::PLATFORM_POCHTOY;
    }

    public function getPlatformName(): string
    {
        return 'Бой гаража';
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiToken) && !empty($this->apiUrl);
    }

    public function publish(PhotoBatch $batch, array $options = []): ListingResult
    {
        if (!$this->isConfigured()) {
            return ListingResult::failure('Pochtoy API не настроен. Проверьте POCHTOY_API_TOKEN и POCHTOY_API_URL.');
        }

        try {
            // 1. Collect images as base64
            $images = [];
            foreach ($batch->photos as $idx => $photo) {
                try {
                    $imageData = Storage::disk('public')->get($photo->image);
                    if (!$imageData) continue;

                    $images[] = [
                        'base64' => base64_encode($imageData),
                        'file_name' => "{$batch->correlation_id}_{$idx}.jpg"
                    ];
                } catch (\Exception $e) {
                    Log::warning("Failed to encode photo {$photo->id}", ['error' => $e->getMessage()]);
                }
            }

            if (empty($images)) {
                return ListingResult::failure('Нет фотографий для публикации');
            }

            // 2. Collect trackings (GG labels + barcodes)
            $trackings = [];

            // GG labels
            $ggLabels = $batch->getGgLabels();
            $trackings = array_merge($trackings, $ggLabels);

            // Barcodes
            foreach ($batch->getAllBarcodes() as $barcode) {
                if (!in_array($barcode->data, $trackings)) {
                    $trackings[] = $barcode->data;
                }
            }

            // Remove duplicates
            $trackings = array_values(array_unique($trackings));

            Log::info("[Pochtoy] Publishing", [
                'batch_id' => $batch->id,
                'images' => count($images),
                'trackings' => count($trackings)
            ]);

            // 3. Build payload with product data
            $payload = [
                'images' => $images,
                'trackings' => $trackings,
                'title' => $batch->title,
                'description' => $batch->description,
                'price' => $options['price'] ?? $batch->price,
                'brand' => $batch->brand,
                'category' => $batch->category,
                'condition' => $batch->condition,
                'size' => $batch->size,
                'color' => $batch->color,
            ];

            // 4. Send to API
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Content-Type' => 'application/json',
                ])
                ->put($this->apiUrl, $payload);

            Log::info("[Pochtoy] Response", ['status' => $response->status()]);

            if ($response->status() === 400) {
                $error = $response->json('message', 'Unknown error');
                return ListingResult::failure($error, $response->json());
            }

            if ($response->successful()) {
                $result = $response->json();
                if (($result['status'] ?? '') === 'ok') {
                    // Generate tracking-based ID if not provided
                    $externalId = $result['id'] ?? ($trackings[0] ?? $batch->correlation_id);

                    return ListingResult::success(
                        externalId: $externalId,
                        externalUrl: $result['url'] ?? null,
                        platformData: [
                            'images_sent' => count($images),
                            'trackings_sent' => count($trackings),
                            'response' => $result,
                        ]
                    );
                }
                return ListingResult::failure($result['message'] ?? 'Ошибка от Pochtoy', $result);
            }

            return ListingResult::failure("HTTP {$response->status()}", ['status' => $response->status()]);

        } catch (\Exception $e) {
            Log::error("[Pochtoy] Publish error", ['error' => $e->getMessage()]);
            return ListingResult::failure($e->getMessage());
        }
    }

    public function update(ProductListing $listing, array $data = []): ListingResult
    {
        // For now, Pochtoy doesn't support updates, so we delete and re-publish
        $deleteResult = $this->delete($listing);
        if (!$deleteResult->success) {
            return $deleteResult;
        }

        return $this->publish($listing->photoBatch, $data);
    }

    public function delete(ProductListing $listing): ListingResult
    {
        if (!$this->isConfigured()) {
            return ListingResult::failure('Pochtoy API не настроен');
        }

        $batch = $listing->photoBatch;
        $trackings = [];

        // Collect trackings
        $ggLabels = $batch->getGgLabels();
        $trackings = array_merge($trackings, $ggLabels);

        foreach ($batch->getAllBarcodes() as $barcode) {
            if (!in_array($barcode->data, $trackings)) {
                $trackings[] = $barcode->data;
            }
        }

        if (empty($trackings)) {
            return ListingResult::failure('Нет trackings для удаления');
        }

        $deleteUrl = str_replace('/store', '/delete', $this->apiUrl);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Content-Type' => 'application/json',
                ])
                ->post($deleteUrl, [
                    'trackings' => array_values(array_unique($trackings)),
                ]);

            Log::info("[Pochtoy] Delete response", ['status' => $response->status()]);

            if ($response->status() === 400) {
                $error = $response->json('message', 'Error');
                return ListingResult::failure($error, $response->json());
            }

            if ($response->successful()) {
                return ListingResult::success(platformData: ['deleted' => true]);
            }

            return ListingResult::failure("HTTP {$response->status()}");

        } catch (\Exception $e) {
            Log::error("[Pochtoy] Delete error", ['error' => $e->getMessage()]);
            return ListingResult::failure($e->getMessage());
        }
    }

    public function checkStatus(ProductListing $listing): ListingResult
    {
        // Pochtoy doesn't have a status check endpoint yet
        return ListingResult::success(
            externalId: $listing->external_id,
            externalUrl: $listing->external_url,
            platformData: ['status' => 'unknown']
        );
    }
}
