<?php

namespace App\Filament\Resources\PhotoBufferResource\Pages;

use App\Filament\Resources\PhotoBufferResource;
use App\Models\PhotoBuffer;
use App\Models\PhotoBatch;
use App\Models\Photo;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ListPhotoBuffers extends Page
{
    protected static string $resource = PhotoBufferResource::class;

    protected static string $view = 'filament.resources.photo-buffer-resource.pages.list-photo-buffers';

    protected static ?string $title = 'Буфер фото';

    public array $selected = [];
    public ?int $lastBatchId = null;

    public function getPhotosProperty()
    {
        return PhotoBuffer::orderByRaw('COALESCE(taken_at, uploaded_at) DESC')
            ->paginate(100);
    }

    public function toggleSelect(int $id): void
    {
        if (in_array($id, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$id]));
        } else {
            $this->selected[] = $id;
        }
    }

    public function selectAll(): void
    {
        $this->selected = $this->photos->pluck('id')->toArray();
    }

    public function deselectAll(): void
    {
        $this->selected = [];
    }

    public function deleteSelected(): void
    {
        $photos = PhotoBuffer::whereIn('id', $this->selected)->get();

        foreach ($photos as $photo) {
            Storage::disk('public')->delete($photo->image);
            $photo->delete();
        }

        Notification::make()
            ->title('Удалено ' . count($this->selected) . ' фото')
            ->success()
            ->send();

        $this->selected = [];
    }

    public function markProcessed(): void
    {
        PhotoBuffer::whereIn('id', $this->selected)->update(['processed' => true]);

        Notification::make()
            ->title('Отмечено ' . count($this->selected) . ' фото')
            ->success()
            ->send();

        $this->selected = [];
    }

    public function createBatch(): void
    {
        if (count($this->selected) === 0) {
            Notification::make()
                ->title('Выберите фото')
                ->warning()
                ->send();
            return;
        }

        $bufferPhotos = PhotoBuffer::whereIn('id', $this->selected)
            ->orderBy('uploaded_at')
            ->get();

        // Create batch
        $batch = PhotoBatch::create([
            'correlation_id' => 'BATCH-' . strtoupper(Str::random(8)),
            'chat_id' => 0,
            'status' => 'pending',
            'uploaded_at' => now(),
        ]);

        // Move photos to batch
        $order = 0;
        foreach ($bufferPhotos as $bufferPhoto) {
            Photo::create([
                'photo_batch_id' => $batch->id,
                'file_id' => $bufferPhoto->file_id,
                'message_id' => $bufferPhoto->message_id,
                'image' => $bufferPhoto->image,
                'is_main' => $order === 0,
                'order' => $order++,
                'uploaded_at' => $bufferPhoto->uploaded_at,
            ]);

            // Delete from buffer
            $bufferPhoto->delete();
        }

        $this->lastBatchId = $batch->id;

        Notification::make()
            ->title('Создана карточка ' . $batch->correlation_id)
            ->body(count($this->selected) . ' фото')
            ->success()
            ->send();

        $this->selected = [];
    }

    public function undoLastBatch(): void
    {
        if (!$this->lastBatchId) {
            Notification::make()
                ->title('Нечего отменять')
                ->warning()
                ->send();
            return;
        }

        $batch = PhotoBatch::find($this->lastBatchId);
        if (!$batch) {
            $this->lastBatchId = null;
            return;
        }

        // Return photos to buffer
        foreach ($batch->photos as $photo) {
            PhotoBuffer::create([
                'file_id' => $photo->file_id,
                'message_id' => $photo->message_id,
                'chat_id' => 0,
                'image' => $photo->image,
                'uploaded_at' => $photo->uploaded_at,
            ]);
            $photo->delete();
        }

        $correlationId = $batch->correlation_id;
        $batch->delete();

        $this->lastBatchId = null;

        Notification::make()
            ->title('Отменена карточка ' . $correlationId)
            ->success()
            ->send();
    }
}


