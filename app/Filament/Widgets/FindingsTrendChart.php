<?php

namespace App\Filament\Widgets;

use App\Models\Finding;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class FindingsTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Tren Penyelesaian Temuan';
    protected static ?int $sort = 20;
    protected int|string|array $columnSpan = '2/3';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $filters = $this->filters;
        $cacheKey = 'findings_trend_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {
            $query = Finding::query()
                ->join('audit_rkos', 'findings.audit_rko_id', '=', 'audit_rkos.id');

            if ($filters['department'] ?? null) {
                $query->where('audit_rkos.departemen_auditee', $filters['department']);
            }

            if ($filters['year'] ?? null) {
                $query->whereYear('findings.created_at', $filters['year']);
            }

            if ($filters['quarter'] ?? null) {
                $query->whereRaw('EXTRACT(QUARTER FROM findings.created_at) = ?', [$filters['quarter']]);
            }

            $data = $query
                ->selectRaw("
                    DATE_TRUNC('month', findings.created_at) as month,
                    COUNT(*) FILTER (WHERE findings.status_finding = 'OPEN') as open_count,
                    COUNT(*) FILTER (WHERE findings.status_finding = 'CLOSED') as closed_count
                ")
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return [
                'datasets' => [
                    [
                        'label' => 'Open (Proses)',
                        'data' => $data->pluck('open_count'),
                        'borderColor' => '#D4AF37',
                        'backgroundColor' => 'rgba(212, 175, 55, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'pointRadius' => 4,
                        'pointHoverRadius' => 6,
                        'pointBackgroundColor' => '#D4AF37',
                    ],
                    [
                        'label' => 'Closed (Selesai)',
                        'data' => $data->pluck('closed_count'),
                        'borderColor' => '#1B4D3E',
                        'backgroundColor' => 'rgba(27, 77, 62, 0.1)',
                        'fill' => true,
                        'tension' => 0.4,
                        'pointRadius' => 4,
                        'pointHoverRadius' => 6,
                        'pointBackgroundColor' => '#1B4D3E',
                    ],
                ],
                'labels' => $data->map(fn($row) => Carbon::parse($row->month)->translatedFormat('M Y')),
            ];
        });
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'font' => [
                            'family' => 'Inter', // Font modern
                        ],
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'backgroundColor' => 'rgba(255, 255, 255, 0.9)',
                    'titleColor' => '#1B4D3E', // Judul tooltip hijau
                    'bodyColor' => '#333',
                    'borderColor' => '#e5e7eb',
                    'borderWidth' => 1,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                    'grid' => [
                        'display' => true,
                        'drawBorder' => false,
                        'color' => 'rgba(0,0,0,0.05)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
        ];
    }
}