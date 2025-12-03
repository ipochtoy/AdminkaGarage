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
                // Pochtoy Status Alert
                Forms\Components\Placeholder::make('pochtoy_alert')
                    ->hiddenLabel()
                    ->content(function ($record) {
                        if (!$record) return null;
                        
                        if ($record->pochtoy_status === 'success') {
                            return new HtmlString('
                                <div style="background: #dcfce7; border: 1px solid #16a34a; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 20px;">✅</span>
                                    <span style="color: #166534; font-weight: 500;">Отправлено в Pochtoy</span>
                                </div>
                            ');
                        }
                        
                        if ($record->pochtoy_status === 'failed') {
                            $error = e($record->pochtoy_error ?? 'Неизвестная ошибка');
                            return new HtmlString('
                                <div style="background: #fef2f2; border: 1px solid #dc2626; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 20px;">❌</span>
                                    <div>
                                        <div style="color: #991b1b; font-weight: 600;">Ошибка отправки в Pochtoy</div>
                                        <div style="color: #b91c1c; font-size: 13px;">' . $error . '</div>
                                    </div>
                                </div>
                            ');
                        }
                        
                        if ($record->pochtoy_status === 'pending' && $record->status !== 'pending') {
                            return new HtmlString('
                                <div style="background: #fef9c3; border: 1px solid #ca8a04; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 20px;">⏳</span>
                                    <span style="color: #854d0e; font-weight: 500;">Ожидает отправки в Pochtoy</span>
                                </div>
                            ');
                        }
                        
                        return null;
                    })
                    ->columnSpanFull(),

                Forms\Components\Tabs::make('main_tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Гараж')
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

                // 3. AI Assistant Section (Compact)
                Forms\Components\Section::make('AI Ассистент')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('generate_openai')
                                        ->label('OpenAI GPT-5.1')
                                        ->icon('heroicon-m-bolt')
                                        ->color('success')
                                        ->size('sm')
                                        ->action(function ($set, $livewire) {
                                            static::generateAIDescription($set, $livewire, 'openai');
                                        }),
                                    Forms\Components\Actions\Action::make('generate_gemini_pro')
                                        ->label('Gemini 3 Pro')
                                        ->icon('heroicon-m-sparkles')
                                        ->color('info')
                                        ->size('sm')
                                        ->action(function ($set, $livewire) {
                                            static::generateAIDescription($set, $livewire, 'gemini', 'gemini-3-pro-preview');
                                        }),
                                    Forms\Components\Actions\Action::make('generate_gemini_flash')
                                        ->label('Gemini 2.5 Flash')
                                        ->icon('heroicon-m-bolt')
                                        ->color('warning')
                                        ->size('sm')
                                        ->action(function ($set, $livewire) {
                                            static::generateAIDescription($set, $livewire, 'gemini', 'gemini-2.5-flash-preview-09-2025');
                                        }),
                                ]),

                                Forms\Components\Placeholder::make('ai_summary_display')
                                    ->label('')
                                    ->content(fn($record) => $record && $record->ai_summary ? new HtmlString('
                                        <div x-data="{ expanded: false }" class="text-xs">
                                            <div x-show="!expanded" class="bg-gray-900 p-2 rounded border border-gray-700 text-gray-300">
                                                <span class="text-green-400">✓ Описание сгенерировано</span>
                                                <button type="button" @click="expanded = true" class="ml-2 text-blue-400 hover:underline">JSON</button>
                                            </div>
                                            <div x-show="expanded" x-cloak>
                                                <pre class="whitespace-pre-wrap text-xs bg-gray-900 p-2 rounded border border-gray-700 text-gray-300 overflow-x-auto max-h-32 overflow-y-auto">' . e($record->ai_summary) . '</pre>
                                                <button type="button" @click="expanded = false" class="mt-1 text-xs text-blue-400 hover:underline">Свернуть</button>
                                            </div>
                                        </div>
                                    ') : new HtmlString('<div class="text-xs text-gray-500 italic">Сгенерируйте описание</div>'))
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\View::make('filament.forms.components.voice-correction'),

                        Forms\Components\Hidden::make('ai_summary'),
                    ])
                    ->columnSpanFull()
                    ->compact(),

                // 4. Price Suggestions
                Forms\Components\Section::make('Рекомендации по цене')
                    ->schema([
                        Forms\Components\View::make('filament.forms.components.price-suggestions'),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->compact(),

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
                            ->rows(8)
                            ->autosize()
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

                // 5.5. Declaration Section (Technical)
                Forms\Components\Section::make('Декларация')
                    ->description('Данные для таможенной декларации Pochtoy Express')
                    ->schema([
                        // Row 1: EN description + RU description
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('declaration_en')
                                    ->label('Description EN (3-5 слов)')
                                    ->placeholder('women sneakers New Balance')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('declaration_ru')
                                    ->label('Описание RU (3-5 слов)')
                                    ->placeholder('женские кроссовки Нью Баланс')
                                    ->maxLength(255),
                            ]),
                        // Row 2: SKU, Brand (brand берём из основного поля)
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('declaration_sku')
                                    ->label('Артикул (ASIN/SKU)')
                                    ->placeholder('B0CZ9TDDN6')
                                    ->maxLength(100),
                                Forms\Components\Placeholder::make('brand_display')
                                    ->label('Бренд')
                                    ->content(fn ($record) => $record?->brand ?? '—'),
                            ]),
                        // Row 3: Quantity, Price, URL
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('quantity_display')
                                    ->label('Количество')
                                    ->content(fn ($record) => $record?->quantity ?? 1),
                                Forms\Components\Placeholder::make('price_display')
                                    ->label('Стоимость (USD)')
                                    ->content(fn ($record) => $record?->price ? '$' . number_format($record->price, 2) : '—'),
                                Forms\Components\TextInput::make('declaration_url')
                                    ->label('Ссылка на товар')
                                    ->placeholder('https://www.amazon.com/dp/B0CZ9TDDN6')
                                    ->url()
                                    ->maxLength(500),
                            ]),
                        // Row 4: Battery checkbox
                        Forms\Components\Toggle::make('declaration_has_battery')
                            ->label('Батарея')
                            ->helperText('Содержит ли товар литиевую батарею')
                            ->inline(false),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

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
                            ]), // End of "Гараж" tab

                        Forms\Components\Tabs\Tab::make('eBay & Shopify')
                            ->schema([
                                // Photos Section (same as in Garage tab)
                                Forms\Components\Section::make('Photos')
                                    ->schema([
                                        Forms\Components\View::make('filament.forms.components.photo-gallery'),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible(),

                                Forms\Components\Section::make('AI Assistant')
                                    ->description('Generate SEO-optimized listing with Gemini AI')
                                    ->schema([
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('generate_ebay_all')
                                                ->label('Generate eBay Listing (Gemini)')
                                                ->icon('heroicon-m-sparkles')
                                                ->color('success')
                                                ->action(function ($set, $livewire) {
                                                    static::generateEbayListing($set, $livewire);
                                                }),
                                        ]),
                                    ])
                                    ->columnSpanFull()
                                    ->compact()
                                    ->collapsible(),

                                Forms\Components\Section::make('Product Listing')
                                    ->description('Edit the SEO-optimized listing for eBay and Shopify')
                                    ->schema([
                                        Forms\Components\TextInput::make('ebay_title')
                                            ->label('Title (English)')
                                            ->placeholder('e.g., Vintage Nike Air Max 90 Running Shoes Size 10')
                                            ->maxLength(80)
                                            ->helperText('Max 80 characters for eBay')
                                            ->columnSpanFull(),

                                        Forms\Components\Textarea::make('ebay_description')
                                            ->label('Description (English)')
                                            ->rows(10)
                                            ->placeholder('Detailed SEO-optimized product description...')
                                            ->helperText('SEO-optimized description')
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Select::make('ebay_condition')
                                                    ->label('Condition')
                                                    ->options([
                                                        'New with tags' => 'New with tags',
                                                        'New without tags' => 'New without tags',
                                                        'Pre-owned - Excellent' => 'Pre-owned - Excellent',
                                                        'Pre-owned - Good' => 'Pre-owned - Good',
                                                        'Pre-owned - Fair' => 'Pre-owned - Fair',
                                                    ])
                                                    ->default('Pre-owned - Good')
                                                    ->required(),

                                                Forms\Components\TextInput::make('ebay_brand')
                                                    ->label('Brand')
                                                    ->placeholder('e.g., Nike'),

                                                Forms\Components\Select::make('ebay_category')
                                                    ->label('eBay Category')
                                                    ->searchable()
                                                    ->options([
                                                        '11450' => 'Clothing, Shoes & Accessories',
                                                        '1059' => 'Men\'s Clothing',
                                                        '15724' => 'Women\'s Clothing',
                                                        '93427' => 'Men\'s Shoes',
                                                        '3034' => 'Women\'s Shoes',
                                                    ])
                                                    ->default('11450'),
                                            ]),

                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('ebay_size')
                                                    ->label('Size')
                                                    ->placeholder('e.g., L, 42, 10 US'),

                                                Forms\Components\TextInput::make('ebay_color')
                                                    ->label('Color')
                                                    ->placeholder('e.g., Black, Blue'),

                                                Forms\Components\TextInput::make('ebay_price')
                                                    ->label('Price (USD)')
                                                    ->numeric()
                                                    ->prefix('$')
                                                    ->default(0),
                                            ]),

                                        Forms\Components\TagsInput::make('ebay_tags')
                                            ->label('SEO Tags / Keywords')
                                            ->placeholder('vintage, retro, nike, running, sport')
                                            ->helperText('Keywords for better visibility on eBay and Shopify')
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),

                                Forms\Components\Section::make('Similar Products')
                                    ->description('Find similar products by barcode or title to copy listings')
                                    ->schema([
                                        Forms\Components\View::make('filament.forms.components.similar-products'),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->collapsed(),

                                Forms\Components\Section::make('Publish')
                                    ->schema([
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('publish_both')
                                                ->label('Publish to eBay & Shopify')
                                                ->icon('heroicon-o-rocket-launch')
                                                ->color('success')
                                                ->size('lg')
                                                ->requiresConfirmation()
                                                ->modalHeading('Publish Listing?')
                                                ->modalDescription('This will create listings on both eBay and Shopify.')
                                                ->action(function ($livewire) {
                                                    static::publishToMarketplaces($livewire);
                                                })
                                        ])->fullWidth(),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('30s') // Auto-refresh every 30 seconds
            ->columns([
                Tables\Columns\TextColumn::make('photos_preview')
                    ->label('')
                    ->getStateUsing(function (PhotoBatch $record): HtmlString {
                        $photo = $record->photos()->first();
                        if (!$photo) {
                            return new HtmlString('<span style="color: #999;">—</span>');
                        }
                        $url = asset('storage/' . $photo->image);
                        return new HtmlString('<img src="' . $url . '" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px;" />');
                    }),

                Tables\Columns\TextColumn::make('title')
                    ->label('Товар')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn(PhotoBatch $record): string => $record->title ?? '')
                    ->description(fn(PhotoBatch $record): ?string => $record->correlation_id),

                Tables\Columns\TextColumn::make('gg_labels')
                    ->label('Лейба')
                    ->getStateUsing(function (PhotoBatch $record): string {
                        $labels = $record->getGgLabels();
                        $ggOnly = array_filter($labels, fn($l) => str_starts_with($l, 'GG'));
                        return implode(', ', $ggOnly) ?: '—';
                    })
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('uploaded_at')
                    ->label('Дата')
                    ->dateTime('d.m H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pochtoy_status')
                    ->label('📦')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'success' => '✓',
                        'failed' => '✗',
                        default => '⏳',
                    })
                    ->tooltip(fn(PhotoBatch $record): ?string => $record->pochtoy_error),

                Tables\Columns\TextColumn::make('photos_count')
                    ->label('📷')
                    ->counts('photos')
                    ->sortable(),

                Tables\Columns\TextColumn::make('telegram_status')
                    ->label('📱')
                    ->getStateUsing(function (PhotoBatch $record): string {
                        $posts = \App\Models\TelegramPost::where('photo_batch_id', $record->id)->get();
                        if ($posts->isEmpty()) return '—';
                        $sent = $posts->where('status', 'sent')->count();
                        $sold = $posts->where('is_sold', true)->count();
                        if ($sold > 0) return "🔴 {$sold}";
                        if ($sent > 0) return "✓ {$sent}";
                        return "⏳ {$posts->count()}";
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_contains($state, '🔴') => 'danger',
                        str_contains($state, '✓') => 'success',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('uploaded_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'pending' => 'Ожидает',
                        'processed' => 'Обработано',
                        'published' => 'Опубликовано',
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
                Tables\Actions\Action::make('mark_sold')
                    ->label('Продано')
                    ->icon('heroicon-o-check-badge')
                    ->color('danger')
                    ->visible(function (PhotoBatch $record): bool {
                        return \App\Models\TelegramPost::where('photo_batch_id', $record->id)
                            ->where('status', 'sent')
                            ->where('is_sold', false)
                            ->exists();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Отметить как продано?')
                    ->modalDescription('Все посты в Telegram будут обновлены с пометкой "ПРОДАНО"')
                    ->action(function (PhotoBatch $record) {
                        $service = app(\App\Services\TelegramPostService::class);
                        $results = $service->markProductAsSold($record);

                        $updated = count(array_filter($results, fn($p) => $p->is_sold));

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title("Отмечено как продано: {$updated} постов")
                            ->send();
                    }),
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
            // No relations needed - tabs are in the main form
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

    protected static function generateAIDescription($set, $livewire, string $provider, ?string $model = null): void
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
            $result = $service->generateProductDescription($photoPaths, null, $model);
        } else {
            // OpenAI - use file paths directly
            $aiService = app(\App\Services\AIService::class)->setProvider('openai');
            $photoPaths = $photos->pluck('image')->toArray();
            $barcodes = collect($record->getAllBarcodes())->pluck('data')->toArray();
            $ggLabels = $record->getGgLabels();

            $result = $aiService->generateSummaryFromPaths($photoPaths, $barcodes, $ggLabels);

            if ($result) {
                // Apply fallback for declaration fields
                $result = static::generateDeclarationFallback($result);

                $set('title', $result['title'] ?? '');
                $set('description', $result['description'] ?? '');
                $set('brand', $result['brand'] ?? '');
                $set('category', $result['category'] ?? '');
                $set('color', $result['color'] ?? '');
                $set('size', $result['size'] ?? '');
                $set('condition', $result['condition'] ?? 'used');
                $set('price', $result['price_estimate'] ?? null);
                $set('declaration_en', $result['declaration_en'] ?? '');
                $set('declaration_ru', $result['declaration_ru'] ?? '');
                $set('declaration_sku', $result['declaration_sku'] ?? '');
                $set('declaration_has_battery', $result['declaration_has_battery'] ?? false);
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
                    'declaration_en' => $result['declaration_en'] ?? null,
                    'declaration_ru' => $result['declaration_ru'] ?? null,
                    'declaration_sku' => $result['declaration_sku'] ?? null,
                    'declaration_has_battery' => $result['declaration_has_battery'] ?? false,
                    'ai_summary' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ]);

                \Filament\Notifications\Notification::make()->title('Описание создано (OpenAI)')->success()->send();
                $livewire->dispatch('$refresh');
            } else {
                \Filament\Notifications\Notification::make()->title('Ошибка OpenAI')->danger()->send();
            }
            return;
        }

        if ($result) {
            // Apply fallback for declaration fields
            $result = static::generateDeclarationFallback($result);

            \Illuminate\Support\Facades\Log::info('Filament AI Generation - Declaration fields', [
                'declaration_en' => $result['declaration_en'] ?? 'NOT SET',
                'declaration_ru' => $result['declaration_ru'] ?? 'NOT SET',
                'declaration_sku' => $result['declaration_sku'] ?? 'NOT SET',
            ]);

            $set('title', $result['title'] ?? '');
            $set('description', $result['description'] ?? '');
            $set('brand', $result['brand'] ?? '');
            $set('category', $result['category'] ?? '');
            $set('color', $result['color'] ?? '');
            $set('size', $result['size'] ?? '');
            $set('condition', $result['condition'] ?? 'used');
            $set('price', $result['price_estimate'] ?? null);
            $set('declaration_en', $result['declaration_en'] ?? '');
            $set('declaration_ru', $result['declaration_ru'] ?? '');
            $set('declaration_sku', $result['declaration_sku'] ?? '');
            $set('declaration_has_battery', $result['declaration_has_battery'] ?? false);
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
                'declaration_en' => $result['declaration_en'] ?? null,
                'declaration_ru' => $result['declaration_ru'] ?? null,
                'declaration_sku' => $result['declaration_sku'] ?? null,
                'declaration_has_battery' => $result['declaration_has_battery'] ?? false,
                'ai_summary' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ]);

            \Filament\Notifications\Notification::make()->title('Описание создано (Gemini)')->success()->send();
            $livewire->dispatch('$refresh');
        } else {
            \Filament\Notifications\Notification::make()->title('Ошибка Gemini')->danger()->send();
        }
    }

    public static function generateEbayListing($set, $livewire): void
    {
        $record = $livewire->getRecord();
        if (!$record) return;

        $photos = $record->photos;
        if ($photos->isEmpty()) {
            \Filament\Notifications\Notification::make()->title('No photos')->warning()->send();
            return;
        }

        \Filament\Notifications\Notification::make()
            ->title('Generating eBay listing with Gemini...')
            ->info()
            ->send();

        set_time_limit(180);

        $service = new \App\Services\GeminiService();
        $photoPaths = $photos->pluck('image')->toArray();

        // Pass context from existing product data
        $productData = [
            'title' => $record->title,
            'brand' => $record->brand,
            'category' => $record->category,
            'condition' => $record->condition,
        ];

        $result = $service->generateEbayListing($photoPaths, $productData);

        if ($result) {
            // Map the results to form fields
            $set('ebay_title', $result['title'] ?? '');
            $set('ebay_description', $result['description'] ?? '');
            $set('ebay_brand', $result['brand'] ?? '');
            $set('ebay_category', $result['category'] ?? '11450');
            $set('ebay_condition', $result['condition'] ?? 'Pre-owned - Good');
            $set('ebay_size', $result['size'] ?? '');
            $set('ebay_color', $result['color'] ?? '');
            $set('ebay_price', $result['price_usd'] ?? 0);
            $set('ebay_tags', $result['tags'] ?? []);

            // Save to database
            $record->update([
                'ebay_title' => $result['title'] ?? null,
                'ebay_description' => $result['description'] ?? null,
                'ebay_brand' => $result['brand'] ?? null,
                'ebay_category' => $result['category'] ?? '11450',
                'ebay_condition' => $result['condition'] ?? 'Pre-owned - Good',
                'ebay_size' => $result['size'] ?? null,
                'ebay_color' => $result['color'] ?? null,
                'ebay_price' => $result['price_usd'] ?? 0,
                'ebay_tags' => $result['tags'] ?? [],
            ]);

            \Filament\Notifications\Notification::make()
                ->title('eBay listing generated successfully!')
                ->success()
                ->send();

            $livewire->dispatch('$refresh');
        } else {
            \Filament\Notifications\Notification::make()
                ->title('Failed to generate eBay listing')
                ->danger()
                ->send();
        }
    }

    /**
     * Generate fallback declaration fields from product data
     */
    protected static function generateDeclarationFallback(array $result): array
    {
        $title = $result['title'] ?? '';
        $category = $result['category'] ?? '';
        $brand = $result['brand'] ?? '';

        // If declaration fields are empty, generate from main fields
        if (empty($result['declaration_en']) || empty($result['declaration_ru'])) {
            // Detect item type from category/title for proper declaration
            $categoryLower = mb_strtolower($category);
            $titleLower = mb_strtolower($title);

            // Mapping for special categories (Pochtoy rules)
            $specialCategories = [
                // Bags, backpacks, wallets
                ['keywords' => ['сумка', 'рюкзак', 'кошелек', 'кошелёк', 'клатч', 'портмоне', 'bag', 'backpack', 'wallet', 'purse', 'clutch'],
                 'en' => 'Hand luggage accessories', 'ru' => 'Аксессуар для ручной клади'],
                // Vinyl records
                ['keywords' => ['винил', 'пластинк', 'vinyl', 'record', 'музык'],
                 'en' => 'Collectible music media', 'ru' => 'Музыкальные носители'],
                // Pet food
                ['keywords' => ['корм для', 'pet food', 'dog food', 'cat food', 'собак', 'кошек', 'животных корм'],
                 'en' => 'Food products', 'ru' => 'Продукты питания'],
                // Pet vitamins
                ['keywords' => ['витамины для животных', 'pet vitamins', 'добавки для собак', 'добавки для кошек'],
                 'en' => 'Vitamins supplements', 'ru' => 'Витамины добавки'],
                // Cosmetics
                ['keywords' => ['косметик', 'помада', 'тушь', 'тени', 'cosmetic', 'makeup', 'lipstick', 'mascara'],
                 'en' => 'Personal hygiene product', 'ru' => 'Средство личной гигиены'],
                // Toys
                ['keywords' => ['игрушк', 'toy', 'кукла', 'doll', 'lego', 'плюш'],
                 'en' => 'Board game', 'ru' => 'Настольная игра'],
            ];

            $foundSpecial = false;
            foreach ($specialCategories as $spec) {
                foreach ($spec['keywords'] as $keyword) {
                    if (str_contains($categoryLower, $keyword) || str_contains($titleLower, $keyword)) {
                        if (empty($result['declaration_en'])) {
                            $result['declaration_en'] = $spec['en'];
                        }
                        if (empty($result['declaration_ru'])) {
                            $result['declaration_ru'] = $spec['ru'];
                        }
                        $foundSpecial = true;
                        break 2;
                    }
                }
            }

            // If not special category, generate from title/category/brand
            if (!$foundSpecial) {
                // Count items in lot
                $itemCount = 1;
                if (preg_match('/(\d+)\s*(шт|pcs|items)/i', $title, $matches)) {
                    $itemCount = (int)$matches[1];
                } elseif (str_contains($titleLower, 'лот') || str_contains($titleLower, 'lot')) {
                    // Try to count items in lot title
                    $itemCount = substr_count($titleLower, ',') + substr_count($titleLower, ' и ') + substr_count($titleLower, ' and ') + 1;
                }

                // Detect gender
                $gender = '';
                $genderEn = '';
                if (str_contains($titleLower, 'женск') || str_contains($titleLower, 'woman') || str_contains($titleLower, 'women')) {
                    $gender = 'женской';
                    $genderEn = 'women';
                } elseif (str_contains($titleLower, 'мужск') || str_contains($titleLower, 'man') || str_contains($titleLower, 'men')) {
                    $gender = 'мужской';
                    $genderEn = 'men';
                } elseif (str_contains($titleLower, 'детск') || str_contains($titleLower, 'kid') || str_contains($titleLower, 'child')) {
                    $gender = 'детской';
                    $genderEn = 'kids';
                }

                // Detect clothing type
                $clothingTypes = [
                    ['keywords' => ['футболк', 't-shirt', 'tshirt', 'майк', 'топ'], 'ru' => 'футболка', 'en' => 't-shirt'],
                    ['keywords' => ['толстовк', 'худи', 'свитер', 'hoodie', 'sweater', 'sweatshirt'], 'ru' => 'толстовка', 'en' => 'sweatshirt'],
                    ['keywords' => ['куртк', 'jacket', 'пальто', 'coat'], 'ru' => 'куртка', 'en' => 'jacket'],
                    ['keywords' => ['джинс', 'jeans', 'брюк', 'pants', 'шорт', 'shorts'], 'ru' => 'штаны', 'en' => 'pants'],
                    ['keywords' => ['плать', 'dress', 'юбк', 'skirt'], 'ru' => 'платье', 'en' => 'dress'],
                    ['keywords' => ['кроссовк', 'sneaker', 'обув', 'shoe', 'ботинк', 'boot'], 'ru' => 'обувь', 'en' => 'footwear'],
                    ['keywords' => ['носк', 'sock', 'колготк', 'tights'], 'ru' => 'носки', 'en' => 'socks'],
                    ['keywords' => ['трус', 'underwear', 'бельё', 'белье', 'panties', 'briefs'], 'ru' => 'белье', 'en' => 'underwear'],
                    ['keywords' => ['шапк', 'hat', 'кепк', 'cap', 'бейсболк'], 'ru' => 'головной убор', 'en' => 'headwear'],
                ];

                $itemType = 'одежда';
                $itemTypeEn = 'clothing';

                foreach ($clothingTypes as $type) {
                    foreach ($type['keywords'] as $keyword) {
                        if (str_contains($titleLower, $keyword) || str_contains($categoryLower, $keyword)) {
                            $itemType = $type['ru'];
                            $itemTypeEn = $type['en'];
                            break 2;
                        }
                    }
                }

                // Generate declaration strings
                if (empty($result['declaration_ru'])) {
                    if ($itemCount > 1 || str_contains($titleLower, 'лот')) {
                        $result['declaration_ru'] = "лот {$gender} {$itemType} {$itemCount} шт.";
                    } else {
                        $result['declaration_ru'] = trim("{$gender} {$itemType}");
                    }
                    // Clean up double spaces
                    $result['declaration_ru'] = preg_replace('/\s+/', ' ', trim($result['declaration_ru']));
                }

                if (empty($result['declaration_en'])) {
                    if ($itemCount > 1 || str_contains($titleLower, 'лот') || str_contains($titleLower, 'lot')) {
                        $result['declaration_en'] = "{$genderEn} {$itemTypeEn} lot {$itemCount}";
                    } else {
                        $result['declaration_en'] = trim("{$genderEn} {$itemTypeEn}");
                    }
                    // Clean up double spaces
                    $result['declaration_en'] = preg_replace('/\s+/', ' ', trim($result['declaration_en']));
                }
            }
        }

        return $result;
    }

    public static function publishToMarketplaces($livewire): void
    {
        $record = $livewire->getRecord();
        if (!$record) return;

        // Validate required fields
        if (empty($record->ebay_title) || empty($record->ebay_description)) {
            \Filament\Notifications\Notification::make()
                ->title('Please generate listing first')
                ->body('You need to generate the eBay listing before publishing')
                ->warning()
                ->send();
            return;
        }

        \Filament\Notifications\Notification::make()
            ->title('Publishing to marketplaces...')
            ->info()
            ->send();

        $results = [
            'ebay' => false,
            'shopify' => false,
        ];

        // TODO: eBay API Integration
        // Requires eBay Trading API or Sell API with OAuth
        // Example endpoints:
        // - POST https://api.ebay.com/sell/inventory/v1/inventory_item
        // - POST https://api.ebay.com/sell/inventory/v1/offer
        //
        // For now, logging the data that would be sent
        \Illuminate\Support\Facades\Log::info('eBay listing data:', [
            'title' => $record->ebay_title,
            'description' => $record->ebay_description,
            'price' => $record->ebay_price,
            'condition' => $record->ebay_condition,
            'category' => $record->ebay_category,
            'photos' => $record->photos()->where('is_public', true)->pluck('image')->toArray(),
        ]);

        // TODO: Shopify API Integration
        // Requires Shopify Admin API with access token
        // Example endpoint:
        // - POST https://{shop}.myshopify.com/admin/api/2024-01/products.json
        //
        // For now, logging the data that would be sent
        \Illuminate\Support\Facades\Log::info('Shopify product data:', [
            'title' => $record->ebay_title,
            'body_html' => $record->ebay_description,
            'vendor' => $record->ebay_brand,
            'product_type' => $record->ebay_category,
            'tags' => implode(', ', $record->ebay_tags ?? []),
            'variants' => [
                [
                    'price' => $record->ebay_price,
                    'sku' => $record->sku ?? uniqid('SKU-'),
                ],
            ],
        ]);

        // Simulate success for now
        $results['ebay'] = true; // Set to true when API is implemented
        $results['shopify'] = true; // Set to true when API is implemented

        $successCount = array_sum($results);

        if ($successCount > 0) {
            $message = 'Published to: ' . implode(', ', array_keys(array_filter($results)));

            \Filament\Notifications\Notification::make()
                ->title('Publishing simulated')
                ->body($message . ' (API integration pending - check logs for data)')
                ->success()
                ->send();

            // Update record status or add marketplace IDs when APIs are ready
            // $record->update(['marketplace_status' => 'published']);
        } else {
            \Filament\Notifications\Notification::make()
                ->title('Publishing failed')
                ->body('Could not publish to any marketplace')
                ->danger()
                ->send();
        }

        // Публикация в Telegram каналы
        try {
            $telegramService = app(\App\Services\TelegramPostService::class);
            $telegramResults = $telegramService->publishProduct($record);

            $telegramSuccess = 0;
            $telegramFailed = 0;

            foreach ($telegramResults as $channelName => $post) {
                if ($post->status === 'sent') {
                    $telegramSuccess++;
                    \Filament\Notifications\Notification::make()
                        ->title("Telegram: {$channelName}")
                        ->body('Пост опубликован')
                        ->success()
                        ->send();
                } else {
                    $telegramFailed++;
                    \Filament\Notifications\Notification::make()
                        ->title("Telegram: {$channelName}")
                        ->body('Ошибка: ' . ($post->error_message ?? 'Unknown'))
                        ->danger()
                        ->send();
                }
            }

            if ($telegramSuccess > 0) {
                \Illuminate\Support\Facades\Log::info('Telegram posts published', [
                    'photo_batch_id' => $record->id,
                    'success' => $telegramSuccess,
                    'failed' => $telegramFailed,
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Telegram publishing failed', [
                'photo_batch_id' => $record->id,
                'error' => $e->getMessage(),
            ]);

            \Filament\Notifications\Notification::make()
                ->title('Telegram: Ошибка')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
