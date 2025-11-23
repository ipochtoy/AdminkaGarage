<div class="space-y-3">
    <div class="flex items-center justify-between">
        <h4 class="text-sm font-medium text-gray-300">Рекомендации по цене</h4>
        <button
            type="button"
            wire:click="dispatch('fetchPriceSuggestions')"
            class="text-xs bg-amber-600 hover:bg-amber-500 text-white px-3 py-1.5 rounded transition-colors"
        >
            <span wire:loading.remove wire:target="dispatch('fetchPriceSuggestions')">
                Получить цены
            </span>
            <span wire:loading wire:target="dispatch('fetchPriceSuggestions')">
                Загрузка...
            </span>
        </button>
    </div>

    @php
        $record = $getRecord();
        $suggestions = [];

        if ($record) {
            // Get AI price estimates from ai_summary
            $aiSummary = $record->ai_summary;
            if ($aiSummary) {
                $aiData = json_decode($aiSummary, true);
                if ($aiData) {
                    if (isset($aiData['price_estimate'])) {
                        $suggestions[] = [
                            'source' => 'AI оценка',
                            'price' => $aiData['price_estimate'],
                            'type' => 'ai'
                        ];
                    }
                    if (isset($aiData['price_min'])) {
                        $suggestions[] = [
                            'source' => 'AI мин',
                            'price' => $aiData['price_min'],
                            'type' => 'ai'
                        ];
                    }
                    if (isset($aiData['price_max'])) {
                        $suggestions[] = [
                            'source' => 'AI макс',
                            'price' => $aiData['price_max'],
                            'type' => 'ai'
                        ];
                    }
                }
            }

            // Get UPC prices from barcodes
            $barcodes = $record->getAllBarcodes();
            if (!empty($barcodes)) {
                $upcService = app(\App\Services\UPCService::class);
                foreach ($barcodes as $barcode) {
                    $prices = $upcService->getPriceSuggestions($barcode->data);
                    foreach ($prices as $key => $price) {
                        $suggestions[] = [
                            'source' => $price['source'] . ' (' . $barcode->data . ')',
                            'price' => $price['price'],
                            'link' => $price['link'] ?? null,
                            'type' => 'upc'
                        ];
                    }
                }
            }

            // Get eBay prices using Browse API
            $ebayService = app(\App\Services\EbayService::class);
            $barcode = !empty($barcodes) ? $barcodes[0]->data : null;
            
            // Try searching by barcode first, then by keyword
            $ebayItems = [];
            if ($barcode) {
                $ebayItems = $ebayService->searchByBarcode($barcode, 20);
            }
            if (empty($ebayItems) && $record->title) {
                $searchQuery = trim(($record->brand ?? '') . ' ' . ($record->title ?? ''));
                if ($searchQuery) {
                    $ebayItems = $ebayService->searchByKeyword($searchQuery, 20);
                }
            }

            if (!empty($ebayItems)) {
                $prices = array_map(fn($item) => (float)$item['price']['value'], $ebayItems);
                
                if (count($prices) > 0) {
                    sort($prices);
                    $minPrice = $prices[0];
                    $maxPrice = end($prices);
                    $medianPrice = $prices[floor(count($prices) / 2)];
                    
                    $suggestions[] = [
                        'source' => 'eBay мин',
                        'price' => $minPrice,
                        'type' => 'ebay'
                    ];
                    $suggestions[] = [
                        'source' => 'eBay медиана',
                        'price' => $medianPrice,
                        'type' => 'ebay'
                    ];
                    $suggestions[] = [
                        'source' => 'eBay макс',
                        'price' => $maxPrice,
                        'type' => 'ebay'
                    ];
                }
            }
        }
    @endphp

    @if(empty($suggestions))
        <p class="text-xs text-gray-500 italic">Нет данных о ценах. Сгенерируйте описание через AI или добавьте баркоды.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach($suggestions as $suggestion)
                @php
                    $bgClass = match($suggestion['type']) {
                        'ai' => 'bg-emerald-900/20 border-emerald-700/50',
                        'ebay' => 'bg-purple-900/20 border-purple-700/50',
                        default => 'bg-blue-900/20 border-blue-700/50',
                    };
                    $textClass = match($suggestion['type']) {
                        'ai' => 'text-emerald-400',
                        'ebay' => 'text-purple-400',
                        default => 'text-blue-400',
                    };
                @endphp
                <div
                    class="p-2 rounded border {{ $bgClass }} cursor-pointer hover:opacity-80 transition-opacity"
                    x-data
                    x-on:click="
                        const input = document.getElementById('data.price');
                        if (input) {
                            input.value = {{ $suggestion['price'] }};
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        } else {
                            const inputs = document.querySelectorAll('[wire\\:model\\.live=\'data.price\'], [wire\\:model=\'data.price\']');
                            inputs.forEach(el => {
                                el.value = {{ $suggestion['price'] }};
                                el.dispatchEvent(new Event('input', { bubbles: true }));
                            });
                        }
                        $wire.set('data.price', {{ $suggestion['price'] }});
                    "
                    title="Нажмите чтобы установить эту цену"
                >
                    <div class="text-xs text-gray-400">{{ $suggestion['source'] }}</div>
                    <div class="text-lg font-bold {{ $textClass }}">
                        ${{ number_format($suggestion['price'], 2) }}
                    </div>
                    @if(isset($suggestion['link']) && $suggestion['link'])
                        <a href="{{ $suggestion['link'] }}" target="_blank" class="text-xs text-blue-400 hover:underline" onclick="event.stopPropagation();">
                            Посмотреть →
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        @if(count($suggestions) > 0)
            @php
                $avgPrice = collect($suggestions)->avg('price');
            @endphp
            <div class="mt-2 p-2 bg-amber-900/30 border border-amber-700/50 rounded">
                <div class="text-xs text-amber-300">Средняя цена</div>
                <div class="text-xl font-bold text-amber-400">${{ number_format($avgPrice, 2) }}</div>
            </div>
        @endif
    @endif
</div>
