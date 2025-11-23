<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .fashn-loading {
        opacity: 0.6;
        pointer-events: none;
    }
    .photo-lightbox {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
    }
    .photo-lightbox img {
        max-width: 90vw;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .photo-lightbox-close {
        position: absolute;
        top: 20px;
        right: 20px;
        color: white;
        font-size: 32px;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    .photo-lightbox-close:hover {
        opacity: 1;
    }
</style>
<div class="w-full">
    @if($getRecord() && $getRecord()->photos->count() > 0)
        <div style="display: flex; flex-wrap: wrap; gap: 16px;">
            @foreach($getRecord()->photos()->orderBy('order')->get() as $photo)
                <div style="width: 180px; position: relative; display: flex; flex-direction: column; background-color: #ffffff; border-radius: 8px; border: {{ $photo->is_public ? '2px solid #10b981' : ($photo->is_main ? '2px solid #3b82f6' : '1px solid #e5e7eb') }}; overflow: hidden; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);">
                    
                    {{-- Image Area --}}
                    <div style="position: relative; aspect-ratio: 3/4; width: 100%; background-color: #f3f4f6;"
                         x-data="{ showLightbox: false }">
                        <img src="{{ asset('storage/' . $photo->image) }}?t={{ time() }}"
                             style="width: 100%; height: 100%; object-fit: cover; cursor: zoom-in;"
                             x-on:click="showLightbox = true">

                        {{-- Lightbox --}}
                        <div x-show="showLightbox"
                             x-on:click="showLightbox = false"
                             x-on:keydown.escape.window="showLightbox = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="photo-lightbox"
                             style="display: none;">
                            <span class="photo-lightbox-close">&times;</span>
                            <img src="{{ asset('storage/' . $photo->image) }}?t={{ time() }}" x-on:click.stop>
                        </div>
                        


                        {{-- Public/For Sale Checkbox (Top Left) --}}
                        <div style="position: absolute; top: 6px; left: 6px; z-index: 10;">
                            <label style="display: flex; align-items: center; gap: 4px; background: rgba(255,255,255,0.9); padding: 2px 6px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); cursor: pointer; font-size: 10px; font-weight: 600; color: #374151;">
                                <input type="checkbox" 
                                       wire:click="togglePublic({{ $photo->id }})" 
                                       {{ $photo->is_public ? 'checked' : '' }}
                                       style="width: 12px; height: 12px; cursor: pointer;">
                                На продажу
                            </label>
                        </div>

                        {{-- Main Badge (Below Checkbox) --}}
                        @if($photo->is_main)
                            <div style="position: absolute; top: 30px; left: 6px; background-color: #2563eb; color: white; font-size: 10px; font-weight: bold; padding: 2px 6px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                                MAIN
                            </div>
                        @endif

                        {{-- Top Actions (Absolute Overlay) --}}
                        <div style="position: absolute; top: 6px; right: 6px; display: flex; gap: 4px;">
                             <button type="button" wire:click="rotatePhoto({{ $photo->id }}, 'left')" 
                                     style="background: rgba(255,255,255,0.9); color: #4b5563; border: 1px solid #d1d5db; border-radius: 4px; padding: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.1);" 
                                     title="Повернуть">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                            </button>
                            <button type="button" wire:click="deletePhoto({{ $photo->id }})" wire:confirm="Удалить?" 
                                    style="background: rgba(254, 226, 226, 0.9); color: #ef4444; border: 1px solid #fecaca; border-radius: 4px; padding: 4px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.1);" 
                                    title="Удалить">
                                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        {{-- Set Main Button (Bottom Overlay) --}}
                        @if(!$photo->is_main)
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 6px; background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);">
                                <button type="button" wire:click="setMainPhoto({{ $photo->id }})" 
                                        style="width: 100%; padding: 6px; background-color: #3b82f6; color: white; font-size: 11px; font-weight: 600; border: none; border-radius: 4px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                    Сделать главной
                                </button>
                            </div>
                        @endif
                    </div>

                    {{-- Bottom Actions --}}
                    <div style="padding: 8px; background-color: #ffffff; border-top: 1px solid #f3f4f6; display: flex; flex-direction: column; gap: 8px;">
                        
                        {{-- Barcodes/Codes Info --}}
                        <div style="display: flex; justify-content: flex-end; align-items: center;">
                            <button type="button" wire:click="scanBarcodes({{ $photo->id }})"
                                    style="font-size: 10px; background: #fee2e2; color: #dc2626; border: none; padding: 3px 8px; border-radius: 4px; cursor: pointer; font-weight: 500;">
                                Сканировать
                            </button>
                        </div>

                        {{-- Fashn / Magic Buttons --}}
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px;">
                            <button type="button"
                                    x-data="{ loading: false }"
                                    x-on:click="loading = true; $wire.generateModel({{ $photo->id }}).then(() => loading = false).catch(() => loading = false)"
                                    x-bind:disabled="loading"
                                    x-bind:class="loading ? 'fashn-loading' : ''"
                                    style="background: #7c3aed; color: white; border: none; padding: 6px; border-radius: 4px; font-size: 11px; font-weight: 500; cursor: pointer; text-align: center; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                <span x-show="!loading">✨</span>
                                <span x-show="loading" style="display: inline-block; animation: spin 1s linear infinite;">⏳</span>
                                <span x-show="!loading">FASHN</span>
                                <span x-show="loading">Генерация...</span>
                            </button>
                            <button type="button"
                                    x-data="{ loading: false }"
                                    x-on:click="loading = true; $wire.magicEnhance({{ $photo->id }}).then(() => loading = false).catch(() => loading = false)"
                                    x-bind:disabled="loading"
                                    x-bind:class="loading ? 'fashn-loading' : ''"
                                    style="background: #db2777; color: white; border: none; padding: 6px; border-radius: 4px; font-size: 11px; font-weight: 500; cursor: pointer; text-align: center; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                <span x-show="!loading">🪄</span>
                                <span x-show="loading" style="display: inline-block; animation: spin 1s linear infinite;">⏳</span>
                                <span x-show="!loading">Magic</span>
                                <span x-show="loading">Обработка...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px; background-color: #f9fafb; border: 2px dashed #e5e7eb; border-radius: 8px; color: #9ca3af;">
            <p style="font-size: 14px; font-weight: 500;">Нет фото</p>
            <p style="font-size: 12px;">Загрузите фото для начала работы</p>
        </div>
    @endif
</div>
