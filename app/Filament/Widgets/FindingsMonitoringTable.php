<?php

namespace App\Filament\Widgets;

use App\Models\Finding;
use App\Filament\Resources\FindingResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class FindingsMonitoringTable extends TableWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Monitoring Temuan Prioritas';
    protected static ?int $sort = 40;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Finding::query()
                    ->join('audit_rkos', 'findings.audit_rko_id', '=', 'audit_rkos.id')
                    ->select('findings.*', 'audit_rkos.departemen_auditee')
                    ->where('findings.status_finding', 'OPEN')
                    
                    // Filter Departemen
                    ->when($this->filters['department'] ?? null, function ($query, $dept) {
                        return $query->where('audit_rkos.departemen_auditee', $dept);
                    })
                    
                    // Filter Tahun
                    ->when($this->filters['year'] ?? null, function ($query, $year) {
                        return $query->whereYear('findings.created_at', $year);
                    })
                    
                    // Filter Quarter
                    ->when($this->filters['quarter'] ?? null, function ($query, $quarter) {
                        return $query->whereRaw('EXTRACT(QUARTER FROM findings.created_at) = ?', [$quarter]);
                    })
                    
                    // Priority Order: Major di atas, kemudian Minor, lalu sisanya.
                    ->orderByRaw("
                        CASE findings.kategori
                            WHEN 'MAJOR' THEN 1
                            WHEN 'MINOR' THEN 2
                            ELSE 3
                        END
                    ")
                    ->orderBy('findings.due_date')
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('departemen_auditee')
                    ->label('Departemen')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'MAJOR' => 'danger',
                        'MINOR' => 'warning',
                        'OBSERVASI' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('deskripsi_temuan')
                    ->label('Temuan')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('akar_penyebab')
                    ->label('Akar Masalah')
                    ->limit(40)
                    ->wrap()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('tindakan_perbaikan')
                    ->label('Rekomendasi')
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date('d M Y')
                    ->color(fn ($record) => 
                        $record->due_date && Carbon::parse($record->due_date)->isPast() 
                            ? 'danger' 
                            : 'gray'
                    ),

                Tables\Columns\TextColumn::make('status_finding')
                    ->label('Status')
                    ->badge()
                    ->color('warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->url(fn ($record) =>
                        FindingResource::getUrl('view', ['record' => $record])
                    ),
            ]);
    }
}