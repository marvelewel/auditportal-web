<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No edit or delete actions - logs are immutable
        ];
    }

    /**
     * Customize the heading
     */
    public function getHeading(): string
    {
        return 'Detail Activity Log';
    }
}
