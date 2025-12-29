<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FindingResource\Pages;
use App\Filament\Resources\FindingResource\RelationManagers;
use App\Models\Finding;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\Auth;

class FindingResource extends Resource
{
    protected static ?string $model = Finding::class;

    // ✅ NAVIGASI & LABEL PROFESIONAL
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';
    protected static ?string $navigationLabel = 'Temuan Audit';
    protected static ?string $modelLabel = 'Temuan Audit'; // Label Tombol (New Temuan Audit)
    protected static ?string $pluralModelLabel = 'Daftar Temuan';
    protected static ?string $recordTitleAttribute = 'deskripsi_temuan';
    
    protected static ?string $navigationGroup = 'Audit Management';
    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()->isAdmin() || Auth::user()->isAuditor();
    }

    /**
     * ======================================================
     * FORM (INPUT DATA)
     * ======================================================
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            // BAGIAN 1: KONTEKS AUDIT
            Forms\Components\Section::make('Konteks Audit')
                ->description('Hubungkan temuan ini dengan rencana audit (RKO).')
                ->schema([
                    Forms\Components\Select::make('audit_rko_id')
                        ->relationship('rko', 'nama_obyek_pemeriksaan')
                        ->label('RKO Terkait')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),
                ])
                ->visible(fn ($livewire) => ! $livewire instanceof RelationManager),

            // BAGIAN 2: RISIKO & DETAIL
            Forms\Components\Section::make('Detail & Risiko')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Select::make('jenis_temuan')
                            ->label('Jenis')
                            ->options([
                                'COMPLIANCE' => 'Compliance',
                                'SUBSTANTIVE' => 'Substantive',
                                'OTHERS' => 'Others',
                            ])
                            ->required(),

                        Forms\Components\Select::make('kategori')
                            ->label('Tingkat Risiko')
                            ->options([
                                'MAJOR' => 'Major (Tinggi)',
                                'MINOR' => 'Minor (Sedang)',
                                'OBSERVASI' => 'Observasi (Rendah)',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('potential_loss')
                            ->label('Potensi Kerugian')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->default(0),
                    ]),

                    Forms\Components\Textarea::make('deskripsi_temuan')
                        ->label('Deskripsi Temuan')
                        ->placeholder('Jelaskan kondisi yang tidak sesuai...')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Textarea::make('akar_penyebab')
                            ->label('Akar Penyebab')
                            ->placeholder('Mengapa ini terjadi?')
                            ->rows(3),

                        Forms\Components\Textarea::make('tindakan_perbaikan')
                            ->label('Rekomendasi')
                            ->placeholder('Saran perbaikan...')
                            ->rows(3),
                    ]),
                ]),

            // BAGIAN 3: TARGET PENYELESAIAN
            Forms\Components\Section::make('Penyelesaian')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('pic_auditee')
                            ->label('PIC Auditee')
                            ->placeholder('Nama PIC')
                            ->required(),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Target Selesai')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y'),

                        Forms\Components\Select::make('status_finding')
                            ->label('Status')
                            ->options([
                                'OPEN' => 'Open (Proses)',
                                'CLOSED' => 'Closed (Selesai)',
                            ])
                            ->default('OPEN')
                            ->required(),
                    ]),
                ]),
        ]);
    }

    /**
     * ======================================================
     * INFOLIST (VIEW REPORT STYLE)
     * ======================================================
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Overview Temuan')
                ->schema([
                    Infolists\Components\Grid::make(4)->schema([
                        Infolists\Components\TextEntry::make('rko.nama_obyek_pemeriksaan')
                            ->label('RKO TERKAIT')
                            ->weight('bold')
                            ->columnSpan(2),

                        Infolists\Components\TextEntry::make('status_finding')
                            ->label('STATUS')
                            ->badge()
                            ->color(fn (string $state) => $state === 'OPEN' ? 'warning' : 'success'),

                        Infolists\Components\TextEntry::make('due_date')
                            ->label('DUE DATE')
                            ->date('d M Y')
                            ->icon('heroicon-m-calendar')
                            ->color(fn ($record) => $record->status_finding === 'OPEN' && now()->gt($record->due_date) ? 'danger' : 'gray'),
                    ]),
                    
                    Infolists\Components\Grid::make(4)->schema([
                        Infolists\Components\TextEntry::make('kategori')
                            ->label('RISIKO')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'MAJOR' => 'danger',
                                'MINOR' => 'warning',
                                'OBSERVASI' => 'info',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('potential_loss')
                            ->label('POTENSI KERUGIAN')
                            ->money('IDR')
                            ->color('danger'),
                        
                        Infolists\Components\TextEntry::make('pic_auditee')
                            ->label('PIC AUDITEE')
                            ->icon('heroicon-m-user'),
                    ]),
                ]),

            Infolists\Components\Section::make('Detail Laporan')
                ->schema([
                    Infolists\Components\TextEntry::make('deskripsi_temuan')
                        ->label('Deskripsi Temuan')
                        ->markdown()
                        ->prose(),
                    
                    Infolists\Components\Grid::make(2)->schema([
                        Infolists\Components\TextEntry::make('akar_penyebab')
                            ->label('Akar Penyebab')
                            ->markdown(),

                        Infolists\Components\TextEntry::make('tindakan_perbaikan')
                            ->label('Rekomendasi')
                            ->markdown(),
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
                Tables\Columns\TextColumn::make('rko.nama_obyek_pemeriksaan')
                    ->label('Obyek Audit')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->rko?->nama_obyek_pemeriksaan),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Risiko')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'MAJOR' => 'danger',
                        'MINOR' => 'warning',
                        'OBSERVASI' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('deskripsi_temuan')
                    ->label('Temuan')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('pic_auditee')
                    ->label('PIC')
                    ->icon('heroicon-m-user')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => 
                        $record->status_finding === 'OPEN' && now()->gt($record->due_date) 
                        ? 'danger' 
                        : 'gray'
                    ),

                Tables\Columns\TextColumn::make('status_finding')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => $state === 'OPEN' ? 'warning' : 'success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_finding')
                    ->label('Status')
                    ->options([
                        'OPEN' => 'Open',
                        'CLOSED' => 'Closed',
                    ]),
                
                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Risiko')
                    ->options([
                        'MAJOR' => 'Major',
                        'MINOR' => 'Minor',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    
                    Tables\Actions\EditAction::make()
                        ->visible(fn () => Auth::user()->isAdmin() || Auth::user()->isAuditor()),
                    
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
            ->emptyStateHeading('Tidak ada temuan')
            ->emptyStateDescription('Semua audit berjalan lancar, atau belum ada temuan yang dicatat.');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\FollowupsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFindings::route('/'),
            'create' => Pages\CreateFinding::route('/create'),
            'view'   => Pages\ViewFinding::route('/{record}'),
            'edit'   => Pages\EditFinding::route('/{record}/edit'),
        ];
    }
}