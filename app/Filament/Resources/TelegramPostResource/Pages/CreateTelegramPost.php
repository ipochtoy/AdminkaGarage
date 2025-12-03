<?php

namespace App\Filament\Resources\TelegramPostResource\Pages;

use App\Filament\Resources\TelegramPostResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTelegramPost extends CreateRecord
{
    protected static string $resource = TelegramPostResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Генерируем buy_link если не указан
        if (empty($data['buy_link']) && !empty($data['telegram_channel_id'])) {
            $channel = \App\Models\TelegramChannel::find($data['telegram_channel_id']);
            if ($channel && !empty($data['photo_batch_id'])) {
                $batch = \App\Models\PhotoBatch::find($data['photo_batch_id']);
                if ($batch) {
                    $data['buy_link'] = $channel->generateBuyLink($batch);
                }
            }
        }

        return $data;
    }
}
