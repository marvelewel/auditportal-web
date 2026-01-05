<?php

namespace App\Filament\Resources\FindingResource\Pages;

use App\Filament\Resources\FindingResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Illuminate\Support\Facades\Auth;

class ViewFinding extends ViewRecord
{
    protected static string $resource = FindingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Edit: Admin & Auditor
            Actions\EditAction::make()
                ->visible(fn() => Auth::user()->isAdmin() || Auth::user()->isAuditor()),

            // Delete: Admin Only
            Actions\DeleteAction::make()
                ->visible(fn() => Auth::user()->isAdmin()),
        ];
    }
}
