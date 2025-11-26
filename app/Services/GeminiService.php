<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiService
{
    protected string $apiKey;
    protected string $model = 'gemini-2.5-flash-preview-09-2025'; // Fast model

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY', '');
    }

    public function generateProductDescription(array $photoPaths, ?string $customPrompt = null, ?string $model = null): ?array
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

            $modelToUse = $model ?? $this->model;
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelToUse}:generateContent?key={$this->apiKey}";

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

ВАЖНО: Если на фотографиях несколько разных товаров (например, шапка и толстовка), это ОДИН ЛОТ, который продаётся вместе. Создай ОДНО описание для всего лота, указав все товары в названии и описании.

ЗАДАЧИ:
1.  **Найти лейблы:** Ищи жёлтую или зелёную наклейку с внутренним кодом (например "GG1418", "Q2630609"). Верни все найденные уникальные коды.
2.  **Найти баркоды:** Ищи любые видимые баркоды (UPC, EAN) на бирках. Верни числа если читаемы.
3.  **Детали товара:** Определи Бренд (если несколько брендов - укажи все через запятую), Размер, Цвет, Материал и Категорию.
4.  **Описание:** Сгенерируй привлекательное Название и подробное Описание НА РУССКОМ для ВСЕГО ЛОТА.
5.  **ДЕКЛАРАЦИЯ для Pochtoy.com:** Заполни данные для таможенной декларации:
    - declaration_en: краткое описание на английском (3-5 слов)
    - declaration_ru: краткое описание на русском (3-5 слов)
    - declaration_hs_code: таможенный код HS (например "6110.20" для свитеров, "6404.11" для обуви)
    - declaration_sku: артикул товара если виден на бирке (ASIN, SKU, MPN)
    - declaration_has_battery: true если товар содержит литиевую батарею (электроника, часы)

ПРАВИЛА ДЛЯ ДЕКЛАРАЦИИ:
- Сумки, рюкзаки, кошельки → EN: "Hand luggage accessories", RU: "Аксессуар для ручной клади"
- Виниловые пластинки/музыка → EN: "Collectible music media", RU: "Музыкальные носители"
- Корма для животных → EN: "Food products", RU: "Продукты питания" (без указания что для животных)
- Витамины для животных → EN: "Vitamins supplements", RU: "Витамины добавки" (не писать "для животных")
- Косметика декоративная → EN: "Personal hygiene product", RU: "Средство личной гигиены"
- Игрушки любого типа → EN: "Board game", RU: "Настольная игра"
- Для лотов указывай: RU: "лот женской одежды 5 шт.", EN: "women clothing lot 5"

ТИПИЧНЫЕ HS КОДЫ:
- Футболки, майки: 6109.10
- Свитера, толстовки: 6110.20
- Куртки: 6201.93
- Джинсы, брюки: 6203.42
- Платья: 6204.42
- Обувь спортивная: 6404.11
- Обувь кожаная: 6403.99
- Сумки: 4202.22
- Электроника: 8471.30
- Часы: 9102.11

ВЕРНИ ТОЛЬКО JSON формат (ОДИН объект, НЕ массив):
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
  "price_max": 35.00,
  "declaration_en": "краткое описание EN 3-5 слов",
  "declaration_ru": "краткое описание RU 3-5 слов",
  "declaration_hs_code": "6110.20",
  "declaration_sku": "артикул если найден или null",
  "declaration_has_battery": false
}

ВАЖНО: price_estimate ОБЯЗАТЕЛЬНО должен быть больше 0! Оцени рыночную стоимость товара на eBay исходя из бренда, состояния и типа товара. Типичный диапазон для одежды $15-50.
PROMPT;
    }

    public function generateEbayListing(array $photoPaths, ?array $productData = null, ?string $model = null): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('GEMINI_API_KEY not set in config or env');
            return null;
        }

        try {
            Log::info('GeminiService: Starting eBay listing generation for ' . count($photoPaths) . ' photos.');

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

            // Construct prompt with optional product data context
            $prompt = $this->getEbayPrompt($productData);

            $contents = [
                [
                    'parts' => array_merge(
                        [['text' => $prompt]],
                        $imageParts
                    ),
                ],
            ];

            $modelToUse = $model ?? $this->model;
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelToUse}:generateContent?key={$this->apiKey}";

            Log::info("GeminiService: Sending eBay request to $url");

            $response = Http::timeout(120)->post($url, [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.7, // Higher temperature for more creative descriptions
                    'topK' => 32,
                    'topP' => 1,
                    'maxOutputTokens' => 8192,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->status() . ' - ' . $response->body());
                return null;
            }

            $data = $response->json();
            Log::info('GeminiService: Raw eBay Response: ' . json_encode($data));

            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                Log::error('GeminiService: No text returned in candidate parts.');
                return null;
            }

            // Parse the structured response
            return $this->parseResponse($text);

        } catch (\Exception $e) {
            Log::error('GeminiService eBay Exception: ' . $e->getMessage() . "\nStack Trace: " . $e->getTraceAsString());
            return null;
        }
    }

    protected function getEbayPrompt(?array $productData = null): string
    {
        $context = '';
        if ($productData) {
            $context = "\nContext from existing listing:\n";
            if (isset($productData['title'])) $context .= "- Current title: {$productData['title']}\n";
            if (isset($productData['brand'])) $context .= "- Brand: {$productData['brand']}\n";
            if (isset($productData['category'])) $context .= "- Category: {$productData['category']}\n";
            if (isset($productData['condition'])) $context .= "- Condition: {$productData['condition']}\n";
        }

        return <<<PROMPT
Analyze these product photos to create a professional eBay/Shopify listing in ENGLISH. You are an expert e-commerce copywriter specializing in clothing and accessories.
{$context}
TASKS:
1. **Title**: Create a compelling, SEO-optimized title (max 80 characters) following eBay best practices. Include: Brand + Product Type + Key Features + Size/Color. Example: "Vintage Nike Air Max 90 Running Shoes Black Size 10 Men's"

2. **Description**: Write a detailed, SEO-optimized product description in professional English that includes:
   - Eye-catching opening line
   - Detailed product features and condition
   - Measurements (if visible)
   - Material composition
   - Style and fit details
   - Selling points and benefits
   - Use bullet points for readability
   - Professional yet engaging tone

3. **SEO Keywords**: Identify 5-10 relevant keywords/tags for search optimization

4. **Category**: Suggest appropriate eBay category

5. **Condition**: Determine condition (New with tags, New without tags, Pre-owned - Excellent, Pre-owned - Good, Pre-owned - Fair)

RETURN ONLY JSON format:
{
  "title": "SEO-optimized title (max 80 chars)",
  "description": "Detailed SEO-optimized description in English with formatting",
  "brand": "Brand name",
  "category": "eBay category",
  "condition": "Condition",
  "size": "Size from tag",
  "color": "Color description",
  "tags": ["keyword1", "keyword2", "keyword3"],
  "price_usd": 25.00
}

IMPORTANT: Make the description compelling and SEO-friendly. Use keywords naturally. Price should reflect market value on eBay (typical range $15-100 for clothing).
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

        // If Gemini returned an array of objects, take the first one
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            Log::info('GeminiService: Response is an array, taking first element');
            $data = $data[0];
        }

        return $data;
    }
}
