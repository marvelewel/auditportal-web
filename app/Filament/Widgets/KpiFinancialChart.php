<?php

namespace App\Filament\Widgets;

use App\Models\AuditRKO;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class KpiFinancialChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'KPI Financial';
    protected static ?string $description = 'Optimasi Anggaran (F1) & Efisiensi (F2)';
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;
    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $filters = $this->filters;

        $totals = AuditRKO::query()
            ->when($filters['department'] ?? null, fn($q, $d) => $q->where('departemen_auditee', $d))
            ->when($filters['year'] ?? null, fn($q, $y) => $q->whereYear('tanggal_mulai', $y))
            ->when($filters['quarter'] ?? null, function ($q, $quarter) {
                $startMonth = ($quarter - 1) * 3 + 1;
                $endMonth = $quarter * 3;
                return $q->whereMonth('tanggal_mulai', '>=', $startMonth)
                    ->whereMonth('tanggal_mulai', '<=', $endMonth);
            })
            ->selectRaw('
                COALESCE(SUM(f1_personnel), 0) as f1_personnel,
                COALESCE(SUM(f1_asset), 0) as f1_asset,
                COALESCE(SUM(f1_other), 0) as f1_other,
                COALESCE(SUM(f2_barang), 0) as f2_barang,
                COALESCE(SUM(f2_uang), 0) as f2_uang,
                COALESCE(SUM(f2_nota), 0) as f2_nota,
                COALESCE(SUM(f2_lain), 0) as f2_lain
            ')
            ->first();

        return [
            'datasets' => [
                [
                    'label' => 'F1 - Optimasi Anggaran',
                    'data' => [
                        $totals->f1_personnel ?? 0,
                        $totals->f1_asset ?? 0,
                        $totals->f1_other ?? 0,
                    ],
                    'backgroundColor' => '#10b981', // Emerald
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'F2 - Efisiensi',
                    'data' => [
                        $totals->f2_barang ?? 0,
                        $totals->f2_uang ?? 0,
                        $totals->f2_nota ?? 0,
                        $totals->f2_lain ?? 0,
                    ],
                    'backgroundColor' => '#06b6d4', // Cyan
                    'borderRadius' => 4,
                ],
            ],
            'labels' => ['Personnel/Barang', 'Asset/Uang', 'Other/Nota', 'Lain-lain'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Horizontal bar
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'x' => [
                    'ticks' => [
                        'callback' => "function(value) { return 'Rp ' + value.toLocaleString('id-ID'); }",
                    ],
                ],
            ],
        ];
    }
}
