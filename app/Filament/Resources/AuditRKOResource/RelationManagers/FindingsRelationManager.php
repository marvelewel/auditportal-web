<?php

namespace App\Filament\Resources\AuditRKOResource\RelationManagers;

use App\Filament\Resources\FindingResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class FindingsRelationManager extends RelationManager
{
    protected static string $relationship = 'findings';

    protected static ?string $title = 'Daftar Temuan';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return FindingResource::form($form);
    }

    public function table(Table $table): Table
    {
        return $table
            /**
             * ==================================================
             * MATIKAN KLIK BARIS (BIAR TIDAK MASUK EDIT)
             * ==================================================
             */
            ->recordAction(null)

            ->recordTitleAttribute('deskripsi_temuan')
            ->columns([
                Tables\Columns\TextColumn::make('deskripsi_temuan')
                    ->label('DESKRIPSI & AKAR PENYEBAB')
                    ->description(fn (Model $record) => 'Akar: ' . ($record->akar_penyebab ?? '-'))
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('pic_auditee')
                    ->label('PIC')
                    ->searchable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('DUE DATE')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'MAJOR' => 'danger',
                        'MINOR' => 'warning',
                        'OBSERVASI' => 'info',
                        default => 'gray',
                    }),
            ])

            /**
             * ==================================================
             * HEADER ACTIONS
             * - Tambah Temuan: ADMIN & AUDITOR
             * ==================================================
             */
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Temuan')
                    ->modalHeading('Input Temuan Baru')
                    ->icon('heroicon-m-plus')
                    ->button()
                    ->color('primary')
                    ->visible(fn () =>
                        Auth::user()->isAdmin() || Auth::user()->isAuditor()
                    ),
            ])

            /**
             * ==================================================
             * ROW ACTIONS
             * ==================================================
             */
            ->actions([
                // SATU-SATUNYA JALUR KE DETAIL TEMUAN
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Model $record) =>
                        FindingResource::getUrl('view', ['record' => $record])
                    ),

                // DELETE TEMUAN: HANYA ADMIN
                Tables\Actions\DeleteAction::make()
                    ->visible(fn () => Auth::user()->isAdmin()),
            ])

            /**
             * ==================================================
             * BULK ACTIONS
             * ==================================================
             */
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->isAdmin()),
                ]),
            ]);
    }
}
