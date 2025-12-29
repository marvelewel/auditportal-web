<?php

namespace App\Filament\Resources\FindingResource\Pages;

use App\Filament\Resources\FindingResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewFinding extends ViewRecord
{
    protected static string $resource = FindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
