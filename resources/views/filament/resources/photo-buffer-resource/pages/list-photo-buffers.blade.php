<x-filament-panels::page>
    <style>
        .photo-preview-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: zoom-out;
        }
        .photo-preview-modal img {
            max-width: 90vw;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
        }
        .photo-preview-close {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 32px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .photo-preview-close:hover {
            opacity: 1;
        }
        .magic-badge {
            position: absolute;
            top: 28px;
            left: 4px;
            background: #db2777;
            color: white;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
        }
        .fashn-badge {
            position: absolute;
            top: 4px;
            left: 4px;
            background: #7c3aed;
            color: white;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
        }
        .processing-buttons {
            position: absolute;
            bottom: 4px;
            left: 4px;
            right: 4px;
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .photo-card:hover .processing-buttons {
            opacity: 1;
        }
        .btn-magic {
            flex: 1;
            background: #db2777;
            color: white;
            border: none;
            padding: 4px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
        }
        .btn-magic.active {
            background: #be185d;
            box-shadow: 0 0 0 2px white inset;
        }
        .btn-fashn {
            flex: 1;
            background: #7c3aed;
            color: white;
            border: none;
            padding: 4px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
        }
        .btn-fashn.active {
            background: #6d28d9;
            box-shadow: 0 0 0 2px white inset;
        }
    </style>

    <div class="mb-4 flex gap-2">
        @if(count($selected) > 0)
            <x-filament::button wire:click="createBatch" color="success" size="sm">
                Создать карточку ({{ count($selected) }})
                @if(count($magicSelected) > 0 || count($fashnSelected) > 0)
                    <span style="font-size: 10px; opacity: 0.8;">
                        (🪄{{ count($magicSelected) }} ✨{{ count($fashnSelected) }})
                    </span>
                @endif
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
                $isSelected = in_array($photo->id, $selected);
                $borderColor = $isSelected ? '#3b82f6' : ($photo->gg_label ? '#f59e0b' : '#e5e7eb');
            @endphp
            <div
                class="photo-card"
                x-data="{ preview: false }"
                style="position: relative; cursor: pointer; border-radius: 8px; overflow: hidden; border: 3px solid {{ $borderColor }}; aspect-ratio: 1;"
            >
                <img
                    src="{{ Storage::disk('public')->url($photo->image) }}"
                    style="width: 100%; height: 100%; object-fit: cover;"
                    loading="lazy"
                    wire:click="toggleSelect({{ $photo->id }})"
                    x-on:dblclick="preview = true"
                >

                {{-- Preview Modal --}}
                <div x-show="preview"
                     x-on:click="preview = false"
                     x-on:keydown.escape.window="preview = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="photo-preview-modal"
                     style="display: none;">
                    <span class="photo-preview-close">&times;</span>
                    <img src="{{ Storage::disk('public')->url($photo->image) }}" x-on:click.stop>
                </div>

                {{-- Selection Checkmark --}}
                @if($isSelected)
                    <div style="position: absolute; top: 4px; right: 4px; background: #3b82f6; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                        ✓
                    </div>
                @endif

                {{-- FASHN Badge --}}
                @if(in_array($photo->id, $fashnSelected))
                    <div class="fashn-badge">✨ FASHN</div>
                @endif

                {{-- Magic Badge --}}
                @if(in_array($photo->id, $magicSelected))
                    <div class="magic-badge">🪄 Magic</div>
                @endif

                {{-- Processed Badge --}}
                @if($photo->processed)
                    <div style="position: absolute; bottom: 28px; left: 4px; background: #22c55e; color: white; border-radius: 4px; padding: 2px 4px; font-size: 10px;">
                        ✓
                    </div>
                @endif

                {{-- GG Label --}}
                @if($photo->gg_label)
                    <div style="position: absolute; bottom: 4px; right: 4px; background: #f59e0b; color: white; border-radius: 4px; padding: 2px 4px; font-size: 9px; font-weight: bold;">
                        {{ $photo->gg_label }}
                    </div>
                @endif

                {{-- Processing Buttons (only show for selected photos) --}}
                @if($isSelected)
                    <div class="processing-buttons">
                        <button
                            type="button"
                            class="btn-magic {{ in_array($photo->id, $magicSelected) ? 'active' : '' }}"
                            wire:click.stop="toggleMagic({{ $photo->id }})"
                        >
                            🪄 Magic
                        </button>
                        <button
                            type="button"
                            class="btn-fashn {{ in_array($photo->id, $fashnSelected) ? 'active' : '' }}"
                            wire:click.stop="toggleFashn({{ $photo->id }})"
                        >
                            ✨ FASHN
                        </button>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $this->photos->links() }}
    </div>
</x-filament-panels::page>
