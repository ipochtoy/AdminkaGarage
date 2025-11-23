<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FashnService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.fashn.ai/v1';

    public function __construct()
    {
        $this->apiKey = config('services.fashn.key') ?? env('FASHN_API_KEY', '');
    }

    public function generateModel(string $productImage, ?string $prompt = null, string $resolution = '1k', ?string $aspectRatio = null): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('FASHN_API_KEY not set');
            return null;
        }

        try {
            // If it's a base64 string, use it directly; otherwise treat as URL
            $imageValue = $productImage;
            if (!str_starts_with($productImage, 'http') && !str_starts_with($productImage, 'data:')) {
                $imageValue = $productImage; // Already base64 data URI
            }

            $inputs = [
                'product_image' => $imageValue,
                'output_format' => 'jpeg',
                'resolution' => $resolution,
            ];

            if ($prompt) {
                $inputs['prompt'] = $prompt;
            }
            if ($aspectRatio) {
                $inputs['aspect_ratio'] = $aspectRatio;
            }

            $response = Http::timeout(60)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/run", [
                        'model_name' => 'product-to-model',
                        'inputs' => $inputs,
                    ]);

            if ($response->failed()) {
                Log::error('FASHN API Error: ' . $response->status() . ' - ' . $response->body());
                return null;
            }

            $data = $response->json();
            Log::info('FASHN API Response: ' . json_encode($data));

            $id = $data['id'] ?? null;

            if (!$id) {
                Log::error('No prediction ID returned from FASHN');
                return null;
            }

            Log::info("FASHN: Starting to poll for result ID: {$id}");
            // Poll for result
            return $this->pollResult($id);

        } catch (\Exception $e) {
            Log::error('FASHN Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function changeBackground(string $image, string $backgroundPrompt = 'studio background'): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('FASHN_API_KEY not set');
            return null;
        }

        try {
            $response = Http::timeout(60)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/run", [
                'model_name' => 'background-change',
                'inputs' => [
                    'image' => $image,
                    'prompt' => $backgroundPrompt,
                    'output_format' => 'jpeg',
                ],
            ]);

            if ($response->failed()) {
                Log::error('FASHN Background API Error: ' . $response->status() . ' - ' . $response->body());
                return null;
            }

            $data = $response->json();
            Log::info('FASHN Background Response: ' . json_encode($data));

            $id = $data['id'] ?? null;
            if (!$id) {
                Log::error('No prediction ID returned from FASHN Background');
                return null;
            }

            Log::info("FASHN Background: Starting to poll for result ID: {$id}");
            return $this->pollResult($id, 40);

        } catch (\Exception $e) {
            Log::error('FASHN Background Exception: ' . $e->getMessage());
            return null;
        }
    }

    protected function pollResult(string $id, int $maxAttempts = 60): ?string
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep(2); // Wait 2 seconds

            $response = Http::timeout(30)->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get("{$this->apiUrl}/status/{$id}");

            Log::info("FASHN polling attempt {$i}: " . ($response->json('status') ?? 'unknown'));

            if ($response->failed()) {
                continue;
            }

            $data = $response->json();
            $status = $data['status'] ?? 'unknown';

            if ($status === 'completed') {
                $output = $data['output'] ?? [];
                Log::info('FASHN completed! Output: ' . json_encode($output));
                $resultUrl = $output[0] ?? null;
                if (!$resultUrl) {
                    Log::error('FASHN: No result URL in output');
                }
                return $resultUrl;
            }

            if ($status === 'failed') {
                Log::error('FASHN Prediction Failed: ' . json_encode($data['error'] ?? []));
                return null;
            }
        }

        Log::error('FASHN Timeout');
        return null;
    }
}
