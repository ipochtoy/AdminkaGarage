<?php

namespace App\Console\Commands;

use App\Models\PhotoBuffer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TelegramBotCommand extends Command
{
    protected $signature = 'telegram:bot';
    protected $description = 'Run Telegram bot for photo uploads';

    private string $token;
    private string $apiUrl;
    private array $batches = []; // Track photo batches per chat

    public function handle()
    {
        $this->token = config('services.telegram.bot_token') ?: env('TELEGRAM_BOT_TOKEN');

        if (!$this->token) {
            $this->error('TELEGRAM_BOT_TOKEN not set in .env');
            return 1;
        }

        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";

        $this->info('Telegram bot started. Listening for photos...');
        $this->info('Press Ctrl+C to stop');

        $offset = 0;

        while (true) {
            try {
                $response = Http::timeout(30)->get("{$this->apiUrl}/getUpdates", [
                    'offset' => $offset,
                    'timeout' => 25,
                ]);

                if ($response->successful()) {
                    $data = $response->json();

                    if ($data['ok'] && !empty($data['result'])) {
                        foreach ($data['result'] as $update) {
                            $offset = $update['update_id'] + 1;
                            $this->processUpdate($update);
                        }
                    }
                } else {
                    $this->warn('HTTP error: ' . $response->status());
                    sleep(5);
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $this->error('Connection error: ' . $e->getMessage());
                sleep(10);
            } catch (\Exception $e) {
                $this->error('Error: ' . $e->getMessage());
                sleep(5);
            }
            
            // Задержка между запросами чтобы не перегружать API
            usleep(500000); // 0.5 секунды
        }
    }

    private function processUpdate(array $update): void
    {
        $message = $update['message'] ?? null;

        if (!$message) {
            return;
        }

        $chatId = $message['chat']['id'];
        $messageId = $message['message_id'];

        // Handle /start command
        if (isset($message['text']) && $message['text'] === '/start') {
            $this->sendMessage($chatId, "Привет! Отправляй фото для загрузки в буфер.\n\n💡 Совет: отправляй фото как файлы (📎) чтобы сохранить дату съёмки.");
            return;
        }

        // Handle photos (compressed, no EXIF)
        if (isset($message['photo'])) {
            $photos = $message['photo'];
            $largestPhoto = end($photos);
            $fileId = $largestPhoto['file_id'];

            $this->downloadAndSavePhoto($fileId, $chatId, $messageId);
        }

        // Handle documents (files with EXIF preserved)
        if (isset($message['document'])) {
            $doc = $message['document'];
            $mimeType = $doc['mime_type'] ?? '';

            // Only process image files
            if (str_starts_with($mimeType, 'image/')) {
                $fileId = $doc['file_id'];
                $this->downloadAndSavePhoto($fileId, $chatId, $messageId);
            }
        }
    }

    private function downloadAndSavePhoto(string $fileId, int $chatId, int $messageId): void
    {
        // Initialize batch for this chat
        if (!isset($this->batches[$chatId])) {
            $this->batches[$chatId] = [
                'count' => 0,
                'message_id' => null,
                'last_update' => 0,
            ];
        }

        // Get file path
        try {
            $response = Http::timeout(30)->get("{$this->apiUrl}/getFile", ['file_id' => $fileId]);

            if (!$response->successful()) {
                $this->sendMessage($chatId, "Ошибка получения файла");
                return;
            }

            $fileData = $response->json();
            if (!isset($fileData['result']['file_path'])) {
                $this->sendMessage($chatId, "Ошибка: файл не найден");
                return;
            }

            $filePath = $fileData['result']['file_path'];
            $fileUrl = "https://api.telegram.org/file/bot{$this->token}/{$filePath}";

            // Download file
            $fileResponse = Http::timeout(60)->get($fileUrl);
            if (!$fileResponse->successful()) {
                $this->sendMessage($chatId, "Ошибка загрузки файла");
                return;
            }
            $fileContent = $fileResponse->body();
        } catch (\Exception $e) {
            $this->error("Download error: " . $e->getMessage());
            $this->sendMessage($chatId, "Ошибка при обработке файла");
            return;
        }

        // Get file extension from original path
        $ext = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'jpg';
        $storagePath = 'buffer/' . date('Y/m/d') . '/' . uniqid() . '.' . $ext;
        Storage::disk('public')->put($storagePath, $fileContent);

        // Save to database (skip if already exists)
        $exists = PhotoBuffer::where('file_id', $fileId)->exists();
        if ($exists) {
            Storage::disk('public')->delete($storagePath);
            return;
        }

        // Read EXIF data for taken_at
        $takenAt = null;
        $fullPath = Storage::disk('public')->path($storagePath);
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($fullPath);
            if ($exif && isset($exif['DateTimeOriginal'])) {
                $takenAt = \Carbon\Carbon::createFromFormat('Y:m:d H:i:s', $exif['DateTimeOriginal']);
            }
        }

        PhotoBuffer::create([
            'file_id' => $fileId,
            'message_id' => $messageId,
            'chat_id' => $chatId,
            'image' => $storagePath,
            'uploaded_at' => now(),
            'taken_at' => $takenAt,
        ]);

        $this->batches[$chatId]['count']++;
        $count = $this->batches[$chatId]['count'];
        $total = PhotoBuffer::count();

        $this->info("Photo saved: {$storagePath}");

        // Build progress bar
        $barLength = 10;
        $progress = min($count, $barLength);
        $bar = str_repeat('▓', $progress) . str_repeat('░', max(0, $barLength - $progress));
        $text = "📸 Загружено: {$count}\n{$bar}\nВсего в буфере: {$total}";

        // Update or send progress message
        if ($this->batches[$chatId]['message_id']) {
            $this->editMessage($chatId, $this->batches[$chatId]['message_id'], $text);
        } else {
            $msgId = $this->sendMessageAndGetId($chatId, $text);
            $this->batches[$chatId]['message_id'] = $msgId;
        }
    }

    private function sendMessageAndGetId(int $chatId, string $text): ?int
    {
        try {
            $response = Http::timeout(10)->post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['result']['message_id'] ?? null;
            }
        } catch (\Exception $e) {
            $this->error("Send message error: " . $e->getMessage());
        }
        return null;
    }

    private function editMessage(int $chatId, int $messageId, string $text): void
    {
        try {
            Http::timeout(10)->post("{$this->apiUrl}/editMessageText", [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
            ]);
        } catch (\Exception $e) {
            $this->error("Edit message error: " . $e->getMessage());
        }
    }

    private function sendMessage(int $chatId, string $text): void
    {
        try {
            Http::timeout(10)->post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
            ]);
        } catch (\Exception $e) {
            $this->error("Send message error: " . $e->getMessage());
        }
    }
}
