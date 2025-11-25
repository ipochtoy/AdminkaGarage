<div class="space-y-2">

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
        <div class="flex flex-wrap gap-2">
            @foreach($suggestions as $suggestion)
                @php
                    $bgClass = match($suggestion['type']) {
                        'ai' => 'bg-emerald-900/20 border-emerald-700/50 hover:bg-emerald-900/30',
                        'ebay' => 'bg-purple-900/20 border-purple-700/50 hover:bg-purple-900/30',
                        default => 'bg-blue-900/20 border-blue-700/50 hover:bg-blue-900/30',
                    };
                    $textClass = match($suggestion['type']) {
                        'ai' => 'text-emerald-400',
                        'ebay' => 'text-purple-400',
                        default => 'text-blue-400',
                    };
                    $icon = match($suggestion['type']) {
                        'ai' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 22.5l-.394-1.933a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"></path></svg>',
                        'ebay' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M7.5 21c1.9 0 3.6-1 4.5-2.6.9 1.6 2.6 2.6 4.5 2.6 2.9 0 5.2-2.3 5.2-5.2V8.3c0-2.9-2.3-5.2-5.2-5.2-1.9 0-3.6 1-4.5 2.6C11.1 4.1 9.4 3.1 7.5 3.1c-2.9 0-5.2 2.3-5.2 5.2v7.5c0 2.9 2.3 5.2 5.2 5.2zm0-15.8c1.8 0 3.2 1.4 3.2 3.2v7.5c0 1.8-1.4 3.2-3.2 3.2s-3.2-1.4-3.2-3.2V8.4c0-1.8 1.4-3.2 3.2-3.2zm9 0c1.8 0 3.2 1.4 3.2 3.2v7.5c0 1.8-1.4 3.2-3.2 3.2s-3.2-1.4-3.2-3.2V8.4c0-1.8 1.4-3.2 3.2-3.2z"></path></svg>',
                        default => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path></svg>',
                    };
                @endphp
                <div
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border {{ $bgClass }} cursor-pointer transition-all duration-200 hover:scale-105"
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
                    title="Нажмите чтобы установить: {{ $suggestion['source'] }}"
                >
                    <div class="{{ $textClass }}">
                        {!! $icon !!}
                    </div>
                    <div class="flex flex-col">
                        <div class="text-xs text-gray-400 leading-tight">{{ str_replace(['AI оценка', 'AI мин', 'AI макс', 'eBay мин', 'eBay медиана', 'eBay макс'], ['AI', 'AI min', 'AI max', 'eBay min', 'eBay avg', 'eBay max'], $suggestion['source']) }}</div>
                        <div class="text-sm font-bold {{ $textClass }} leading-tight">
                            ${{ number_format($suggestion['price'], 2) }}
                        </div>
                    </div>
                    @if(isset($suggestion['link']) && $suggestion['link'])
                        <a href="{{ $suggestion['link'] }}" target="_blank" class="text-gray-400 hover:text-blue-400 ml-1" onclick="event.stopPropagation();">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>
                    @endif
                </div>
            @endforeach

            @if(count($suggestions) > 0)
                @php
                    $avgPrice = collect($suggestions)->avg('price');
                @endphp
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border bg-amber-900/30 border-amber-700/50">
                    <div class="text-amber-400">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"></path></svg>
                    </div>
                    <div class="flex flex-col">
                        <div class="text-xs text-amber-300 leading-tight">Средняя</div>
                        <div class="text-sm font-bold text-amber-400 leading-tight">${{ number_format($avgPrice, 2) }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
