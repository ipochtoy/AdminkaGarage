<?php

namespace App\Filament\Resources\ProductListingResource\Pages;

use App\Filament\Resources\ProductListingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductListings extends ListRecords
{
    protected static string $resource = ProductListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - listings are created through PhotoBatchResource
        ];
    }
}
