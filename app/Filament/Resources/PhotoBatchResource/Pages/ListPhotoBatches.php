<?php

namespace App\Filament\Resources\PhotoBatchResource\Pages;

use App\Filament\Resources\PhotoBatchResource;
use App\Models\PhotoBatch;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListPhotoBatches extends ListRecords
{
    protected static string $resource = PhotoBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'товары' => Tab::make('Товары')
                ->badge(PhotoBatch::whereIn('status', ['pending', 'processed'])->count())
                ->modifyQueryUsing(fn($query) => $query->whereIn('status', ['pending', 'processed'])),

            'обработано' => Tab::make('Обработано')
                ->badge(PhotoBatch::where('status', 'published')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn($query) => $query->where('status', 'published')),
        ];
    }
}
