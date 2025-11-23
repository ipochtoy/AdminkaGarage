<?php

namespace Database\Seeders;

use App\Models\Prompt;
use Illuminate\Database\Seeder;

class PromptsSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = [
            [
                'key' => 'generate_summary',
                'name' => 'Генерация описания товара',
                'prompt' => 'Analyze the product images and provide a detailed description for selling on eBay/marketplace.

Barcodes found: {barcodes}
GG Labels: {gg_labels}

Please provide:
1. Product title (brand + model + key features)
2. Detailed description
3. Suggested category
4. Condition assessment
5. Key specifications (size, color, material if visible)
6. Suggested price range based on product type

Format the response as JSON with keys: title, description, category, condition, brand, size, color, price_min, price_max',
                'model' => 'gpt-4o',
                'max_tokens' => 2000,
                'temperature' => 0.3,
            ],
            [
                'key' => 'analyze_barcode',
                'name' => 'Анализ штрихкода',
                'prompt' => 'Look at this product barcode/label image and extract all text information including:
- UPC/EAN barcode numbers
- SKU codes
- Model numbers
- Size information
- Any other identifying text

Return as JSON with keys: barcode, sku, model, size, other_text',
                'model' => 'gpt-4o',
                'max_tokens' => 500,
                'temperature' => 0.1,
            ],
        ];

        foreach ($prompts as $prompt) {
            Prompt::updateOrCreate(
                ['key' => $prompt['key']],
                $prompt
            );
        }
    }
}
