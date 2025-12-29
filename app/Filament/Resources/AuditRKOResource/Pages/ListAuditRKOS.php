<?php

namespace App\Filament\Resources\AuditRKOResource\Pages;

use App\Filament\Resources\AuditRKOResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAuditRKOS extends ListRecords
{
    protected static string $resource = AuditRKOResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
