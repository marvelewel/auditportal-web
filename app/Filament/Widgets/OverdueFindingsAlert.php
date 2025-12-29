<?php

namespace App\Filament\Widgets;

use App\Models\Finding;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class OverdueFindingsAlert extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $filters = $this->filters;

        // Base query untuk temuan overdue (OPEN + due_date sudah lewat)
        $overdueQuery = Finding::query()
            ->join('audit_rkos', 'findings.audit_rko_id', '=', 'audit_rkos.id')
            ->where('findings.status_finding', 'OPEN')
            ->whereDate('findings.due_date', '<', now())
            ->when($filters['department'] ?? null, fn($q, $d) => $q->where('audit_rkos.departemen_auditee', $d))
            ->when($filters['year'] ?? null, fn($q, $y) => $q->whereYear('findings.created_at', $y))
            ->when($filters['quarter'] ?? null, fn($q, $qt) => $q->whereRaw('EXTRACT(QUARTER FROM findings.created_at) = ?', [$qt]));

        $overdueCount = (clone $overdueQuery)->count();

        // Hitung rata-rata keterlambatan
        $overdueFindings = (clone $overdueQuery)->select('findings.due_date')->get();
        $totalDaysLate = 0;

        foreach ($overdueFindings as $finding) {
            $dueDate = Carbon::parse($finding->due_date);
            $totalDaysLate += now()->diffInDays($dueDate);
        }

        $avgDaysLate = $overdueCount > 0 ? round($totalDaysLate / $overdueCount) : 0;

        // Breakdown Major vs Minor overdue
        $majorOverdue = (clone $overdueQuery)->where('findings.kategori', 'MAJOR')->count();
        $minorOverdue = (clone $overdueQuery)->where('findings.kategori', 'MINOR')->count();

        // Potential Loss dari temuan overdue
        $potentialLossOverdue = (clone $overdueQuery)->sum('findings.potential_loss');

        return [
            Stat::make('⚠️ Temuan Overdue', $overdueCount)
                ->description($overdueCount > 0
                    ? "Rata-rata terlambat {$avgDaysLate} hari"
                    : 'Tidak ada temuan terlambat')
                ->descriptionIcon($overdueCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($overdueCount > 0 ? 'danger' : 'success')
                ->chart($overdueCount > 0 ? [5, 8, 12, 15, 10, 18] : [0, 0, 0, 0, 0]),

            Stat::make('Major Overdue', $majorOverdue)
                ->description('Temuan kritis terlambat')
                ->descriptionIcon('heroicon-m-fire')
                ->color($majorOverdue > 0 ? 'danger' : 'gray'),

            Stat::make('Minor Overdue', $minorOverdue)
                ->description('Temuan minor terlambat')
                ->descriptionIcon('heroicon-m-clock')
                ->color($minorOverdue > 0 ? 'warning' : 'gray'),

            Stat::make('Potensi Kerugian Overdue', 'Rp ' . number_format($potentialLossOverdue, 0, ',', '.'))
                ->description('Risiko finansial tertunda')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($potentialLossOverdue > 0 ? 'danger' : 'gray'),
        ];
    }
}
