<?php

namespace App\Filament\Resources\TelegramPostResource\Pages;

use App\Filament\Resources\TelegramPostResource;
use App\Models\TelegramChannel;
use App\Models\PhotoBatch;
use App\Services\TelegramPostService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListTelegramPosts extends ListRecords
{
    protected static string $resource = TelegramPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_from_batch')
                ->label('Создать из товара')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\Select::make('photo_batch_id')
                        ->label('Выберите товар')
                        ->options(function () {
                            return PhotoBatch::whereNotNull('title')
                                ->latest()
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn ($batch) => [
                                    $batch->id => "#{$batch->id} - " . ($batch->title ?? 'Без названия') . " - $" . ($batch->ebay_price ?? $batch->price ?? '0')
                                ]);
                        })
                        ->searchable()
                        ->required(),

                    \Filament\Forms\Components\CheckboxList::make('channels')
                        ->label('Каналы')
                        ->options(TelegramChannel::active()->ordered()->pluck('name', 'id'))
                        ->default(TelegramChannel::active()->pluck('id')->toArray())
                        ->columns(2)
                        ->required(),

                    \Filament\Forms\Components\Toggle::make('send_immediately')
                        ->label('Отправить сразу')
                        ->default(false),
                ])
                ->action(function (array $data) {
                    $batch = PhotoBatch::find($data['photo_batch_id']);
                    $service = app(TelegramPostService::class);

                    $created = 0;
                    $sent = 0;

                    foreach ($data['channels'] as $channelId) {
                        $channel = TelegramChannel::find($channelId);
                        if ($channel) {
                            $post = $service->createPostFromBatch($batch, $channel);
                            $created++;

                            if ($data['send_immediately']) {
                                $result = $service->sendPost($post);
                                if ($result->status === 'sent') {
                                    $sent++;
                                }
                            }
                        }
                    }

                    $message = "Создано постов: {$created}";
                    if ($data['send_immediately']) {
                        $message .= ", отправлено: {$sent}";
                    }

                    Notification::make()
                        ->success()
                        ->title($message)
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Создать вручную'),
        ];
    }
}
