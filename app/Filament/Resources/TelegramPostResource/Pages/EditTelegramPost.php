<?php

namespace App\Filament\Resources\TelegramPostResource\Pages;

use App\Filament\Resources\TelegramPostResource;
use App\Services\TelegramPostService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditTelegramPost extends EditRecord
{
    protected static string $resource = TelegramPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send')
                ->label('Отправить')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn () => $this->record->canBeSent())
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(TelegramPostService::class);
                    $result = $service->sendPost($this->record);

                    if ($result->status === 'sent') {
                        Notification::make()
                            ->success()
                            ->title('Пост отправлен!')
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title('Ошибка отправки')
                            ->body($result->error_message)
                            ->send();
                    }

                    $this->refreshFormData(['status', 'sent_at', 'telegram_message_id', 'error_message']);
                }),

            Actions\Action::make('mark_sold')
                ->label('Продано')
                ->icon('heroicon-o-check-badge')
                ->color('danger')
                ->visible(fn () => $this->record->status === 'sent' && !$this->record->is_sold)
                ->requiresConfirmation()
                ->action(function () {
                    $service = app(TelegramPostService::class);
                    $service->markAsSold($this->record);

                    Notification::make()
                        ->success()
                        ->title('Отмечено как продано')
                        ->send();

                    $this->refreshFormData(['is_sold', 'sold_at']);
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
