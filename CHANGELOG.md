# AdminkaGarage - Changelog

## 2025-11-25: Система табов для карточек товаров

### Что было сделано

Реализована система вкладок (табов) для управления карточками товаров с автоматическим перемещением между статусами.

### Основные изменения

#### 1. Добавлен новый статус `published` в базу данных

**Файл:** `database/migrations/2025_11_25_015849_add_published_status_to_photo_batches.php`

```php
// Добавили новый статус 'published' в ENUM поле status
DB::statement("ALTER TABLE photo_batches MODIFY COLUMN status ENUM('pending', 'processed', 'published', 'failed') DEFAULT 'pending'");
```

**Статусы:**
- `pending` - Ожидает обработки (необработанные товары)
- `processed` - Обработано (старый статус, больше не используется)
- `published` - Опубликовано (товары отправленные в Гараж)
- `failed` - Ошибка

#### 2. Реализованы вкладки на странице списка карточек

**Файл:** `app/Filament/Resources/PhotoBatchResource/Pages/ListPhotoBatches.php`

```php
public function getTabs(): array
{
    return [
        'товары' => Tab::make('Товары')
            ->badge(PhotoBatch::where('status', 'pending')->count())
            ->modifyQueryUsing(fn($query) => $query->where('status', 'pending')),

        'обработано' => Tab::make('Обработано')
            ->badge(PhotoBatch::where('status', 'published')->count())
            ->badgeColor('success')
            ->modifyQueryUsing(fn($query) => $query->where('status', 'published')),
    ];
}
```

**Описание вкладок:**

1. **Товары** (по умолчанию)
   - Показывает все карточки со статусом `pending`
   - Бейдж с количеством необработанных товаров
   - Сюда попадают новые карточки из Telegram бота

2. **Обработано**
   - Показывает все карточки со статусом `published`
   - Зелёный бейдж (success) с количеством
   - Сюда автоматически перемещаются карточки после отправки "В бой"

#### 3. Автоматическая смена статуса при отправке в Гараж

**Файл:** `app/Filament/Resources/PhotoBatchResource/Pages/EditPhotoBatch.php:109`

```php
Actions\Action::make('to_battle')
    ->label('В бой')
    ->icon('heroicon-o-rocket-launch')
    ->color('success')
    ->requiresConfirmation()
    ->modalHeading('Отправить в Гараж?')
    ->modalDescription('Будет создана карточка товара с выбранными фото (отмеченными "На продажу").')
    ->action(function (PhotoBatchResource\Pages\EditPhotoBatch $livewire) {
        $record = $livewire->getRecord();

        // Меняем статус на published
        $record->update(['status' => 'published']);

        // Создаём Product в Garage
        $product = \App\Models\Product::create([...]);

        // Создаём ProductPhotos
        // Отправляем уведомление в Telegram
    })
```

### Workflow (Рабочий процесс)

1. **Telegram бот** отправляет фото и создаёт `PhotoBatch` со статусом `pending`
2. **Карточка появляется** во вкладке "Товары"
3. **Пользователь обрабатывает** карточку:
   - Генерирует описание через AI (OpenAI/Gemini)
   - Получает рекомендации по цене (AI, eBay, UPC)
   - Редактирует название, описание, цену
   - Выбирает фото для продажи
4. **Нажимает "В бой"** → Карточка:
   - Меняет статус на `published`
   - Создаётся товар в таблице `products`
   - Создаются `product_photos`
   - Отправляется уведомление в Telegram
   - **Перемещается** во вкладку "Обработано"

### UI улучшения (сделанные ранее)

#### Компактные плиточки с ценами

**Файл:** `resources/views/filament/forms/components/price-suggestions.blade.php`

Дизайн рекомендаций по цене изменён на компактные плиточки с иконками:
- **AI оценки** (зелёный) - из AI-генерированного описания
- **eBay цены** (фиолетовый) - min/avg/max с eBay Browse API
- **UPC цены** (синий) - из UPC баз данных
- **Средняя цена** (янтарный) - среднее по всем источникам

Клик по плиточке автоматически устанавливает цену в поле формы.

### Технические детали

**Технологии:**
- Laravel 12.39.0
- PHP 8.4.15
- Filament v3.3.45
- Livewire для реактивности
- TailwindCSS для стилей

**Автообновление:**
Таблица обновляется каждые 30 секунд (`->poll('30s')`)

**Ключевые файлы:**
- `app/Filament/Resources/PhotoBatchResource.php` - Основной ресурс
- `app/Filament/Resources/PhotoBatchResource/Pages/ListPhotoBatches.php` - Вкладки
- `app/Filament/Resources/PhotoBatchResource/Pages/EditPhotoBatch.php` - Кнопка "В бой"
- `app/Models/PhotoBatch.php` - Модель карточки
- `resources/views/filament/forms/components/price-suggestions.blade.php` - Плиточки цен
- `resources/views/filament/forms/components/barcode-list.blade.php` - Баркоды

### База данных

**Таблица:** `photo_batches`

**Основные поля:**
- `status` - ENUM('pending', 'processed', 'published', 'failed')
- `title` - Название товара
- `description` - Описание
- `price` - Цена (decimal)
- `ai_summary` - JSON с AI-генерированными данными
- `correlation_id` - ID карточки
- `chat_id` - Telegram chat ID
- `message_ids` - JSON array с message IDs

**Связи:**
- `hasMany(Photo::class)` - Фотографии карточки
- `hasMany(Product::class)` - Созданные товары в Garage

### Как использовать

1. Откройте `/admin/photo-batches`
2. По умолчанию открыта вкладка "Товары"
3. Выберите карточку → Edit
4. Сгенерируйте описание (OpenAI/Gemini)
5. Посмотрите рекомендации по цене
6. Отредактируйте данные
7. Нажмите "В бой"
8. Карточка переместится во вкладку "Обработано"

### Troubleshooting

**Проблема:** Табы не отображаются
**Решение:**
```bash
php artisan optimize:clear
php artisan view:clear
```
Затем жёсткое обновление страницы (Cmd+Shift+R / Ctrl+Shift+R)

**Проблема:** Ошибка "Data truncated for column 'status'"
**Решение:** Запустить миграцию:
```bash
php artisan migrate --path=database/migrations/2025_11_25_015849_add_published_status_to_photo_batches.php
```

### TODO / Будущие улучшения

- [ ] Добавить вкладку "Ошибки" для failed статуса
- [ ] Добавить фильтры по дате
- [ ] Добавить bulk actions для массовой обработки
- [ ] Оптимизировать запросы badge counters (кеширование)
- [ ] Добавить уведомления при перемещении между вкладками

---

**Дата:** 25.11.2025
**Версия:** 1.0.0
**Автор:** Реализовано с помощью Claude Code
