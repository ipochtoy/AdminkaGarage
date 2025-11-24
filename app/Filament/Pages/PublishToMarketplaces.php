<?php

namespace App\Filament\Pages;

use App\Models\PhotoBatch;
use App\Models\ProductListing;
use App\Services\Marketplaces\ListingOrchestrator;
use App\Services\Marketplaces\EbayMarketplaceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class PublishToMarketplaces extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Публикация';
    protected static ?string $title = 'Публикация на маркетплейсы';
    protected static ?string $navigationGroup = 'Продажи';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.publish-to-marketplaces';

    public ?int $photoBatchId = null;
    public ?PhotoBatch $photoBatch = null;

    // Form data
    public ?array $data = [];

    // Platform selections
    public bool $publishToPochtoy = true;
    public bool $publishToEbay = false;
    public bool $publishToShopify = false;

    // Common fields
    public ?string $productTitle = '';
    public ?string $productDescription = '';
    public ?float $price = null;
    public ?string $condition = 'used';
    public ?string $brand = '';
    public ?string $category = '';
    public ?string $size = '';
    public ?string $color = '';
    public int $quantity = 1;

    // eBay specific
    public ?string $ebay_category_id = '11450'; // Default: Clothing
    public ?string $ebay_condition_id = '3000'; // Default: Used
    public ?string $ebay_listing_format = 'FIXED_PRICE';
    public ?array $ebay_item_specifics = [];

    // Shopify specific
    public ?string $shopify_product_type = '';
    public ?array $shopify_tags = [];

    public function mount(): void
    {
        $this->photoBatchId = request()->query('batch');

        if ($this->photoBatchId) {
            $this->photoBatch = PhotoBatch::with('photos')->find($this->photoBatchId);

            if ($this->photoBatch) {
                $this->productTitle = $this->photoBatch->title ?? '';
                $this->productDescription = $this->photoBatch->description ?? '';
                $this->price = $this->photoBatch->price;
                $this->condition = $this->photoBatch->condition ?? 'used';
                $this->brand = $this->photoBatch->brand ?? '';
                $this->category = $this->photoBatch->category ?? '';
                $this->size = $this->photoBatch->size ?? '';
                $this->color = $this->photoBatch->color ?? '';
                $this->quantity = $this->photoBatch->quantity ?? 1;

                // Set eBay item specifics from batch data
                $this->ebay_item_specifics = [
                    'Brand' => $this->brand,
                    'Size' => $this->size,
                    'Color' => $this->color,
                ];

                // Shopify
                $this->shopify_product_type = $this->category;
                $this->shopify_tags = array_filter([$this->brand, $this->category, $this->condition]);
            }
        }

        $this->form->fill([
            'title' => $this->productTitle,
            'description' => $this->productDescription,
            'price' => $this->price,
            'condition' => $this->condition,
            'brand' => $this->brand,
            'category' => $this->category,
            'size' => $this->size,
            'color' => $this->color,
            'quantity' => $this->quantity,
            'publishToPochtoy' => $this->publishToPochtoy,
            'publishToEbay' => $this->publishToEbay,
            'publishToShopify' => $this->publishToShopify,
            'ebay_category_id' => $this->ebay_category_id,
            'ebay_condition_id' => $this->ebay_condition_id,
            'ebay_listing_format' => $this->ebay_listing_format,
            'ebay_item_specifics' => $this->ebay_item_specifics,
            'shopify_product_type' => $this->shopify_product_type,
            'shopify_tags' => $this->shopify_tags,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Product Selection
                Forms\Components\Section::make('Выбор товара')
                    ->schema([
                        Forms\Components\Select::make('photoBatchId')
                            ->label('Карточка товара')
                            ->options(
                                PhotoBatch::whereNotNull('title')
                                    ->orderByDesc('created_at')
                                    ->limit(100)
                                    ->pluck('title', 'id')
                            )
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                if ($state) {
                                    return redirect()->route('filament.admin.pages.publish-to-marketplaces', ['batch' => $state]);
                                }
                            }),

                        Forms\Components\Placeholder::make('photos_preview')
                            ->label('Фото')
                            ->content(function () {
                                if (!$this->photoBatch) {
                                    return 'Выберите карточку товара';
                                }

                                $photos = $this->photoBatch->photos;
                                if ($photos->isEmpty()) {
                                    return 'Нет фото';
                                }

                                $html = '<div class="flex gap-2 flex-wrap">';
                                foreach ($photos->take(6) as $photo) {
                                    $url = asset('storage/' . $photo->image);
                                    $html .= "<img src='{$url}' class='w-20 h-20 object-cover rounded border' />";
                                }
                                if ($photos->count() > 6) {
                                    $remaining = $photos->count() - 6;
                                    $html .= "<span class='flex items-center text-sm text-gray-500'>+{$remaining} ещё</span>";
                                }
                                $html .= '</div>';
                                return new HtmlString($html);
                            })
                            ->visible(fn() => $this->photoBatch !== null),
                    ]),

                // Platform Selection
                Forms\Components\Section::make('Выбор платформ')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Toggle::make('publishToPochtoy')
                                ->label('Бой гаража')
                                ->default(true)
                                ->live(),
                            Forms\Components\Toggle::make('publishToEbay')
                                ->label('eBay')
                                ->default(false)
                                ->live(),
                            Forms\Components\Toggle::make('publishToShopify')
                                ->label('Shopify')
                                ->default(false)
                                ->live(),
                        ]),
                    ]),

                // Common Product Data
                Forms\Components\Section::make('Данные товара')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название')
                            ->required()
                            ->maxLength(500),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(4),

                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\TextInput::make('price')
                                ->label('Цена')
                                ->numeric()
                                ->prefix('$')
                                ->required(),

                            Forms\Components\Select::make('condition')
                                ->label('Состояние')
                                ->options([
                                    'new' => 'Новое',
                                    'like_new' => 'Как новое',
                                    'used' => 'Б/у',
                                    'refurbished' => 'Восстановленное',
                                ]),

                            Forms\Components\TextInput::make('brand')
                                ->label('Бренд'),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Кол-во')
                                ->numeric()
                                ->default(1),
                        ]),

                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('category')
                                ->label('Категория'),
                            Forms\Components\TextInput::make('size')
                                ->label('Размер'),
                            Forms\Components\TextInput::make('color')
                                ->label('Цвет'),
                        ]),
                    ]),

                // eBay Settings
                Forms\Components\Section::make('Настройки eBay')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('ebay_category_id')
                                ->label('Категория eBay')
                                ->options([
                                    '11450' => 'Clothing, Shoes & Accessories',
                                    '11484' => "Men's Clothing",
                                    '15724' => "Women's Clothing",
                                    '171146' => 'Athletic Shoes',
                                    '3034' => 'Sunglasses',
                                    '169291' => 'Bags & Handbags',
                                    '4251' => 'Jewelry',
                                    '281' => 'Computers & Tablets',
                                    '15032' => 'Cell Phones',
                                ])
                                ->searchable()
                                ->required(),

                            Forms\Components\Select::make('ebay_condition_id')
                                ->label('Состояние (eBay)')
                                ->options([
                                    '1000' => 'New with tags',
                                    '1500' => 'New without tags',
                                    '2500' => 'New with defects',
                                    '3000' => 'Pre-owned',
                                    '4000' => 'Very Good',
                                    '5000' => 'Good',
                                    '6000' => 'Acceptable',
                                ])
                                ->required(),
                        ]),

                        Forms\Components\Select::make('ebay_listing_format')
                            ->label('Формат листинга')
                            ->options([
                                'FIXED_PRICE' => 'Фиксированная цена (Buy It Now)',
                                'AUCTION' => 'Аукцион',
                            ])
                            ->default('FIXED_PRICE'),

                        Forms\Components\Repeater::make('ebay_item_specifics')
                            ->label('Item Specifics (характеристики)')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Название')
                                    ->required(),
                                Forms\Components\TextInput::make('value')
                                    ->label('Значение')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Добавить характеристику')
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => ($state['name'] ?? '') . ': ' . ($state['value'] ?? '')),

                        Forms\Components\Placeholder::make('ebay_required_specifics')
                            ->label('')
                            ->content(new HtmlString('
                                <div class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-800 p-3 rounded">
                                    <strong>Рекомендуемые характеристики для одежды:</strong><br>
                                    Brand, Size, Color, Material, Style, Department, Type, Pattern, Sleeve Length, Neckline
                                </div>
                            ')),
                    ])
                    ->visible(fn($get) => $get('publishToEbay'))
                    ->collapsible(),

                // Shopify Settings
                Forms\Components\Section::make('Настройки Shopify')
                    ->schema([
                        Forms\Components\TextInput::make('shopify_product_type')
                            ->label('Тип продукта')
                            ->placeholder('Clothing, Shoes, Accessories...'),

                        Forms\Components\TagsInput::make('shopify_tags')
                            ->label('Теги')
                            ->placeholder('Добавить тег...')
                            ->separator(','),
                    ])
                    ->visible(fn($get) => $get('publishToShopify'))
                    ->collapsible(),

                // Pochtoy Settings
                Forms\Components\Section::make('Настройки Бой гаража')
                    ->schema([
                        Forms\Components\Placeholder::make('pochtoy_info')
                            ->label('')
                            ->content(function () {
                                if (!$this->photoBatch) {
                                    return 'Выберите карточку товара';
                                }

                                $ggLabels = $this->photoBatch->getGgLabels();
                                $barcodes = collect($this->photoBatch->getAllBarcodes())->pluck('data')->toArray();

                                $html = '<div class="space-y-2">';
                                if (!empty($ggLabels)) {
                                    $html .= '<div><strong>GG метки:</strong> ' . implode(', ', $ggLabels) . '</div>';
                                }
                                if (!empty($barcodes)) {
                                    $html .= '<div><strong>Штрихкоды:</strong> ' . implode(', ', array_slice($barcodes, 0, 5));
                                    if (count($barcodes) > 5) {
                                        $html .= ' и ещё ' . (count($barcodes) - 5);
                                    }
                                    $html .= '</div>';
                                }
                                $html .= '</div>';
                                return new HtmlString($html);
                            }),
                    ])
                    ->visible(fn($get) => $get('publishToPochtoy'))
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function publish(): void
    {
        $data = $this->form->getState();

        if (!$this->photoBatch) {
            Notification::make()
                ->title('Выберите карточку товара')
                ->warning()
                ->send();
            return;
        }

        // Update batch with form data
        $this->photoBatch->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'price' => $data['price'],
            'condition' => $data['condition'],
            'brand' => $data['brand'],
            'category' => $data['category'],
            'size' => $data['size'],
            'color' => $data['color'],
            'quantity' => $data['quantity'],
        ]);

        $orchestrator = new ListingOrchestrator();
        $platforms = [];
        $results = [];

        // Prepare platform-specific options
        $options = [
            'price' => $data['price'],
        ];

        if ($data['publishToPochtoy']) {
            $platforms[] = 'pochtoy';
        }

        if ($data['publishToEbay']) {
            $platforms[] = 'ebay';
            $options['ebay'] = [
                'category_id' => $data['ebay_category_id'],
                'condition_id' => $data['ebay_condition_id'],
                'listing_format' => $data['ebay_listing_format'],
                'item_specifics' => $this->formatItemSpecifics($data['ebay_item_specifics'] ?? []),
            ];
        }

        if ($data['publishToShopify']) {
            $platforms[] = 'shopify';
            $options['shopify'] = [
                'product_type' => $data['shopify_product_type'],
                'tags' => $data['shopify_tags'],
            ];
        }

        if (empty($platforms)) {
            Notification::make()
                ->title('Выберите хотя бы одну платформу')
                ->warning()
                ->send();
            return;
        }

        $results = $orchestrator->publishToMultiple($this->photoBatch, $platforms, $options);

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($results as $platform => $result) {
            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
                $errors[] = ProductListing::platforms()[$platform] . ': ' . ($result['error'] ?? 'Unknown error');
            }
        }

        if ($successCount > 0) {
            Notification::make()
                ->title("Опубликовано на {$successCount} платформ(ы)")
                ->success()
                ->send();
        }

        if ($failedCount > 0) {
            Notification::make()
                ->title("Ошибки публикации: {$failedCount}")
                ->body(implode("\n", $errors))
                ->danger()
                ->persistent()
                ->send();
        }
    }

    protected function formatItemSpecifics(array $specifics): array
    {
        $formatted = [];
        foreach ($specifics as $item) {
            if (!empty($item['name']) && !empty($item['value'])) {
                $formatted[$item['name']] = [$item['value']];
            }
        }
        return $formatted;
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('publish')
                ->label('Опубликовать')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->size('lg')
                ->action('publish'),
        ];
    }
}
