<?php

namespace App\Services\Marketplaces;

use App\Models\PhotoBatch;
use App\Models\ProductListing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EbayMarketplaceService implements MarketplaceServiceInterface
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $refreshToken;
    protected string $environment;

    // API URLs
    protected string $authUrl;
    protected string $inventoryApiUrl;
    protected string $sellApiUrl;

    // Default listing settings
    protected string $merchantLocationKey = 'default_location';
    protected string $fulfillmentPolicyId;
    protected string $paymentPolicyId;
    protected string $returnPolicyId;
    protected string $categoryId = '11450'; // Clothing, Shoes & Accessories

    public function __construct()
    {
        $this->clientId = config('services.ebay.client_id', '');
        $this->clientSecret = config('services.ebay.client_secret', '');
        $this->refreshToken = config('services.ebay.refresh_token', '');
        $this->environment = config('services.ebay.environment', 'sandbox');

        // Set URLs based on environment
        $baseUrl = $this->environment === 'production'
            ? 'https://api.ebay.com'
            : 'https://api.sandbox.ebay.com';

        $this->authUrl = $baseUrl . '/identity/v1/oauth2/token';
        $this->inventoryApiUrl = $baseUrl . '/sell/inventory/v1';
        $this->sellApiUrl = $baseUrl . '/sell/fulfillment/v1';

        // Policy IDs (should be configured per account)
        $this->fulfillmentPolicyId = config('services.ebay.fulfillment_policy_id', '');
        $this->paymentPolicyId = config('services.ebay.payment_policy_id', '');
        $this->returnPolicyId = config('services.ebay.return_policy_id', '');
    }

    public function getPlatform(): string
    {
        return ProductListing::PLATFORM_EBAY;
    }

    public function getPlatformName(): string
    {
        return 'eBay';
    }

    public function isConfigured(): bool
    {
        return !empty($this->clientId)
            && !empty($this->clientSecret)
            && !empty($this->refreshToken);
    }

    protected function getAccessToken(): ?string
    {
        return Cache::remember('ebay_user_access_token', 3500, function () {
            if (!$this->isConfigured()) {
                Log::error('[eBay] Not configured for user access token');
                return null;
            }

            try {
                $response = Http::asForm()
                    ->withBasicAuth($this->clientId, $this->clientSecret)
                    ->post($this->authUrl, [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $this->refreshToken,
                        'scope' => 'https://api.ebay.com/oauth/api_scope/sell.inventory https://api.ebay.com/oauth/api_scope/sell.marketing https://api.ebay.com/oauth/api_scope/sell.account https://api.ebay.com/oauth/api_scope/sell.fulfillment',
                    ]);

                if ($response->failed()) {
                    Log::error('[eBay] Auth Failed: ' . $response->body());
                    return null;
                }

                return $response->json('access_token');
            } catch (\Exception $e) {
                Log::error('[eBay] Auth Exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function publish(PhotoBatch $batch, array $options = []): ListingResult
    {
        if (!$this->isConfigured()) {
            return ListingResult::failure('eBay API не настроен. Требуется refresh_token для OAuth 2.0.');
        }

        $token = $this->getAccessToken();
        if (!$token) {
            return ListingResult::failure('Не удалось получить access token для eBay');
        }

        try {
            // Step 1: Create or update inventory item
            $sku = $batch->sku ?: 'BATCH-' . $batch->id;
            $inventoryResult = $this->createInventoryItem($token, $batch, $sku, $options);

            if (!$inventoryResult->success) {
                return $inventoryResult;
            }

            // Step 2: Create offer
            $offerResult = $this->createOffer($token, $batch, $sku, $options);

            if (!$offerResult->success) {
                return $offerResult;
            }

            $offerId = $offerResult->externalId;

            // Step 3: Publish offer
            $publishResult = $this->publishOffer($token, $offerId);

            if (!$publishResult->success) {
                return $publishResult;
            }

            $listingId = $publishResult->externalId;
            $listingUrl = $this->environment === 'production'
                ? "https://www.ebay.com/itm/{$listingId}"
                : "https://www.sandbox.ebay.com/itm/{$listingId}";

            return ListingResult::success(
                externalId: $listingId,
                externalUrl: $listingUrl,
                platformData: [
                    'sku' => $sku,
                    'offer_id' => $offerId,
                    'listing_id' => $listingId,
                ]
            );

        } catch (\Exception $e) {
            Log::error('[eBay] Publish error', ['error' => $e->getMessage()]);
            return ListingResult::failure($e->getMessage());
        }
    }

    protected function createInventoryItem(string $token, PhotoBatch $batch, string $sku, array $options): ListingResult
    {
        // Upload images first
        $imageUrls = $this->uploadImages($token, $batch);

        $condition = match ($batch->condition) {
            'new' => 'NEW_WITH_TAGS',
            'like_new' => 'NEW_WITHOUT_TAGS',
            default => 'USED_EXCELLENT',
        };

        $payload = [
            'availability' => [
                'shipToLocationAvailability' => [
                    'quantity' => $batch->quantity ?: 1,
                ],
            ],
            'condition' => $condition,
            'product' => [
                'title' => mb_substr($batch->title ?: 'Product', 0, 80),
                'description' => $batch->description ?: 'No description',
                'aspects' => $this->buildAspects($batch),
                'imageUrls' => $imageUrls,
            ],
        ];

        if ($batch->brand) {
            $payload['product']['brand'] = $batch->brand;
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders(['Content-Language' => 'en-US'])
                ->put("{$this->inventoryApiUrl}/inventory_item/{$sku}", $payload);

            if ($response->failed() && $response->status() !== 204) {
                Log::error('[eBay] Create inventory item failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ListingResult::failure(
                    'Ошибка создания товара в eBay: ' . $response->json('errors.0.message', $response->body()),
                    $response->json()
                );
            }

            return ListingResult::success(externalId: $sku);

        } catch (\Exception $e) {
            return ListingResult::failure('Inventory item error: ' . $e->getMessage());
        }
    }

    protected function createOffer(string $token, PhotoBatch $batch, string $sku, array $options): ListingResult
    {
        $price = $options['price'] ?? $batch->price ?? 10.00;

        $payload = [
            'sku' => $sku,
            'marketplaceId' => 'EBAY_US',
            'format' => 'FIXED_PRICE',
            'listingDuration' => 'GTC', // Good 'Til Cancelled
            'pricingSummary' => [
                'price' => [
                    'value' => number_format((float)$price, 2, '.', ''),
                    'currency' => 'USD',
                ],
            ],
            'categoryId' => $this->categoryId,
            'merchantLocationKey' => $this->merchantLocationKey,
        ];

        // Add policies if configured
        if ($this->fulfillmentPolicyId) {
            $payload['listingPolicies']['fulfillmentPolicyId'] = $this->fulfillmentPolicyId;
        }
        if ($this->paymentPolicyId) {
            $payload['listingPolicies']['paymentPolicyId'] = $this->paymentPolicyId;
        }
        if ($this->returnPolicyId) {
            $payload['listingPolicies']['returnPolicyId'] = $this->returnPolicyId;
        }

        try {
            $response = Http::withToken($token)
                ->withHeaders(['Content-Language' => 'en-US'])
                ->post("{$this->inventoryApiUrl}/offer", $payload);

            if ($response->failed()) {
                Log::error('[eBay] Create offer failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ListingResult::failure(
                    'Ошибка создания offer в eBay: ' . $response->json('errors.0.message', $response->body()),
                    $response->json()
                );
            }

            $offerId = $response->json('offerId');
            return ListingResult::success(externalId: $offerId);

        } catch (\Exception $e) {
            return ListingResult::failure('Offer error: ' . $e->getMessage());
        }
    }

    protected function publishOffer(string $token, string $offerId): ListingResult
    {
        try {
            $response = Http::withToken($token)
                ->post("{$this->inventoryApiUrl}/offer/{$offerId}/publish");

            if ($response->failed()) {
                Log::error('[eBay] Publish offer failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ListingResult::failure(
                    'Ошибка публикации в eBay: ' . $response->json('errors.0.message', $response->body()),
                    $response->json()
                );
            }

            $listingId = $response->json('listingId');
            return ListingResult::success(externalId: $listingId);

        } catch (\Exception $e) {
            return ListingResult::failure('Publish error: ' . $e->getMessage());
        }
    }

    protected function uploadImages(string $token, PhotoBatch $batch): array
    {
        // For now, return public URLs if the images are accessible
        // In production, you'd upload to eBay's hosting or use external URLs
        $urls = [];
        $baseUrl = config('app.url');

        foreach ($batch->photos as $photo) {
            if ($photo->image) {
                // Assuming images are in public storage
                $urls[] = $baseUrl . '/storage/' . $photo->image;
            }
        }

        return array_slice($urls, 0, 12); // eBay allows max 12 images
    }

    protected function buildAspects(PhotoBatch $batch): array
    {
        $aspects = [];

        if ($batch->brand) {
            $aspects['Brand'] = [$batch->brand];
        }
        if ($batch->size) {
            $aspects['Size'] = [$batch->size];
        }
        if ($batch->color) {
            $aspects['Color'] = [$batch->color];
        }

        return $aspects;
    }

    public function update(ProductListing $listing, array $data = []): ListingResult
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ListingResult::failure('Не удалось получить access token');
        }

        $platformData = $listing->platform_data ?? [];
        $sku = $platformData['sku'] ?? null;
        $offerId = $platformData['offer_id'] ?? null;

        if (!$sku || !$offerId) {
            return ListingResult::failure('Отсутствуют данные для обновления листинга');
        }

        // Update inventory item
        $batch = $listing->photoBatch;
        $inventoryResult = $this->createInventoryItem($token, $batch, $sku, $data);

        if (!$inventoryResult->success) {
            return $inventoryResult;
        }

        // Update offer price if provided
        if (isset($data['price'])) {
            try {
                $response = Http::withToken($token)
                    ->withHeaders(['Content-Language' => 'en-US'])
                    ->put("{$this->inventoryApiUrl}/offer/{$offerId}", [
                        'pricingSummary' => [
                            'price' => [
                                'value' => number_format((float)$data['price'], 2, '.', ''),
                                'currency' => 'USD',
                            ],
                        ],
                    ]);

                if ($response->failed()) {
                    return ListingResult::failure('Ошибка обновления цены: ' . $response->body());
                }
            } catch (\Exception $e) {
                return ListingResult::failure('Update price error: ' . $e->getMessage());
            }
        }

        return ListingResult::success(
            externalId: $listing->external_id,
            externalUrl: $listing->external_url,
            platformData: array_merge($platformData, ['updated_at' => now()->toIso8601String()])
        );
    }

    public function delete(ProductListing $listing): ListingResult
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ListingResult::failure('Не удалось получить access token');
        }

        $platformData = $listing->platform_data ?? [];
        $offerId = $platformData['offer_id'] ?? null;
        $sku = $platformData['sku'] ?? null;

        try {
            // Withdraw the offer (end listing)
            if ($offerId) {
                $response = Http::withToken($token)
                    ->post("{$this->inventoryApiUrl}/offer/{$offerId}/withdraw");

                if ($response->failed() && $response->status() !== 404) {
                    Log::warning('[eBay] Withdraw offer failed', ['body' => $response->body()]);
                }
            }

            // Delete inventory item
            if ($sku) {
                $response = Http::withToken($token)
                    ->delete("{$this->inventoryApiUrl}/inventory_item/{$sku}");

                if ($response->failed() && $response->status() !== 404) {
                    return ListingResult::failure('Ошибка удаления товара: ' . $response->body());
                }
            }

            return ListingResult::success(platformData: ['deleted' => true]);

        } catch (\Exception $e) {
            return ListingResult::failure('Delete error: ' . $e->getMessage());
        }
    }

    public function checkStatus(ProductListing $listing): ListingResult
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ListingResult::failure('Не удалось получить access token');
        }

        $platformData = $listing->platform_data ?? [];
        $offerId = $platformData['offer_id'] ?? null;

        if (!$offerId) {
            return ListingResult::failure('Отсутствует offer_id');
        }

        try {
            $response = Http::withToken($token)
                ->get("{$this->inventoryApiUrl}/offer/{$offerId}");

            if ($response->failed()) {
                if ($response->status() === 404) {
                    return ListingResult::failure('Листинг не найден', ['status' => 'not_found']);
                }
                return ListingResult::failure('Ошибка проверки статуса');
            }

            $data = $response->json();
            return ListingResult::success(
                externalId: $listing->external_id,
                externalUrl: $listing->external_url,
                platformData: [
                    'status' => $data['status'] ?? 'unknown',
                    'listing_status' => $data['listing']['listingStatus'] ?? 'unknown',
                ]
            );

        } catch (\Exception $e) {
            return ListingResult::failure('Check status error: ' . $e->getMessage());
        }
    }
}
