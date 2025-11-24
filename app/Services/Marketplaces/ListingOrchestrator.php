<?php

namespace App\Services\Marketplaces;

use App\Models\PhotoBatch;
use App\Models\ProductListing;
use Illuminate\Support\Facades\Log;

class ListingOrchestrator
{
    protected array $services = [];

    public function __construct()
    {
        // Register all marketplace services
        $this->registerService(new PochtoyMarketplaceService());
        $this->registerService(new EbayMarketplaceService());
        $this->registerService(new ShopifyMarketplaceService());
    }

    public function registerService(MarketplaceServiceInterface $service): void
    {
        $this->services[$service->getPlatform()] = $service;
    }

    public function getService(string $platform): ?MarketplaceServiceInterface
    {
        return $this->services[$platform] ?? null;
    }

    public function getAllServices(): array
    {
        return $this->services;
    }

    public function getConfiguredServices(): array
    {
        return array_filter($this->services, fn($service) => $service->isConfigured());
    }

    public function getAvailablePlatforms(): array
    {
        $platforms = [];
        foreach ($this->services as $platform => $service) {
            $platforms[$platform] = [
                'name' => $service->getPlatformName(),
                'configured' => $service->isConfigured(),
            ];
        }
        return $platforms;
    }

    /**
     * Publish to multiple platforms at once
     *
     * @param PhotoBatch $batch
     * @param array $platforms List of platform identifiers to publish to
     * @param array $options Additional options (price override, etc.)
     * @return array Results keyed by platform
     */
    public function publishToMultiple(PhotoBatch $batch, array $platforms, array $options = []): array
    {
        $results = [];

        foreach ($platforms as $platform) {
            $results[$platform] = $this->publishTo($batch, $platform, $options);
        }

        return $results;
    }

    /**
     * Publish to all configured platforms
     */
    public function publishToAll(PhotoBatch $batch, array $options = []): array
    {
        $platforms = array_keys($this->getConfiguredServices());
        return $this->publishToMultiple($batch, $platforms, $options);
    }

    /**
     * Publish to a single platform
     */
    public function publishTo(PhotoBatch $batch, string $platform, array $options = []): array
    {
        $service = $this->getService($platform);

        if (!$service) {
            return [
                'success' => false,
                'error' => "Платформа '{$platform}' не найдена",
            ];
        }

        if (!$service->isConfigured()) {
            return [
                'success' => false,
                'error' => "Платформа '{$service->getPlatformName()}' не настроена",
            ];
        }

        // Check if already listed on this platform
        $existingListing = $batch->getListingFor($platform);
        if ($existingListing && $existingListing->isPublished()) {
            return [
                'success' => false,
                'error' => "Товар уже опубликован на {$service->getPlatformName()}",
                'listing' => $existingListing,
            ];
        }

        // Create or get pending listing
        $listing = $existingListing ?? ProductListing::create([
            'photo_batch_id' => $batch->id,
            'platform' => $platform,
            'status' => ProductListing::STATUS_PENDING,
            'listed_price' => $options['price'] ?? $batch->price,
        ]);

        Log::info("[ListingOrchestrator] Publishing to {$platform}", [
            'batch_id' => $batch->id,
            'listing_id' => $listing->id,
        ]);

        // Attempt to publish
        $result = $service->publish($batch, $options);

        if ($result->success) {
            $listing->markAsPublished(
                $result->externalId,
                $result->externalUrl,
                $result->platformData
            );

            Log::info("[ListingOrchestrator] Published successfully", [
                'platform' => $platform,
                'external_id' => $result->externalId,
            ]);

            return [
                'success' => true,
                'listing' => $listing->fresh(),
                'external_id' => $result->externalId,
                'external_url' => $result->externalUrl,
            ];
        } else {
            $listing->markAsFailed($result->error);

            Log::error("[ListingOrchestrator] Publish failed", [
                'platform' => $platform,
                'error' => $result->error,
            ]);

            return [
                'success' => false,
                'error' => $result->error,
                'listing' => $listing->fresh(),
            ];
        }
    }

