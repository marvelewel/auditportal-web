<?php

namespace App\Filament\Resources\FindingResource\Pages;

use App\Filament\Resources\FindingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFindings extends ListRecords
{
    protected static string $resource = FindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
