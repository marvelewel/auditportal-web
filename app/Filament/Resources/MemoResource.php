<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemoResource\Pages;
use App\Models\Memo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;

class MemoResource extends Resource
{
    protected static ?string $model = Memo::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Dokumen Internal';
    protected static ?string $modelLabel = 'Dokumen Internal';
    protected static ?string $pluralModelLabel = 'Arsip Dokumen';
    
    protected static ?string $navigationGroup = 'Audit Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    // KOLOM KIRI (UTAMA)
                    Section::make('Identitas Dokumen')
                        ->description('Informasi registrasi dokumen internal.')
                        ->schema([
                            // ID UNIK (AUTO) - Read-Only saat Edit
                            TextInput::make('id_unik')
                                ->label('ID System')
                                ->disabled() 
                                ->dehydrated(false)
                                ->visible(fn ($operation) => $operation === 'edit'), 

                            TextInput::make('no_dokumen')
                                ->label('Nomor Dokumen')
                                ->placeholder('Contoh: 001/IA/MEMO/2025')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('perihal_dokumen')
                                ->label('Perihal / Judul')
                                ->placeholder('Ringkasan isi dokumen')
                                ->required()
                                ->maxLength(255),

                            Grid::make(2)->schema([
                                Select::make('jenis_dokumen')
                                    ->label('Kategori')
                                    ->options([
                                        'MEMO' => 'Internal Memo',
                                        'PNP' => 'P&P (Kebijakan)',
                                        'MEKANISME' => 'Mekanisme Kerja',
                                        'LNI' => 'LNI (Lembar Note)',
                                        'BERITA_ACARA' => 'Berita Acara',
                                        'GUIDANCE' => 'Guidance / Panduan',
                                        'OTHERS' => 'Lainnya',
                                    ])
                                    ->required()
                                    ->native(false),

                                DatePicker::make('tanggal_terbit')
                                    ->label('Tanggal Terbit')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y'),
                            ]),
                        ])->columnSpan(2),

                    // KOLOM KANAN (ATRIBUT)
                    Section::make('Atribut & Akses')
                        ->schema([
                            Select::make('dept_author')
                                ->label('Departemen Pembuat')
                                ->options([
                                    'IA' => 'Internal Audit',
                                    'HR' => 'HRD',
                                    'MARKETING' => 'Marketing',
                                    'QC' => 'QC',
                                    'ENGINEERING' => 'Engineering',
                                    'ACCOUNTING' => 'Accounting',
                                    'PPIC' => 'PPIC',
                                    'FINANCE' => 'Finance',
                                ])
                                ->searchable()
                                ->required(),
                            
                            CheckboxList::make('ruang_lingkup')
                                ->label('Ruang Lingkup (Scope)')
                                ->options([
                                    'GD' => 'GD (Gudang)',
                                    'GJ' => 'GJ (Ganjuran)',
                                    'WIM' => 'WIM (Pusat)',
                                ])
                                ->columns(1)
                                ->afterStateHydrated(function (CheckboxList $component, $state) {
                                    if (is_string($state)) {
                                        $component->state(explode(', ', $state));
                                    }
                                })
                                ->dehydrateStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state),

                            Select::make('status')
                                ->label('Status Dokumen')
                                ->options([
                                    'EXISTING' => 'Existing (Berlaku)',
                                    'EXPIRED' => 'Expired (Kadaluarsa)',
                                    'OBSOLETE' => 'Obsolete (Usang)',
                                ])
                                ->default('EXISTING')
                                ->required()
                                ->native(false),

                            FileUpload::make('file_dokumen')
                                ->label('File Lampiran (PDF)')
                                ->directory('memos')
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(10240)
                                ->openable()
                                ->downloadable()
                                ->columnSpanFull(),
                        ])->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_unik')
                    ->label('ID')
                    ->weight('bold')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->color('primary'),

                TextColumn::make('no_dokumen')
                    ->label('No. Ref')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('perihal_dokumen')
                    ->label('Perihal')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->perihal_dokumen)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('jenis_dokumen')
                    ->label('Jenis')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('dept_author')
                    ->label('Dept')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'EXISTING' => 'success',
                        'EXPIRED' => 'danger',
                        'OBSOLETE' => 'warning',
                        default => 'gray',
                    }),
                
                // ❌ KOLOM 'file_dokumen' DIHAPUS DARI SINI
                // Kita pindahkan ke bagian ->actions() di bawah agar jadi tombol
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_dokumen')
                    ->label('Filter Jenis'),
                Tables\Filters\SelectFilter::make('dept_author')
                    ->label('Filter Departemen'),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Filter Status'),
            ])
            ->actions([
                // ✅ FITUR BARU: TOMBOL VIEW PDF (Action Button)
                Tables\Actions\Action::make('view_file')
                    ->label('Lihat PDF')   // Label pendek/jelas
                    ->icon('heroicon-m-document-text')
                    ->color('warning')     // Warna Emas/Kuning
                    // Membuka file di tab baru
                    ->url(fn (Memo $record) => $record->file_dokumen ? Storage::url($record->file_dokumen) : null)
                    ->openUrlInNewTab()
                    // Hanya muncul jika file ada
                    ->visible(fn (Memo $record) => !empty($record->file_dokumen))
                    ->tooltip('Buka file dokumen'),

                // Menu Titik Tiga (Edit & Delete)
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada dokumen')
            ->emptyStateDescription('Silakan upload dokumen internal, SOP, atau kebijakan baru.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMemos::route('/'),
            'create' => Pages\CreateMemo::route('/create'),
            'edit' => Pages\EditMemo::route('/{record}/edit'),
        ];
    }
}