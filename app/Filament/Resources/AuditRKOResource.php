<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditRKOResource\Pages;
use App\Filament\Resources\AuditRKOResource\RelationManagers;
use App\Models\AuditRKO;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AuditRKOResource extends Resource
{
    protected static ?string $model = AuditRKO::class;

    // ✅ ICON & LABEL YANG RAPI
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Rencana Audit (RKO)';
    protected static ?string $modelLabel = 'Rencana Audit';
    protected static ?string $pluralModelLabel = 'Rencana Audit';
    protected static ?string $recordTitleAttribute = 'nama_obyek_pemeriksaan';
    
    // ✅ NAVIGATION GROUP
    protected static ?string $navigationGroup = 'Audit Management';
    protected static ?int $navigationSort = 1;

    /**
     * ======================================================
     * FORM (INPUT DATA)
     * ======================================================
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Audit')
                ->description('Lengkapi data rencana kunjungan observasi di bawah ini.')
                ->schema([
                    Forms\Components\TextInput::make('nama_obyek_pemeriksaan')
                        ->label('Obyek Pemeriksaan')
                        ->placeholder('Contoh: Gudang Bahan Baku Line A')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('departemen_auditee')
                        ->label('Departemen')
                        ->options([
                            'Dapur' => 'Dapur',
                            'Gudang' => 'Gudang',
                            'Produksi' => 'Produksi',
                            'HRD' => 'HRD',
                            'Finance' => 'Finance',
                            'IT' => 'IT',
                            'Marketing' => 'Marketing',
                            'GA' => 'General Affair',
                        ])
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('pic_audit')
                        ->label('Lead Auditor')
                        ->placeholder('Nama Auditor')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Pelaksanaan')
                        ->required()
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->default(now()),

                    Forms\Components\Select::make('status_audit')
                        ->label('Status')
                        ->options([
                            'PLAN' => 'Plan (Rencana)',
                            'ONGOING' => 'On-Going (Berjalan)',
                            'CLOSED' => 'Closed (Selesai)',
                        ])
                        ->default('PLAN')
                        ->required()
                        ->native(false),
                ])
                ->columns(2),
        ]);
    }

    /**
     * ======================================================
     * INFOLIST (TAMPILAN READ-ONLY / VIEW)
     * ======================================================
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // ❌ BAGIAN TOMBOL ACTION DIHAPUS DARI SINI
            // Agar tidak muncul duplikat di tengah halaman detail.
            
            Infolists\Components\Section::make('Detail RKO')
                ->description('Ringkasan informasi rencana kerja.')
                ->schema([
                    Infolists\Components\Grid::make(4)->schema([
                        Infolists\Components\TextEntry::make('nama_obyek_pemeriksaan')
                            ->label('OBYEK PEMERIKSAAN')
                            ->weight('bold')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->columnSpan(2),

                        Infolists\Components\TextEntry::make('status_audit')
                            ->label('STATUS')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'PLAN' => 'gray',
                                'ONGOING' => 'warning',
                                'CLOSED' => 'success',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('tanggal_mulai')
                            ->label('TANGGAL')
                            ->date('d F Y')
                            ->icon('heroicon-m-calendar'),
                        
                        Infolists\Components\TextEntry::make('departemen_auditee')
                            ->label('DEPARTEMEN')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('pic_audit')
                            ->label('LEAD AUDITOR')
                            ->icon('heroicon-m-user-circle'),
                    ]),
                ]),
        ]);
    }

    /**
     * ======================================================
     * TABLE (LIST DATA)
     * ======================================================
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_obyek_pemeriksaan')
                    ->label('Obyek Audit')
                    ->searchable()
                    ->weight('bold')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->nama_obyek_pemeriksaan),

                Tables\Columns\TextColumn::make('departemen_auditee')
                    ->label('Departemen')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pic_audit')
                    ->label('Auditor')
                    ->icon('heroicon-m-user')
                    ->limit(20)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->label('Tgl. Mulai')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_audit')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PLAN' => 'gray',
                        'ONGOING' => 'warning',
                        'CLOSED' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('departemen_auditee')
                    ->label('Filter Departemen')
                    ->options([
                        'Dapur' => 'Dapur',
                        'Gudang' => 'Gudang',
                        'Produksi' => 'Produksi',
                        'HRD' => 'HRD',
                        'Finance' => 'Finance',
                        'IT' => 'IT',
                    ]),
                Tables\Filters\SelectFilter::make('status_audit')
                    ->label('Filter Status')
                    ->options([
                        'PLAN' => 'Plan',
                        'ONGOING' => 'On-Going',
                        'CLOSED' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    
                    Tables\Actions\EditAction::make()
                        ->visible(fn () => Auth::user()->isAdmin() || Auth::user()->isAuditor()),
                    
                    // ✅ Tombol Export di List Tabel TETAP ADA (Shortcut)
                    Tables\Actions\Action::make('export_notulen')
                        ->label('Export Notulen')
                        ->icon('heroicon-o-printer')
                        ->color('gold')
                        ->url(fn (AuditRKO $record) => route('rko.print-notulen', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn () => Auth::user()->isAdmin()),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->isAdmin()),
                ]),
            ])
            ->emptyStateHeading('Belum ada Rencana Audit')
            ->emptyStateDescription('Buat rencana kunjungan audit baru untuk memulai.');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FindingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAuditRKOS::route('/'),
            'create' => Pages\CreateAuditRKO::route('/create'),
            'view'   => Pages\ViewAuditRKO::route('/{record}'),
            'edit'   => Pages\EditAuditRKO::route('/{record}/edit'),
        ];
    }
}