<?php

namespace App\Filament\Widgets;

use App\Models\AuditRKO;
use App\Models\Finding;
use App\Models\Followup;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class AuditStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $filters = $this->filters;

        // 1. Query Total RKO
        $rkoQuery = AuditRKO::query()
            ->when($filters['department'] ?? null, fn ($q, $d) => $q->where('departemen_auditee', $d))
            ->when($filters['year'] ?? null, fn ($q, $y) => $q->whereYear('created_at', $y))
            ->when($filters['quarter'] ?? null, fn ($q, $qt) => $q->whereRaw('EXTRACT(QUARTER FROM created_at) = ?', [$qt]));

        // 2. Query Base Finding
        $findingBaseQuery = Finding::query()
            ->join('audit_rkos', 'findings.audit_rko_id', '=', 'audit_rkos.id')
            ->when($filters['department'] ?? null, fn ($q, $d) => $q->where('audit_rkos.departemen_auditee', $d))
            ->when($filters['year'] ?? null, fn ($q, $y) => $q->whereYear('findings.created_at', $y))
            ->when($filters['quarter'] ?? null, fn ($q, $qt) => $q->whereRaw('EXTRACT(QUARTER FROM findings.created_at) = ?', [$qt]));

        // 3. Query Total Follow-Up (KOREKSI NAMA TABEL: follow_ups)
        $followupQuery = Followup::query()
            ->join('findings', 'follow_ups.finding_id', '=', 'findings.id') // Gunakan follow_ups
            ->join('audit_rkos', 'findings.audit_rko_id', '=', 'audit_rkos.id')
            ->when($filters['department'] ?? null, fn ($q, $d) => $q->where('audit_rkos.departemen_auditee', $d))
            ->when($filters['year'] ?? null, fn ($q, $y) => $q->whereYear('follow_ups.created_at', $y)) // Gunakan follow_ups
            ->when($filters['quarter'] ?? null, fn ($q, $qt) => $q->whereRaw('EXTRACT(QUARTER FROM follow_ups.created_at) = ?', [$qt])); // Gunakan follow_ups

        return [
            Stat::make('Total RKO', $rkoQuery->count())
                ->description('Rencana audit terdaftar')
                ->icon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Temuan Open', (clone $findingBaseQuery)->where('findings.status_finding', 'OPEN')->count())
                ->description('Belum ditutup')
                ->icon('heroicon-m-exclamation-circle')
                ->color('warning'),

            Stat::make('Temuan Closed', (clone $findingBaseQuery)->where('findings.status_finding', 'CLOSED')->count())
                ->description('Sudah ditutup')
                ->icon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Follow-Up', $followupQuery->count())
                ->description('Semua aktivitas tindak lanjut')
                ->icon('heroicon-m-arrow-path')
                ->color('info'),
        ];
    }
}