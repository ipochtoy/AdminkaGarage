<?php

namespace App\Filament\Pages;

use App\Models\PhotoBuffer;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class BulkUpload extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Массовая загрузка';

    protected static ?string $title = 'Массовая загрузка фото';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.bulk-upload';

    public $photos = [];
    public $uploadedCount = 0;

    public function updatedPhotos()
    {
        $this->validate([
            'photos.*' => 'image|max:20480', // 20MB max per file
        ]);
    }

    public function upload()
    {
        if (empty($this->photos)) {
            Notification::make()
                ->title('Нет фото для загрузки')
                ->warning()
                ->send();
            return;
        }

        $count = 0;
        foreach ($this->photos as $photo) {
            $path = $photo->store('buffer/' . date('Y/m/d'), 'public');

            PhotoBuffer::create([
                'file_id' => uniqid('upload_'),
                'message_id' => 0,
                'chat_id' => 0,
                'image' => $path,
                'uploaded_at' => now(),
            ]);

            $count++;
        }

        $this->uploadedCount = $count;
        $this->photos = [];

        Notification::make()
            ->title("Загружено {$count} фото")
            ->success()
            ->send();
    }
}
