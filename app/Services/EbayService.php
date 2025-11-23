<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EbayService
{
    protected string $appId;

    public function __construct()
    {
        $this->appId = config('services.ebay.app_id', '');
    }

    public function searchProducts(
        ?string $brand = null,
        ?string $model = null,
        ?string $barcode = null,
        ?string $title = null,
        ?string $categoryId = null
    ): ?array {
        if (!$this->appId) {
            Log::warning('eBay App ID not configured');
            return null;
        }

        // Build search query
        $keywords = [];
        if ($brand && $model) {
            $keywords = [$brand, $model];
        } elseif ($brand) {
            $keywords[] = $brand;
        } elseif ($title) {
            $keywords = array_slice(explode(' ', $title), 0, 5);
        } elseif ($barcode) {
            $keywords[] = $barcode;
        }

        if (empty($keywords)) {
            return null;
        }

        $searchQuery = implode(' ', array_slice($keywords, 0, 10));

        // Add barcode to search if available
        if ($barcode && strlen($barcode) >= 8) {
            $searchQuery = trim("{$searchQuery} {$barcode}");
        }

        $params = [
            'OPERATION-NAME' => 'findItemsAdvanced',
            'SERVICE-VERSION' => '1.0.0',
            'SECURITY-APPNAME' => $this->appId,
            'RESPONSE-DATA-FORMAT' => 'JSON',
            'REST-PAYLOAD' => '',
            'keywords' => $searchQuery,
            'paginationInput.entriesPerPage' => '20',
            'sortOrder' => 'PricePlusShippingLowest',
            'itemFilter(0).name' => 'ListingType',
            'itemFilter(0).value' => 'FixedPrice',
        ];

        if ($categoryId) {
            $params['categoryId'] = $categoryId;
        }

        try {
            Log::info("[eBay] Searching for: {$searchQuery}");

            $response = Http::timeout(15)
                ->get('https://svcs.ebay.com/services/search/FindingService/v1', $params);

            if (!$response->successful()) {
                Log::error("[eBay] API error", ['status' => $response->status()]);
                return null;
            }

            $data = $response->json();
            $items = $data['findItemsAdvancedResponse'][0]['searchResult'][0]['item'] ?? [];

            if (empty($items)) {
                Log::info("[eBay] No items found");
                return null;
            }

            return $this->parseItems($items);

        } catch (\Exception $e) {
            Log::error("[eBay] Error", ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function parseItems(array $items): array
    {
        $prices = [];
        $images = [];
        $titles = [];
        $urls = [];
        $comps = [];

        foreach (array_slice($items, 0, 20) as $item) {
            try {
                $priceElem = $item['sellingStatus'][0]['currentPrice'][0] ?? null;
                if (!$priceElem) continue;

                $price = (float) ($priceElem['__value__'] ?? 0);
                if ($price <= 0) continue;

                $prices[] = $price;

                // Shipping cost
                $shippingCost = 0;
                $shippingInfo = $item['shippingInfo'][0] ?? null;
                if ($shippingInfo) {
                    $shippingCostElem = $shippingInfo['shippingServiceCost'][0] ?? null;
                    if ($shippingCostElem) {
                        $shippingCost = (float) ($shippingCostElem['__value__'] ?? 0);
                    }
                }

                // Image URL
                $galleryUrl = $item['galleryURL'][0] ?? '';
                if ($galleryUrl && str_starts_with($galleryUrl, 'http')) {
                    // Upgrade to larger image
                    $galleryUrl = str_replace(['s-l64', 's-l140', 's-l225'], 's-l500', $galleryUrl);
                    if (!in_array($galleryUrl, $images)) {
                        $images[] = $galleryUrl;
                    }
                }

                // Title
                $itemTitle = $item['title'][0] ?? '';
                if ($itemTitle) {
                    $titles[] = $itemTitle;
                }

                // URL
                $viewUrl = $item['viewItemURL'][0] ?? '';
                if ($viewUrl) {
                    $urls[] = $viewUrl;
                }

                // Comps data
                $comps[] = [
                    'item_id' => $item['itemId'][0] ?? '',
                    'title' => $itemTitle,
                    'price' => $price,
                    'shipping_cost' => $shippingCost,
                    'total_price' => $price + $shippingCost,
                    'url' => $viewUrl,
                    'image_url' => $galleryUrl,
                ];

            } catch (\Exception $e) {
                continue;
            }
        }

        $result = [];

        if ($prices) {
            sort($prices);
            $result['price'] = $prices[count($prices) / 2]; // median
            $result['price_min'] = min($prices);
            $result['price_max'] = max($prices);
            $result['price_count'] = count($prices);
        }

        if ($images) {
            $result['images'] = array_slice($images, 0, 20);
        }

        if ($titles) {
            $result['titles'] = array_slice($titles, 0, 10);
        }

        if ($urls) {
            $result['urls'] = array_slice($urls, 0, 10);
        }

        if ($comps) {
            $result['comps'] = array_slice($comps, 0, 10);
        }

        return $result ?: null;
    }
}
