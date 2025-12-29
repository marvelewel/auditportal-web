<?php

namespace App\Filament\Resources\AuditRKOResource\Pages;

use App\Filament\Resources\AuditRKOResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAuditRKO extends CreateRecord
{
    protected static string $resource = AuditRKOResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
