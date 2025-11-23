<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiService
{
    protected string $apiKey;
    protected string $model = 'gemini-3-pro-preview'; // Latest model

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY', '');
    }

    public function generateProductDescription(array $photoPaths, ?string $customPrompt = null): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('GEMINI_API_KEY not set in config or env');
            return null;
        }

        try {
            Log::info('GeminiService: Starting generation for ' . count($photoPaths) . ' photos.');

            // Prepare images (convert to base64)
            $imageParts = [];
            foreach ($photoPaths as $path) {
                $fullPath = Storage::disk('public')->path($path);
                if (file_exists($fullPath)) {
                    $imageData = base64_encode(file_get_contents($fullPath));
                    $mimeType = mime_content_type($fullPath);

                    $imageParts[] = [
                        'inline_data' => [
                            'mime_type' => $mimeType,
                            'data' => $imageData,
                        ],
                    ];
                } else {
                    Log::warning("GeminiService: File not found at $fullPath");
                }
            }

            if (empty($imageParts)) {
                Log::error('GeminiService: No valid images found to process.');
                return null;
            }

            // Construct prompt
            $prompt = $customPrompt ?? $this->getDefaultPrompt();

            $contents = [
                [
                    'parts' => array_merge(
                        [['text' => $prompt]],
                        $imageParts
                    ),
                ],
            ];

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

            Log::info("GeminiService: Sending request to $url");

            $response = Http::timeout(120)->post($url, [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.4,
                    'topK' => 32,
                    'topP' => 1,
                    'maxOutputTokens' => 8192,  // Increased from 2048 to prevent truncation
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->status() . ' - ' . $response->body());
                return null;
            }

            $data = $response->json();
            Log::info('GeminiService: Raw Response: ' . json_encode($data));

            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                Log::error('GeminiService: No text returned in candidate parts.');
                return null;
            }

            // Parse the structured response
            return $this->parseResponse($text);

        } catch (\Exception $e) {
            Log::error('GeminiService Exception: ' . $e->getMessage() . "\nStack Trace: " . $e->getTraceAsString());
            return null;
        }
    }

    protected function getDefaultPrompt(): string
    {
        return <<<PROMPT
Проанализируй фотографии товара для e-commerce листинга. ОТВЕЧАЙ ТОЛЬКО НА РУССКОМ ЯЗЫКЕ.

ЗАДАЧИ:
1.  **Найти лейблы:** Ищи жёлтую или зелёную наклейку с внутренним кодом (например "GG1418", "Q2630609"). Верни все найденные уникальные коды.
2.  **Найти баркоды:** Ищи любые видимые баркоды (UPC, EAN) на бирках. Верни числа если читаемы.
3.  **Детали товара:** Определи Бренд, Размер (внимательно читай бирку размера), Цвет, Материал и Категорию.
4.  **Описание:** Сгенерируй привлекательное Название и подробное Описание НА РУССКОМ.

ВЕРНИ ТОЛЬКО JSON формат:
{
  "internal_ids": ["GGxxxx", "Qxxxx"],
  "barcodes": ["123456789012"],
  "title": "Название товара на русском",
  "description": "Подробное описание товара на русском...",
  "brand": "Бренд",
  "category": "Категория",
  "color": "Цвет",
  "material": "Материал",
  "size": "Размер с бирки",
  "condition": "new или used",
  "price_estimate": 25.00,
  "price_min": 20.00,
  "price_max": 35.00
}

ВАЖНО: price_estimate ОБЯЗАТЕЛЬНО должен быть больше 0! Оцени рыночную стоимость товара на eBay исходя из бренда, состояния и типа товара. Типичный диапазон для одежды $15-50.
PROMPT;
    }

    protected function parseResponse(string $text): ?array
    {
        Log::info('GeminiService: Parsing text: ' . $text);

        // Try to extract JSON from the response
        $text = trim($text);

        // Remove markdown code block if present
        if (str_starts_with($text, '```json')) {
            $text = preg_replace('/^```json\s*/', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);
        } elseif (str_starts_with($text, '```')) {
            $text = preg_replace('/^```\s*/', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);
        }

        $data = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('GeminiService: Failed to parse JSON. Error: ' . json_last_error_msg() . "\nText: " . $text);
            return null;
        }

        return $data;
    }
}
