<?php

namespace App\Filament\Resources\ProductListingResource\Pages;

use App\Filament\Resources\ProductListingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductListing extends EditRecord
{
    protected static string $resource = ProductListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
