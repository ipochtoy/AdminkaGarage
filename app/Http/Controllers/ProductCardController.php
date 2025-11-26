<?php

namespace App\Http\Controllers;

use App\Models\PhotoBatch;
use App\Models\Photo;
use App\Services\AIService;
use App\Services\EbayService;
use App\Services\PochtoyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductCardController extends Controller
{
    public function show(PhotoBatch $photoBatch)
    {
        $photoBatch->load('photos.barcodes');

        return Inertia::render('ProductCard', [
            'card' => $photoBatch,
            'photos' => $photoBatch->photos()->orderBy('order')->get(),
            'barcodes' => $photoBatch->getAllBarcodes(),
            'ggLabels' => $photoBatch->getGgLabels(),
        ]);
    }

    public function save(Request $request, PhotoBatch $photoBatch)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'condition' => 'nullable|in:new,used,refurbished',
            'category' => 'nullable|string|max:200',
            'brand' => 'nullable|string|max:200',
            'size' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:200',
            'quantity' => 'nullable|integer|min:1',
            'ai_summary' => 'nullable|string',
            'declaration_en' => 'nullable|string|max:255',
            'declaration_ru' => 'nullable|string|max:255',
        ]);

        $photoBatch->update($validated);

        return response()->json(['success' => true]);
    }

    public function generateSummary(Request $request, PhotoBatch $photoBatch)
    {
        $provider = $request->input('provider', config('services.ai.provider'));

        $photoUrls = $photoBatch->photos()
            ->orderBy('order')
            ->limit(5)
            ->get()
            ->map(fn($p) => url('storage/' . $p->image))
            ->toArray();

        $barcodes = collect($photoBatch->getAllBarcodes())->pluck('data')->toArray();
        $ggLabels = $photoBatch->getGgLabels();

        $aiService = app(AIService::class)->setProvider($provider);
        $summary = $aiService->generateSummary($photoUrls, $barcodes, $ggLabels);

        if ($summary) {
            $updateData = ['ai_summary' => $summary];
            $declarationEn = null;
            $declarationRu = null;

            // Try to parse JSON to extract declaration fields
            $parsed = $this->parseJsonFromSummary($summary);
            if ($parsed) {
                if (isset($parsed['declaration_en'])) {
                    $declarationEn = $parsed['declaration_en'];
                    $updateData['declaration_en'] = $declarationEn;
                }
                if (isset($parsed['declaration_ru'])) {
                    $declarationRu = $parsed['declaration_ru'];
                    $updateData['declaration_ru'] = $declarationRu;
                }
            }

            $photoBatch->update($updateData);

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'declaration_en' => $declarationEn,
                'declaration_ru' => $declarationRu,
            ]);
        }

        return response()->json(['success' => false, 'error' => 'Failed to generate summary'], 500);
    }

    protected function parseJsonFromSummary(string $summary): ?array
    {
        $text = $summary;

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

    public function scanBarcodes(PhotoBatch $photoBatch)
    {
        $photoUrls = $photoBatch->photos()
            ->orderBy('order')
            ->get()
            ->map(fn($p) => url('storage/' . $p->image))
            ->toArray();

        if (empty($photoUrls)) {
            return response()->json(['success' => false, 'error' => 'No photos'], 400);
        }

        $aiService = app(AIService::class);
        $result = $aiService->scanBarcodes($photoUrls);

        if (!$result) {
            return response()->json(['success' => false, 'error' => 'Scan failed'], 500);
        }

        $firstPhoto = $photoBatch->photos()->orderBy('order')->first();
        $added = ['barcodes' => [], 'gg_labels' => []];

        // Save regular barcodes
        if (!empty($result['barcodes'])) {
            foreach ($result['barcodes'] as $barcode) {
                $existing = $firstPhoto->barcodes()
                    ->where('data', $barcode['data'])
                    ->first();

                if (!$existing) {
                    $source = ($barcode['is_gg_label'] ?? false) ? 'gg-label' : 'gemini';
                    $firstPhoto->barcodes()->create([
                        'data' => $barcode['data'],
                        'symbology' => $barcode['symbology'] ?? 'UNKNOWN',
                        'source' => $source,
                    ]);
                    $added['barcodes'][] = $barcode['data'];
                }
            }
        }

        // Save GG labels
        if (!empty($result['gg_labels'])) {
            foreach ($result['gg_labels'] as $label) {
                $existing = $firstPhoto->barcodes()
                    ->where('data', $label['data'])
                    ->first();

                if (!$existing) {
                    $firstPhoto->barcodes()->create([
                        'data' => $label['data'],
                        'symbology' => $label['symbology'] ?? 'CODE39',
                        'source' => 'gg-label',
                    ]);
                    $added['gg_labels'][] = $label['data'];
                }
            }
        }

        return response()->json([
            'success' => true,
            'added' => $added,
            'total' => [
                'barcodes' => count($added['barcodes']),
                'gg_labels' => count($added['gg_labels']),
            ]
        ]);
    }

    public function searchEbay(Request $request, PhotoBatch $photoBatch)
    {
        $ebayService = app(EbayService::class);

        $result = $ebayService->searchProducts(
            brand: $photoBatch->brand,
            model: $photoBatch->title,
            barcode: collect($photoBatch->getAllBarcodes())->first()?->data,
            title: $photoBatch->title,
        );

        if ($result) {
            return response()->json(['success' => true, 'data' => $result]);
        }

        return response()->json(['success' => false, 'error' => 'No results found'], 404);
    }

    public function sendToPochtoy(PhotoBatch $photoBatch)
    {
        $pochtoyService = app(PochtoyService::class);
        $result = $pochtoyService->sendCard($photoBatch);

        if ($result['success']) {
            $photoBatch->update(['status' => 'processed', 'processed_at' => now()]);
        }

        return response()->json($result);
    }

    public function rotatePhoto(Photo $photo)
    {
        $path = Storage::disk('public')->path($photo->image);

        if (!file_exists($path)) {
            return response()->json(['success' => false, 'error' => 'File not found'], 404);
        }

        // Simple GD rotation
        $imageInfo = getimagesize($path);
        $mime = $imageInfo['mime'];

        $image = match($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            default => null,
        };

        if (!$image) {
            return response()->json(['success' => false, 'error' => 'Cannot read image'], 500);
        }

        $rotated = imagerotate($image, 90, 0);

        match($mime) {
            'image/jpeg' => imagejpeg($rotated, $path, 90),
            'image/png' => imagepng($rotated, $path),
            default => null,
        };

        imagedestroy($image);
        imagedestroy($rotated);

        return response()->json(['success' => true]);
    }

    public function setMainPhoto(Photo $photo)
    {
        Photo::where('photo_batch_id', $photo->photo_batch_id)
            ->update(['is_main' => false]);

        $photo->update(['is_main' => true]);

        return response()->json(['success' => true]);
    }

    public function deletePhoto(Photo $photo)
    {
        Storage::disk('public')->delete($photo->image);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    public function reorderPhotos(Request $request, PhotoBatch $photoBatch)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:photos,id',
        ]);

        foreach ($request->order as $index => $photoId) {
            Photo::where('id', $photoId)
                ->where('photo_batch_id', $photoBatch->id)
                ->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function addBarcode(Request $request, PhotoBatch $photoBatch)
    {
        $request->validate([
            'data' => 'required|string|max:100',
            'symbology' => 'nullable|string|max:50',
        ]);

        $firstPhoto = $photoBatch->photos()->orderBy('order')->first();
        if (!$firstPhoto) {
            return response()->json(['success' => false, 'error' => 'No photos'], 400);
        }

        $firstPhoto->barcodes()->create([
            'data' => $request->data,
            'symbology' => $request->symbology ?? 'MANUAL',
            'source' => 'manual',
        ]);

        return response()->json(['success' => true]);
    }

    public function deleteCard(PhotoBatch $photoBatch)
    {
        $trackings = array_merge(
            $photoBatch->getGgLabels(),
            collect($photoBatch->getAllBarcodes())->pluck('data')->toArray()
        );

        if (!empty($trackings)) {
            $pochtoyService = app(PochtoyService::class);
            $pochtoyService->deleteCard($trackings);
        }

        foreach ($photoBatch->photos as $photo) {
            Storage::disk('public')->delete($photo->image);
        }

        $photoBatch->delete();

        return response()->json(['success' => true]);
    }
}
