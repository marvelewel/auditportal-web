<?php

namespace App\Filament\Widgets;

use App\Models\Finding;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Cache;

class TopDepartmentRiskChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Top 5 Departemen Berisiko';
    protected static ?int $sort = 21;
    protected int|string|array $columnSpan = '2/3';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $filters = $this->filters;
        $cacheKey = 'top_dept_risk_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {
            // Query top 5 departemen dengan temuan terbanyak
            $data = Finding::query()
                ->join('audit_rkos', 'findings.audit_rko_id', '=', 'audit_rkos.id')
                ->whereNotNull('audit_rkos.departemen_auditee')
                ->when($filters['year'] ?? null, fn($q, $y) => $q->whereYear('findings.created_at', $y))
                ->when($filters['quarter'] ?? null, fn($q, $qt) => $q->whereRaw('EXTRACT(QUARTER FROM findings.created_at) = ?', [$qt]))
                ->selectRaw('audit_rkos.departemen_auditee as department, COUNT(*) as total_findings')
                ->groupBy('audit_rkos.departemen_auditee')
                ->orderByDesc('total_findings')
                ->limit(5)
                ->get();

            // Warna gradient dari merah (risiko tinggi) ke kuning (risiko rendah)
            $colors = [
                '#DC2626', // Merah - Risiko tertinggi
                '#EA580C', // Orange tua
                '#F59E0B', // Kuning tua
                '#FBBF24', // Kuning
                '#FCD34D', // Kuning muda - Risiko terendah
            ];

            return [
                'datasets' => [
                    [
                        'label' => 'Jumlah Temuan',
                        'data' => $data->pluck('total_findings'),
                        'backgroundColor' => array_slice($colors, 0, $data->count()),
                        'borderColor' => array_slice($colors, 0, $data->count()),
                        'borderWidth' => 1,
                        'borderRadius' => 4,
                    ],
                ],
                'labels' => $data->pluck('department')->map(function ($dept) {
                    return strlen($dept) > 15 ? substr($dept, 0, 12) . '...' : $dept;
                }),
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Horizontal bar chart
            'plugins' => [
                'legend' => [
                    'display' => false, // Sembunyikan legend karena sudah jelas
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(255, 255, 255, 0.95)',
                    'titleColor' => '#1B4D3E',
                    'bodyColor' => '#333',
                    'borderColor' => '#e5e7eb',
                    'borderWidth' => 1,
                    'callbacks' => [
                        'label' => "function(context) { return context.raw + ' temuan'; }",
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(0,0,0,0.05)',
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
