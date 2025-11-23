<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UPCService
{
    protected string $baseUrl = 'https://api.upcitemdb.com/prod/trial/lookup';

    public function lookup(string $barcode): ?array
    {
        // Cache results for 24 hours
        $cacheKey = "upc_lookup_{$barcode}";

        return Cache::remember($cacheKey, 86400, function () use ($barcode) {
            try {
                $response = Http::timeout(10)
                    ->get($this->baseUrl, ['upc' => $barcode]);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['items'][0])) {
                        $item = $data['items'][0];

                        return [
                            'title' => $item['title'] ?? null,
                            'brand' => $item['brand'] ?? null,
                            'description' => $item['description'] ?? null,
                            'lowest_price' => $item['lowest_recorded_price'] ?? null,
                            'highest_price' => $item['highest_recorded_price'] ?? null,
                            'offers' => $this->parseOffers($item['offers'] ?? []),
                            'images' => $item['images'] ?? [],
                            'category' => $item['category'] ?? null,
                        ];
                    }
                }

                Log::warning('UPCitemdb lookup failed', [
                    'barcode' => $barcode,
                    'status' => $response->status(),
                ]);
            } catch (\Exception $e) {
                Log::error('UPCitemdb error', ['error' => $e->getMessage()]);
            }

            return null;
        });
    }

    protected function parseOffers(array $offers): array
    {
        $parsed = [];
        foreach (array_slice($offers, 0, 5) as $offer) {
            $parsed[] = [
                'merchant' => $offer['merchant'] ?? 'Unknown',
                'price' => $offer['price'] ?? null,
                'currency' => $offer['currency'] ?? 'USD',
                'link' => $offer['link'] ?? null,
            ];
        }
        return $parsed;
    }

    public function getPriceSuggestions(string $barcode): array
    {
        $result = $this->lookup($barcode);

        if (!$result) {
            return [];
        }

        $suggestions = [];

        if ($result['lowest_price']) {
            $suggestions['upc_lowest'] = [
                'source' => 'UPCitemdb (мин)',
                'price' => $result['lowest_price'],
            ];
        }

        if ($result['highest_price']) {
            $suggestions['upc_highest'] = [
                'source' => 'UPCitemdb (макс)',
                'price' => $result['highest_price'],
            ];
        }

        foreach ($result['offers'] as $i => $offer) {
            if ($offer['price']) {
                $suggestions["offer_{$i}"] = [
                    'source' => $offer['merchant'],
                    'price' => $offer['price'],
                    'link' => $offer['link'],
                ];
            }
        }

        return $suggestions;
    }
}
