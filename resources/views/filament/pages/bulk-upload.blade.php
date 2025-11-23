<x-filament-panels::page>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="space-y-6">
        <!-- Filter Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 p-6">
            <h3 class="text-lg font-medium mb-4">Фильтр по дате</h3>
            <div class="flex gap-4 mb-4">
                <button type="button" onclick="setFilter('today')" class="filter-btn px-4 py-2 rounded-lg bg-primary-500 text-white hover:bg-primary-600" data-filter="today">
                    За сегодня
                </button>
                <button type="button" onclick="setFilter('5h')" class="filter-btn px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300" data-filter="5h">
                    За 5 часов
                </button>
                <button type="button" onclick="setFilter('1h')" class="filter-btn px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300" data-filter="1h">
                    За 1 час
                </button>
                <button type="button" onclick="setFilter('all')" class="filter-btn px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300" data-filter="all">
                    Все
                </button>
            </div>
            <p class="text-sm text-gray-500" id="filter-info">Выбрано: за сегодня</p>
        </div>

        <!-- Upload Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 p-6">
            <h3 class="text-lg font-medium mb-4">Выбор папки с фото</h3>

            <div class="mb-4">
                <label class="block w-full cursor-pointer">
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-primary-500 transition-colors" id="drop-zone">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-lg font-medium text-gray-700 dark:text-gray-300">Выберите папку с фото</p>
                        <p class="text-sm text-gray-500 mt-1">или перетащите файлы сюда</p>
                    </div>
                    <input type="file" id="folder-input" webkitdirectory multiple accept="image/*" class="hidden" onchange="handleFolderSelect(event)">
                </label>
            </div>

            <!-- Preview -->
            <div id="preview-section" class="hidden">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-medium">Найдено фото: <span id="photo-count" class="text-primary-500">0</span></h4>
                    <button type="button" onclick="uploadPhotos()" class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600">
                        Загрузить все
                    </button>
                </div>
                <div id="preview-grid" class="grid grid-cols-6 gap-2 max-h-96 overflow-y-auto"></div>
            </div>

            <!-- Progress -->
            <div id="progress-section" class="hidden mt-4">
                <div class="bg-gray-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-primary-500 h-2 rounded-full transition-all" style="width: 0%"></div>
                </div>
                <p id="progress-text" class="text-sm text-gray-500 mt-2">Загрузка...</p>
            </div>
        </div>
    </div>

    <script>
        let currentFilter = 'today';
        let selectedFiles = [];

        function setFilter(filter) {
            currentFilter = filter;
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.dataset.filter === filter) {
                    btn.classList.remove('bg-gray-200', 'dark:bg-gray-700');
                    btn.classList.add('bg-primary-500', 'text-white');
                } else {
                    btn.classList.add('bg-gray-200', 'dark:bg-gray-700');
                    btn.classList.remove('bg-primary-500', 'text-white');
                }
            });
            const texts = {'today': 'за сегодня', '5h': 'за последние 5 часов', '1h': 'за последний час', 'all': 'все файлы'};
            document.getElementById('filter-info').textContent = 'Выбрано: ' + texts[filter];
            if (selectedFiles.length > 0) filterAndPreview();
        }

        function handleFolderSelect(event) {
            selectedFiles = Array.from(event.target.files).filter(f => f.type.startsWith('image/'));
            filterAndPreview();
        }

        function filterAndPreview() {
            const now = new Date();
            let filtered = selectedFiles;

            if (currentFilter === 'today') {
                const todayStart = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                filtered = selectedFiles.filter(f => new Date(f.lastModified) >= todayStart);
            } else if (currentFilter === '5h') {
                filtered = selectedFiles.filter(f => new Date(f.lastModified) >= new Date(now - 5*60*60*1000));
            } else if (currentFilter === '1h') {
                filtered = selectedFiles.filter(f => new Date(f.lastModified) >= new Date(now - 60*60*1000));
            }

            window.filteredFiles = filtered;
            document.getElementById('photo-count').textContent = filtered.length;
            document.getElementById('preview-section').classList.remove('hidden');

            const grid = document.getElementById('preview-grid');
            grid.innerHTML = '';
            filtered.slice(0, 50).forEach(file => {
                const div = document.createElement('div');
                div.className = 'aspect-square bg-gray-100 rounded overflow-hidden';
                const img = document.createElement('img');
                img.className = 'w-full h-full object-cover';
                img.src = URL.createObjectURL(file);
                div.appendChild(img);
                grid.appendChild(div);
            });
            if (filtered.length > 50) {
                const more = document.createElement('div');
                more.className = 'aspect-square bg-gray-200 rounded flex items-center justify-center text-gray-500';
                more.textContent = '+' + (filtered.length - 50);
                grid.appendChild(more);
            }
        }

        async function uploadPhotos() {
            const files = window.filteredFiles || [];
            if (files.length === 0) return;

            const progressSection = document.getElementById('progress-section');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            progressSection.classList.remove('hidden');

            for (let i = 0; i < files.length; i++) {
                const formData = new FormData();
                formData.append('photo', files[i]);
                try {
                    await fetch('/api/upload-photo', {
                        method: 'POST',
                        body: formData,
                        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''}
                    });
                } catch (e) { console.error(e); }
                const progress = Math.round(((i + 1) / files.length) * 100);
                progressBar.style.width = progress + '%';
                progressText.textContent = 'Загружено ' + (i + 1) + ' из ' + files.length;
            }
            progressText.textContent = 'Готово! Загружено ' + files.length + ' фото';
            setTimeout(() => window.location.reload(), 2000);
        }

        const dropZone = document.getElementById('drop-zone');
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('border-primary-500'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-primary-500'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-primary-500');
            selectedFiles = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            filterAndPreview();
        });
    </script>
</x-filament-panels::page>
