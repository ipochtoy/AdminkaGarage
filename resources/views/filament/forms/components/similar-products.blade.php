@php
    use App\Models\PhotoBatch;
    use App\Models\BarcodeResult;

    $currentRecord = $this->getRecord();

    // Get barcodes from current batch
    $barcodes = [];
    if ($currentRecord && $currentRecord->photos) {
        foreach ($currentRecord->photos as $photo) {
            foreach ($photo->barcodes as $barcode) {
                $barcodes[] = $barcode->data;
            }
        }
    }
    $barcodes = array_unique($barcodes);

    // Search similar products by barcodes and title
    $similarProducts = collect();

    // Search by barcodes
    if (!empty($barcodes)) {
        $similarByBarcode = PhotoBatch::where('id', '!=', $currentRecord?->id)
            ->where('status', 'published')
            ->whereHas('photos.barcodes', function($query) use ($barcodes) {
                $query->whereIn('data', $barcodes);
            })
            ->with('photos')
            ->limit(10)
            ->get();
        $similarProducts = $similarProducts->merge($similarByBarcode);
    }

    // Search by title (if we have title)
    if ($currentRecord && $currentRecord->title) {
        $titleWords = explode(' ', $currentRecord->title);
        $mainWords = array_filter($titleWords, function($word) {
            return strlen($word) > 3; // Only words longer than 3 chars
        });

        if (!empty($mainWords)) {
            $similarByTitle = PhotoBatch::where('id', '!=', $currentRecord->id)
                ->where('status', 'published')
                ->where(function($query) use ($mainWords) {
                    foreach ($mainWords as $word) {
                        $query->orWhere('title', 'like', '%' . $word . '%');
                    }
                })
                ->with('photos')
                ->limit(5)
                ->get();
            $similarProducts = $similarProducts->merge($similarByTitle);
        }
    }

    $similarProducts = $similarProducts->unique('id')->take(15);
@endphp

<div x-data="{
    searchQuery: '',
    searchResults: @js($similarProducts->toArray())
}" class="space-y-4">

    {{-- Search Input --}}
    <div class="flex gap-2">
        <input
            type="text"
            x-model="searchQuery"
            placeholder="Search by barcode or title..."
            class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 dark:focus:border-primary-600 dark:focus:ring-primary-700"
        />
        <button
            type="button"
            @click="alert('Search functionality will be implemented')"
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition"
        >
            Search
        </button>
    </div>

    @if($similarProducts->isEmpty())
        <div class="text-sm text-gray-500 dark:text-gray-400 italic py-4 text-center">
            No similar products found. Products will appear here once you have published items with matching barcodes or titles.
        </div>
    @else
        <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">
            Found {{ $similarProducts->count() }} similar products
        </div>

        {{-- Similar Products Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($similarProducts as $product)
                @php
                    $firstPhoto = $product->photos->first();
                    $photoUrl = $firstPhoto ? asset('storage/' . $firstPhoto->image) : null;
                @endphp

                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md transition cursor-pointer"
                     wire:click="copyListing({{ $product->id }})"
                     x-on:click="
                        // Copy listing data to current form
                        if (confirm('Copy listing data from this product?')) {
                            @this.set('data.ebay_title', '{{ addslashes($product->ebay_title ?? $product->title) }}');
                            @this.set('data.ebay_description', '{{ addslashes($product->ebay_description ?? $product->description) }}');
                            @this.set('data.ebay_brand', '{{ addslashes($product->ebay_brand ?? $product->brand) }}');
                            @this.set('data.ebay_size', '{{ addslashes($product->ebay_size ?? $product->size) }}');
                            @this.set('data.ebay_color', '{{ addslashes($product->ebay_color ?? $product->color) }}');
                            @this.set('data.ebay_price', {{ $product->ebay_price ?? $product->price ?? 0 }});
                            @this.set('data.ebay_condition', '{{ $product->ebay_condition ?? $product->condition ?? 'Pre-owned - Good' }}');
                            @this.set('data.ebay_category', '{{ $product->ebay_category ?? '11450' }}');

                            new FilamentNotification()
                                .title('Listing data copied!')
                                .success()
                                .send();
                        }
                     "
                >
                    <div class="flex gap-3">
                        {{-- Photo --}}
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}"
                                 alt="Product"
                                 class="w-20 h-20 object-cover rounded-md flex-shrink-0"
                            />
                        @else
                            <div class="w-20 h-20 bg-gray-200 dark:bg-gray-700 rounded-md flex items-center justify-center flex-shrink-0">
                                <span class="text-gray-400 text-xs">No photo</span>
                            </div>
                        @endif

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate mb-1">
                                {{ Str::limit($product->title, 50) }}
                            </h4>

                            @if($product->brand)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                    {{ $product->brand }}
                                </p>
                            @endif

                            <div class="flex items-center gap-2 text-xs">
                                @if($product->price)
                                    <span class="font-semibold text-green-600 dark:text-green-400">
                                        ${{ number_format($product->price, 2) }}
                                    </span>
                                @endif

                                @if($product->ebay_title)
                                    <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-xs">
                                        eBay
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Copy Button --}}
                    <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            class="text-xs text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium w-full text-center"
                        >
                            📋 Copy Listing
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
    /* Ensure AlpineJS x-cloak works */
    [x-cloak] { display: none !important; }
</style>
