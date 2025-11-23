<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Карточка товара {{ $card->correlation_id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 { font-size: 18px; color: #111827; }
        .header a { color: #2563eb; text-decoration: none; }
        .card {
            background: white;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header {
            background: #eff6ff;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 600;
            font-size: 14px;
        }
        .card-body { padding: 16px; }
        .photos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 12px;
        }
        .photo-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #e5e7eb;
        }
        .photo-item.main { border-color: #f59e0b; }
        .photo-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .photo-badge {
            position: absolute;
            top: 8px;
            left: 8px;
            background: #f59e0b;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .photo-actions {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 4px;
        }
        .photo-actions button, .photo-actions form button {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            font-size: 10px;
            cursor: pointer;
        }
        .btn-star { background: #f59e0b; color: white; }
        .btn-delete { background: #ef4444; color: white; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        .form-group { margin-bottom: 12px; }
        .form-group label {
            display: block;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group.full { grid-column: span 2; }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-primary { background: #2563eb; color: white; }
        .btn-primary:hover { background: #1d4ed8; }
        .barcode-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .barcode-tag {
            padding: 4px 8px;
            background: #f3f4f6;
            border-radius: 4px;
            font-size: 12px;
        }
        .barcode-tag.gg {
            background: #fef3c7;
            color: #92400e;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
        }
        .alert-success { background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Карточка {{ $card->correlation_id }}</h1>
            <a href="{{ route('filament.admin.resources.photo-batches.index') }}">← Назад к списку</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Photos -->
        <div class="card">
            <div class="card-header">Фото ({{ $photos->count() }})</div>
            <div class="card-body">
                <div class="photos-grid">
                    @forelse($photos as $photo)
                        <div class="photo-item {{ $photo->is_main ? 'main' : '' }}">
                            @if($photo->is_main)
                                <span class="photo-badge">Главное</span>
                            @endif
                            <img src="{{ asset('storage/' . $photo->image) }}" alt="Photo">
                            <div class="photo-actions">
                                @if(!$photo->is_main)
                                    <form action="{{ route('product-card.set-main', [$card, $photo->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn-star">★</button>
                                    </form>
                                @endif
                                <form action="{{ route('product-card.delete-photo', [$card, $photo->id]) }}" method="POST" onsubmit="return confirm('Удалить фото?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">×</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p style="color: #6b7280;">Нет фото</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Barcodes -->
        <div class="card">
            <div class="card-header">Баркоды</div>
            <div class="card-body">
                <div class="barcode-list">
                    @foreach($ggLabels as $label)
                        <span class="barcode-tag gg">{{ $label }}</span>
                    @endforeach
                    @foreach($allBarcodes as $barcode)
                        <span class="barcode-tag">{{ $barcode->symbology }}: {{ $barcode->data }}</span>
                    @endforeach
                    @if(empty($ggLabels) && empty($allBarcodes))
                        <span style="color: #6b7280;">Нет баркодов</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Product Info Form -->
        <div class="card">
            <div class="card-header">Описание товара</div>
            <div class="card-body">
                <form action="{{ route('product-card.update', $card) }}" method="POST">
                    @csrf
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Название товара</label>
                            <input type="text" name="title" value="{{ $card->title }}">
                        </div>
                        <div class="form-group full">
                            <label>Описание</label>
                            <textarea name="description" rows="3">{{ $card->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Цена ($)</label>
                            <input type="number" step="0.01" name="price" value="{{ $card->price }}">
                        </div>
                        <div class="form-group">
                            <label>Состояние</label>
                            <select name="condition">
                                <option value="">—</option>
                                <option value="new" {{ $card->condition === 'new' ? 'selected' : '' }}>Новое</option>
                                <option value="used" {{ $card->condition === 'used' ? 'selected' : '' }}>Б/у</option>
                                <option value="refurbished" {{ $card->condition === 'refurbished' ? 'selected' : '' }}>Восстановленное</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Бренд</label>
                            <input type="text" name="brand" value="{{ $card->brand }}">
                        </div>
                        <div class="form-group">
                            <label>Категория</label>
                            <input type="text" name="category" value="{{ $card->category }}">
                        </div>
                        <div class="form-group">
                            <label>Размер</label>
                            <input type="text" name="size" value="{{ $card->size }}">
                        </div>
                        <div class="form-group">
                            <label>Цвет</label>
                            <input type="text" name="color" value="{{ $card->color }}">
                        </div>
                        <div class="form-group">
                            <label>SKU/Артикул</label>
                            <input type="text" name="sku" value="{{ $card->sku }}">
                        </div>
                        <div class="form-group">
                            <label>Количество</label>
                            <input type="number" name="quantity" value="{{ $card->quantity }}" min="1">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                </form>
            </div>
        </div>

        @if($card->ai_summary)
        <div class="card">
            <div class="card-header">AI Сводка</div>
            <div class="card-body">
                <p>{{ $card->ai_summary }}</p>
            </div>
        </div>
        @endif
    </div>
</body>
</html>
