<?php

namespace App\Filament\Widgets;

use App\Models\AuditRKO;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class KpiOverviewStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $filters = $this->filters;

        // Base query with filters
        $query = AuditRKO::query()
            ->when($filters['department'] ?? null, fn($q, $d) => $q->where('departemen_auditee', $d))
            ->when($filters['year'] ?? null, fn($q, $y) => $q->whereYear('tanggal_mulai', $y))
            ->when($filters['quarter'] ?? null, function ($q, $quarter) {
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $quarter * 3;
                return $q->whereMonth('tanggal_mulai', '>=', $startMonth)
                    ->whereMonth('tanggal_mulai', '<=', $endMonth);
            });

        // Calculate KPI totals
        $totals = $query->selectRaw('
            COALESCE(SUM(temuan_major), 0) as total_major,
            COALESCE(SUM(temuan_minor), 0) as total_minor,
            COALESCE(SUM(temuan_observasi), 0) as total_observasi,
            COALESCE(SUM(f1_personnel), 0) + COALESCE(SUM(f1_asset), 0) + COALESCE(SUM(f1_other), 0) as total_f1,
            COALESCE(SUM(f2_barang), 0) + COALESCE(SUM(f2_uang), 0) + COALESCE(SUM(f2_nota), 0) + COALESCE(SUM(f2_lain), 0) as total_f2,
            COALESCE(AVG(c11_skor_survei), 0) as avg_skor_survei,
            COALESCE(SUM(c12_prosedur_dilanggar), 0) as total_prosedur_dilanggar,
            COALESCE(AVG(audit_lead_time), 0) as avg_lead_time,
            COUNT(*) as total_rko
        ')->first();

        $totalTemuan = ($totals->total_major ?? 0) + ($totals->total_minor ?? 0) + ($totals->total_observasi ?? 0);

        return [
            // Card 1: Total Temuan
            Stat::make('Total Temuan', number_format($totalTemuan))
                ->description(
                    "Major: {$totals->total_major} | Minor: {$totals->total_minor} | Obs: {$totals->total_observasi}"
                )
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($totalTemuan > 0 ? 'danger' : 'success')
                ->chart($this->getTemuanTrend()),

            // Card 2: Optimasi Anggaran (F1)
            Stat::make('Optimasi Anggaran (F1)', 'Rp ' . Number::abbreviate($totals->total_f1 ?? 0, precision: 1))
                ->description('Personnel + Asset + Other')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            // Card 3: Efisiensi (F2)
            Stat::make('Efisiensi (F2)', 'Rp ' . Number::abbreviate($totals->total_f2 ?? 0, precision: 1))
                ->description('Barang + Uang + Nota + Lain')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),

            // Card 4: Skor Survei Rata-rata
            Stat::make('Avg Skor Survei', number_format($totals->avg_skor_survei ?? 0, 1) . ' / 100')
                ->description("{$totals->total_prosedur_dilanggar} prosedur dilanggar")
                ->descriptionIcon('heroicon-m-star')
                ->color($totals->avg_skor_survei >= 70 ? 'success' : ($totals->avg_skor_survei >= 50 ? 'warning' : 'danger')),
        ];
    }

    /**
     * Get temuan trend data for sparkline chart
     */
    protected function getTemuanTrend(): array
    {
        $filters = $this->filters;
        $year = $filters['year'] ?? now()->year;

        $monthlyData = AuditRKO::query()
            ->selectRaw('EXTRACT(MONTH FROM tanggal_mulai) as month, 
                         COALESCE(SUM(temuan_major), 0) + COALESCE(SUM(temuan_minor), 0) + COALESCE(SUM(temuan_observasi), 0) as total')
            ->whereYear('tanggal_mulai', $year)
            ->when($filters['department'] ?? null, fn($q, $d) => $q->where('departemen_auditee', $d))
            ->groupByRaw('EXTRACT(MONTH FROM tanggal_mulai)')
            ->orderByRaw('EXTRACT(MONTH FROM tanggal_mulai)')
            ->pluck('total', 'month')
            ->toArray();

        // Fill all 12 months
        $trend = [];
        for ($i = 1; $i <= 12; $i++) {
            $trend[] = (int) ($monthlyData[$i] ?? 0);
        }

        return $trend;
    }
}
