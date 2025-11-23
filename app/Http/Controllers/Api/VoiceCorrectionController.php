<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhotoBatch;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoiceCorrectionController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'photo_batch_id' => 'required|integer',
            'corrections' => 'required|string',
        ]);

        $batch = PhotoBatch::find($request->photo_batch_id);
        if (!$batch) {
            return response()->json(['success' => false, 'error' => 'Batch not found']);
        }

        try {
            $gemini = new GeminiService();

            $currentData = [
                'title' => $batch->title,
                'description' => $batch->description,
                'price' => $batch->price,
                'brand' => $batch->brand,
                'category' => $batch->category,
                'size' => $batch->size,
                'color' => $batch->color,
                'condition' => $batch->condition,
                'sku' => $batch->sku,
                'quantity' => $batch->quantity,
            ];

            $prompt = "Ты помощник для редактирования карточки товара.

Текущие данные товара:
" . json_encode($currentData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "

Пользователь надиктовал следующие правки голосом:
\"{$request->corrections}\"

Проанализируй правки и верни JSON с обновленными полями. Включай только те поля, которые нужно изменить.
Возможные поля: title, description, price, brand, category, size, color, condition (new/used), sku, quantity.

Примеры правок:
- \"цена должна быть 50 долларов\" -> {\"price\": \"50\"}
- \"это платье, а не рубашка\" -> {\"category\": \"платье\"}
- \"бренд найк\" -> {\"brand\": \"Nike\"}
- \"размер XL\" -> {\"size\": \"XL\"}

Верни ТОЛЬКО валидный JSON без пояснений.";

            $response = $gemini->generateContent($prompt);

            // Parse JSON from response
            $jsonMatch = preg_match('/\{[^{}]*\}/', $response, $matches);
            if (!$jsonMatch) {
                Log::error('Voice correction: No JSON in response', ['response' => $response]);
                return response()->json(['success' => false, 'error' => 'AI не вернул JSON']);
            }

            $updates = json_decode($matches[0], true);
            if (!$updates) {
                Log::error('Voice correction: Invalid JSON', ['json' => $matches[0]]);
                return response()->json(['success' => false, 'error' => 'Невалидный JSON от AI']);
            }

            // Update batch with new values
            $batch->update($updates);

            // Also update ai_summary to reflect changes
            if ($batch->ai_summary) {
                $summary = json_decode($batch->ai_summary, true) ?: [];
                $summary = array_merge($summary, $updates);
                $batch->update(['ai_summary' => json_encode($summary, JSON_UNESCAPED_UNICODE)]);
            }

            Log::info('Voice correction applied', ['batch_id' => $batch->id, 'updates' => $updates]);

            return response()->json([
                'success' => true,
                'updates' => $updates,
            ]);

        } catch (\Exception $e) {
            Log::error('Voice correction error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
