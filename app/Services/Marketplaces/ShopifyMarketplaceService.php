<?php

namespace App\Services\Marketplaces;

use App\Models\PhotoBatch;
use App\Models\ProductListing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ShopifyMarketplaceService implements MarketplaceServiceInterface
{
    protected string $shopDomain;
    protected string $accessToken;
    protected string $apiVersion = '2024-01';

    public function __construct()
    {
        $this->shopDomain = config('services.shopify.shop_domain', ''); // your-shop.myshopify.com
        $this->accessToken = config('services.shopify.access_token', '');
    }

    public function getPlatform(): string
    {
        return ProductListing::PLATFORM_SHOPIFY;
    }

    public function getPlatformName(): string
    {
        return 'Shopify';
    }

    public function isConfigured(): bool
    {
        return !empty($this->shopDomain) && !empty($this->accessToken);
    }

    protected function getApiUrl(string $endpoint): string
    {
        return "https://{$this->shopDomain}/admin/api/{$this->apiVersion}/{$endpoint}";
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $url = $this->getApiUrl($endpoint);

        $request = Http::withHeaders([
            'X-Shopify-Access-Token' => $this->accessToken,
            'Content-Type' => 'application/json',
        ]);

        $response = match (strtoupper($method)) {
            'GET' => $request->get($url, $data),
            'POST' => $request->post($url, $data),
            'PUT' => $request->put($url, $data),
            'DELETE' => $request->delete($url),
            default => throw new \InvalidArgumentException("Invalid HTTP method: {$method}"),
        };

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'data' => $response->json(),
            'body' => $response->body(),
        ];
    }

    public function publish(PhotoBatch $batch, array $options = []): ListingResult
    {
        if (!$this->isConfigured()) {
            return ListingResult::failure('Shopify API не настроен. Проверьте SHOPIFY_SHOP_DOMAIN и SHOPIFY_ACCESS_TOKEN.');
        }

        try {
            // Build product data
            $productData = $this->buildProductData($batch, $options);

            Log::info('[Shopify] Creating product', ['title' => $productData['product']['title']]);

            // Create product
            $response = $this->makeRequest('POST', 'products.json', $productData);

            if (!$response['success']) {
                Log::error('[Shopify] Create product failed', [
                    'status' => $response['status'],
                    'body' => $response['body']
                ]);
                $error = $response['data']['errors'] ?? $response['body'];
                if (is_array($error)) {
                    $error = json_encode($error);
                }
                return ListingResult::failure('Ошибка создания товара в Shopify: ' . $error, $response['data'] ?? []);
            }

            $product = $response['data']['product'] ?? null;
            if (!$product) {
                return ListingResult::failure('Не удалось получить данные созданного товара');
            }

            $productId = $product['id'];
            $handle = $product['handle'];
            $variantId = $product['variants'][0]['id'] ?? null;

            // Upload images
            $imageResults = $this->uploadImages($batch, $productId);

            // Build public URL
            $shopUrl = str_replace('.myshopify.com', '', $this->shopDomain);
            $productUrl = "https://{$shopUrl}.myshopify.com/products/{$handle}";

            return ListingResult::success(
                externalId: (string) $productId,
                externalUrl: $productUrl,
                platformData: [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'handle' => $handle,
                    'images_uploaded' => count($imageResults),
                ]
            );

        } catch (\Exception $e) {
            Log::error('[Shopify] Publish error', ['error' => $e->getMessage()]);
            return ListingResult::failure($e->getMessage());
        }
    }

    protected function buildProductData(PhotoBatch $batch, array $options): array
    {
        $price = $options['price'] ?? $batch->price ?? 0;
        $quantity = $batch->quantity ?: 1;

        // Map condition to Shopify tags
        $tags = [];
        if ($batch->condition) {
            $tags[] = 'condition:' . $batch->condition;
        }
        if ($batch->brand) {
            $tags[] = 'brand:' . $batch->brand;
        }
        if ($batch->category) {
            $tags[] = $batch->category;
        }
        if ($batch->color) {
            $tags[] = 'color:' . $batch->color;
        }
        if ($batch->size) {
            $tags[] = 'size:' . $batch->size;
        }

        $product = [
            'product' => [
                'title' => $batch->title ?: 'Product ' . $batch->id,
                'body_html' => nl2br(e($batch->description ?: '')),
                'vendor' => $batch->brand ?: config('app.name'),
                'product_type' => $batch->category ?: 'Clothing',
                'tags' => implode(', ', $tags),
                'status' => 'active',
                'variants' => [
                    [
                        'price' => number_format((float) $price, 2, '.', ''),
                        'sku' => $batch->sku ?: 'BATCH-' . $batch->id,
                        'inventory_quantity' => $quantity,
                        'inventory_management' => 'shopify',
                        'requires_shipping' => true,
                        'taxable' => true,
                    ],
                ],
            ],
        ];

        // Add size as option if present
        if ($batch->size) {
            $product['product']['options'] = [
                ['name' => 'Size', 'values' => [$batch->size]],
            ];
            $product['product']['variants'][0]['option1'] = $batch->size;
        }

        // Add color as option if present
        if ($batch->color) {
            if (!isset($product['product']['options'])) {
                $product['product']['options'] = [];
            }
            $product['product']['options'][] = ['name' => 'Color', 'values' => [$batch->color]];
            $optionIndex = count($product['product']['options']);
            $product['product']['variants'][0]["option{$optionIndex}"] = $batch->color;
        }

        return $product;
    }

    protected function uploadImages(PhotoBatch $batch, int $productId): array
    {
        $results = [];
        $position = 1;

        foreach ($batch->photos as $photo) {
            if (!$photo->image) {
                continue;
            }

            try {
                // Read image and convert to base64
                $imageData = Storage::disk('public')->get($photo->image);
                if (!$imageData) {
                    continue;
                }

                $base64Image = base64_encode($imageData);
                $filename = basename($photo->image);

                $imagePayload = [
                    'image' => [
                        'attachment' => $base64Image,
                        'filename' => $filename,
                        'position' => $position,
                    ],
                ];

                $response = $this->makeRequest('POST', "products/{$productId}/images.json", $imagePayload);

                if ($response['success']) {
                    $results[] = $response['data']['image']['id'] ?? null;
                    $position++;
                } else {
                    Log::warning('[Shopify] Image upload failed', [
                        'photo_id' => $photo->id,
                        'error' => $response['body']
                    ]);
                }

            } catch (\Exception $e) {
                Log::warning('[Shopify] Image upload error', [
                    'photo_id' => $photo->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Shopify rate limit: max 2 requests per second
            usleep(500000); // 0.5 second delay
        }

        return $results;
    }

    public function update(ProductListing $listing, array $data = []): ListingResult
    {
        if (!$this->isConfigured()) {
            return ListingResult::failure('Shopify API не настроен');
        }

        $productId = $listing->external_id;
        if (!$productId) {
            return ListingResult::failure('Отсутствует product_id для обновления');
        }

        $batch = $listing->photoBatch;
        $platformData = $listing->platform_data ?? [];
        $variantId = $platformData['variant_id'] ?? null;

        try {
            // Update product info
            $updateData = ['product' => ['id' => $productId]];

            if ($batch->title) {
                $updateData['product']['title'] = $batch->title;
            }
            if ($batch->description) {
                $updateData['product']['body_html'] = nl2br(e($batch->description));
            }

            $response = $this->makeRequest('PUT', "products/{$productId}.json", $updateData);

            if (!$response['success']) {
                return ListingResult::failure('Ошибка обновления товара: ' . $response['body']);
            }

            // Update variant price if provided
            if (isset($data['price']) && $variantId) {
                $variantData = [
                    'variant' => [
                        'id' => $variantId,
                        'price' => number_format((float) $data['price'], 2, '.', ''),
                    ],
                ];

                $this->makeRequest('PUT', "variants/{$variantId}.json", $variantData);
            }

            return ListingResult::success(
                externalId: $listing->external_id,
                externalUrl: $listing->external_url,
                platformData: array_merge($platformData, ['updated_at' => now()->toIso8601String()])
            );

        } catch (\Exception $e) {
            return ListingResult::failure('Update error: ' . $e->getMessage());
        }
    }

    public function delete(ProductListing $listing): ListingResult
    {
        if (!$this->isConfigured()) {
            return ListingResult::failure('Shopify API не настроен');
        }

        $productId = $listing->external_id;
        if (!$productId) {
            return ListingResult::failure('Отсутствует product_id для удаления');
        }

        try {
            $response = $this->makeRequest('DELETE', "products/{$productId}.json");

            if (!$response['success'] && $response['status'] !== 404) {
                return ListingResult::failure('Ошибка удаления товара: ' . $response['body']);
            }

            return ListingResult::success(platformData: ['deleted' => true]);

        } catch (\Exception $e) {
            return ListingResult::failure('Delete error: ' . $e->getMessage());
        }
    }

    public function checkStatus(ProductListing $listing): ListingResult
    {
        if (!$this->isConfigured()) {
            return ListingResult::failure('Shopify API не настроен');
        }

        $productId = $listing->external_id;
        if (!$productId) {
            return ListingResult::failure('Отсутствует product_id');
        }

        try {
            $response = $this->makeRequest('GET', "products/{$productId}.json");

            if (!$response['success']) {
                if ($response['status'] === 404) {
                    return ListingResult::failure('Товар не найден', ['status' => 'not_found']);
                }
                return ListingResult::failure('Ошибка проверки статуса');
            }

            $product = $response['data']['product'] ?? [];

            return ListingResult::success(
                externalId: $listing->external_id,
                externalUrl: $listing->external_url,
                platformData: [
                    'status' => $product['status'] ?? 'unknown',
                    'inventory_quantity' => $product['variants'][0]['inventory_quantity'] ?? 0,
                    'title' => $product['title'] ?? '',
                ]
            );

        } catch (\Exception $e) {
            return ListingResult::failure('Check status error: ' . $e->getMessage());
        }
    }
}
