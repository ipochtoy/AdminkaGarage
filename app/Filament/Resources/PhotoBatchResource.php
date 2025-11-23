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
                // 1. Photos Section (Full Width)
                Forms\Components\Section::make('Фото')
                    ->schema([
                        Forms\Components\View::make('filament.forms.components.photo-gallery'),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                // 2. Barcodes Section (Full Width)
                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\View::make('filament.forms.components.barcode-list'),
                    ])
                    ->columnSpanFull(),

                // 3. AI Assistant Section
                Forms\Components\Section::make('AI Ассистент')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('generate_openai')
                                ->label('OpenAI GPT-5.1')
                                ->icon('heroicon-m-bolt')
                                ->color('success')
                                ->action(function ($set, $livewire) {
                                    static::generateAIDescription($set, $livewire, 'openai');
                                }),
                            Forms\Components\Actions\Action::make('generate_gemini')
                                ->label('Gemini 3 Pro')
                                ->icon('heroicon-m-sparkles')
                                ->color('info')
                                ->action(function ($set, $livewire) {
                                    static::generateAIDescription($set, $livewire, 'gemini');
                                }),
                        ])->fullWidth(),

                        Forms\Components\Placeholder::make('ai_summary_display')
                            ->label('Результат AI')
                            ->content(fn($record) => $record && $record->ai_summary ? new HtmlString('
                                <div x-data="{ expanded: false }">
                                    <div x-show="!expanded" class="text-xs bg-gray-900 p-3 rounded border border-gray-700 text-gray-300">
                                        <span class="text-green-400">✓ Описание сгенерировано</span>
                                        <button type="button" @click="expanded = true" class="ml-2 text-blue-400 hover:underline">Показать JSON</button>
                                    </div>
                                    <div x-show="expanded" x-cloak>
                                        <pre class="whitespace-pre-wrap text-xs bg-gray-900 p-3 rounded border border-gray-700 text-gray-300 overflow-x-auto max-h-48 overflow-y-auto">' . e($record->ai_summary) . '</pre>
                                        <button type="button" @click="expanded = false" class="mt-1 text-xs text-blue-400 hover:underline">Свернуть</button>
                                    </div>
                                </div>
                            ') : null)
                            ->hidden(fn($record) => !$record || empty($record->ai_summary)),

                        Forms\Components\View::make('filament.forms.components.voice-correction'),

                        Forms\Components\Hidden::make('ai_summary'),
                    ])
                    ->columnSpanFull(),

                // 4. Price Suggestions
                Forms\Components\Section::make('Рекомендации по цене')
                    ->schema([
                        Forms\Components\View::make('filament.forms.components.price-suggestions'),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                // 5. Product Description Form
                Forms\Components\Section::make('Редактирование')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название товара')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(5)
                            ->columnSpanFull(),

                        Forms\Components\Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'md' => 4,
                        ])
                            ->schema([
                                Forms\Components\TextInput::make('price')
                                    ->label('Цена')
                                    ->numeric()
                                    ->prefix('$')
                                    ->hintAction(
                                        Forms\Components\Actions\Action::make('search_ebay')
                                            ->label('Найти на eBay')
                                            ->icon('heroicon-m-magnifying-glass')
                                            ->modalHeading('Поиск цены на eBay')
                                            ->modalSubmitActionLabel('Применить цену')
                                            ->form([
                                                Forms\Components\Radio::make('search_type')
                                                    ->label('Тип поиска')
                                                    ->options([
                                                        'barcode' => 'По штрихкоду',
                                                        'keyword' => 'По названию',
                                                        'image' => 'По фото',
                                                    ])
                                                    ->default(function ($livewire) {
                                                        $record = $livewire->getRecord();
                                                        // Check if any photo has barcodes
                                                        $hasBarcodes = $record && \App\Models\BarcodeResult::whereHas('photo', function ($query) use ($record) {
                                                            $query->where('photo_batch_id', $record->id);
                                                        })->exists();

                                                        return $hasBarcodes ? 'barcode' : 'keyword';
                                                    })
                                                    ->live(),
                                                Forms\Components\Select::make('ebay_item')
                                                    ->label('Выберите товар')
                                                    ->searchable()
                                                    ->getSearchResultsUsing(function (string $search, $get, $livewire) {
                                                        $service = new \App\Services\EbayService();
                                                        $searchType = $get('search_type');
                                                        $record = $livewire->getRecord();

                                                        if ($searchType === 'image') {
                                                            if (!$record || $record->photos->isEmpty()) {
                                                                return [];
                                                            }
                                                            $results = $service->searchByImage($record->photos->first()->image);
                                                        } elseif ($searchType === 'barcode') {
                                                            // Try to find barcode from record if search is empty
                                                            if (empty($search) && $record) {
                                                                $barcode = \App\Models\BarcodeResult::whereHas('photo', function ($query) use ($record) {
                                                                    $query->where('photo_batch_id', $record->id);
                                                                })->first();
                                                                $search = $barcode ? $barcode->data : '';
                                                            }

                                                            if (empty($search)) {
                                                                return [];
                                                            }
                                                            $results = $service->searchByBarcode($search);
                                                        } else {
                                                            if (empty($search)) {
                                                                $search = $record->title ?? '';
                                                            }
                                                            if (empty($search)) {
                                                                return [];
                                                            }
                                                            $results = $service->searchByKeyword($search);
                                                        }

                                                        return collect($results)->mapWithKeys(function ($item) {
                                                            $price = $item['price']['value'];
                                                            $currency = $item['price']['currency'];
                                                            $title = $item['title'];
                                                            $image = $item['image'];

                                                            $label = "<div class='flex items-center gap-2'>
                                                            <img src='{$image}' class='w-8 h-8 object-cover rounded'>
                                                            <div class='flex flex-col text-left'>
                                                                <span class='text-xs font-medium truncate w-64'>{$title}</span>
                                                                <span class='text-xs text-gray-500'>{$price} {$currency}</span>
                                                            </div>
                                                        </div>";

                                                            return [$price => $label];
                                                        })->toArray();
                                                    })
                                                    ->allowHtml()
                                                    ->required(),
                                            ])
                                            ->action(function (array $data, $set) {
                                                $set('price', $data['ebay_item']);

                                                \Filament\Notifications\Notification::make()
                                                    ->title('Price updated from eBay')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),
                                Forms\Components\Select::make('condition')
                                    ->label('Состояние')
                                    ->options([
                                        'new' => 'Новое',
                                        'used' => 'Б/у',
                                        'refurbished' => 'Восстановленное',
                                    ]),
                                Forms\Components\TextInput::make('brand')
                                    ->label('Бренд')
                                    ->maxLength(200),
                                Forms\Components\TextInput::make('category')
                                    ->label('Категория')
                                    ->maxLength(200),
                                Forms\Components\TextInput::make('size')
                                    ->label('Размер')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('color')
                                    ->label('Цвет')
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU')
                                    ->maxLength(200),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Кол-во')
                                    ->numeric()
                                    ->default(1),
                            ]),
                    ]),

                // 6. Preview Button
                Forms\Components\Section::make('')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('preview')
                                ->label('Предварительный просмотр карточки товара')
                                ->icon('heroicon-o-eye')
                                ->color('warning')
                                ->modalHeading('Предварительный просмотр')
                                ->modalWidth('4xl')
                                ->modalContent(function ($record, $livewire) {
                                    // Get current form data
                                    $formData = $livewire->data ?? [];
                                    return view('filament.forms.components.product-preview', [
                                        'record' => $record,
                                        'formData' => $formData,
                                    ]);
                                })
                                ->modalSubmitAction(false)
                                ->modalCancelActionLabel('Закрыть'),
                        ])->fullWidth(),
                    ])
                    ->columnSpanFull(),

                // 5. Tech Info (Collapsed)
                Forms\Components\Section::make('Техническая информация')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('correlation_id')->label('ID')->disabled(),
                            Forms\Components\Select::make('status')
                                ->label('Статус')
                                ->options(['pending' => 'Ожидает', 'processed' => 'Обработано', 'failed' => 'Ошибка'])
                                ->required(),
                            Forms\Components\TextInput::make('chat_id')->label('Chat ID')->disabled(),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\DateTimePicker::make('uploaded_at')->label('Загружено')->disabled(),
                            Forms\Components\DateTimePicker::make('processed_at')->label('Обработано')->disabled(),
                        ]),
                    ])
                    ->collapsed()
                    ->columnSpanFull(),
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
                    ->url(fn(PhotoBatch $record): string => route('filament.admin.resources.photo-batches.edit', $record)),

                Tables\Columns\TextColumn::make('title')
                    ->label('Название товара')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn(PhotoBatch $record): string => $record->title ?? ''),

                Tables\Columns\TextColumn::make('gg_labels')
                    ->label('Наша лейба')
                    ->getStateUsing(function (PhotoBatch $record): string {
                        $labels = $record->getGgLabels();
                        $ggOnly = array_filter($labels, fn($l) => str_starts_with($l, 'GG'));
                        return implode(', ', $ggOnly) ?: '—';
                    })
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'processed' => 'success',
                        'failed' => 'danger',
                        'default' => 'gray'
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
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
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_card')
                    ->label('Просмотр')
                    ->icon('heroicon-o-eye')
                    ->url(fn(PhotoBatch $record): string => route('product-card', $record->id))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListPhotoBatches::route('/'),
            'create' => Pages\CreatePhotoBatch::route('/create'),
            'edit' => Pages\EditPhotoBatch::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('photos');
    }

    protected static function generateAIDescription($set, $livewire, string $provider): void
    {
        $record = $livewire->getRecord();
        if (!$record)
            return;

        $photos = $record->photos;
        if ($photos->isEmpty()) {
            \Filament\Notifications\Notification::make()->title('Нет фото')->warning()->send();
            return;
        }

        \Filament\Notifications\Notification::make()->title('Генерация описания...')->info()->send();

        // Increase timeout for AI operations
        set_time_limit(120);

        if ($provider === 'gemini') {
            $service = new \App\Services\GeminiService();
            $photoPaths = $photos->pluck('image')->toArray();
            $result = $service->generateProductDescription($photoPaths);
        } else {
            // OpenAI - use file paths directly
            $aiService = app(\App\Services\AIService::class)->setProvider('openai');
            $photoPaths = $photos->pluck('image')->toArray();
            $barcodes = collect($record->getAllBarcodes())->pluck('data')->toArray();
            $ggLabels = $record->getGgLabels();

            $response = $aiService->generateSummaryFromPaths($photoPaths, $barcodes, $ggLabels);

            if ($response) {
                // Try to parse JSON
                $result = json_decode($response, true);
                if ($result) {
                    $set('title', $result['title'] ?? '');
                    $set('description', $result['description'] ?? '');
                    $set('brand', $result['brand'] ?? '');
                    $set('category', $result['category'] ?? '');
                    $set('color', $result['color'] ?? '');
                    $set('size', $result['size'] ?? '');
                    $set('condition', $result['condition'] ?? 'used');
                    $set('price', $result['price_estimate'] ?? null);
                    $set('ai_summary', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                    // Save detected codes
                    $firstPhoto = $photos->first();
                    if ($firstPhoto) {
                        if (!empty($result['internal_ids'])) {
                            foreach ($result['internal_ids'] as $code) {
                                if (!$firstPhoto->barcodes()->where('data', $code)->exists()) {
                                    $firstPhoto->barcodes()->create([
                                        'data' => $code,
                                        'symbology' => 'MANUAL-AI',
                                        'source' => 'gg-label'
                                    ]);
                                }
                            }
                        }
                        if (!empty($result['barcodes'])) {
                            foreach ($result['barcodes'] as $bc) {
                                if (!$firstPhoto->barcodes()->where('data', $bc)->exists()) {
                                    $firstPhoto->barcodes()->create([
                                        'data' => $bc,
                                        'symbology' => 'MANUAL-AI',
                                        'source' => 'manual'
                                    ]);
                                }
                            }
                        }
                    }

                    // Save to database for price panel
                    $record->update([
                        'title' => $result['title'] ?? $record->title,
                        'description' => $result['description'] ?? null,
                        'brand' => $result['brand'] ?? null,
                        'category' => $result['category'] ?? null,
                        'color' => $result['color'] ?? null,
                        'size' => $result['size'] ?? null,
                        'condition' => $result['condition'] ?? 'used',
                        'price' => $result['price_estimate'] ?? null,
                        'ai_summary' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                    ]);
                } else {
                    $set('description', $response);
                    $set('ai_summary', $response);
                    $record->update(['description' => $response, 'ai_summary' => $response]);
                }
                \Filament\Notifications\Notification::make()->title('Описание создано (OpenAI)')->success()->send();
                $livewire->dispatch('$refresh');
            } else {
                \Filament\Notifications\Notification::make()->title('Ошибка OpenAI')->danger()->send();
            }
            return;
        }

        if ($result) {
            $set('title', $result['title'] ?? '');
            $set('description', $result['description'] ?? '');
            $set('brand', $result['brand'] ?? '');
            $set('category', $result['category'] ?? '');
            $set('color', $result['color'] ?? '');
            $set('size', $result['size'] ?? '');
            $set('condition', $result['condition'] ?? 'used');
            $set('price', $result['price_estimate'] ?? null);
            $set('ai_summary', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Save detected codes
            $firstPhoto = $photos->first();
            if ($firstPhoto) {
                if (!empty($result['internal_ids'])) {
                    foreach ($result['internal_ids'] as $code) {
                        if (!$firstPhoto->barcodes()->where('data', $code)->exists()) {
                            $firstPhoto->barcodes()->create([
                                'data' => $code,
                                'symbology' => 'MANUAL-AI',
                                'source' => 'gg-label'
                            ]);
                        }
                    }
                }
                if (!empty($result['barcodes'])) {
                    foreach ($result['barcodes'] as $bc) {
                        if (!$firstPhoto->barcodes()->where('data', $bc)->exists()) {
                            $firstPhoto->barcodes()->create([
                                'data' => $bc,
                                'symbology' => 'MANUAL-AI',
                                'source' => 'manual'
                            ]);
                        }
                    }
                }
            }

            // Save to database for price panel
            $record->update([
                'title' => $result['title'] ?? $record->title,
                'description' => $result['description'] ?? null,
                'brand' => $result['brand'] ?? null,
                'category' => $result['category'] ?? null,
                'color' => $result['color'] ?? null,
                'size' => $result['size'] ?? null,
                'condition' => $result['condition'] ?? 'used',
                'price' => $result['price_estimate'] ?? null,
                'ai_summary' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ]);

            \Filament\Notifications\Notification::make()->title('Описание создано (Gemini)')->success()->send();
            $livewire->dispatch('$refresh');
        } else {
            \Filament\Notifications\Notification::make()->title('Ошибка Gemini')->danger()->send();
        }
    }
}
