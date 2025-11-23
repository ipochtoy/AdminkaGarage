<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EbayService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl = 'https://api.ebay.com/buy/browse/v1';
    protected string $authUrl = 'https://api.ebay.com/identity/v1/oauth2/token';
    protected string $scope = 'https://api.ebay.com/oauth/api_scope';

    public function __construct()
    {
        // Using keys provided by user directly in code for now, 
        // ideally these should be in .env
        $this->clientId = config('services.ebay.client_id') ?? 'DzianisM-Shoestes-PRD-f6e49d341-f06ff5f5';
        $this->clientSecret = config('services.ebay.client_secret') ?? 'PRD-6e49d341367f-71ae-440b-93ef-daed';
    }

    protected function getAccessToken(): ?string
    {
        return Cache::remember('ebay_access_token', 3500, function () {
            try {
                $response = Http::asForm()
                    ->withBasicAuth($this->clientId, $this->clientSecret)
                    ->post($this->authUrl, [
                        'grant_type' => 'client_credentials',
                        'scope' => $this->scope,
                    ]);

                if ($response->failed()) {
                    Log::error('eBay Auth Failed: ' . $response->body());
                    return null;
                }

                return $response->json('access_token');
            } catch (\Exception $e) {
                Log::error('eBay Auth Exception: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function searchByKeyword(string $query, int $limit = 10): array
    {
        $cacheKey = 'ebay_keyword_' . md5($query . $limit);
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $limit) {
            $token = $this->getAccessToken();
            if (!$token) {
                return [];
            }

            try {
                Log::info("[eBay Browse API] Searching by keyword: {$query}");
                
                $response = Http::withToken($token)
                    ->get("{$this->baseUrl}/item_summary/search", [
                        'q' => $query,
                        'limit' => $limit,
                        'filter' => 'priceCurrency:USD',
                    ]);

                if ($response->failed()) {
                    Log::error('[eBay Browse API] Search Failed', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    return [];
                }

                $results = $this->formatResults($response->json('itemSummaries') ?? []);
                Log::info("[eBay Browse API] Found " . count($results) . " items");
                
                return $results;

            } catch (\Exception $e) {
                Log::error('[eBay Browse API] Search Exception: ' . $e->getMessage());
                return [];
            }
        });
    }

    public function searchByImage(string $imagePath, int $limit = 10): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [];
        }

        $fullPath = Storage::disk('public')->path($imagePath);
        if (!file_exists($fullPath)) {
            Log::error('eBay Image Search: File not found - ' . $fullPath);
            return [];
        }

        try {
            // eBay expects a JSON payload with 'image' as base64
            $base64Image = base64_encode(file_get_contents($fullPath));

            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/item_summary/search_by_image", [
                    'image' => $base64Image,
                    'limit' => $limit,
                    'filter' => 'priceCurrency:USD',
                ]);

            if ($response->failed()) {
                Log::error('eBay Image Search Failed: ' . $response->body());
                return [];
            }

            return $this->formatResults($response->json('itemSummaries') ?? []);

        } catch (\Exception $e) {
            Log::error('eBay Image Search Exception: ' . $e->getMessage());
            return [];
        }
    }

    public function searchByBarcode(string $barcode, int $limit = 10): array
    {
        $cacheKey = 'ebay_barcode_' . md5($barcode . $limit);
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($barcode, $limit) {
            $token = $this->getAccessToken();
            if (!$token) {
                return [];
            }

            try {
                Log::info("[eBay Browse API] Searching by barcode: {$barcode}");
                
                $response = Http::withToken($token)
                    ->get("{$this->baseUrl}/item_summary/search", [
                        'q' => $barcode,
                        'limit' => $limit,
                        'filter' => 'priceCurrency:USD',
                    ]);

                if ($response->failed()) {
                    Log::error('[eBay Browse API] Barcode Search Failed', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    return [];
                }

                $results = $this->formatResults($response->json('itemSummaries') ?? []);
                Log::info("[eBay Browse API] Found " . count($results) . " items");
                
                return $results;

            } catch (\Exception $e) {
                Log::error('[eBay Browse API] Barcode Search Exception: ' . $e->getMessage());
                return [];
            }
        });
    }

    protected function formatResults(array $items): array
    {
        return array_map(function ($item) {
            return [
                'itemId' => $item['itemId'] ?? null,
                'title' => $item['title'] ?? 'Unknown Title',
                'price' => [
                    'value' => $item['price']['value'] ?? 0,
                    'currency' => $item['price']['currency'] ?? 'USD',
                ],
                'image' => $item['image']['imageUrl'] ?? null,
                'url' => $item['itemWebUrl'] ?? '#',
                'condition' => $item['condition'] ?? 'Unknown',
            ];
        }, $items);
    }
}
