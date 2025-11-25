<?php

namespace App\Filament\Widgets;

use App\Models\PhotoBatch;
use App\Models\Product;
use App\Models\Photo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public ?string $filter = 'week';

    protected function getStats(): array
    {
        $period = match($this->filter) {
            'today' => now()->startOfDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            default => now()->subWeek(),
        };

        // Статистика по карточкам товаров
        $totalBatches = PhotoBatch::count();
        $newBatches = PhotoBatch::where('created_at', '>=', $period)->count();
        $processedBatches = PhotoBatch::where('status', 'processed')->count();

        // Статистика по товарам на продажу
        $totalProducts = Product::count();
        $newProducts = Product::where('created_at', '>=', $period)->count();
        $activeProducts = Product::where('status', 'active')->count();

        // Статистика по фото
        $totalPhotos = Photo::count();
        $newPhotos = Photo::where('uploaded_at', '>=', $period)->count();
        $fashionProcessed = Photo::whereHas('processingTasks', function($q) {
            $q->where('api_name', 'fashn')->where('status', 'completed');
        })->count();
        $ebaySelected = Photo::where('ebay_selected', true)->count();

        // Статистика по выручке
        $totalRevenue = Product::where('status', 'active')->sum('price') ?? 0;
        $newRevenue = Product::where('status', 'active')
            ->where('created_at', '>=', $period)
            ->sum('price') ?? 0;

        // Статистика по проданным товарам (пока нет таблицы продаж, показываем 0)
        $soldCount = 0;
        $soldRevenue = 0;

        return [
            Stat::make('Карточки товаров', $totalBatches)
                ->description($newBatches > 0 ? "+{$newBatches} новых" : 'Нет новых')
                ->descriptionIcon('heroicon-m-photo')
                ->color($newBatches > 0 ? 'success' : 'gray')
                ->chart($this->getChartData(PhotoBatch::class)),

            Stat::make('Обработано карточек', $processedBatches)
                ->description(round($totalBatches > 0 ? ($processedBatches / $totalBatches * 100) : 0, 1) . '% от всех')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),

            Stat::make('Товаров на продажу', $activeProducts)
                ->description($newProducts > 0 ? "+{$newProducts} новых" : 'Нет новых')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color($newProducts > 0 ? 'success' : 'gray')
                ->chart($this->getChartData(Product::class)),

            Stat::make('Загружено фото', $totalPhotos)
                ->description($newPhotos > 0 ? "+{$newPhotos} новых" : 'Нет новых')
                ->descriptionIcon('heroicon-m-camera')
                ->color($newPhotos > 0 ? 'success' : 'gray'),

            Stat::make('Обработано в FASHN', $fashionProcessed)
                ->description(round($totalPhotos > 0 ? ($fashionProcessed / $totalPhotos * 100) : 0, 1) . '% от всех фото')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),

            Stat::make('Помечено для eBay', $ebaySelected)
                ->description(round($totalPhotos > 0 ? ($ebaySelected / $totalPhotos * 100) : 0, 1) . '% от всех фото')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Стоимость товаров', '$' . number_format($totalRevenue, 2))
                ->description($newRevenue > 0 ? '+$' . number_format($newRevenue, 2) . ' новых' : 'Нет новых')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($this->getRevenueChartData()),

            Stat::make('Продано товаров', $soldCount)
                ->description('$' . number_format($soldRevenue, 2) . ' выручка')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Сегодня',
            'week' => 'Неделя',
            'month' => 'Месяц',
        ];
    }

    protected function getChartData($model): array
    {
        $period = match($this->filter) {
            'today' => 24,
            'week' => 7,
            'month' => 30,
            default => 7,
        };

        $data = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            $date = match($this->filter) {
                'today' => now()->subHours($i)->startOfHour(),
                default => now()->subDays($i)->startOfDay(),
            };

            $count = $model::where('created_at', '>=', $date)
                ->where('created_at', '<', $date->copy()->add(match($this->filter) {
                    'today' => 'hour',
                    default => 'day',
                }, 1))
                ->count();

            $data[] = $count;
        }

        return $data;
    }

    protected function getRevenueChartData(): array
    {
        $period = match($this->filter) {
            'today' => 24,
            'week' => 7,
            'month' => 30,
            default => 7,
        };

        $data = [];
        for ($i = $period - 1; $i >= 0; $i--) {
            $date = match($this->filter) {
                'today' => now()->subHours($i)->startOfHour(),
                default => now()->subDays($i)->startOfDay(),
            };

            $sum = Product::where('created_at', '>=', $date)
                ->where('created_at', '<', $date->copy()->add(match($this->filter) {
                    'today' => 'hour',
                    default => 'day',
                }, 1))
                ->sum('price') ?? 0;

            $data[] = (float) $sum;
        }

        return $data;
    }
}
