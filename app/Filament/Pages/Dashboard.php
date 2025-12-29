<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    /**
     * ======================================================
     * WIDGET REGISTRATION
     * Urutan: KPI → Status → Alert → Stats → Charts → Tables
     * ======================================================
     */
    public function getWidgets(): array
    {
        return [
            // === ROW 1: STATISTIK DASAR (SUDAH ADA DARI AWAL) ===
            \App\Filament\Widgets\AuditStatsOverview::class,

            // === ROW 2: OVERDUE ALERT (PENTING - Pantau temuan terlambat) ===
            \App\Filament\Widgets\OverdueFindingsAlert::class,

            // === ROW 3: QUICK LINKS (PRAKTIS - Akses cepat input data) ===
            \App\Filament\Widgets\QuickLinksWidget::class,

            // === ROW 4: TREND CHART (2/3) + TOP DEPARTMENT RISK (1/3) ===
            \App\Filament\Widgets\FindingsTrendChart::class,
            \App\Filament\Widgets\TopDepartmentRiskChart::class,

            // === ROW 5: TABEL MONITORING (SUDAH ADA DARI AWAL) ===
            \App\Filament\Widgets\FindingsMonitoringTable::class,

            // === ROW 6: FOLLOW-UP TERBARU ===
            \App\Filament\Widgets\LatestFollowupsFeed::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Pages\Dashboard\Actions\FilterAction::make()
                ->form([
                    \Filament\Forms\Components\Section::make('Filter Audit Portal')
                        ->schema([
                            \Filament\Forms\Components\Select::make('department')
                                ->label('Departemen')
                                ->placeholder('Semua Departemen')
                                ->options(
                                    \App\Models\AuditRKO::query()
                                        ->whereNotNull('departemen_auditee')
                                        ->distinct()
                                        ->pluck('departemen_auditee', 'departemen_auditee')
                                )
                                ->searchable(),

                            \Filament\Forms\Components\Select::make('year')
                                ->label('Tahun')
                                ->options(
                                    collect(range(now()->year, now()->year - 4))
                                        ->mapWithKeys(fn($y) => [$y => $y])
                                        ->toArray()
                                )
                                ->default(now()->year),

                            \Filament\Forms\Components\Select::make('quarter')
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
