<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnifiedMemoResource\Pages;
use App\Models\UnifiedMemo;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\DocumentNumberService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class UnifiedMemoResource extends Resource
{
    protected static ?string $model = UnifiedMemo::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Memo';

    protected static ?string $modelLabel = 'Memo';

    protected static ?string $pluralModelLabel = 'Memo';

    protected static ?string $navigationGroup = 'Memorandum';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'unified-memos';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user->isAdmin() || $user->isManajer() || $user->isSupervisor();
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        if ($user->isAdmin() || $user->isManajer()) {
            $count = UnifiedMemo::query()
                ->where('status', UnifiedMemo::STATUS_SUBMITTED)
                ->whereHas('approvals', fn($q) => $q
                    ->where('user_id', Auth::id())
                    ->where('status', 'pending'))
                ->count();
            return $count > 0 ? (string) $count : null;
        }
        return null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    // ══════════════════════════════════════════════════════
    // FORM: Conditional rendering based on type
    // ══════════════════════════════════════════════════════
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── Step 1: Select Type ──
                Forms\Components\Section::make('Tipe Dokumen')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Tipe Memo')
                            ->required()
                            ->options([
                                UnifiedMemo::TYPE_GENERAL => '📄 General',
                                UnifiedMemo::TYPE_CONFIDENTIAL => '🔒 Confidential',
                            ])
                            ->default(UnifiedMemo::TYPE_GENERAL)
                            ->native(false)
                            ->live()
                            ->columnSpanFull()
                            ->helperText('Pilih tipe memo. General = memo dengan isi terbuka. Confidential = memo dengan lampiran PDF saja.'),
                    ])
                    ->visible(fn($operation) => $operation === 'create'),

                // ── Informasi Memo ──
                Forms\Components\Section::make('Informasi Memo')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Placeholder::make('number_preview')
                                    ->label('No Surat')
                                    ->content(fn() => DocumentNumberService::preview('MEMO'))
                                    ->helperText('Nomor surat akan digenerate otomatis'),

                                Forms\Components\DatePicker::make('date')
                                    ->label('Tanggal Memo')
                                    ->required()
                                    ->default(now())
                                    ->native(false),
                            ]),

                        Forms\Components\TextInput::make('subject')
                            ->label('Perihal')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                // ── Recipients (GENERAL only) ──
                Forms\Components\Section::make('Penerima')
                    ->visible(fn(Forms\Get $get) => $get('type') === UnifiedMemo::TYPE_GENERAL)
                    ->schema([
                        Forms\Components\Select::make('recipients_to')
                            ->label('Kepada')
                            ->multiple()
                            ->options(fn() => User::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('recipients_cc')
                            ->label('CC (Carbon Copy)')
                            ->multiple()
                            ->options(fn() => User::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('recipients_fi')
                            ->label('FI (For Information)')
                            ->multiple()
                            ->options(fn() => User::pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ]),

                // ── Lampiran Counts (GENERAL only) ──
                Forms\Components\Section::make('Lampiran')
                    ->description('Isikan jumlah dokumen untuk setiap kategori lampiran')
                    ->visible(fn(Forms\Get $get) => $get('type') === UnifiedMemo::TYPE_GENERAL)
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('lampiran_executive_summary')
                                    ->label('Executive Summary')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Forms\Components\TextInput::make('lampiran_summary_report')
                                    ->label('Summary Report')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Forms\Components\TextInput::make('lampiran_laporan_pemeriksaan')
                                    ->label('Laporan Hasil Pemeriksaan')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Forms\Components\TextInput::make('lampiran_lampiran')
                                    ->label('Lampiran')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Forms\Components\TextInput::make('lampiran_berita_acara')
                                    ->label('Berita Acara Serah Terima')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Forms\Components\TextInput::make('lampiran_kertas_kerja')
                                    ->label('Kertas Kerja Pemeriksaan')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),
                            ]),
                    ]),

                // ── Body (GENERAL only) ──
                Forms\Components\Section::make('Isi Memo')
                    ->visible(fn(Forms\Get $get) => $get('type') === UnifiedMemo::TYPE_GENERAL)
                    ->schema([
                        Forms\Components\RichEditor::make('body')
                            ->label('Isi Memo')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('attachment_file')
                            ->label('Attachment (Opsional)')
                            ->directory('memo-attachments')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ]),

                // ── PDF Upload (CONFIDENTIAL only) ──
                Forms\Components\Section::make('Dokumen Confidential')
                    ->visible(fn(Forms\Get $get) => $get('type') === UnifiedMemo::TYPE_CONFIDENTIAL)
                    ->schema([
                        Forms\Components\FileUpload::make('attachment_file')
                            ->label('File PDF')
                            ->required()
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('memo-confidential')
                            ->maxSize(2048)
                            ->helperText('File harus dalam format PDF, maksimal 2 MB')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('confidential_warning')
                            ->label('')
                            ->content(fn() => new \Illuminate\Support\HtmlString(
                                '<div style="padding: 12px 16px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; color: #92400e; font-size: 13px;">' .
                                '🔒 <strong>Memo Confidential</strong> — Isi memo tidak akan ditampilkan di sistem. Hanya file PDF lampiran yang dapat diunduh oleh pihak yang berwenang.' .
                                '</div>'
                            ))
                            ->columnSpanFull(),
                    ]),

                // ── Approval Chain ──
                Forms\Components\Section::make('Approval')
                    ->description('Pilih approver, urutan sesuai urutan dipilih (maks 5)')
                    ->compact()
                    ->schema([
                        Forms\Components\Select::make('approvers_data')
                            ->label('Pilih Approver')
                            ->multiple()
                            ->options(fn() => User::whereIn('role', [User::ROLE_MANAJER, User::ROLE_ADMIN])->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->maxItems(5)
                            ->helperText('Urutan approval = urutan pilih. Hapus & pilih ulang untuk ubah urutan.')
                            ->live()
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('approval_order_preview')
                            ->label('Urutan Approval')
                            ->content(function (Forms\Get $get) {
                                $selectedIds = $get('approvers_data') ?? [];
                                if (empty($selectedIds))
                                    return '—';
                                $users = User::whereIn('id', $selectedIds)->pluck('name', 'id');
                                $lines = [];
                                foreach ($selectedIds as $index => $id) {
                                    $name = $users[$id] ?? '?';
                                    $num = $index + 1;
                                    $lines[] = "<strong>{$num}.</strong> {$name}";
                                }
                                return new \Illuminate\Support\HtmlString(implode(' → ', $lines));
                            })
                            ->visible(fn(Forms\Get $get) => !empty($get('approvers_data')))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    // ══════════════════════════════════════════════════════
    // TABLE
    // ══════════════════════════════════════════════════════
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'GENERAL' => '📄 General',
                        'CONFIDENTIAL' => '🔒 Confidential',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'GENERAL' => 'primary',
                        'CONFIDENTIAL' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('number')
                    ->label('No Memo')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Perihal')
                    ->searchable()
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Pembuat')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        UnifiedMemo::STATUS_DRAFT => 'gray',
                        UnifiedMemo::STATUS_SUBMITTED => 'warning',
                        UnifiedMemo::STATUS_APPROVED => 'success',
                        UnifiedMemo::STATUS_REJECTED => 'danger',
                        UnifiedMemo::STATUS_REVISED => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        UnifiedMemo::STATUS_DRAFT => 'Draft',
                        UnifiedMemo::STATUS_SUBMITTED => 'Submitted',
                        UnifiedMemo::STATUS_APPROVED => 'Approved',
                        UnifiedMemo::STATUS_REJECTED => 'Rejected',
                        UnifiedMemo::STATUS_REVISED => 'Perlu Revisi',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        UnifiedMemo::TYPE_GENERAL => '📄 General',
                        UnifiedMemo::TYPE_CONFIDENTIAL => '🔒 Confidential',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        UnifiedMemo::STATUS_DRAFT => 'Draft',
                        UnifiedMemo::STATUS_SUBMITTED => 'Submitted',
                        UnifiedMemo::STATUS_APPROVED => 'Approved',
                        UnifiedMemo::STATUS_REJECTED => 'Rejected',
                        UnifiedMemo::STATUS_REVISED => 'Perlu Revisi',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->canBeEditedBy(Auth::user())),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->canBeDeletedBy(Auth::user())),

                // ── SUBMIT ──
                Tables\Actions\Action::make('submit')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn($record) => $record->canBeSubmittedBy(Auth::user()))
                    ->requiresConfirmation()
                    ->modalHeading('Submit Memo untuk Approval?')
                    ->modalDescription('Memo akan dikirim ke approver untuk disetujui. Anda tidak dapat mengedit memo setelah submit.')
                    ->action(function ($record) {
                        $record->submit(Auth::user());
                        Notification::make()->title('Memo berhasil di-submit!')->body('Memo telah dikirim ke approver.')->success()->send();
                    }),

                // ── APPROVE ──
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === UnifiedMemo::STATUS_SUBMITTED
                        && $record->isCurrentApprover(Auth::id()))
                    ->requiresConfirmation()
                    ->modalHeading('Approve Memo?')
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Keterangan (Opsional)')
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        ApprovalService::approve($record, Auth::id(), $data['note'] ?? null);
                        Notification::make()->title('Memo berhasil di-approve!')->success()->send();
                    }),

                // ── REVISE ──
                Tables\Actions\Action::make('revise')
                    ->label('Revisi')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->visible(fn($record) => $record->status === UnifiedMemo::STATUS_SUBMITTED
                        && $record->isCurrentApprover(Auth::id()))
                    ->requiresConfirmation()
                    ->modalHeading('Minta Revisi Memo?')
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Catatan Revisi')
                            ->required()
                            ->placeholder('Jelaskan apa yang perlu direvisi...')
                            ->rows(4)
                            ->helperText('Wajib diisi.'),
                    ])
                    ->action(function ($record, array $data) {
                        ApprovalService::revise($record, Auth::id(), $data['note'], Auth::user()->isAdmin());
                        Notification::make()->title('Memo dikembalikan untuk revisi')->warning()->send();
                    }),

                // ── REJECT ──
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === UnifiedMemo::STATUS_SUBMITTED
                        && $record->isCurrentApprover(Auth::id()))
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Memo?')
                    ->modalDescription('⚠️ Keputusan ini bersifat FINAL.')
                    ->form([
                        Forms\Components\Textarea::make('note')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(4)
                            ->helperText('Wajib diisi. Keputusan ini bersifat final.'),
                    ])
                    ->action(function ($record, array $data) {
                        ApprovalService::reject($record, Auth::id(), $data['note']);
                        Notification::make()->title('Memo ditolak')->danger()->send();
                    }),
            ])
            ->bulkActions([]);
    }

    // ══════════════════════════════════════════════════════
    // INFOLIST (VIEW)
    // ══════════════════════════════════════════════════════
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ── Header ──
                Infolists\Components\Section::make('Informasi Memo')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('number')
                                    ->label('No Surat')
                                    ->weight('bold')
                                    ->size('lg'),

                                Infolists\Components\TextEntry::make('type')
                                    ->label('Tipe')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'GENERAL' => 'primary',
                                        'CONFIDENTIAL' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'GENERAL' => 'General',
                                        'CONFIDENTIAL' => 'Confidential',
                                        default => $state,
                                    }),

                                Infolists\Components\TextEntry::make('date')
                                    ->label('Tanggal Memo')
                                    ->date('d M Y'),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        UnifiedMemo::STATUS_DRAFT => 'gray',
                                        UnifiedMemo::STATUS_SUBMITTED => 'warning',
                                        UnifiedMemo::STATUS_APPROVED => 'success',
                                        UnifiedMemo::STATUS_REJECTED => 'danger',
                                        UnifiedMemo::STATUS_REVISED => 'info',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        UnifiedMemo::STATUS_DRAFT => 'Draft',
                                        UnifiedMemo::STATUS_SUBMITTED => 'Submitted',
                                        UnifiedMemo::STATUS_APPROVED => 'Approved',
                                        UnifiedMemo::STATUS_REJECTED => 'Rejected',
                                        UnifiedMemo::STATUS_REVISED => 'Perlu Revisi',
                                        default => $state,
                                    }),
                            ]),

                        Infolists\Components\TextEntry::make('subject')
                            ->label('Perihal'),

                        Infolists\Components\TextEntry::make('creator.name')
                            ->label('Dari'),
                    ]),

                // ── Recipients (GENERAL only) ──
                Infolists\Components\Section::make('Penerima')
                    ->visible(fn($record) => $record->isGeneral())
                    ->schema([
                        Infolists\Components\ViewEntry::make('recipients_display')
                            ->label('')
                            ->view('filament.infolists.memo-recipients-table')
                            ->columnSpanFull(),
                    ]),

                // ── Body (GENERAL only) ──
                Infolists\Components\Section::make('Isi Memo')
                    ->visible(fn($record) => $record->isGeneral())
                    ->schema([
                        Infolists\Components\TextEntry::make('body')
                            ->label('')
                            ->html()
                            ->columnSpanFull(),
                    ]),

                // ── Attachment (CONFIDENTIAL) ──
                Infolists\Components\Section::make('Attachment')
                    ->schema([
                        Infolists\Components\ViewEntry::make('attachments_display')
                            ->label('')
                            ->view('filament.infolists.memo-attachments-list')
                            ->columnSpanFull(),
                    ]),

                // ── Approval ──
                Infolists\Components\Section::make('Status Approval')
                    ->description(fn($record) => $record->approvals->count() . ' approver(s)')
                    ->compact()
                    ->schema([
                        Infolists\Components\ViewEntry::make('approvals')
                            ->label('')
                            ->view('filament.infolists.approval-status-table')
                            ->columnSpanFull(),
                    ]),

                // ── Activity Log ──
                Infolists\Components\Section::make('Riwayat Aktivitas')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\ViewEntry::make('approvalLogs')
                            ->label('')
                            ->view('filament.infolists.approval-logs-table')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnifiedMemos::route('/'),
            'create' => Pages\CreateUnifiedMemo::route('/create'),
            'view' => Pages\ViewUnifiedMemo::route('/{record}'),
            'edit' => Pages\EditUnifiedMemo::route('/{record}/edit'),
        ];
    }
}
