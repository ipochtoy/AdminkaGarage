<?php

namespace App\Filament\Resources\BarcodeResultResource\Pages;

use App\Filament\Resources\BarcodeResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarcodeResult extends EditRecord
{
    protected static string $resource = BarcodeResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
