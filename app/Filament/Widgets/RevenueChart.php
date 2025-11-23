<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Динамика стоимости товаров';

    protected static ?int $sort = 3;

    public ?string $filter = 'week';

    protected function getData(): array
    {
        $days = match($this->filter) {
            'today' => 24,
            'week' => 7,
            'month' => 30,
            default => 7,
        };

        $revenueData = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = match($this->filter) {
                'today' => now()->subHours($i)->startOfHour(),
                default => now()->subDays($i)->startOfDay(),
            };

            $nextDate = $date->copy()->add(match($this->filter) {
                'today' => 'hour',
                default => 'day',
            }, 1);

            $sum = Product::where('created_at', '>=', $date)
                ->where('created_at', '<', $nextDate)
                ->sum('price') ?? 0;

            $revenueData[] = (float) $sum;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Стоимость товаров ($)',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => true,
                ],
            ],
            'labels' => $this->getLabels(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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

