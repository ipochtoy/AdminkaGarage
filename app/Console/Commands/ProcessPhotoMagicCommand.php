<?php

namespace App\Console\Commands;

use App\Models\Photo;
use App\Models\PhotoBatch;
use App\Services\FashnService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoMagicCommand extends Command
{
    protected $signature = 'process:photo-magic {batch_id} {photo_id}';

    protected $description = 'Process photo with Magic background change';

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

        $this->info("Processing Magic for photo {$photoId} in batch {$batchId}...");

        $imagePath = Storage::disk('public')->path($photo->image);

        if (!file_exists($imagePath)) {
            $this->error('Image file not found');
            return 1;
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);
        $base64Image = "data:{$mimeType};base64,{$imageData}";

        // Random background prompts
        $bgOptions = [
            "clean smooth gradient background light gray to white, no details, professional product photo",
            "soft beige gradient background, minimal lighting, neutral studio setup, no objects",
            "pure white seamless background, professional studio lighting, catalog quality",
            "light cream gradient, soft shadows, minimalist product photography, no distractions",
            "subtle gray gradient background, diffused lighting, clean professional look",
            "warm light beige solid background, even lighting, commercial product photo",
            "cool light blue gray gradient, soft studio lighting, neutral backdrop",
            "off-white smooth background, professional catalog style, no details or objects"
        ];
        $bgPrompt = $bgOptions[array_rand($bgOptions)];

        $fullPrompt = "product exactly as photographed, preserve all original condition including wear marks and wrinkles, straighten product alignment, remove only price tags and hangers, {$bgPrompt}, realistic product catalog photography";

        Log::info("Magic: Starting background change for photo {$photoId}");

        $service = new FashnService();
        $resultUrl = $service->changeBackground($base64Image, $fullPrompt);

        if ($resultUrl) {
            Log::info("Magic: Downloading result from: {$resultUrl}");
            $contents = file_get_contents($resultUrl);
            $filename = 'magic_' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $contents);

            // Create new photo in the same batch
            $batch->photos()->create([
                'image' => $filename,
                'file_id' => 'magic_' . uniqid(),
                'message_id' => 0,
                'chat_id' => $batch->chat_id,
                'order' => $batch->photos()->max('order') + 1,
            ]);

            Log::info("Magic: Photo created successfully for batch {$batchId}");
            $this->info("Magic processed successfully!");
            return 0;
        } else {
            Log::error("Magic: Failed for photo {$photoId}");
            $this->error('Magic processing failed');
            return 1;
        }
    }
}
