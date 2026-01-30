<?php

namespace App\Filament\Widgets;

use App\Models\AuditRKO;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class KpiByDepartmentTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'KPI per Departemen';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    /**
     * Override getTableRecordKey because we're using aggregated data without primary key
     */
    public function getTableRecordKey(\Illuminate\Database\Eloquent\Model $record): string
    {
        return $record->departemen_auditee ?? 'unknown';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (): Builder {
                $filters = $this->filters;

                return AuditRKO::query()
                    ->selectRaw('
                        departemen_auditee,
                        COUNT(*) as total_rko,
                        COALESCE(SUM(temuan_major), 0) as total_major,
                        COALESCE(SUM(temuan_minor), 0) as total_minor,
                        COALESCE(SUM(temuan_observasi), 0) as total_observasi,
                        COALESCE(SUM(f1_personnel), 0) + COALESCE(SUM(f1_asset), 0) + COALESCE(SUM(f1_other), 0) as total_f1,
                        COALESCE(SUM(f2_barang), 0) + COALESCE(SUM(f2_uang), 0) + COALESCE(SUM(f2_nota), 0) + COALESCE(SUM(f2_lain), 0) as total_f2,
                        COALESCE(AVG(c11_skor_survei), 0) as avg_skor_survei,
                        COALESCE(SUM(c12_prosedur_dilanggar), 0) as total_prosedur,
                        COALESCE(AVG(audit_lead_time), 0) as avg_lead_time
                    ')
                    ->whereNotNull('departemen_auditee')
                    ->when($filters['department'] ?? null, fn($q, $d) => $q->where('departemen_auditee', $d))
                    ->when($filters['year'] ?? null, fn($q, $y) => $q->whereYear('tanggal_mulai', $y))
                    ->when($filters['quarter'] ?? null, function ($q, $quarter) {
                        $startMonth = ($quarter - 1) * 3 + 1;
                        $endMonth = $quarter * 3;
                        return $q->whereMonth('tanggal_mulai', '>=', $startMonth)
                            ->whereMonth('tanggal_mulai', '<=', $endMonth);
                    })
                    ->groupBy('departemen_auditee')
                    ->orderByDesc('total_major');
            })
            ->columns([
                Tables\Columns\TextColumn::make('departemen_auditee')
                    ->label('Departemen')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('total_rko')
                    ->label('Jumlah RKO')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_major')
                    ->label('Major')
                    ->alignCenter()
                    ->color('danger')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_minor')
                    ->label('Minor')
                    ->alignCenter()
                    ->color('warning')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_observasi')
                    ->label('Observasi')
                    ->alignCenter()
                    ->color('info')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_f1')
                    ->label('Optimasi (F1)')
                    ->money('IDR')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_f2')
                    ->label('Efisiensi (F2)')
                    ->money('IDR')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('avg_skor_survei')
                    ->label('Skor Survei')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 1))
                    ->color(fn($state) => $state >= 70 ? 'success' : ($state >= 50 ? 'warning' : 'danger'))
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('avg_lead_time')
                    ->label('Lead Time')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => number_format($state, 0) . ' hari')
                    ->sortable(),
            ])
            ->defaultSort('total_major', 'desc')
            ->striped()
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(10);
    }
}
