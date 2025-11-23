<?php

namespace App\Filament\Resources\PhotoBatchResource\Pages;

use App\Filament\Resources\PhotoBatchResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;

class EditPhotoBatch extends EditRecord
{
    protected static string $resource = PhotoBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_model')
                ->label('Generate Model (FASHN)')
                ->icon('heroicon-o-sparkles')
                ->form([
                    Forms\Components\TextInput::make('prompt')
                        ->label('Custom Prompt')
                        ->placeholder('Leave empty for auto-generation based on title'),
                    Forms\Components\Select::make('background')
                        ->options([
                            'urban city street' => 'Urban City',
                            'cozy cafe' => 'Cafe',
                            'modern apartment' => 'Apartment',
                            'park' => 'Park',
                            'studio' => 'Studio',
                        ])
                        ->default('urban city street'),
                ])
                ->action(function (array $data, PhotoBatchResource\Pages\EditPhotoBatch $livewire) {
                    $record = $livewire->getRecord();
                    $photo = $record->photos()->first();

                    if (!$photo) {
                        \Filament\Notifications\Notification::make()->title('No photos found')->danger()->send();
                        return;
                    }

                    // Convert image to base64 for FASHN API
                    $imagePath = \Illuminate\Support\Facades\Storage::disk('public')->path($photo->image);
                    if (!file_exists($imagePath)) {
                        \Filament\Notifications\Notification::make()->title('Image file not found')->danger()->send();
                        return;
                    }

                    $imageData = base64_encode(file_get_contents($imagePath));
                    $mimeType = mime_content_type($imagePath);
                    $base64Image = "data:{$mimeType};base64,{$imageData}";

                    $bg = $data['background'];
                    $prompt = $data['prompt'];

                    if (empty($prompt)) {
                        $title = strtolower($record->title ?? '');
                        $prompt = "realistic e-commerce catalog photo, product exactly as shown, background: {$bg}";
                        if (str_contains($title, 'pants') || str_contains($title, 'брюки')) {
                            $prompt = "realistic full body catalog photo, product exactly as is, background: {$bg}";
                        } elseif (str_contains($title, 'dress') || str_contains($title, 'платье')) {
                            $prompt = "realistic catalog photo, product exactly as shown, natural pose, background: {$bg}";
                        }
                    }

                    \Filament\Notifications\Notification::make()->title('Generating model...')->info()->send();

                    $service = new \App\Services\FashnService();
                    $resultUrl = $service->generateModel($base64Image, $prompt);

                    if ($resultUrl) {
                        $contents = file_get_contents($resultUrl);
                        $filename = 'fashn_' . uniqid() . '.jpg';
                        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $contents);

                        $record->photos()->create([
                            'image' => $filename,
                            'file_id' => 'fashn_' . uniqid(),
                            'message_id' => 0,
                            'chat_id' => $record->chat_id,
                        ]);

                        \Filament\Notifications\Notification::make()->title('Model generated successfully')->success()->send();
                        $livewire->dispatch('$refresh');
                    } else {
                        \Filament\Notifications\Notification::make()->title('Generation failed')->danger()->send();
                    }
                }),
            Actions\DeleteAction::make(),
        ];
    }

    public function setMainPhoto(int $photoId): void
    {
        $photo = \App\Models\Photo::find($photoId);
        if (!$photo) return;
        
        \App\Models\Photo::where('photo_batch_id', $photo->photo_batch_id)->update(['is_main' => false]);
        $photo->update(['is_main' => true]);
        
        \Filament\Notifications\Notification::make()->title('Главное фото обновлено')->success()->send();
        $this->dispatch('$refresh');
    }

    public function rotatePhoto(int $photoId, string $direction): void
    {
        $photo = \App\Models\Photo::find($photoId);
        if (!$photo) return;

        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($photo->image);
        if (!file_exists($path)) {
            \Filament\Notifications\Notification::make()->title('File not found')->danger()->send();
            return;
        }

        try {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->read($path);
            $angle = $direction === 'left' ? 90 : -90;
            $image->rotate($angle);
            $image->save($path);
            \Filament\Notifications\Notification::make()->title('Photo rotated')->success()->send();
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()->title('Error rotating photo')->body($e->getMessage())->danger()->send();
        }
    }

    public function deletePhoto(int $photoId): void
    {
        $photo = \App\Models\Photo::find($photoId);
        if ($photo) {
            // Переносим баркоды на другое фото в батче перед удалением
            $barcodes = $photo->barcodes;
            if ($barcodes->count() > 0) {
                $otherPhoto = \App\Models\Photo::where('photo_batch_id', $photo->photo_batch_id)
                    ->where('id', '!=', $photo->id)
                    ->first();

                if ($otherPhoto) {
                    foreach ($barcodes as $barcode) {
                        $barcode->update(['photo_id' => $otherPhoto->id]);
                    }
                    \Illuminate\Support\Facades\Log::info("Moved {$barcodes->count()} barcodes from photo {$photoId} to photo {$otherPhoto->id}");
                }
            }

            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($photo->image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($photo->image);
            }
            $photo->delete();
            \Filament\Notifications\Notification::make()->title('Photo deleted')->success()->send();
            $this->dispatch('$refresh');
        }
    }

    public function scanBarcodes(int $photoId): void
    {
        \Filament\Notifications\Notification::make()->title('Scanning barcodes...')->info()->send();
        // TODO: Implement
    }

    public function generateModel(int $photoId): void
    {
        $photo = \App\Models\Photo::find($photoId);
        if (!$photo) {
            \Filament\Notifications\Notification::make()->title('Photo not found')->danger()->send();
            return;
        }

        $record = $this->getRecord();
        $imagePath = \Illuminate\Support\Facades\Storage::disk('public')->path($photo->image);

        if (!file_exists($imagePath)) {
            \Filament\Notifications\Notification::make()->title('Image file not found')->danger()->send();
            return;
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);
        $base64Image = "data:{$mimeType};base64,{$imageData}";

        // Берём данные из формы (если не сохранены) или из базы
        $formData = $this->form->getState();
        $title = strtolower($formData['title'] ?? $record->title ?? '');
        $category = strtolower($formData['category'] ?? $record->category ?? '');

        \Illuminate\Support\Facades\Log::info("FASHN form data: " . json_encode(['title' => $title, 'category' => $category]));

        // Определяем пол модели
        $isFemale = str_contains($title, 'женс') || str_contains($title, 'woman') || str_contains($title, 'female')
            || str_contains($category, 'женс') || str_contains($title, 'платье') || str_contains($title, 'dress')
            || str_contains($title, 'блуз') || str_contains($title, 'blouse') || str_contains($title, 'юбк')
            || str_contains($title, 'skirt') || str_contains($title, 'women');
        $isMale = str_contains($title, 'мужс') || str_contains($title, 'man ') || str_contains($title, 'male')
            || str_contains($category, 'мужс') || str_contains($title, 'men ') || str_contains($title, "men's");

        // Рандомные характеристики модели для разнообразия
        $modelVariants = $isFemale
            ? ['young woman', 'female model', 'woman in her 20s', 'woman in her 30s', 'diverse female model']
            : ($isMale
                ? ['young man', 'male model', 'man in his 20s', 'man in his 30s', 'diverse male model']
                : ['model', 'young adult model', 'fashion model']);

        $modelType = $modelVariants[array_rand($modelVariants)];

        // Рандомные фоны
        $backgrounds = [
            'urban city street',
            'modern studio',
            'neutral gray background',
            'minimalist white space',
            'contemporary interior'
        ];
        $background = $backgrounds[array_rand($backgrounds)];

        \Illuminate\Support\Facades\Log::info("FASHN: Title='{$title}', Female={$isFemale}, Male={$isMale}, Model={$modelType}");

        // Добавляем название товара в промпт для контекста
        $productTitle = $formData['title'] ?? $record->title ?? '';
        $productInfo = $productTitle ? "product: {$productTitle}, " : "";

        \Illuminate\Support\Facades\Log::info("FASHN prompt will use: productInfo='{$productInfo}', modelType='{$modelType}'");

        // Базовый промпт
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

        \Filament\Notifications\Notification::make()->title('Generating model... (может занять до 2 минут)')->info()->send();

        set_time_limit(180);

        $service = new \App\Services\FashnService();
        $resultUrl = $service->generateModel($base64Image, $prompt);

        if ($resultUrl) {
            \Illuminate\Support\Facades\Log::info("FASHN: Downloading result from: {$resultUrl}");
            $contents = file_get_contents($resultUrl);
            $filename = 'fashn_' . uniqid() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $contents);
            \Illuminate\Support\Facades\Log::info("FASHN: Saved to {$filename}");

            $record->photos()->create([
                'image' => $filename,
                'file_id' => 'fashn_' . uniqid(),
                'message_id' => 0,
                'chat_id' => $record->chat_id,
            ]);

            \Filament\Notifications\Notification::make()->title('Model generated!')->success()->send();
            $this->dispatch('$refresh');
        } else {
            \Filament\Notifications\Notification::make()->title('Generation failed')->danger()->send();
        }
    }

    public function magicEnhance(int $photoId): void
    {
        $photo = \App\Models\Photo::find($photoId);
        if (!$photo) {
            \Filament\Notifications\Notification::make()->title('Photo not found')->danger()->send();
            return;
        }

        $record = $this->getRecord();
        $imagePath = \Illuminate\Support\Facades\Storage::disk('public')->path($photo->image);

        if (!file_exists($imagePath)) {
            \Filament\Notifications\Notification::make()->title('Image file not found')->danger()->send();
            return;
        }

        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);
        $base64Image = "data:{$mimeType};base64,{$imageData}";

        \Filament\Notifications\Notification::make()->title('Magic: меняем фон...')->info()->send();

        set_time_limit(180);

        // Выбираем случайный фон как в Django
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

        // Оригинальный промпт из Django
        $fullPrompt = "product exactly as photographed, preserve all original condition including wear marks and wrinkles, straighten product alignment, remove only price tags and hangers, {$bgPrompt}, realistic product catalog photography";

        $service = new \App\Services\FashnService();
        $resultUrl = $service->changeBackground($base64Image, $fullPrompt);

        if ($resultUrl) {
            \Illuminate\Support\Facades\Log::info("Magic: Downloading result from: {$resultUrl}");
            $contents = file_get_contents($resultUrl);
            $filename = 'magic_' . uniqid() . '.jpg';
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $contents);

            // Create new photo, keep original
            $record->photos()->create([
                'image' => $filename,
                'file_id' => 'magic_' . uniqid(),
                'message_id' => 0,
                'chat_id' => $record->chat_id,
            ]);

            \Filament\Notifications\Notification::make()->title('Magic: новое фото создано!')->success()->send();
            $this->dispatch('$refresh');
        } else {
            \Filament\Notifications\Notification::make()->title('Magic failed')->danger()->send();
        }
    }

    public function autoAdjust(int $photoId): void
    {
        $photo = \App\Models\Photo::find($photoId);
        if (!$photo) return;

        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($photo->image);
        if (!file_exists($path)) return;

        try {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->read($path);
            $image->brightness(10);
            $image->contrast(10);
            $image->save($path);
            \Filament\Notifications\Notification::make()->title('Photo auto-adjusted')->success()->send();
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()->title('Error')->danger()->send();
        }
    }

    public function whiteBalance(int $photoId): void
    {
        $photo = \App\Models\Photo::find($photoId);
        if (!$photo) return;

        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($photo->image);
        if (!file_exists($path)) return;

        try {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->read($path);
            $image->colorize(0, 0, 5);
            $image->save($path);
            \Filament\Notifications\Notification::make()->title('White balance adjusted')->success()->send();
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()->title('Error')->danger()->send();
        }
    }
}
