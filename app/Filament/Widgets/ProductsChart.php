<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\PhotoBatch;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Динамика создания товаров и карточек';

    protected static ?int $sort = 2;

    public ?string $filter = 'week';

    protected function getData(): array
    {
        $period = match($this->filter) {
            'today' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            default => now()->subWeek(),
        };

        $interval = match($this->filter) {
            'today' => 'perHour',
            'week' => 'perDay',
            'month' => 'perDay',
            default => 'perDay',
        };

        // Данные по товарам
        $productsData = [];
        $batchesData = [];
        
        $days = match($this->filter) {
            'today' => 24,
            'week' => 7,
            'month' => 30,
            default => 7,
        };

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = match($this->filter) {
                'today' => now()->subHours($i)->startOfHour(),
                default => now()->subDays($i)->startOfDay(),
            };

            $nextDate = $date->copy()->add(match($this->filter) {
                'today' => 'hour',
                default => 'day',
            }, 1);

            $productsData[] = Product::where('created_at', '>=', $date)
                ->where('created_at', '<', $nextDate)
                ->count();

            $batchesData[] = PhotoBatch::where('created_at', '>=', $date)
                ->where('created_at', '<', $nextDate)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Товары на продажу',
                    'data' => $productsData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
                [
                    'label' => 'Карточки товаров',
                    'data' => $batchesData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
            ],
            'labels' => $this->getLabels(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Сегодня',
            'week' => 'Неделя',
            'month' => 'Месяц',
        ];
    }

    protected function getLabels(): array
    {
        $days = match($this->filter) {
            'today' => 24,
            'week' => 7,
            'month' => 30,
            default => 7,
        };

        $labels = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            if ($this->filter === 'today') {
                $labels[] = now()->subHours($i)->format('H:00');
            } else {
                $labels[] = now()->subDays($i)->format('d.m');
            }
        }

        return $labels;
    }
}


