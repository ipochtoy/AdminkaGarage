@php
    $record = $getRecord();
    $photos = $record->photos ?? collect();
@endphp

<div class="w-full">
    @if($photos->count() > 0)
        <div class="space-y-4">
            @foreach($photos as $photo)
                <div class="rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                    <img src="{{ asset('storage/' . $photo->image_path) }}" alt="{{ $record->title ?? '' }}"
                        class="w-full h-auto object-cover" style="max-height: 600px; min-height: 300px;" />
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 text-gray-500">
            Нет фотографий
        </div>
    @endif
</div>