    /**
     * Update listing on a platform
     */
    public function updateListing(ProductListing $listing, array $data = []): array
    {
        $service = $this->getService($listing->platform);

        if (!$service) {
            return ['success' => false, 'error' => 'Сервис не найден'];
        }

        $result = $service->update($listing, $data);

        if ($result->success) {
            $listing->update([
                'platform_data' => array_merge($listing->platform_data ?? [], $result->platformData),
                'listed_price' => $data['price'] ?? $listing->listed_price,
            ]);

            return [
                'success' => true,
                'listing' => $listing->fresh(),
            ];
        }

        return [
            'success' => false,
            'error' => $result->error,
        ];
    }

    /**
     * Delete/unpublish from a platform
     */
    public function deleteListing(ProductListing $listing): array
    {
        $service = $this->getService($listing->platform);

        if (!$service) {
            return ['success' => false, 'error' => 'Сервис не найден'];
        }

        $result = $service->delete($listing);

        if ($result->success) {
            $listing->markAsDeleted();
            return ['success' => true];
        }

        return [
            'success' => false,
            'error' => $result->error,
        ];
    }

    /**
     * Delete from all platforms
     */
    public function deleteFromAll(PhotoBatch $batch): array
    {
        $results = [];

        foreach ($batch->listings as $listing) {
            if ($listing->isPublished()) {
                $results[$listing->platform] = $this->deleteListing($listing);
            }
        }

        return $results;
    }

    /**
     * Check status of a listing
     */
    public function checkListingStatus(ProductListing $listing): array
    {
        $service = $this->getService($listing->platform);

        if (!$service) {
            return ['success' => false, 'error' => 'Сервис не найден'];
        }

        $result = $service->checkStatus($listing);

        return [
            'success' => $result->success,
            'status' => $result->platformData['status'] ?? 'unknown',
            'data' => $result->platformData,
            'error' => $result->error,
        ];
    }

    /**
     * Sync all listings for a batch
     */
    public function syncBatchListings(PhotoBatch $batch): array
    {
        $results = [];

        foreach ($batch->listings as $listing) {
            if ($listing->isPublished()) {
                $results[$listing->platform] = $this->checkListingStatus($listing);
            }
        }

        return $results;
    }

    /**
     * Get summary of listings for a batch
     */
    public function getBatchListingSummary(PhotoBatch $batch): array
    {
        $summary = [
            'total_platforms' => count($this->services),
            'configured_platforms' => count($this->getConfiguredServices()),
            'published' => [],
            'pending' => [],
            'failed' => [],
            'not_listed' => [],
        ];

        $listedPlatforms = [];

        foreach ($batch->listings as $listing) {
            $listedPlatforms[] = $listing->platform;

            switch ($listing->status) {
                case ProductListing::STATUS_PUBLISHED:
                    $summary['published'][$listing->platform] = [
                        'listing_id' => $listing->id,
                        'external_id' => $listing->external_id,
                        'external_url' => $listing->external_url,
                        'price' => $listing->listed_price,
                        'published_at' => $listing->published_at?->toIso8601String(),
                    ];
                    break;
                case ProductListing::STATUS_PENDING:
                    $summary['pending'][$listing->platform] = [
                        'listing_id' => $listing->id,
                    ];
                    break;
                case ProductListing::STATUS_FAILED:
                    $summary['failed'][$listing->platform] = [
                        'listing_id' => $listing->id,
                        'error' => $listing->error_message,
                    ];
                    break;
            }
        }

        // Find platforms where not listed
        foreach ($this->services as $platform => $service) {
            if (!in_array($platform, $listedPlatforms)) {
                $summary['not_listed'][$platform] = [
                    'name' => $service->getPlatformName(),
                    'configured' => $service->isConfigured(),
                ];
            }
        }

        return $summary;
    }
}
