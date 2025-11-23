<div class="p-4 bg-gray-900 rounded-lg">
    @php
        // Use form data if available, otherwise fall back to record
        $title = $formData['title'] ?? $record->title ?? null;
        $price = $formData['price'] ?? $record->price ?? null;
        $condition = $formData['condition'] ?? $record->condition ?? null;
        $brand = $formData['brand'] ?? $record->brand ?? null;
        $category = $formData['category'] ?? $record->category ?? null;
        $size = $formData['size'] ?? $record->size ?? null;
        $color = $formData['color'] ?? $record->color ?? null;
        $sku = $formData['sku'] ?? $record->sku ?? null;
        $quantity = $formData['quantity'] ?? $record->quantity ?? null;
        $description = $formData['description'] ?? $record->description ?? null;
    @endphp

    @if($record)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Photos --}}
            <div>
                @php
                    $mainPhoto = $record->photos()->where('is_main', true)->first() ?? $record->photos()->first();
                    $otherPhotos = $record->photos()->where('id', '!=', $mainPhoto?->id)->get();
                @endphp

                @if($mainPhoto)
                    <div class="mb-4">
                        <img src="{{ asset('storage/' . $mainPhoto->image) }}"
                             class="w-full rounded-lg shadow-lg"
                             style="max-height: 400px; object-fit: contain; background: #f3f4f6;">
                    </div>
                @endif

                @if($otherPhotos->count() > 0)
                    <div class="flex gap-2 flex-wrap">
                        @foreach($otherPhotos->take(4) as $photo)
                            <img src="{{ asset('storage/' . $photo->image) }}"
                                 class="w-16 h-16 rounded object-cover border border-gray-600">
                        @endforeach
                        @if($otherPhotos->count() > 4)
                            <div class="w-16 h-16 rounded bg-gray-700 flex items-center justify-center text-gray-400 text-xs">
                                +{{ $otherPhotos->count() - 4 }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div class="space-y-4">
                {{-- Title --}}
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $title ?? 'Без названия' }}
                </h2>

                {{-- Price --}}
                @if($price)
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400">
                        ${{ number_format((float)$price, 2) }}
                    </div>
                @else
                    <div class="text-xl text-gray-500 italic">
                        Цена не указана
                    </div>
                @endif

                {{-- Condition --}}
                @if($condition)
                    <div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $condition === 'new' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400' }}">
                            {{ $condition === 'new' ? 'Новое' : 'Б/у' }}
                        </span>
                    </div>
                @endif

                {{-- Attributes --}}
                <div class="grid grid-cols-2 gap-3 text-sm">
                    @if($brand)
                        <div>
                            <span class="text-gray-500">Бренд:</span>
                            <span class="text-gray-900 dark:text-white ml-1">{{ $brand }}</span>
                        </div>
                    @endif
                    @if($category)
                        <div>
                            <span class="text-gray-500">Категория:</span>
                            <span class="text-gray-900 dark:text-white ml-1">{{ $category }}</span>
                        </div>
                    @endif
                    @if($size)
                        <div>
                            <span class="text-gray-500">Размер:</span>
                            <span class="text-gray-900 dark:text-white ml-1">{{ $size }}</span>
                        </div>
                    @endif
                    @if($color)
                        <div>
                            <span class="text-gray-500">Цвет:</span>
                            <span class="text-gray-900 dark:text-white ml-1">{{ $color }}</span>
                        </div>
                    @endif
                    @if($sku)
                        <div>
                            <span class="text-gray-500">SKU:</span>
                            <span class="text-gray-900 dark:text-white ml-1">{{ $sku }}</span>
                        </div>
                    @endif
                    @if($quantity)
                        <div>
                            <span class="text-gray-500">Кол-во:</span>
                            <span class="text-gray-900 dark:text-white ml-1">{{ $quantity }}</span>
                        </div>
                    @endif
                </div>

                {{-- Description --}}
                @if($description)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-2">Описание</h3>
                        <p class="text-gray-700 dark:text-gray-300 text-sm whitespace-pre-line">{{ $description }}</p>
                    </div>
                @endif

                {{-- Barcodes --}}
                @php
                    $barcodes = $record->getAllBarcodes();
                    $ggLabels = $record->getGgLabels();
                @endphp

                @if(!empty($ggLabels) || !empty($barcodes))
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-400 mb-2">Коды</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_unique($ggLabels) as $label)
                                @if(str_starts_with($label, 'GG') || str_starts_with($label, 'Q'))
                                    <span class="px-2 py-1 text-xs bg-orange-500/20 text-orange-400 rounded">
                                        {{ $label }}
                                    </span>
                                @endif
                            @endforeach
                            @foreach($barcodes as $barcode)
                                <span class="px-2 py-1 text-xs bg-gray-700 text-gray-300 rounded">
                                    {{ $barcode->data }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="text-center text-gray-500 py-8">
            Нет данных для предпросмотра
        </div>
    @endif
</div>
