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
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

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
     * FORM (INPUT DATA) - WIZARD BASED
     * ======================================================
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                // ======================================================
                // STEP 1: INFORMASI DASAR
                // ======================================================
                Forms\Components\Wizard\Step::make('Informasi Dasar')
                    ->icon('heroicon-o-identification')
                    ->description('Identitas dan jadwal audit')
                    ->schema([
                        // Section: Identitas Audit
                        Forms\Components\Section::make('Identitas Audit')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('no_urut_laporan')
                                        ->label('No. Urut Laporan')
                                        ->placeholder('Contoh: RKO-2024-001')
                                        ->maxLength(50),

                                    Forms\Components\TextInput::make('pic_tl_pemeriksaan')
                                        ->label('PIC/TL Pemeriksaan')
                                        ->placeholder('Nama Team Leader')
                                        ->maxLength(128),

                                    Forms\Components\TextInput::make('inisial_pic')
                                        ->label('Inisial PIC')
                                        ->placeholder('Contoh: AB')
                                        ->maxLength(10),
                                ]),

                                Forms\Components\TextInput::make('nama_obyek_pemeriksaan')
                                    ->label('Obyek Pemeriksaan')
                                    ->placeholder('Contoh: Gudang Bahan Baku Line A')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Grid::make(2)->schema([
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
                                        ->required()
                                        ->native(false),

                                    Forms\Components\Select::make('sub_departemen')
                                        ->label('Sub Departemen')
                                        ->options([
                                            'MSFM 1' => 'MSFM 1',
                                            'MSFM 2' => 'MSFM 2',
                                            'SFAT' => 'SFAT',
                                            'FA' => 'FA',
                                        ])
                                        ->searchable()
                                        ->preload()
                                        ->native(false)
                                        ->placeholder('Pilih Sub Departemen (Opsional)'),
                                ]),

                                Forms\Components\TextInput::make('pic_audit')
                                    ->label('Lead Auditor')
                                    ->placeholder('Nama Auditor')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        // Section: Jadwal & Status
                        Forms\Components\Section::make('Jadwal & Status')
                            ->icon('heroicon-o-calendar')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\DatePicker::make('tanggal_mulai')
                                        ->label('Tanggal Pelaksanaan')
                                        ->required()
                                        ->native(false)
                                        ->displayFormat('d M Y')
                                        ->default(now()),

                                    Forms\Components\DatePicker::make('tanggal_komitmen_followup')
                                        ->label('Tanggal Komitmen Follow Up')
                                        ->native(false)
                                        ->displayFormat('d M Y'),

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
                                ]),
                            ]),
                    ]),

                // ======================================================
                // STEP 2: TIMELINE DOKUMEN
                // ======================================================
                Forms\Components\Wizard\Step::make('Timeline Dokumen')
                    ->icon('heroicon-o-clock')
                    ->description('Timeline A00 - E00')
                    ->schema([
                        Forms\Components\Section::make('Timeline Dokumen (A00 - E00)')
                            ->description('Tanggal-tanggal penting dalam proses audit')
                            ->icon('heroicon-o-document-duplicate')
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\DatePicker::make('a00_surat_tugas')
                                        ->label('A00 - Surat Tugas')
                                        ->native(false)
                                        ->displayFormat('d M Y')
                                        ->prefixIcon('heroicon-o-document'),

                                    Forms\Components\DatePicker::make('b00_meeting_dts')
                                        ->label('B00 - Meeting DTS')
                                        ->native(false)
                                        ->displayFormat('d M Y')
                                        ->prefixIcon('heroicon-o-user-group'),

                                    Forms\Components\DatePicker::make('c00_notulen')
                                        ->label('C00 - Notulen')
                                        ->native(false)
                                        ->displayFormat('d M Y')
                                        ->prefixIcon('heroicon-o-clipboard-document-list'),
                                ]),

                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\DatePicker::make('d00_report_dirut')
                                        ->label('D00 - Report Dirut')
                                        ->native(false)
                                        ->displayFormat('d M Y')
                                        ->prefixIcon('heroicon-o-document-chart-bar'),

                                    Forms\Components\DatePicker::make('e00_report_distribusi')
                                        ->label('E00 - Report Distribusi')
                                        ->native(false)
                                        ->displayFormat('d M Y')
                                        ->prefixIcon('heroicon-o-paper-airplane'),
                                ]),
                            ]),
                    ]),

                // ======================================================
                // STEP 3: SCORECARD KPI
                // ======================================================
                Forms\Components\Wizard\Step::make('Scorecard KPI')
                    ->icon('heroicon-o-chart-bar')
                    ->description('Metrik dan indikator kinerja')
                    ->schema([
                        // KPI Internal Process
                        Forms\Components\Section::make('KPI Internal Process (P-2.1 Temuan)')
                            ->description('Jumlah temuan audit')
                            ->icon('heroicon-o-magnifying-glass')
                            ->collapsible()
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('temuan_major')
                                        ->label('Major')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->prefixIcon('heroicon-o-exclamation-triangle')
                                        ->suffix('temuan'),

                                    Forms\Components\TextInput::make('temuan_minor')
                                        ->label('Minor')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->prefixIcon('heroicon-o-exclamation-circle')
                                        ->suffix('temuan'),

                                    Forms\Components\TextInput::make('temuan_observasi')
                                        ->label('Observasi')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->prefixIcon('heroicon-o-eye')
                                        ->suffix('temuan'),
                                ]),
                            ]),

                        // KPI Financial F-1
                        Forms\Components\Section::make('KPI Financial F-1 (Optimasi Anggaran)')
                            ->description('Nilai optimasi anggaran dalam Rupiah')
                            ->icon('heroicon-o-banknotes')
                            ->collapsible()
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('f1_personnel')
                                        ->label('Personnel')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->inputMode('decimal'),

                                    Forms\Components\TextInput::make('f1_asset')
                                        ->label('Asset')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->inputMode('decimal'),

                                    Forms\Components\TextInput::make('f1_other')
                                        ->label('Other')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->inputMode('decimal'),
                                ]),
                            ]),

                        // KPI Financial F-2
                        Forms\Components\Section::make('KPI Financial F-2 (Efisiensi)')
                            ->description('Nilai efisiensi dalam Rupiah')
                            ->icon('heroicon-o-arrow-trending-up')
                            ->collapsible()
                            ->schema([
                                Forms\Components\Grid::make(4)->schema([
                                    Forms\Components\TextInput::make('f2_barang')
                                        ->label('Barang')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->inputMode('decimal'),

                                    Forms\Components\TextInput::make('f2_uang')
                                        ->label('Uang')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->inputMode('decimal'),

                                    Forms\Components\TextInput::make('f2_nota')
                                        ->label('Nota')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->inputMode('decimal'),

                                    Forms\Components\TextInput::make('f2_lain')
                                        ->label('Lain-lain')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('Rp')
                                        ->inputMode('decimal'),
                                ]),
                            ]),

                        // KPI Customer
                        Forms\Components\Section::make('KPI Customer')
                            ->description('Metrik kepuasan dan kepatuhan')
                            ->icon('heroicon-o-users')
                            ->collapsible()
                            ->schema([
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\TextInput::make('c11_skor_survei')
                                        ->label('C-1.1 Skor Survei')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->suffix('/ 100')
                                        ->helperText('Skor 0-100'),

                                    Forms\Components\TextInput::make('c12_prosedur_dilanggar')
                                        ->label('C-1.2 Prosedur Dilanggar')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->suffix('prosedur'),

                                    Forms\Components\TextInput::make('audit_lead_time')
                                        ->label('Audit Lead Time')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->suffix('hari'),
                                ]),
                            ]),

                        // Cloud Link
                        Forms\Components\Section::make('Cloud Link')
                            ->description('Link ke dokumen detail RKO')
                            ->icon('heroicon-o-cloud')
                            ->collapsible()
                            ->schema([
                                Forms\Components\TextInput::make('link_detail_rko')
                                    ->label('Link Detail RKO')
                                    ->placeholder('https://drive.google.com/...')
                                    ->url()
                                    ->prefixIcon('heroicon-o-link')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
                ->skippable()
                ->persistStepInQueryString()
                ->columnSpanFull(),
        ]);
    }


    /**
     * ======================================================
     * INFOLIST (TAMPILAN READ-ONLY / VIEW) - DASHBOARD STYLE
     * ======================================================
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // ======================================================
            // SECTION: INFORMASI UMUM
            // ======================================================
            Infolists\Components\Section::make('Informasi Umum')
                ->description('Ringkasan identitas dan status audit')
                ->icon('heroicon-o-identification')
                ->schema([
                    // Header Row
                    Infolists\Components\Grid::make(4)->schema([
                        Infolists\Components\TextEntry::make('nama_obyek_pemeriksaan')
                            ->label('OBYEK PEMERIKSAAN')
                            ->weight('bold')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->columnSpan(2),

                        Infolists\Components\TextEntry::make('status_audit')
                            ->label('STATUS')
                            ->badge()
                            ->color(fn(?string $state): string => match ($state) {
                                'PLAN' => 'gray',
                                'ONGOING' => 'warning',
                                'CLOSED' => 'success',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('no_urut_laporan')
                            ->label('NO. LAPORAN')
                            ->placeholder('-')
                            ->icon('heroicon-m-hashtag'),
                    ]),

                    // Identity Details
                    Infolists\Components\Grid::make(4)->schema([
                        Infolists\Components\TextEntry::make('departemen_auditee')
                            ->label('DEPARTEMEN')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('sub_departemen')
                            ->label('SUB DEPARTEMEN')
                            ->badge()
                            ->color('gray')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('pic_tl_pemeriksaan')
                            ->label('PIC/TL PEMERIKSAAN')
                            ->icon('heroicon-m-user-circle')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('inisial_pic')
                            ->label('INISIAL')
                            ->badge()
                            ->color('primary')
                            ->placeholder('-'),
                    ]),

                    // Auditor & Date Info
                    Infolists\Components\Grid::make(4)->schema([
                        Infolists\Components\TextEntry::make('pic_audit')
                            ->label('LEAD AUDITOR')
                            ->icon('heroicon-m-user'),

                        Infolists\Components\TextEntry::make('tanggal_mulai')
                            ->label('TGL. PELAKSANAAN')
                            ->date('d F Y')
                            ->icon('heroicon-m-calendar'),

                        Infolists\Components\TextEntry::make('tanggal_komitmen_followup')
                            ->label('TGL. KOMITMEN FOLLOW UP')
                            ->date('d F Y')
                            ->icon('heroicon-m-calendar-days')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('audit_lead_time')
                            ->label('AUDIT LEAD TIME')
                            ->suffix(' hari')
                            ->icon('heroicon-m-clock')
                            ->placeholder('0 hari'),
                    ]),
                ]),

            // ======================================================
            // SECTION: TIMELINE DOKUMEN
            // ======================================================
            Infolists\Components\Section::make('Timeline Dokumen (A00 - E00)')
                ->description('Tanggal-tanggal penting dalam proses audit')
                ->icon('heroicon-o-clock')
                ->collapsible()
                ->schema([
                    Infolists\Components\Grid::make(5)->schema([
                        Infolists\Components\TextEntry::make('a00_surat_tugas')
                            ->label('A00 - SURAT TUGAS')
                            ->date('d M Y')
                            ->icon('heroicon-m-document')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('b00_meeting_dts')
                            ->label('B00 - MEETING DTS')
                            ->date('d M Y')
                            ->icon('heroicon-m-user-group')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('c00_notulen')
                            ->label('C00 - NOTULEN')
                            ->date('d M Y')
                            ->icon('heroicon-m-clipboard-document-list')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('d00_report_dirut')
                            ->label('D00 - REPORT DIRUT')
                            ->date('d M Y')
                            ->icon('heroicon-m-document-chart-bar')
                            ->placeholder('-'),

                        Infolists\Components\TextEntry::make('e00_report_distribusi')
                            ->label('E00 - REPORT DISTRIBUSI')
                            ->date('d M Y')
                            ->icon('heroicon-m-paper-airplane')
                            ->placeholder('-'),
                    ]),
                ]),

            // ======================================================
            // SECTION: SCORECARD KPI
            // ======================================================
            Infolists\Components\Section::make('Scorecard KPI')
                ->description('Metrik dan indikator kinerja audit')
                ->icon('heroicon-o-chart-bar')
                ->collapsible()
                ->schema([
                    // KPI Internal Process (P-2.1 Temuan)
                    Infolists\Components\Fieldset::make('KPI Internal Process (P-2.1 Temuan)')
                        ->schema([
                            Infolists\Components\Grid::make(3)->schema([
                                Infolists\Components\TextEntry::make('temuan_major')
                                    ->label('MAJOR')
                                    ->badge()
                                    ->color('danger')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->suffix(' temuan'),

                                Infolists\Components\TextEntry::make('temuan_minor')
                                    ->label('MINOR')
                                    ->badge()
                                    ->color('warning')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->suffix(' temuan'),

                                Infolists\Components\TextEntry::make('temuan_observasi')
                                    ->label('OBSERVASI')
                                    ->badge()
                                    ->color('info')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->suffix(' temuan'),
                            ]),
                        ]),

                    // KPI Financial F-1 (Optimasi Anggaran)
                    Infolists\Components\Fieldset::make('KPI Financial F-1 (Optimasi Anggaran)')
                        ->schema([
                            Infolists\Components\Grid::make(3)->schema([
                                Infolists\Components\TextEntry::make('f1_personnel')
                                    ->label('PERSONNEL')
                                    ->money('IDR')
                                    ->icon('heroicon-m-banknotes'),

                                Infolists\Components\TextEntry::make('f1_asset')
                                    ->label('ASSET')
                                    ->money('IDR')
                                    ->icon('heroicon-m-building-office'),

                                Infolists\Components\TextEntry::make('f1_other')
                                    ->label('OTHER')
                                    ->money('IDR')
                                    ->icon('heroicon-m-squares-plus'),
                            ]),
                        ]),

                    // KPI Financial F-2 (Efisiensi)
                    Infolists\Components\Fieldset::make('KPI Financial F-2 (Efisiensi)')
                        ->schema([
                            Infolists\Components\Grid::make(4)->schema([
                                Infolists\Components\TextEntry::make('f2_barang')
                                    ->label('BARANG')
                                    ->money('IDR')
                                    ->icon('heroicon-m-cube'),

                                Infolists\Components\TextEntry::make('f2_uang')
                                    ->label('UANG')
                                    ->money('IDR')
                                    ->icon('heroicon-m-currency-dollar'),

                                Infolists\Components\TextEntry::make('f2_nota')
                                    ->label('NOTA')
                                    ->money('IDR')
                                    ->icon('heroicon-m-receipt-percent'),

                                Infolists\Components\TextEntry::make('f2_lain')
                                    ->label('LAIN-LAIN')
                                    ->money('IDR')
                                    ->icon('heroicon-m-ellipsis-horizontal-circle'),
                            ]),
                        ]),

                    // KPI Customer
                    Infolists\Components\Fieldset::make('KPI Customer')
                        ->schema([
                            Infolists\Components\Grid::make(2)->schema([
                                Infolists\Components\TextEntry::make('c11_skor_survei')
                                    ->label('C-1.1 SKOR SURVEI')
                                    ->suffix(' / 100')
                                    ->icon('heroicon-m-star')
                                    ->color(fn(?int $state): string => match (true) {
                                        $state === null => 'gray',
                                        $state >= 80 => 'success',
                                        $state >= 60 => 'warning',
                                        default => 'danger',
                                    }),

                                Infolists\Components\TextEntry::make('c12_prosedur_dilanggar')
                                    ->label('C-1.2 PROSEDUR DILANGGAR')
                                    ->suffix(' prosedur')
                                    ->icon('heroicon-m-exclamation-triangle')
                                    ->color(fn(?int $state): string => match (true) {
                                        $state === null => 'gray',
                                        $state === 0 => 'success',
                                        $state <= 2 => 'warning',
                                        default => 'danger',
                                    }),
                            ]),
                        ]),
                ]),

            // ======================================================
            // SECTION: CLOUD LINK
            // ======================================================
            Infolists\Components\Section::make('Cloud Link')
                ->description('Link ke dokumen detail RKO')
                ->icon('heroicon-o-cloud')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('link_detail_rko')
                        ->label('LINK DETAIL RKO')
                        ->url(fn($record) => $record->link_detail_rko)
                        ->openUrlInNewTab()
                        ->icon('heroicon-m-link')
                        ->placeholder('Belum ada link')
                        ->columnSpanFull(),
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
                    ->tooltip(fn($record) => $record->nama_obyek_pemeriksaan),

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
                    ->color(fn(string $state): string => match ($state) {
                        'PLAN' => 'gray',
                        'ONGOING' => 'warning',
                        'CLOSED' => 'success',
                        default => 'gray',
                    }),
            ])
            ->filters([
                // ======================================================
                // FILTER: Departemen Auditee
                // ======================================================
                Tables\Filters\SelectFilter::make('departemen_auditee')
                    ->label('Filter Departemen')
                    ->options([
                        'Dapur' => 'Dapur',
                        'Gudang' => 'Gudang',
                        'Produksi' => 'Produksi',
                        'HRD' => 'HRD',
                        'Finance' => 'Finance',
                        'IT' => 'IT',
                        'Marketing' => 'Marketing',
                        'GA' => 'General Affair',
                    ]),

                // ======================================================
                // FILTER: Status Audit
                // ======================================================
                Tables\Filters\SelectFilter::make('status_audit')
                    ->label('Filter Status')
                    ->options([
                        'PLAN' => 'Plan',
                        'ONGOING' => 'On-Going',
                        'CLOSED' => 'Closed',
                    ]),

                // ======================================================
                // FILTER: Date Range (tanggal_mulai)
                // ======================================================
                Filter::make('tanggal_mulai')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('from')
                                    ->label('Dari Tanggal')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->placeholder('Pilih tanggal awal'),
                                Forms\Components\DatePicker::make('until')
                                    ->label('Sampai Tanggal')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->placeholder('Pilih tanggal akhir'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_mulai', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_mulai', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Dari: ' . Carbon::parse($data['from'])->format('d M Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Sampai: ' . Carbon::parse($data['until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),

                    Tables\Actions\EditAction::make()
                        ->visible(fn() => Auth::user()->isAdmin() || Auth::user()->isAuditor()),

                    // ✅ Tombol Export di List Tabel TETAP ADA (Shortcut)
                    Tables\Actions\Action::make('export_notulen')
                        ->label('Export Notulen')
                        ->icon('heroicon-o-printer')
                        ->color('gold')
                        ->url(fn(AuditRKO $record) => route('rko.print-notulen', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn() => Auth::user()->isAdmin()),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Aksi'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->isAdmin()),
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
            'index' => Pages\ListAuditRKOS::route('/'),
            'create' => Pages\CreateAuditRKO::route('/create'),
            'view' => Pages\ViewAuditRKO::route('/{record}'),
            'edit' => Pages\EditAuditRKO::route('/{record}/edit'),
        ];
    }
}