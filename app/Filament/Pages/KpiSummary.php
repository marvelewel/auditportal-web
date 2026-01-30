<?php

namespace App\Filament\Pages;

use App\Models\AuditRKO;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class KpiSummary extends BaseDashboard
{
    use HasFiltersAction;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'KPI Summary';
    protected static ?string $title = 'KPI Summary Dashboard';
    protected static ?string $navigationGroup = 'Audit Management';
    protected static ?int $navigationSort = 5;
    protected static string $routePath = 'kpi-summary';

    /**
     * Get widgets for this page
     */
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\KpiOverviewStats::class,
            \App\Filament\Widgets\KpiTemuanChart::class,
            \App\Filament\Widgets\KpiFinancialChart::class,
            \App\Filament\Widgets\KpiByDepartmentTable::class,
        ];
    }

    /**
     * Filter action with Department, Year, Quarter
     */
    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->form([
                    Section::make('Filter KPI')
                        ->schema([
                            Select::make('department')
                                ->label('Departemen')
                                ->placeholder('Semua Departemen')
                                ->options(
                                    AuditRKO::query()
                                        ->whereNotNull('departemen_auditee')
                                        ->distinct()
                                        ->pluck('departemen_auditee', 'departemen_auditee')
                                )
                                ->searchable(),

                            Select::make('year')
                                ->label('Tahun')
                                ->options(
                                    collect(range(now()->year, now()->year - 4))
                                        ->mapWithKeys(fn($y) => [$y => $y])
                                        ->toArray()
                                )
                                ->default(now()->year),

                            Select::make('quarter')
                                ->label('Quarter')
                                ->placeholder('Semua Quarter')
                                ->options([
                                    1 => 'Q1 (Jan - Mar)',
                                    2 => 'Q2 (Apr - Jun)',
                                    3 => 'Q3 (Jul - Sep)',
                                    4 => 'Q4 (Okt - Des)',
                                ]),
                        ])->columns(3),
                ]),
        ];
    }
}
