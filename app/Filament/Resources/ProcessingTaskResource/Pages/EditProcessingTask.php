<?php

namespace App\Filament\Resources\ProcessingTaskResource\Pages;

use App\Filament\Resources\ProcessingTaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcessingTask extends EditRecord
{
    protected static string $resource = ProcessingTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
