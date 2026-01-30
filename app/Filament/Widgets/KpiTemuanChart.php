<?php

namespace App\Filament\Widgets;

use App\Models\AuditRKO;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class KpiTemuanChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Distribusi Temuan Audit';
    protected static ?string $description = 'Breakdown Major, Minor, dan Observasi';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;
    protected static ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'doughnut';
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
                COALESCE(SUM(temuan_major), 0) as major,
                COALESCE(SUM(temuan_minor), 0) as minor,
                COALESCE(SUM(temuan_observasi), 0) as observasi
            ')
            ->first();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Temuan',
                    'data' => [
                        $totals->major ?? 0,
                        $totals->minor ?? 0,
                        $totals->observasi ?? 0,
                    ],
                    'backgroundColor' => [
                        '#ef4444', // Red - Major
                        '#f59e0b', // Amber - Minor
                        '#3b82f6', // Blue - Observasi
                    ],
                    'borderColor' => '#fff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Major', 'Minor', 'Observasi'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'maintainAspectRatio' => true,
        ];
    }
}
