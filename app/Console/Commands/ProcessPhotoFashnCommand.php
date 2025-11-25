<?php

namespace App\Console\Commands;

use App\Models\Photo;
use App\Models\PhotoBatch;
use App\Services\FashnService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoFashnCommand extends Command
{
    protected $signature = 'process:photo-fashn {batch_id} {photo_id}';

    protected $description = 'Process photo with FASHN model generation';

    public function handle()
    {
        $batchId = $this->argument('batch_id');
        $photoId = $this->argument('photo_id');

        $batch = PhotoBatch::find($batchId);
        $photo = Photo::find($photoId);

        if (!$batch || !$photo) {
            $this->error("Batch {$batchId} or Photo {$photoId} not found");
            return 1;
        }

        $this->info("Processing FASHN for photo {$photoId} in batch {$batchId}...");

        $imagePath = Storage::disk('public')->path($photo->image);

        if (!file_exists($imagePath)) {
            $this->error('Image file not found');
            return 1;
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);
        $base64Image = "data:{$mimeType};base64,{$imageData}";

        $title = strtolower($batch->title ?? '');
        $category = strtolower($batch->category ?? '');

        // Determine model gender
        $isFemale = str_contains($title, 'женс') || str_contains($title, 'woman') || str_contains($title, 'female')
            || str_contains($category, 'женс') || str_contains($title, 'платье') || str_contains($title, 'dress')
            || str_contains($title, 'блуз') || str_contains($title, 'blouse') || str_contains($title, 'юбк')
            || str_contains($title, 'skirt') || str_contains($title, 'women');
        $isMale = str_contains($title, 'мужс') || str_contains($title, 'man ') || str_contains($title, 'male')
            || str_contains($category, 'мужс') || str_contains($title, 'men ') || str_contains($title, "men's");

        // Random model variants
        $modelVariants = $isFemale
            ? ['young woman', 'female model', 'woman in her 20s', 'woman in her 30s', 'diverse female model']
            : ($isMale
                ? ['young man', 'male model', 'man in his 20s', 'man in his 30s', 'diverse male model']
                : ['model', 'young adult model', 'fashion model']);

        $modelType = $modelVariants[array_rand($modelVariants)];

        // Random backgrounds
        $backgrounds = [
            'urban city street',
            'modern studio',
            'neutral gray background',
            'minimalist white space',
            'contemporary interior'
        ];
        $background = $backgrounds[array_rand($backgrounds)];

        $productTitle = $batch->title ?? '';
        $productInfo = $productTitle ? "product: {$productTitle}, " : "";

        // Build prompt
        $basePrompt = "realistic e-commerce catalog photo, {$productInfo}{$modelType} wearing the product, product exactly as shown, remove price tags and labels, clean product, background: {$background}";

        if (str_contains($title, 'pants') || str_contains($title, 'брюки') || str_contains($title, 'джинс') || str_contains($title, 'jeans')) {
            $prompt = "realistic full body catalog photo, {$productInfo}{$modelType} wearing the product, product exactly as is, remove price tags and labels, clean product, background: {$background}";
        } elseif (str_contains($title, 'dress') || str_contains($title, 'платье')) {
            $prompt = "realistic catalog photo, {$productInfo}{$modelType} wearing the product, natural pose, remove price tags and labels, clean product, background: {$background}";
        } elseif (str_contains($title, 'shirt') || str_contains($title, 'рубаш') || str_contains($title, 'блуз')) {
            $prompt = "realistic catalog photo, {$productInfo}{$modelType} wearing the shirt, upper body shot, remove price tags and labels, clean product, background: {$background}";
        } else {
            $prompt = $basePrompt;
        }

        Log::info("FASHN: Starting model generation for photo {$photoId} with prompt: {$prompt}");

        $service = new FashnService();
        $resultUrl = $service->generateModel($base64Image, $prompt);

        if ($resultUrl) {
            Log::info("FASHN: Downloading result from: {$resultUrl}");
            $contents = file_get_contents($resultUrl);
            $filename = 'fashn_' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $contents);

            // Create new photo in the same batch
            $batch->photos()->create([
                'image' => $filename,
                'file_id' => 'fashn_' . uniqid(),
                'message_id' => 0,
                'chat_id' => $batch->chat_id,
                'order' => $batch->photos()->max('order') + 1,
            ]);

            Log::info("FASHN: Photo created successfully for batch {$batchId}");
            $this->info("FASHN processed successfully!");
            return 0;
        } else {
            Log::error("FASHN: Failed for photo {$photoId}");
            $this->error('FASHN processing failed');
            return 1;
        }
    }
}
