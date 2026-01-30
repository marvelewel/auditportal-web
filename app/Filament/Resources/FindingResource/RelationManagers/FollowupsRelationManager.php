<?php

namespace App\Filament\Resources\FindingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class FollowupsRelationManager extends RelationManager
{
    protected static string $relationship = 'followups';

    protected static ?string $title = 'Riwayat Tindak Lanjut (Follow-Up)';

    protected static ?string $icon = 'heroicon-o-chat-bubble-left-right';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Update Progress')
                ->schema([
                    Forms\Components\DatePicker::make('tanggal_fu')
                        ->label('Tanggal Follow-Up')
                        ->required()
                        ->default(now())
                        ->native(false)
                        ->displayFormat('d M Y')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('keterangan')
                        ->label('Deskripsi / Keterangan')
                        ->required()
                        ->rows(3)
                        ->placeholder('Deskripsikan progres perbaikan...')
                        ->columnSpanFull(),

                    // ✅ FIX: Ganti 'evidence_file' jadi 'evidence_path' sesuai database
                    Forms\Components\FileUpload::make('evidence_path')
                        ->label('Bukti Perbaikan (Evidence)')
                        ->disk('public')
                        ->directory('followups')
                        ->preserveFilenames()
                        ->downloadable()
                        ->openable()
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->maxSize(5120)
                        ->helperText('Format: PDF atau Gambar (Maks. 5MB)')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->latest('tanggal_fu'))
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_fu')
                    ->label('TANGGAL')
                    ->date('d M Y')
                    ->sortable()
                    ->width('15%'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('PIC / OLEH')
                    ->badge()
                    ->color('info')
                    // ✅ FIX ERROR: Hapus type hint 'Model' untuk menghindari crash saat null
                    ->description(fn($record) => strtoupper($record->creator?->role ?? '-'))
                    ->width('20%'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('KETERANGAN')
                    ->wrap()
                    ->limit(100)
                    ->tooltip(fn($record) => $record->keterangan),

                Tables\Columns\ViewColumn::make('evidence_path')
                    ->label('BUKTI')
                    ->view('filament.tables.columns.file-viewer-button'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Follow-Up')
                    ->icon('heroicon-m-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['created_by'] = auth()->id();

                        // ✅ FIX: Ambil dari 'evidence_path' (bukan evidence_file)
                        // Filament otomatis mengisi $data['evidence_path'] dengan path file
                        if (!empty($data['evidence_path'])) {
                            // Ambil nama file saja untuk kolom evidence_name
                            $data['evidence_name'] = basename($data['evidence_path']);
                        }

                        return $data;
                    })
                    ->visible(
                        fn() => auth()->user()->isAdmin()
                        || auth()->user()->isAuditor()
                        || auth()->user()->isAuditee()
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->visible(fn($record) => $record && ($record->created_by === auth()->id() || auth()->user()->isAdmin())),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn() => auth()->user()->isAdmin()),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Aksi'),
            ])
            ->emptyStateHeading('Belum ada tindak lanjut')
            ->emptyStateDescription('Klik tombol "Tambah Follow-Up" untuk melaporkan progres perbaikan.');
    }
}