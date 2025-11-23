<x-filament-panels::page>
    <div class="mb-4 flex gap-2">
        @if(count($selected) > 0)
            <x-filament::button wire:click="createBatch" color="success" size="sm">
                Создать карточку ({{ count($selected) }})
            </x-filament::button>
            <x-filament::button wire:click="deleteSelected" color="danger" size="sm">
                Удалить ({{ count($selected) }})
            </x-filament::button>
        @endif
        @if($lastBatchId)
            <x-filament::button wire:click="undoLastBatch" color="warning" size="sm">
                Отменить последнюю
            </x-filament::button>
        @endif
    </div>

    <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 8px;">
        @foreach($this->photos as $photo)
            @php
                $borderColor = in_array($photo->id, $selected) ? '#3b82f6' : ($photo->gg_label ? '#f59e0b' : '#e5e7eb');
            @endphp
            <div
                wire:click="toggleSelect({{ $photo->id }})"
                style="position: relative; cursor: pointer; border-radius: 8px; overflow: hidden; border: 3px solid {{ $borderColor }}; aspect-ratio: 1;"
            >
                <img
                    src="{{ Storage::disk('public')->url($photo->image) }}"
                    style="width: 100%; height: 100%; object-fit: cover;"
                    loading="lazy"
                >
                @if(in_array($photo->id, $selected))
                    <div style="position: absolute; top: 4px; right: 4px; background: #3b82f6; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                        ✓
                    </div>
                @endif
                @if($photo->processed)
                    <div style="position: absolute; bottom: 4px; left: 4px; background: #22c55e; color: white; border-radius: 4px; padding: 2px 4px; font-size: 10px;">
                        ✓
                    </div>
                @endif
                @if($photo->gg_label)
                    <div style="position: absolute; bottom: 4px; right: 4px; background: #f59e0b; color: white; border-radius: 4px; padding: 2px 4px; font-size: 9px; font-weight: bold;">
                        {{ $photo->gg_label }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $this->photos->links() }}
    </div>
</x-filament-panels::page>
