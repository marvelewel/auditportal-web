<?php

namespace App\Filament\Resources\AuditRKOResource\Pages;

use App\Filament\Resources\AuditRKOResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAuditRKO extends EditRecord
{
    protected static string $resource = AuditRKOResource::class;

    /**
     * ======================================================
     * AUTH GUARD
     * - AUDITEE TIDAK BOLEH MASUK PAGE INI
     * ======================================================
     */
    protected function authorizeAccess(): void
    {
        abort_unless(
            Auth::user()->isAdmin() || Auth::user()->isAuditor(),
            403
        );
    }

    /**
     * ======================================================
     * HEADER ACTIONS
     * - Delete hanya ADMIN
     * ======================================================
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus RKO')
                ->color('danger')
                ->visible(fn () => Auth::user()->isAdmin()),
        ];
    }

    /**
     * ======================================================
     * NONAKTIFKAN RELATION MANAGER
     * (Temuan tidak boleh muncul di page Edit)
     * ======================================================
     */
    public function getRelationManagers(): array
    {
        return [];
    }

    /**
     * ======================================================
     * FORM ACTIONS
     * - Save: ADMIN & AUDITOR
     * - Cancel: kembali ke View
     * ======================================================
     */
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Simpan Perubahan')
                ->submit('save')
                ->color('primary')
                ->visible(fn () => Auth::user()->isAdmin() || Auth::user()->isAuditor()),

            Actions\Action::make('cancel')
                ->label('Kembali')
                ->url(
                    $this->getResource()::getUrl('view', [
                        'record' => $this->record,
                    ])
                ),
        ];
    }
}
