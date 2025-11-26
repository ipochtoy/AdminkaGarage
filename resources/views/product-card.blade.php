<!DOCTYPE html>
<html lang="ru" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $card->correlation_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gray: {
                            750: '#2d3748',
                            850: '#1a202c',
                            900: '#0f172a', // Main bg
                            800: '#1e293b', // Panel bg
                            700: '#334155', // Borders
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #0f172a; color: #e2e8f0; }
        .sortable-ghost { opacity: 0.5; border: 2px dashed #4b5563; }
    </style>
</head>
<body class="antialiased h-screen flex overflow-hidden">

    <!-- Narrow Sidebar -->
    <aside class="w-16 bg-[#1e293b] border-r border-gray-700 flex flex-col items-center py-4 z-20 flex-shrink-0">
        <div class="mb-6">
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">S</div>
        </div>
        
        <nav class="space-y-4 w-full flex flex-col items-center">
            <a href="{{ route('filament.admin.resources.photo-batches.index') }}" class="w-10 h-10 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-700 hover:text-white transition-colors" title="Назад к списку">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="w-8 h-px bg-gray-700 my-2"></div>
            <button class="w-10 h-10 flex items-center justify-center rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-500/20" title="Карточка">
                <i class="fa-solid fa-box-open"></i>
            </button>
            <button class="w-10 h-10 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-700 hover:text-white transition-colors" title="Фото">
                <i class="fa-regular fa-images"></i>
            </button>
            <button class="w-10 h-10 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-700 hover:text-white transition-colors" title="Баркоды">
                <i class="fa-solid fa-barcode"></i>
            </button>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        
        <!-- Header -->
        <header class="h-16 bg-[#1e293b]/80 backdrop-blur border-b border-gray-700 flex items-center justify-between px-6 flex-shrink-0 z-10">
            <div class="flex items-center gap-4">
                <h1 class="font-bold text-lg tracking-wide">{{ $card->correlation_id }}</h1>
                <div class="flex items-center gap-2 text-xs bg-gray-800 px-2 py-1 rounded border border-gray-700">
                    <span class="text-gray-400">Статус:</span>
                    <span class="{{ $card->status === 'processed' ? 'text-emerald-400' : 'text-amber-400' }} font-medium">
                        {{ $card->status === 'processed' ? 'Обработано' : 'В работе' }}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="saveCard()" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-1.5 rounded-md text-sm font-medium transition-colors shadow-lg shadow-blue-900/20">
                    <i class="fa-solid fa-save mr-1.5"></i> Сохранить
                </button>
            </div>
        </header>

        <!-- Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6 scroll-smooth">

            <!-- 1. Photos Section -->
            <section class="bg-[#1e293b] rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-700 flex justify-between items-center bg-gray-800/50">
                    <h2 class="text-sm font-semibold text-gray-300 flex items-center gap-2">
                        <i class="fa-regular fa-images text-blue-400"></i> Фото <span class="text-gray-500 text-xs bg-gray-800 px-1.5 py-0.5 rounded border border-gray-700">{{ $photos->count() }}</span>
                    </h2>
                    <div class="flex gap-2">
                        <button class="text-xs bg-gray-700 hover:bg-gray-600 text-gray-200 px-3 py-1.5 rounded border border-gray-600 transition-colors" onclick="searchEbay()">
                            <i class="fa-brands fa-ebay"></i> Найти фото
                        </button>
                        <label class="text-xs bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded cursor-pointer transition-colors shadow-sm">
                            <i class="fa-solid fa-plus"></i> Добавить
                            <input type="file" class="hidden" onchange="uploadPhoto(this)">
                        </label>
                    </div>
                </div>
                
                <div class="p-4">
                    <div id="photos-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3">
                        @foreach($photos as $photo)
                        <div class="group relative aspect-[3/4] bg-gray-900 rounded-lg border {{ $photo->is_main ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-700 hover:border-gray-500' }} transition-all overflow-hidden cursor-move" data-id="{{ $photo->id }}">
                            <img src="{{ asset('storage/' . $photo->image) }}?t={{ time() }}" class="w-full h-full object-cover">
                            
                            <!-- Overlay Actions -->
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
                                <div class="flex justify-end gap-1">
                                    <button onclick="rotatePhoto({{ $photo->id }})" class="w-7 h-7 bg-gray-900/80 text-white rounded hover:bg-blue-600 transition-colors flex items-center justify-center"><i class="fa-solid fa-rotate-right text-xs"></i></button>
                                    <button onclick="deletePhoto({{ $photo->id }})" class="w-7 h-7 bg-gray-900/80 text-white rounded hover:bg-red-600 transition-colors flex items-center justify-center"><i class="fa-solid fa-times text-xs"></i></button>
                                </div>
                                @if(!$photo->is_main)
                                <button onclick="setMainPhoto({{ $photo->id }})" class="w-full py-1.5 bg-gray-900/80 text-xs font-medium text-white rounded hover:bg-yellow-600 transition-colors">
                                    Сделать главной
                                </button>
                                @endif
                            </div>

                            @if($photo->is_main)
                                <span class="absolute top-1 left-1 bg-blue-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded shadow-sm">MAIN</span>
                            @endif
                            
                            <!-- Barcode count badge -->
                            @if($photo->barcodes->count() > 0)
                                <span class="absolute bottom-1 right-1 bg-gray-800/90 text-gray-200 text-[10px] px-1.5 py-0.5 rounded border border-gray-600 flex items-center gap-1">
                                    <i class="fa-solid fa-barcode text-[8px]"></i> {{ $photo->barcodes->count() }}
                                </span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- 2. Barcodes -->
            <section class="bg-[#1e293b] rounded-xl border border-gray-700 overflow-hidden">
                <div class="px-4 py-2 border-b border-gray-700 bg-gray-800/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-barcode text-gray-500 text-sm"></i>
                        <h3 class="text-sm font-semibold text-gray-300">Баркоды</h3>
                    </div>
                    <button onclick="scanBarcodes()" class="text-xs bg-purple-600 hover:bg-purple-500 text-white px-3 py-1.5 rounded transition-colors shadow-sm">
                        <i class="fa-solid fa-qrcode mr-1"></i> Сканировать (Gemini)
                    </button>
                </div>
                <div class="p-4 flex flex-wrap items-center gap-2">
                    @foreach($ggLabels as $label)
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-amber-900/30 text-amber-200 border border-amber-700/50">
                            <i class="fa-solid fa-tag mr-1.5 opacity-60"></i> {{ $label }}
                        </span>
                    @endforeach
                    @foreach($barcodes as $barcode)
                        <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-emerald-900/30 text-emerald-200 border border-emerald-700/50">
                            <i class="fa-solid fa-barcode mr-1.5 opacity-60"></i> {{ $barcode->data }}
                        </span>
                    @endforeach

                    <div class="flex items-center bg-gray-900 rounded border border-gray-700 ml-2 focus-within:border-blue-500 transition-colors">
                        <input type="text" id="new-barcode" placeholder="Добавить..." class="bg-transparent text-xs px-3 py-1.5 outline-none text-gray-200 w-24 placeholder-gray-600">
                        <button onclick="addBarcode()" class="px-2 py-1.5 text-gray-400 hover:text-blue-400 transition-colors border-l border-gray-700">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
            </section>

            <!-- 3. AI Generation -->
            <section class="bg-gradient-to-r from-emerald-900/20 to-emerald-900/10 rounded-xl border border-emerald-800/50 overflow-hidden">
                <div class="px-4 py-3 border-b border-emerald-800/30">
                    <h3 class="text-sm font-semibold text-emerald-400 flex items-center gap-2">
                        <i class="fa-solid fa-robot"></i> AI Ассистент
                    </h3>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="generateSummary('openai')" class="py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded-lg shadow-lg shadow-emerald-900/20 transition-all active:scale-[0.99] flex items-center justify-center gap-2">
                            <i class="fa-solid fa-bolt"></i> OpenAI GPT-5.1
                        </button>
                        <button onclick="generateSummary('gemini')" class="py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg shadow-lg shadow-blue-900/20 transition-all active:scale-[0.99] flex items-center justify-center gap-2">
                            <i class="fa-brands fa-google"></i> Gemini 3 Pro
                        </button>
                    </div>
                    <div id="ai-summary" class="text-sm text-gray-300 leading-relaxed hidden bg-gray-900/50 p-4 rounded border border-emerald-800/30"></div>
                </div>
            </section>

            <!-- 4. Declaration (Technical - not sent to Pochtoy) -->
            <section class="bg-gradient-to-r from-amber-900/20 to-amber-900/10 rounded-xl border border-amber-800/50 overflow-hidden">
                <div class="px-4 py-3 border-b border-amber-800/30 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-amber-400 flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice"></i> Декларация
                        <span class="text-[10px] bg-amber-900/50 text-amber-300 px-1.5 py-0.5 rounded font-normal">техническое</span>
                    </h3>
                </div>
                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-amber-400/80 mb-1.5 uppercase tracking-wider">Description EN (3-5 слов)</label>
                            <input type="text" id="declaration_en" value="{{ $card->declaration_en }}" class="w-full bg-gray-900 border border-amber-700/50 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition-colors placeholder-gray-600" placeholder="women sneakers New Balance">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-amber-400/80 mb-1.5 uppercase tracking-wider">Описание RU (3-5 слов)</label>
                            <input type="text" id="declaration_ru" value="{{ $card->declaration_ru }}" class="w-full bg-gray-900 border border-amber-700/50 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 outline-none transition-colors placeholder-gray-600" placeholder="женские кроссовки Нью Баланс">
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-relaxed">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Короткие описания для таможенной декларации Pochtoy. Генерируются автоматически вместе с описанием товара.
                    </p>
                </div>
            </section>

             <!-- eBay Results (Hidden) -->
             <div id="ebay-results-wrapper" class="hidden">
                <section class="bg-[#1e293b] rounded-xl border border-gray-700 p-4">
                    <h3 class="text-sm font-semibold text-gray-400 mb-3">Результаты eBay</h3>
                    <div id="ebay-results" class="grid grid-cols-5 gap-3"></div>
                </section>
            </div>

            <!-- 4. Form -->
            <section class="bg-[#1e293b] rounded-xl border border-gray-700 p-6 pb-8">
                <h3 class="text-sm font-semibold text-gray-300 mb-6 flex items-center gap-2 border-b border-gray-700 pb-2">
                    <i class="fa-regular fa-pen-to-square text-gray-500"></i> Редактирование
                </h3>
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Название</label>
                        <input type="text" id="title" value="{{ $card->title }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors placeholder-gray-600" placeholder="Название товара">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Описание</label>
                        <textarea id="description" rows="5" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors placeholder-gray-600 scrollbar-thin">{{ $card->description }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div class="group">
                            <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Цена ($)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-gray-500">$</span>
                                <input type="number" step="0.01" id="price" value="{{ $card->price }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg pl-7 pr-4 py-2.5 text-sm text-gray-100 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Состояние</label>
                            <select id="condition" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-blue-500 outline-none appearance-none">
                                <option value="" class="text-gray-500">Не выбрано</option>
                                <option value="new" {{ $card->condition === 'new' ? 'selected' : '' }}>Новое</option>
                                <option value="used" {{ $card->condition === 'used' ? 'selected' : '' }}>Б/у</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Бренд</label>
                            <input type="text" id="brand" value="{{ $card->brand }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-blue-500 outline-none">
                        </div>

                         <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Категория</label>
                            <input type="text" id="category" value="{{ $card->category }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                         <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Размер</label>
                            <input type="text" id="size" value="{{ $card->size }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Цвет</label>
                            <input type="text" id="color" value="{{ $card->color }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-blue-500 outline-none">
                        </div>
                         <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">SKU</label>
                            <input type="text" id="sku" value="{{ $card->sku }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5 uppercase tracking-wider">Кол-во</label>
                            <input type="number" id="quantity" value="{{ $card->quantity ?? 1 }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-100 focus:border-blue-500 outline-none">
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-700 flex justify-between items-center">
                    <button onclick="deleteCard()" class="text-red-500 hover:text-red-400 text-sm font-medium px-4 py-2 hover:bg-red-500/10 rounded transition-colors">
                        <i class="fa-regular fa-trash-can mr-2"></i> Удалить
                    </button>
                    <div class="flex gap-3">
                         <button onclick="sendToPochtoy()" class="bg-gray-700 hover:bg-gray-600 text-gray-200 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                            В Pochtoy
                        </button>
                        <button onclick="saveCard()" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-2 rounded-lg text-sm font-medium shadow-lg shadow-blue-600/20 transition-all transform active:scale-95">
                            Сохранить
                        </button>
                    </div>
                </div>
            </section>

            <div class="h-12"></div> <!-- Spacer -->
        </div>
    </main>

    <script>
        const cardId = {{ $card->id }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Initialize Sortable
        const el = document.getElementById('photos-grid');
        if(el) {
            new Sortable(el, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function() {
                    const order = Array.from(el.children).map(c => c.dataset.id);
                    apiCall(`/api/card/${cardId}/reorder-photos`, 'POST', { order });
                }
            });
        }

        function showToast(msg, type = 'success') {
            const div = document.createElement('div');
            const color = type === 'success' ? 'bg-emerald-600' : 'bg-red-600';
            div.className = `fixed bottom-6 right-6 px-6 py-3 rounded-lg text-white shadow-xl z-50 ${color} transition-all transform translate-y-10 opacity-0`;
            div.innerHTML = msg;
            document.body.appendChild(div);
            requestAnimationFrame(() => div.classList.remove('translate-y-10', 'opacity-0'));
            setTimeout(() => {
                div.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => div.remove(), 300);
            }, 3000);
        }

        async function apiCall(url, method = 'POST', body = null) {
            try {
                const opts = { method, headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' } };
                if (body) opts.body = JSON.stringify(body);
                const r = await fetch(url, opts);
                const data = await r.json().catch(() => ({}));
                return { ok: r.ok, data };
            } catch (e) {
                console.error(e);
                return { ok: false };
            }
        }

        async function rotatePhoto(id) {
            if ((await apiCall(`/api/photo/${id}/rotate`)).ok) location.reload();
        }
        async function deletePhoto(id) {
            if (confirm('Удалить фото?') && (await apiCall(`/api/photo/${id}/delete`, 'DELETE')).ok) location.reload();
        }
        async function setMainPhoto(id) {
            if ((await apiCall(`/api/photo/${id}/set-main`)).ok) location.reload();
        }
        
        async function addBarcode() {
            const val = document.getElementById('new-barcode').value.trim();
            if (!val) return;
            if ((await apiCall(`/api/card/${cardId}/add-barcode`, 'POST', { data: val })).ok) location.reload();
        }

        async function saveCard() {
            const data = {
                title: document.getElementById('title').value,
                description: document.getElementById('description').value,
                brand: document.getElementById('brand').value,
                category: document.getElementById('category').value,
                price: document.getElementById('price').value,
                condition: document.getElementById('condition').value,
                size: document.getElementById('size').value,
                color: document.getElementById('color').value,
                sku: document.getElementById('sku').value,
                quantity: document.getElementById('quantity').value,
                declaration_en: document.getElementById('declaration_en').value,
                declaration_ru: document.getElementById('declaration_ru').value,
            };
            const res = await apiCall(`/api/card/${cardId}/save`, 'POST', data);
            showToast(res.ok ? 'Сохранено' : 'Ошибка сохранения', res.ok ? 'success' : 'error');
        }

        async function generateSummary(provider) {
            const btn = event.currentTarget;
            const oldHtml = btn.innerHTML;
            btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i>';

            const res = await apiCall(`/api/card/${cardId}/generate-summary`, 'POST', { provider });

            if (res.ok) {
                const box = document.getElementById('ai-summary');
                box.innerHTML = res.data.summary.replace(/\n/g, '<br>');
                box.classList.remove('hidden');
                document.getElementById('description').value = res.data.summary;

                // Fill declaration fields if returned
                if (res.data.declaration_en) {
                    document.getElementById('declaration_en').value = res.data.declaration_en;
                }
                if (res.data.declaration_ru) {
                    document.getElementById('declaration_ru').value = res.data.declaration_ru;
                }

                showToast('Описание создано');
            } else {
                showToast('Ошибка AI', 'error');
            }
            btn.disabled = false; btn.innerHTML = oldHtml;
        }

        async function searchEbay() {
            const res = await apiCall(`/api/card/${cardId}/search-ebay`);
            if (res.ok && res.data.data.images) {
                const c = document.getElementById('ebay-results');
                c.innerHTML = '';
                document.getElementById('ebay-results-wrapper').classList.remove('hidden');
                res.data.data.images.slice(0, 5).forEach(src => {
                    c.innerHTML += `<div class="aspect-square bg-gray-800 rounded overflow-hidden"><img src="${src}" class="w-full h-full object-cover"></div>`;
                });
                showToast('Найдено на eBay');
            } else {
                showToast('Ничего не найдено', 'error');
            }
        }

        async function scanBarcodes() {
            const btn = event.currentTarget;
            const oldHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Сканирую...';

            const res = await apiCall(`/api/card/${cardId}/scan-barcodes`);

            if (res.ok) {
                const total = res.data.total.barcodes + res.data.total.gg_labels;
                if (total > 0) {
                    showToast(`Найдено: ${res.data.total.barcodes} баркодов, ${res.data.total.gg_labels} GG лейб`);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Новых кодов не найдено');
                }
            } else {
                showToast('Ошибка сканирования', 'error');
            }

            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }

        async function sendToPochtoy() {
            if(!confirm('Отправить?')) return;
            const res = await apiCall(`/api/card/${cardId}/send-pochtoy`);
            if(res.ok && res.data.success) { showToast('Отправлено!'); setTimeout(() => location.reload(), 1000); }
            else showToast('Ошибка отправки', 'error');
        }
        
        async function deleteCard() {
            if(confirm('Удалить карточку?') && (await apiCall(`/api/card/${cardId}/delete`, 'DELETE')).ok) {
                window.location.href = '{{ route("filament.admin.resources.photo-batches.index") }}';
            }
        }

        // Simple file upload handler if needed
        async function uploadPhoto(input) {
            if (!input.files || !input.files[0]) return;
            const fd = new FormData();
            fd.append('photo', input.files[0]);
            
            // This needs a route to handle single photo upload to batch
            // Assuming we reuse the main upload but we need to link it to batch
            // For now, placeholder. To implement fully we need a backend endpoint: /api/card/{id}/upload-photo
            alert('Загрузка фото с этой кнопки пока требует бэкенда. Используйте drag-and-drop на главной.');
        }
    </script>
</body>
</html>
