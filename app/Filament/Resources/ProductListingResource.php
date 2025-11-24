<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductListingResource\Pages;
use App\Models\ProductListing;
use App\Services\Marketplaces\ListingOrchestrator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class ProductListingResource extends Resource
{
    protected static ?string $model = ProductListing::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationLabel = 'Листинги';

    protected static ?string $modelLabel = 'Листинг';

    protected static ?string $pluralModelLabel = 'Листинги';

    protected static ?string $navigationGroup = 'Продажи';

    protected static ?int $navigationSort = 10;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'published')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Информация о листинге')
                    ->schema([
                        Forms\Components\Select::make('photo_batch_id')
                            ->label('Карточка товара')
                            ->relationship('photoBatch', 'title')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('platform')
                            ->label('Платформа')
                            ->options(ProductListing::platforms())
                            ->required()
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options(ProductListing::statuses())
                            ->required(),

                        Forms\Components\TextInput::make('external_id')
                            ->label('ID на платформе')
                            ->disabled(),

                        Forms\Components\TextInput::make('external_url')
                            ->label('Ссылка')
                            ->url()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('open_link')
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->url(fn($state) => $state)
                                    ->openUrlInNewTab()
                                    ->visible(fn($state) => !empty($state))
                            ),

                        Forms\Components\TextInput::make('listed_price')
                            ->label('Цена')
                            ->numeric()
                            ->prefix('$'),

                        Forms\Components\Textarea::make('error_message')
                            ->label('Сообщение об ошибке')
                            ->disabled()
                            ->visible(fn($record) => $record && $record->status === 'failed'),

                        Forms\Components\KeyValue::make('platform_data')
                            ->label('Данные платформы')
                            ->disabled()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('photoBatch.title')
                    ->label('Товар')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn($record) => $record->photoBatch?->title)
                    ->url(fn($record) => $record->photoBatch
                        ? route('filament.admin.resources.photo-batches.edit', $record->photoBatch)
                        : null),

                Tables\Columns\TextColumn::make('platform')
                    ->label('Платформа')
                    ->badge()
                    ->formatStateUsing(fn($state) => ProductListing::platforms()[$state] ?? $state)
                    ->color(fn($state) => match ($state) {
                        'pochtoy' => 'warning',
                        'ebay' => 'info',
                        'shopify' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn($state) => ProductListing::statuses()[$state] ?? $state)
                    ->color(fn($state) => match ($state) {
                        'published' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'deleted' => 'gray',
                        'sold' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('listed_price')
                    ->label('Цена')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('external_url')
                    ->label('Ссылка')
                    ->formatStateUsing(fn($state) => $state ? new HtmlString('<a href="' . e($state) . '" target="_blank" class="text-primary-500 hover:underline">Открыть →</a>') : '—'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Опубликовано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->label('Платформа')
                    ->options(ProductListing::platforms()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options(ProductListing::statuses()),
            ])
            ->actions([
                Tables\Actions\Action::make('open_listing')
                    ->label('Открыть')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn($record) => $record->external_url)
                    ->openUrlInNewTab()
                    ->visible(fn($record) => !empty($record->external_url)),

                Tables\Actions\Action::make('sync_status')
                    ->label('Проверить')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function ($record) {
                        $orchestrator = new ListingOrchestrator();
                        $result = $orchestrator->checkListingStatus($record);

                        if ($result['success']) {
                            \Filament\Notifications\Notification::make()
                                ->title('Статус: ' . ($result['status'] ?? 'unknown'))
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Ошибка проверки')
                                ->body($result['error'] ?? 'Unknown error')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn($record) => $record->status === 'published'),

                Tables\Actions\Action::make('retry_publish')
                    ->label('Повторить')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $orchestrator = new ListingOrchestrator();
                        $result = $orchestrator->publishTo($record->photoBatch, $record->platform);

                        if ($result['success']) {
                            \Filament\Notifications\Notification::make()
                                ->title('Успешно опубликовано')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Ошибка публикации')
                                ->body($result['error'] ?? 'Unknown error')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn($record) => $record->status === 'failed'),

                Tables\Actions\Action::make('delete_listing')
                    ->label('Снять')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Снять с публикации?')
                    ->action(function ($record) {
                        $orchestrator = new ListingOrchestrator();
                        $result = $orchestrator->deleteListing($record);

                        if ($result['success']) {
                            \Filament\Notifications\Notification::make()
                                ->title('Снято с публикации')
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Ошибка')
                                ->body($result['error'] ?? 'Unknown error')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn($record) => $record->status === 'published'),

                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProductListings::route('/'),
            'edit' => Pages\EditProductListing::route('/{record}/edit'),
        ];
    }
}
