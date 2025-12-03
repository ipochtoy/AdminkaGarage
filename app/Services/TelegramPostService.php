<?php

namespace App\Services;

use App\Models\PhotoBatch;
use App\Models\TelegramChannel;
use App\Models\TelegramPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TelegramPostService
{
    /**
     * Опубликовать товар во все активные каналы
     */
    public function publishProduct(PhotoBatch $batch): array
    {
        $channels = TelegramChannel::active()->ordered()->get();
        $results = [];

        foreach ($channels as $channel) {
            $post = $this->createPostFromBatch($batch, $channel);
            $results[$channel->name] = $this->sendPost($post);
        }

        return $results;
    }

    /**
     * Создать пост из товара для конкретного канала
     */
    public function createPostFromBatch(PhotoBatch $batch, TelegramChannel $channel): TelegramPost
    {
        // Собираем публичные фото
        $images = $batch->photos()
            ->where('is_public', true)
            ->orderBy('order')
            ->pluck('image')
            ->toArray();

        return TelegramPost::create([
            'telegram_channel_id' => $channel->id,
            'photo_batch_id' => $batch->id,
            'title' => $batch->ebay_title ?? $batch->title ?? 'Товар',
            'description' => $this->formatDescription($batch),
            'price' => $batch->ebay_price ?? $batch->price,
            'currency' => 'USD',
            'buy_link' => $channel->generateBuyLink($batch),
            'images' => $images,
            'status' => 'draft',
        ]);
    }

    /**
     * Отправить пост в Telegram
     */
    public function sendPost(TelegramPost $post): TelegramPost
    {
        $channel = $post->channel;
        $images = $post->images ?? [];

        if (empty($images)) {
            $post->update([
                'status' => 'failed',
                'error_message' => 'Нет фото для публикации',
            ]);
            return $post->fresh();
        }

        try {
            if (count($images) === 1) {
                $result = $this->sendSinglePhoto($channel, $images[0], $post);
            } else {
                $result = $this->sendMediaGroup($channel, $images, $post);
            }

            $post->update([
                'status' => 'sent',
                'sent_at' => now(),
                'telegram_message_id' => $result['message_id'] ?? $result['result'][0]['message_id'] ?? null,
                'error_message' => null,
            ]);

            Log::info("Telegram post sent", [
                'post_id' => $post->id,
                'channel' => $channel->name,
                'message_id' => $post->telegram_message_id,
            ]);

        } catch (\Exception $e) {
            $post->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Telegram post failed", [
                'post_id' => $post->id,
                'channel' => $channel->name,
                'error' => $e->getMessage(),
            ]);
        }

        return $post->fresh();
    }

    /**
     * Отправить одно фото
     */
    protected function sendSinglePhoto(TelegramChannel $channel, string $imagePath, TelegramPost $post): array
    {
        $imageData = Storage::disk('public')->get($imagePath);

        if (!$imageData) {
            throw new \Exception("Фото не найдено: {$imagePath}");
        }

        $response = Http::attach('photo', $imageData, 'photo.jpg')
            ->post("https://api.telegram.org/bot{$channel->bot_token}/sendPhoto", [
                'chat_id' => $channel->chat_id,
                'caption' => $this->buildCaption($post),
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($this->buildKeyboard($post)),
            ]);

        if (!$response->successful()) {
            throw new \Exception("Telegram API error: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Отправить группу фото
     */
    protected function sendMediaGroup(TelegramChannel $channel, array $images, TelegramPost $post): array
    {
        $media = [];

        // Готовим media array
        foreach ($images as $idx => $imagePath) {
            $media[] = [
                'type' => 'photo',
                'media' => "attach://photo_{$idx}",
                'caption' => $idx === 0 ? $this->buildCaption($post) : null,
                'parse_mode' => $idx === 0 ? 'HTML' : null,
            ];
        }

        // Создаём multipart запрос
        $multipart = [
            [
                'name' => 'chat_id',
                'contents' => $channel->chat_id,
            ],
            [
                'name' => 'media',
                'contents' => json_encode($media),
            ],
        ];

        // Добавляем фото
        foreach ($images as $idx => $imagePath) {
            $imageData = Storage::disk('public')->get($imagePath);
            if (!$imageData) {
                throw new \Exception("Фото не найдено: {$imagePath}");
            }

            $multipart[] = [
                'name' => "photo_{$idx}",
                'contents' => $imageData,
                'filename' => "photo_{$idx}.jpg",
            ];
        }

        $response = Http::asMultipart()
            ->post("https://api.telegram.org/bot{$channel->bot_token}/sendMediaGroup", $multipart);

        if (!$response->successful()) {
            throw new \Exception("Telegram API error: " . $response->body());
        }

        $result = $response->json();

        // MediaGroup не поддерживает inline keyboard, отправляем отдельным сообщением
        $this->sendBuyButton($channel, $post);

        return $result;
    }

    /**
     * Отправить кнопку "Купить" отдельным сообщением
     */
    protected function sendBuyButton(TelegramChannel $channel, TelegramPost $post): void
    {
        Http::post("https://api.telegram.org/bot{$channel->bot_token}/sendMessage", [
            'chat_id' => $channel->chat_id,
            'text' => "💰 <b>{$post->formatted_price}</b>",
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($this->buildKeyboard($post)),
        ]);
    }

    /**
     * Построить подпись к фото
     */
    protected function buildCaption(TelegramPost $post): string
    {
        $parts = [];

        $parts[] = "🛒 <b>{$post->title}</b>";

        if ($post->description) {
            $parts[] = "\n" . $post->description;
        }

        if ($post->price) {
            $parts[] = "\n💰 <b>{$post->formatted_price}</b>";
        }

        if ($post->is_sold) {
            $parts[] = "\n\n❌ <b>ПРОДАНО</b>";
        }

        return implode('', $parts);
    }

    /**
     * Построить inline keyboard
     */
    protected function buildKeyboard(TelegramPost $post): array
    {
        $buttons = [];

        if (!$post->is_sold && $post->buy_link) {
            $buttons[] = [
                ['text' => '🛒 Купить', 'url' => $post->buy_link]
            ];
        }

        if ($post->is_sold) {
            $buttons[] = [
                ['text' => '❌ Продано', 'callback_data' => 'sold']
            ];
        }

        return ['inline_keyboard' => $buttons];
    }

    /**
     * Форматировать описание из batch
     */
    protected function formatDescription(PhotoBatch $batch): string
    {
        $desc = $batch->ebay_description ?? $batch->description ?? '';

        // Убираем HTML теги
        $desc = strip_tags($desc);

        // Ограничиваем длину для Telegram (макс 1024 символа для caption)
        if (mb_strlen($desc) > 300) {
            $desc = mb_substr($desc, 0, 297) . '...';
        }

        return $desc;
    }

    /**
     * Отметить пост как проданный и обновить в Telegram
     */
    public function markAsSold(TelegramPost $post): TelegramPost
    {
        $post->update([
            'is_sold' => true,
            'sold_at' => now(),
        ]);

        // Пытаемся обновить сообщение в Telegram
        if ($post->canBeEdited()) {
            $this->updateCaption($post);
        }

        return $post->fresh();
    }

    /**
     * Снять пометку "Продано"
     */
    public function markAsAvailable(TelegramPost $post): TelegramPost
    {
        $post->update([
            'is_sold' => false,
            'sold_at' => null,
        ]);

        if ($post->canBeEdited()) {
            $this->updateCaption($post);
        }

        return $post->fresh();
    }

    /**
     * Обновить подпись в Telegram
     */
    protected function updateCaption(TelegramPost $post): bool
    {
        $channel = $post->channel;

        try {
            $response = Http::post("https://api.telegram.org/bot{$channel->bot_token}/editMessageCaption", [
                'chat_id' => $channel->chat_id,
                'message_id' => $post->telegram_message_id,
                'caption' => $this->buildCaption($post),
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($this->buildKeyboard($post)),
            ]);

            if (!$response->successful()) {
                Log::warning("Failed to update Telegram caption", [
                    'post_id' => $post->id,
                    'response' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error updating Telegram caption", [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Отметить все посты товара как проданные
     */
    public function markProductAsSold(PhotoBatch $batch): array
    {
        $posts = TelegramPost::where('photo_batch_id', $batch->id)
            ->where('status', 'sent')
            ->get();

        $results = [];
        foreach ($posts as $post) {
            $results[$post->channel->name] = $this->markAsSold($post);
        }

        return $results;
    }

    /**
     * Удалить пост из Telegram
     */
    public function deletePost(TelegramPost $post): bool
    {
        if (!$post->telegram_message_id) {
            $post->delete();
            return true;
        }

        $channel = $post->channel;

        try {
            Http::post("https://api.telegram.org/bot{$channel->bot_token}/deleteMessage", [
                'chat_id' => $channel->chat_id,
                'message_id' => $post->telegram_message_id,
            ]);

            $post->delete();
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete Telegram message", [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
