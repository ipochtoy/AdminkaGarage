<?php

namespace App\Services;

use App\Models\Prompt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIService
{
    protected string $provider;

    public function __construct()
    {
        $this->provider = config('services.ai.provider', 'openai');
    }

    public function generateSummary(array $photoUrls, array $barcodes = [], array $ggLabels = []): ?string
    {
        $prompt = Prompt::get('generate_summary');

        $systemPrompt = $prompt ? $prompt->render([
            'barcodes' => implode(', ', $barcodes),
            'gg_labels' => implode(', ', $ggLabels),
        ]) : $this->getDefaultSummaryPrompt($barcodes, $ggLabels);

        if ($this->provider === 'gemini') {
            return $this->callGemini($systemPrompt, $photoUrls, $prompt);
        }

        return $this->callOpenAI($systemPrompt, $photoUrls, $prompt);
    }

    public function generateSummaryFromPaths(array $photoPaths, array $barcodes = [], array $ggLabels = []): ?string
    {
        $prompt = Prompt::get('generate_summary');

        $systemPrompt = $prompt ? $prompt->render([
            'barcodes' => implode(', ', $barcodes),
            'gg_labels' => implode(', ', $ggLabels),
        ]) : $this->getDefaultSummaryPrompt($barcodes, $ggLabels);

        return $this->callOpenAIWithPaths($systemPrompt, $photoPaths, $prompt);
    }

    protected function callOpenAIWithPaths(string $systemPrompt, array $photoPaths, ?Prompt $promptConfig): ?string
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            Log::error('OpenAI API key not configured');
            return null;
        }

        $content = [['type' => 'text', 'text' => $systemPrompt]];

        foreach (array_slice($photoPaths, 0, 5) as $path) {
            $fullPath = Storage::disk('public')->path($path);
            if (file_exists($fullPath)) {
                $imageData = base64_encode(file_get_contents($fullPath));
                $mimeType = mime_content_type($fullPath);

                $content[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => "data:{$mimeType};base64,{$imageData}"]
                ];
            }
        }

        $model = $promptConfig?->model ?? 'gpt-5.1';
        $maxTokens = $promptConfig?->max_tokens ?? 2000;
        $temperature = $promptConfig?->temperature ?? 0.3;

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => $content]],
                    'max_tokens' => $maxTokens,
                    'temperature' => $temperature,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('OpenAI request failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    public function analyzePhotos(array $photoUrls): ?array
    {
        $prompt = Prompt::get('analyze_photos');
        $systemPrompt = $prompt ? $prompt->prompt : $this->getDefaultAnalyzePrompt();

        $response = $this->provider === 'gemini'
            ? $this->callGemini($systemPrompt, $photoUrls, $prompt)
            : $this->callOpenAI($systemPrompt, $photoUrls, $prompt);

        if (!$response) return null;

        return $this->parseJsonResponse($response);
    }

    public function scanBarcodes(array $photoUrls): ?array
    {
        $prompt = Prompt::get('scan_barcodes');
        $systemPrompt = $prompt ? $prompt->prompt : $this->getDefaultScanBarcodesPrompt();

        // Use configured provider (default OpenAI for better results)
        $response = $this->provider === 'gemini'
            ? $this->callGemini($systemPrompt, $photoUrls, $prompt)
            : $this->callOpenAI($systemPrompt, $photoUrls, $prompt);

        if (!$response) return null;

        return $this->parseJsonResponse($response);
    }

    protected function getDefaultScanBarcodesPrompt(): string
    {
        return <<<PROMPT
Внимательно проанализируй все фотографии и найди ВСЕ баркоды и QR-коды.

Особое внимание удели:
1. Стандартным баркодам (UPC, EAN, CODE128, CODE39)
2. QR-кодам
3. Лейблам "GG" - это наши внутренние этикетки с кодом формата Q123456 или просто числовым кодом. Они обычно на белой бумажной наклейке с логотипом GG.

Верни JSON массив найденных кодов:
{
  "barcodes": [
    {
      "data": "значение баркода",
      "symbology": "тип (EAN13, UPC-A, CODE128, CODE39, QR, etc)",
      "is_gg_label": false
    }
  ],
  "gg_labels": [
    {
      "data": "Q123456",
      "symbology": "CODE39"
    }
  ]
}

Если на фото есть лейбла GG с кодом - обязательно добавь её в gg_labels.
Верни ТОЛЬКО валидный JSON без markdown.
PROMPT;
    }

    protected function callOpenAI(string $systemPrompt, array $photoUrls, ?Prompt $promptConfig): ?string
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            Log::error('OpenAI API key not configured');
            return null;
        }

        $content = [['type' => 'text', 'text' => $systemPrompt]];

        foreach (array_slice($photoUrls, 0, 5) as $url) {
            if (str_starts_with($url, 'http')) {
                try {
                    // Download and convert to base64 for local URLs
                    $imageData = Http::timeout(10)->get($url)->body();
                    $base64 = base64_encode($imageData);
                    $mimeType = $this->getMimeType($url);

                    $content[] = [
                        'type' => 'image_url',
                        'image_url' => ['url' => "data:{$mimeType};base64,{$base64}"]
                    ];
                } catch (\Exception $e) {
                    Log::warning('Failed to download image for OpenAI', ['url' => $url]);
                }
            }
        }

        $model = $promptConfig?->model ?? 'gpt-5.1';
        $maxTokens = $promptConfig?->max_tokens ?? 2000;
        $temperature = $promptConfig?->temperature ?? 0.3;

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [['role' => 'user', 'content' => $content]],
                    'max_tokens' => $maxTokens,
                    'temperature' => $temperature,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('OpenAI API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('OpenAI request failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected function callGemini(string $systemPrompt, array $photoUrls, ?Prompt $promptConfig): ?string
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            Log::error('Gemini API key not configured');
            return null;
        }

        $parts = [['text' => $systemPrompt]];

        // Add images as base64 for Gemini
        foreach (array_slice($photoUrls, 0, 5) as $url) {
            if (str_starts_with($url, 'http')) {
                try {
                    $imageData = Http::timeout(10)->get($url)->body();
                    $base64 = base64_encode($imageData);
                    $mimeType = $this->getMimeType($url);

                    $parts[] = [
                        'inline_data' => [
                            'mime_type' => $mimeType,
                            'data' => $base64
                        ]
                    ];
                } catch (\Exception $e) {
                    Log::warning('Failed to download image for Gemini', ['url' => $url]);
                }
            }
        }

        $model = 'gemini-3-pro-preview';

        try {
            $response = Http::timeout(60)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => $parts]],
                    'generationConfig' => [
                        'temperature' => $promptConfig?->temperature ?? 0.3,
                        'maxOutputTokens' => $promptConfig?->max_tokens ?? 2000,
                    ]
                ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('Gemini request failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    protected function getMimeType(string $url): string
    {
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        return match($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    protected function parseJsonResponse(string $response): ?array
    {
        $text = $response;

        // Remove markdown code blocks
        if (str_starts_with($text, '```')) {
            $parts = explode('```', $text);
            if (count($parts) > 1) {
                $text = $parts[1];
                if (str_starts_with($text, 'json')) {
                    $text = substr($text, 4);
                }
            }
        }

        try {
            return json_decode(trim($text), true);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getDefaultSummaryPrompt(array $barcodes, array $ggLabels): string
    {
        $context = '';
        if ($barcodes) {
            $context .= "Barcodes: " . implode(', ', $barcodes) . "\n";
        }
        if ($ggLabels) {
            $context .= "GG Labels: " . implode(', ', $ggLabels) . "\n";
        }

        return <<<PROMPT
Ты эксперт по анализу товаров для eBay. Проанализируй фотографии.

{$context}

КРИТИЧЕСКИ ВАЖНО:
- ВСЕ текстовые поля ДОЛЖНЫ быть НА РУССКОМ ЯЗЫКЕ
- Если есть баркод - используй его для определения точной модели и рыночной цены

Верни ТОЛЬКО валидный JSON (без markdown):
{
  "title": "Название товара на русском (макс 80 символов)",
  "description": "Подробное описание НА РУССКОМ для eBay листинга",
  "brand": "Бренд",
  "category": "Категория на русском",
  "size": "Размер с бирки",
  "color": "Цвет на русском",
  "condition": "new или used",
  "material": "Материал на русском",
  "price_estimate": 25.00,
  "price_min": 20.00,
  "price_max": 35.00,
  "barcodes": ["найденные баркоды"],
  "internal_ids": ["GG коды с жёлтых/зелёных наклеек"]
}

Цену оценивай на основе бренда, состояния и типичных цен на eBay.
ВСЁ НА РУССКОМ!
PROMPT;
    }

    protected function getDefaultAnalyzePrompt(): string
    {
        return <<<PROMPT
Analyze the product photos and extract information.

Return JSON format:
{
  "title": "product name",
  "brand": "brand or null",
  "description": "detailed description",
  "category": "category",
  "price": number or null,
  "size": "size or null",
  "color": "color or null",
  "condition": "new or used"
}

Return ONLY valid JSON.
PROMPT;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;
        return $this;
    }
}
