<?php

namespace App\Services;

use App\Models\PhotoBatch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PochtoyService
{
    protected string $apiUrl;
    protected string $apiToken;

    public function __construct()
    {
        $this->apiUrl = 'https://pochtoy-test.pochtoy3.ru/api/garage-tg/store';
        $this->apiToken = config('services.pochtoy.api_token', '');
    }

    public function sendCard(PhotoBatch $card): array
    {
        try {
            // 1. Collect images as base64
            $images = [];
            foreach ($card->photos as $idx => $photo) {
                try {
                    $imageData = Storage::disk('public')->get($photo->image);
                    if (!$imageData) continue;

                    $images[] = [
                        'base64' => base64_encode($imageData),
                        'file_name' => "{$card->correlation_id}_{$idx}.jpg"
                    ];
                } catch (\Exception $e) {
                    Log::warning("Failed to encode photo {$photo->id}", ['error' => $e->getMessage()]);
                }
            }

            // 2. Collect trackings (GG labels + barcodes)
            $trackings = [];

            // GG labels
            $ggLabels = $card->getGgLabels();
            $trackings = array_merge($trackings, $ggLabels);

            // Barcodes
            foreach ($card->getAllBarcodes() as $barcode) {
                if (!in_array($barcode->data, $trackings)) {
                    $trackings[] = $barcode->data;
                }
            }

            // Remove duplicates
            $trackings = array_values(array_unique($trackings));

            Log::info("Sending to Pochtoy", [
                'images' => count($images),
                'trackings' => count($trackings)
            ]);

            // 3. Send to API
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Content-Type' => 'application/json',
                ])
                ->put($this->apiUrl, [
                    'images' => $images,
                    'trackings' => $trackings,
                ]);

            Log::info("Pochtoy response", ['status' => $response->status()]);

            if ($response->status() === 400) {
                $error = $response->json('message', 'Unknown error');
                return ['success' => false, 'error' => $error];
            }

            if ($response->successful()) {
                $result = $response->json();
                if (($result['status'] ?? '') === 'ok') {
                    return [
                        'success' => true,
                        'message' => 'Product sent successfully',
                        'images_sent' => count($images),
                        'trackings_sent' => count($trackings),
                    ];
                }
                return ['success' => false, 'error' => $result['message'] ?? 'Error from Pochtoy'];
            }

            return ['success' => false, 'error' => "HTTP {$response->status()}"];

        } catch (\Exception $e) {
            Log::error("Pochtoy send error", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteCard(array $trackings): array
    {
        if (empty($trackings)) {
            return ['success' => false, 'error' => 'No trackings provided'];
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

            Log::info("Pochtoy delete response", ['status' => $response->status()]);

            if ($response->status() === 400) {
                $error = $response->json('message', 'Error');
                return ['success' => false, 'error' => $error];
            }

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Deleted from Pochtoy'];
            }

            return ['success' => false, 'error' => "HTTP {$response->status()}"];

        } catch (\Exception $e) {
            Log::error("Pochtoy delete error", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
