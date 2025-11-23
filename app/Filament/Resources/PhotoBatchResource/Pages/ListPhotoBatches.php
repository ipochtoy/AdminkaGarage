<?php

namespace App\Filament\Resources\PhotoBatchResource\Pages;

use App\Filament\Resources\PhotoBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPhotoBatches extends ListRecords
{
    protected static string $resource = PhotoBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
