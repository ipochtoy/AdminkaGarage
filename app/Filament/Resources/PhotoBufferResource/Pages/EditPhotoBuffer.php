<?php

namespace App\Filament\Resources\PhotoBufferResource\Pages;

use App\Filament\Resources\PhotoBufferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPhotoBuffer extends EditRecord
{
    protected static string $resource = PhotoBufferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
