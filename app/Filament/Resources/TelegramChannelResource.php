<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TelegramChannelResource\Pages;
use App\Models\TelegramChannel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TelegramChannelResource extends Resource
{
    protected static ?string $model = TelegramChannel::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Telegram каналы';

    protected static ?string $modelLabel = 'Канал';

    protected static ?string $pluralModelLabel = 'Каналы';

    protected static ?string $navigationGroup = 'Telegram';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основные настройки')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Название канала')
                            ->placeholder('Гараж 1')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('site_name')
                            ->label('Название сайта')
                            ->placeholder('garage1.pochtoy.com')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Порядок сортировки')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Telegram настройки')
                    ->schema([
                        Forms\Components\TextInput::make('bot_token')
                            ->label('Bot Token')
                            ->placeholder('123456789:ABCdefGHIjklMNOpqrsTUVwxyz')
                            ->required()
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText('Получите токен у @BotFather'),

                        Forms\Components\TextInput::make('chat_id')
                            ->label('Chat ID канала')
                            ->placeholder('-1001234567890')
                            ->required()
                            ->maxLength(255)
                            ->helperText('ID канала или группы (начинается с -100 для каналов)'),
                    ]),

                Forms\Components\Section::make('Ссылка на товар')
                    ->schema([
                        Forms\Components\TextInput::make('link_template')
                            ->label('Шаблон ссылки')
                            ->placeholder('https://garage1.pochtoy.com/item/{correlation_id}')
                            ->required()
                            ->maxLength(500)
                            ->helperText('Доступные переменные: {id}, {correlation_id}, {sku}'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('site_name')
                    ->label('Сайт')
                    ->searchable(),

                Tables\Columns\TextColumn::make('chat_id')
                    ->label('Chat ID')
                    ->copyable()
                    ->copyMessage('Chat ID скопирован'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),

                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Постов')
                    ->counts('posts')
                    ->badge(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Порядок')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активные'),
            ])
            ->actions([
                Tables\Actions\Action::make('test')
                    ->label('Тест')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Отправить тестовое сообщение?')
                    ->action(function (TelegramChannel $record) {
                        try {
                            $response = \Illuminate\Support\Facades\Http::post(
                                "https://api.telegram.org/bot{$record->bot_token}/sendMessage",
                                [
                                    'chat_id' => $record->chat_id,
                                    'text' => "✅ Тестовое сообщение\n\nКанал: {$record->name}\nВремя: " . now()->format('d.m.Y H:i:s'),
                                ]
                            );

                            if ($response->successful() && ($response->json()['ok'] ?? false)) {
                                \Filament\Notifications\Notification::make()
                                    ->success()
                                    ->title('Сообщение отправлено!')
                                    ->send();
                            } else {
                                throw new \Exception($response->json()['description'] ?? 'Unknown error');
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Ошибка')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListTelegramChannels::route('/'),
            'create' => Pages\CreateTelegramChannel::route('/create'),
            'edit' => Pages\EditTelegramChannel::route('/{record}/edit'),
        ];
    }
}
