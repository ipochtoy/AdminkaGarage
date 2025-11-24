<x-filament-panels::page>
    <form wire:submit="publish">
        {{ $this->form }}

        <div class="mt-6 flex gap-4">
            <x-filament::button type="submit" size="lg" color="success" icon="heroicon-o-arrow-up-tray">
                Опубликовать на выбранные платформы
            </x-filament::button>

            @if($this->photoBatch)
                <x-filament::button
                    tag="a"
                    href="{{ route('filament.admin.resources.photo-batches.edit', $this->photoBatch) }}"
                    color="gray"
                    icon="heroicon-o-arrow-left"
                >
                    Назад к карточке
                </x-filament::button>
            @endif
        </div>
    </form>

    @if($this->photoBatch)
        <div class="mt-8">
            <h3 class="text-lg font-medium mb-4">Текущие публикации</h3>
            @php
                $listings = $this->photoBatch->listings;
            @endphp

            @if($listings->isEmpty())
                <p class="text-gray-500">Товар ещё не опубликован ни на одной платформе</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($listings as $listing)
                        <div class="p-4 rounded-lg border {{ $listing->status === 'published' ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : ($listing->status === 'failed' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 bg-gray-50 dark:bg-gray-800') }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium">
                                    {{ \App\Models\ProductListing::platforms()[$listing->platform] ?? $listing->platform }}
                                </span>
                                <span class="text-xs px-2 py-1 rounded-full {{ $listing->status === 'published' ? 'bg-green-500 text-white' : ($listing->status === 'failed' ? 'bg-red-500 text-white' : 'bg-gray-400 text-white') }}">
                                    {{ \App\Models\ProductListing::statuses()[$listing->status] ?? $listing->status }}
                                </span>
                            </div>

                            @if($listing->listed_price)
                                <p class="text-sm text-gray-600 dark:text-gray-400">${{ number_format($listing->listed_price, 2) }}</p>
                            @endif

                            @if($listing->external_url)
                                <a href="{{ $listing->external_url }}" target="_blank" class="text-sm text-blue-500 hover:underline">
                                    Открыть на платформе →
                                </a>
                            @endif

                            @if($listing->error_message)
                                <p class="text-xs text-red-500 mt-2">{{ $listing->error_message }}</p>
                            @endif

                            @if($listing->published_at)
                                <p class="text-xs text-gray-400 mt-2">
                                    Опубликовано: {{ $listing->published_at->format('d.m.Y H:i') }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</x-filament-panels::page>
