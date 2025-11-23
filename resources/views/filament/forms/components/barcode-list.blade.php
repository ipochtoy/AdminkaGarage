<div class="flex flex-wrap gap-2 items-center">
    @php
        $record = $getRecord();
        $barcodes = $record ? \App\Models\BarcodeResult::whereHas('photo', fn($q) => $q->where('photo_batch_id', $record->id))->get()->unique('data') : collect();
        $ggLabels = $record ? array_unique($record->getGgLabels()) : [];
    @endphp

    @foreach($ggLabels as $label)
        @php
            $isGgLabel = str_starts_with($label, 'GG') || str_starts_with($label, 'Q');
        @endphp
        @if($isGgLabel)
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-gradient-to-r from-orange-600/30 to-amber-500/20 text-orange-300 border-2 border-orange-500/60 ring-2 ring-orange-500/30 shadow-lg shadow-orange-500/10">
                <svg class="w-3.5 h-3.5 mr-2 text-orange-400" fill="currentColor" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                <span class="text-[10px] text-orange-400/80 font-medium">Наша лейба</span>
                <span class="mx-2 text-orange-500/50">•</span>
                {{ $label }}
            </span>
        @else
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-amber-500/10 text-amber-500 border border-amber-500/20 ring-1 ring-amber-500/10">
                <svg class="w-3 h-3 mr-1.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                {{ $label }}
            </span>
        @endif
    @endforeach

    @foreach($barcodes as $barcode)
        @if(!in_array($barcode->data, $ggLabels))
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 ring-1 ring-emerald-500/10">
                 <svg class="w-3 h-3 mr-1.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                {{ $barcode->data }}
            </span>
        @endif
    @endforeach

    @if(count($ggLabels) === 0 && $barcodes->isEmpty())
        <span class="text-sm text-gray-500 italic px-2">Баркоды не найдены</span>
    @endif
</div>
