<?php

namespace App\Filament\Resources\AuditRKOResource\Pages;

use App\Filament\Resources\AuditRKOResource;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewAuditRKO extends ViewRecord
{
    protected static string $resource = AuditRKOResource::class;

    protected function getHeaderActions(): array
    {
        return [
            /**
             * ==================================================
             * 1. EXPORT NOTULEN (PDF)
             * - TOMBOL EMAS WISMILAK
             * ==================================================
             */
            Actions\Action::make('export_notulen')
                ->label('Export Notulen')
                ->icon('heroicon-o-printer')
                ->color('gold') // Warna Emas Custom
                ->url(fn ($record) => route('rko.print-notulen', $record))
                ->openUrlInNewTab(),

            /**
             * ==================================================
             * 2. EDIT RKO (MODAL / SLIDE OVER)
             * - Admin & Auditor Only
             * ==================================================
             */
            Actions\EditAction::make()
                ->label('Edit RKO')
                ->icon('heroicon-m-pencil-square')
                ->color('primary') // Hijau Wismilak
                ->visible(fn () => Auth::user()->isAdmin() || Auth::user()->isAuditor())
                ->form(fn (Form $form) => AuditRKOResource::form($form))
                ->modalHeading('Edit Audit RKO')
                ->modalSubmitActionLabel('Simpan Perubahan')
                ->modalWidth('4xl')
                ->slideOver()
                ->url(null), // Mencegah redirect ke halaman edit terpisah

            /**
             * ==================================================
             * 3. DELETE RKO
             * - Admin Only
             * ==================================================
             */
            Actions\DeleteAction::make()
                ->label('Hapus RKO')
                ->icon('heroicon-m-trash')
                ->color('danger')
                ->visible(fn () => Auth::user()->isAdmin()),
        ];
    }
}