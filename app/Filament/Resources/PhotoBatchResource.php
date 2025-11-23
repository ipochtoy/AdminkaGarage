<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoBatchResource\Pages;
use App\Filament\Resources\PhotoBatchResource\RelationManagers;
use App\Models\PhotoBatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\HtmlString;

class PhotoBatchResource extends Resource
{
    protected static ?string $model = PhotoBatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Карточки товаров';

    protected static ?string $modelLabel = 'Карточка товара';

    protected static ?string $pluralModelLabel = 'Карточки товаров';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('correlation_id')
                            ->label('ID карточки')
                            ->disabled()
                            ->maxLength(32),
                        Forms\Components\TextInput::make('chat_id')
                            ->label('Chat ID')
                            ->disabled()
                            ->numeric(),
                        Forms\Components\Select::make('status')
                            ->label('Статус')
                            ->options([
                                'pending' => 'Ожидает',
                                'processed' => 'Обработано',
                                'failed' => 'Ошибка',
                            ])
                            ->required(),
                        Forms\Components\DateTimePicker::make('uploaded_at')
                            ->label('Загружено')
                            ->disabled(),
                        Forms\Components\DateTimePicker::make('processed_at')
                            ->label('Обработано')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Описание товара')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название товара')
                            ->maxLength(500),
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('price')
                            ->label('Цена')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Select::make('condition')
                            ->label('Состояние')
                            ->options([
                                'new' => 'Новое',
                                'used' => 'Б/у',
                                'refurbished' => 'Восстановленное',
                            ]),
                        Forms\Components\TextInput::make('category')
                            ->label('Категория')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('brand')
                            ->label('Бренд')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('size')
                            ->label('Размер')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('color')
                            ->label('Цвет')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU/Артикул')
                            ->maxLength(200),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Количество')
                            ->numeric()
                            ->default(1),
                        Forms\Components\Textarea::make('ai_summary')
                            ->label('AI Сводка')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('correlation_id')
                    ->label('ID карточки')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->url(fn (PhotoBatch $record): string => route('filament.admin.resources.photo-batches.edit', $record)),

                Tables\Columns\TextColumn::make('title')
                    ->label('Название товара')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (PhotoBatch $record): string => $record->title ?? ''),

                Tables\Columns\TextColumn::make('gg_labels')
                    ->label('Наша лейба')
                    ->getStateUsing(function (PhotoBatch $record): string {
                        $labels = $record->getGgLabels();
                        $ggOnly = array_filter($labels, fn($l) => str_starts_with($l, 'GG'));
                        return implode(', ', $ggOnly) ?: '—';
                    })
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('chat_id')
                    ->label('Chat ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processed' => 'success',
                        'failed' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Ожидает',
                        'processed' => 'Обработано',
                        'failed' => 'Ошибка',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('uploaded_at')
                    ->label('Загружено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('photos_preview')
                    ->label('Превью')
                    ->getStateUsing(function (PhotoBatch $record): HtmlString {
                        $photos = $record->photos()->limit(4)->get();
                        if ($photos->isEmpty()) {
                            return new HtmlString('<span style="color: #999;">—</span>');
                        }
                        $html = '<div style="display: flex; gap: 4px;">';
                        foreach ($photos as $photo) {
                            $url = asset('storage/' . $photo->image);
                            $html .= '<img src="' . $url . '" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;" />';
                        }
                        $html .= '</div>';
                        return new HtmlString($html);
                    }),

                Tables\Columns\TextColumn::make('photos_count')
                    ->label('Фото')
                    ->counts('photos')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает',
                        'processed' => 'Обработано',
                        'failed' => 'Ошибка',
                    ]),
                Tables\Filters\Filter::make('uploaded_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('От'),
                        Forms\Components\DatePicker::make('until')
                            ->label('До'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('uploaded_at', '>=', $date))
                            ->when($data['until'], fn (Builder $query, $date): Builder => $query->whereDate('uploaded_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_card')
                    ->label('Карточка')
                    ->icon('heroicon-o-eye')
                    ->url(fn (PhotoBatch $record): string => route('product-card', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('send_to_ebay')
                        ->label('Отправить в eBay')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->action(function (Collection $records): void {
                            // TODO: Implement eBay integration
                            foreach ($records as $record) {
                                // Create eBay candidate
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PhotosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotoBatches::route('/'),
            'create' => Pages\CreatePhotoBatch::route('/create'),
            'edit' => Pages\EditPhotoBatch::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('photos');
    }
}
