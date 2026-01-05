<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use App\Filament\Resources\ActivityLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - logs are auto-generated
        ];
    }

    /**
     * Customize the heading
     */
    public function getHeading(): string
    {
        return 'Activity Log - Audit Trail';
    }

    /**
     * Customize the subheading
     */
    public function getSubheading(): ?string
    {
        return 'Catatan aktivitas sistem (WHO, WHAT, WHEN)';
    }
}
