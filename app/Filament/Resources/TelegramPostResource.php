<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelegramPostResource\Pages;
use App\Models\TelegramChannel;
use App\Models\TelegramPost;
use App\Models\PhotoBatch;
use App\Services\TelegramPostService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class TelegramPostResource extends Resource
{
    protected static ?string $model = TelegramPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationLabel = 'Telegram посты';

    protected static ?string $modelLabel = 'Пост';

    protected static ?string $pluralModelLabel = 'Посты';

    protected static ?string $navigationGroup = 'Telegram';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'draft')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        // Левая колонка - основная информация
                        Forms\Components\Section::make('Контент поста')
                            ->schema([
                                Forms\Components\Select::make('telegram_channel_id')
                                    ->label('Канал')
                                    ->options(TelegramChannel::active()->ordered()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),

                                Forms\Components\Select::make('photo_batch_id')
                                    ->label('Товар (опционально)')
                                    ->options(function () {
                                        return PhotoBatch::whereNotNull('title')
                                            ->latest()
                                            ->limit(100)
                                            ->get()
                                            ->mapWithKeys(fn ($batch) => [
                                                $batch->id => "#{$batch->id} - " . ($batch->title ?? 'Без названия')
                                            ]);
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $batch = PhotoBatch::with('photos')->find($state);
                                            if ($batch) {
                                                $set('title', $batch->ebay_title ?? $batch->title ?? '');
                                                $set('description', $batch->ebay_description ?? $batch->description ?? '');
                                                $set('price', $batch->ebay_price ?? $batch->price);

                                                $images = $batch->photos()
                                                    ->where('is_public', true)
                                                    ->orderBy('order')
                                                    ->pluck('image')
                                                    ->toArray();
                                                $set('images', $images);
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('title')
                                    ->label('Заголовок')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\Textarea::make('description')
                                    ->label('Описание')
                                    ->rows(4)
                                    ->maxLength(500)
                                    ->helperText('Максимум 500 символов для Telegram'),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('price')
                                            ->label('Цена')
                                            ->numeric()
                                            ->prefix('$'),

                                        Forms\Components\Select::make('currency')
                                            ->label('Валюта')
                                            ->options([
                                                'USD' => '$ USD',
                                                'EUR' => '€ EUR',
                                                'RUB' => '₽ RUB',
                                            ])
                                            ->default('USD'),
                                    ]),

                                Forms\Components\TextInput::make('buy_link')
                                    ->label('Ссылка "Купить"')
                                    ->url()
                                    ->maxLength(500)
                                    ->helperText('Оставьте пустым для автогенерации из канала'),
                            ])
                            ->columnSpan(2),

                        // Правая колонка - фото и статус
                        Forms\Components\Section::make('Фото и статус')
                            ->schema([
                                Forms\Components\Placeholder::make('images_preview')
                                    ->label('Превью фото')
                                    ->content(function ($get) {
                                        $images = $get('images') ?? [];
                                        if (empty($images)) {
                                            return 'Нет фото';
                                        }
                                        $html = '<div class="grid grid-cols-2 gap-2">';
                                        foreach (array_slice($images, 0, 4) as $image) {
                                            $url = asset('storage/' . $image);
                                            $html .= "<img src=\"{$url}\" class=\"rounded w-full h-20 object-cover\" />";
                                        }
                                        if (count($images) > 4) {
                                            $html .= '<div class="flex items-center justify-center bg-gray-100 rounded">+' . (count($images) - 4) . '</div>';
                                        }
                                        $html .= '</div>';
                                        return new \Illuminate\Support\HtmlString($html);
                                    }),

                                Forms\Components\Hidden::make('images'),

                                Forms\Components\Select::make('status')
                                    ->label('Статус')
                                    ->options([
                                        'draft' => 'Черновик',
                                        'scheduled' => 'Запланирован',
                                        'sent' => 'Отправлен',
                                        'failed' => 'Ошибка',
                                    ])
                                    ->default('draft')
                                    ->disabled(),

                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->label('Запланировать на')
                                    ->helperText('Оставьте пустым для немедленной отправки'),

                                Forms\Components\Toggle::make('is_sold')
                                    ->label('Продано')
                                    ->helperText('Отметить товар как проданный'),

                                Forms\Components\Placeholder::make('sent_info')
                                    ->label('Отправлен')
                                    ->content(fn ($record) => $record?->sent_at?->format('d.m.Y H:i') ?? '-')
                                    ->visible(fn ($record) => $record?->sent_at),

                                Forms\Components\Placeholder::make('error_info')
                                    ->label('Ошибка')
                                    ->content(fn ($record) => $record?->error_message)
                                    ->visible(fn ($record) => $record?->error_message),
                            ])
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('preview_image_url')
                    ->label('')
                    ->size(60)
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->limit(40)
                    ->description(fn (TelegramPost $record) => $record->channel?->name),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->formatStateUsing(fn ($record) => $record->formatted_price)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->colors([
                        'gray' => 'draft',
                        'warning' => 'scheduled',
                        'success' => 'sent',
                        'danger' => 'failed',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'draft' => 'Черновик',
                        'scheduled' => 'Запланирован',
                        'sent' => 'Отправлен',
                        'failed' => 'Ошибка',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_sold')
                    ->label('Продано')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('danger')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Отправлен')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('telegram_channel_id')
                    ->label('Канал')
                    ->options(TelegramChannel::pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'scheduled' => 'Запланирован',
                        'sent' => 'Отправлен',
                        'failed' => 'Ошибка',
                    ]),

                Tables\Filters\TernaryFilter::make('is_sold')
                    ->label('Продано'),
            ])
            ->actions([
                Tables\Actions\Action::make('send')
                    ->label('Отправить')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (TelegramPost $record) => $record->canBeSent())
                    ->requiresConfirmation()
                    ->action(function (TelegramPost $record) {
                        $service = app(TelegramPostService::class);
                        $result = $service->sendPost($record);

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
                    }),

                Tables\Actions\Action::make('mark_sold')
                    ->label('Продано')
                    ->icon('heroicon-o-check-badge')
                    ->color('danger')
                    ->visible(fn (TelegramPost $record) => $record->status === 'sent' && !$record->is_sold)
                    ->requiresConfirmation()
                    ->modalHeading('Отметить как продано?')
                    ->modalDescription('Пост будет обновлён в Telegram с пометкой "ПРОДАНО"')
                    ->action(function (TelegramPost $record) {
                        $service = app(TelegramPostService::class);
                        $service->markAsSold($record);

                        Notification::make()
                            ->success()
                            ->title('Отмечено как продано')
                            ->send();
                    }),

                Tables\Actions\Action::make('mark_available')
                    ->label('Вернуть')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('info')
                    ->visible(fn (TelegramPost $record) => $record->is_sold)
                    ->requiresConfirmation()
                    ->action(function (TelegramPost $record) {
                        $service = app(TelegramPostService::class);
                        $service->markAsAvailable($record);

                        Notification::make()
                            ->success()
                            ->title('Пометка снята')
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('delete_with_telegram')
                    ->label('Удалить')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Удалить пост?')
                    ->modalDescription('Пост будет удалён из базы и из Telegram канала')
                    ->action(function (TelegramPost $record) {
                        $service = app(TelegramPostService::class);
                        $service->deletePost($record);

                        Notification::make()
                            ->success()
                            ->title('Пост удалён')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('send_all')
                        ->label('Отправить выбранные')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $service = app(TelegramPostService::class);
                            $sent = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                if ($record->canBeSent()) {
                                    $result = $service->sendPost($record);
                                    if ($result->status === 'sent') {
                                        $sent++;
                                    } else {
                                        $failed++;
                                    }
                                }
                            }

                            Notification::make()
                                ->title("Отправлено: {$sent}, ошибок: {$failed}")
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('mark_sold_all')
                        ->label('Отметить проданными')
                        ->icon('heroicon-o-check-badge')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $service = app(TelegramPostService::class);
                            foreach ($records as $record) {
                                if ($record->status === 'sent' && !$record->is_sold) {
                                    $service->markAsSold($record);
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title('Посты отмечены как проданные')
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTelegramPosts::route('/'),
            'create' => Pages\CreateTelegramPost::route('/create'),
            'edit' => Pages\EditTelegramPost::route('/{record}/edit'),
        ];
    }
}
