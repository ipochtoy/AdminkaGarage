<?php

namespace App\Filament\Resources\PhotoBatchResource\Pages;

use App\Filament\Resources\PhotoBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhotoBatch extends EditRecord
{
    protected static string $resource = PhotoBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
