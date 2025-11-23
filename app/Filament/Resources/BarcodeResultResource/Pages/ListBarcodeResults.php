<?php

namespace App\Filament\Resources\BarcodeResultResource\Pages;

use App\Filament\Resources\BarcodeResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarcodeResults extends ListRecords
{
    protected static string $resource = BarcodeResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
