<?php

namespace App\Filament\Resources\AuditRKOResource\Pages;

use App\Filament\Resources\AuditRKOResource;
use App\Models\AuditRKO;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ListAuditRKOS extends ListRecords
{
    protected static string $resource = AuditRKOResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ======================================================
            // EXPORT RKO LIST (PDF) - Sejajar dengan Create Button
            // ======================================================
            Actions\Action::make('export_rko_list_pdf')
                ->label('Export RKO List (PDF)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('primary')
                ->visible(fn() => Auth::user()->isAdmin() || Auth::user()->isAuditor())
                ->action(function () {
                    // Get current filter state from table
                    $filters = $this->tableFilters;

                    // Build query with active filters
                    $query = AuditRKO::query()
                        ->with([
                            'findings' => function ($q) {
                        $q->withCount('followups');
                    }
                        ]);

                    // Apply Departemen filter
                    if (!empty($filters['departemen_auditee']['value'])) {
                        $query->where('departemen_auditee', $filters['departemen_auditee']['value']);
                    }

                    // Apply Status filter
                    if (!empty($filters['status_audit']['value'])) {
                        $query->where('status_audit', $filters['status_audit']['value']);
                    }

                    // Apply Date Range filter
                    $fromDate = $filters['tanggal_mulai']['from'] ?? null;
                    $untilDate = $filters['tanggal_mulai']['until'] ?? null;

                    if ($fromDate) {
                        $query->whereDate('tanggal_mulai', '>=', $fromDate);
                    }
                    if ($untilDate) {
                        $query->whereDate('tanggal_mulai', '<=', $untilDate);
                    }

                    // Order by date
                    $query->orderBy('tanggal_mulai', 'desc');

                    // Get the data
                    $rkoList = $query->get();

                    // Aggregate finding counts per RKO
                    $rkoData = $rkoList->map(function ($rko) {
                        $findings = $rko->findings;

                        return [
                            'rko' => $rko,
                            'finding_counts' => [
                                'MAJOR' => $findings->where('kategori', 'MAJOR')->count(),
                                'MINOR' => $findings->where('kategori', 'MINOR')->count(),
                                'OBSERVASI' => $findings->where('kategori', 'OBSERVASI')->count(),
                            ],
                            'total_followups' => $findings->sum('followups_count'),
                        ];
                    });

                    // Format date range for header
                    $fromDateFormatted = $fromDate
                        ? Carbon::parse($fromDate)->translatedFormat('d F Y')
                        : 'Awal';
                    $untilDateFormatted = $untilDate
                        ? Carbon::parse($untilDate)->translatedFormat('d F Y')
                        : 'Sekarang';

                    // Generate PDF
                    $pdf = Pdf::loadView('pdf.rko-list', [
                        'rkoData' => $rkoData,
                        'fromDate' => $fromDateFormatted,
                        'untilDate' => $untilDateFormatted,
                        'generatedAt' => now()->translatedFormat('d F Y, H:i'),
                        'generatedBy' => Auth::user()->name,
                        'filters' => [
                            'departemen' => $filters['departemen_auditee']['value'] ?? 'Semua',
                            'status' => $filters['status_audit']['value'] ?? 'Semua',
                        ],
                    ])
                        ->setPaper('a4', 'landscape');

                    // Generate filename
                    $filename = 'RKO_List_' . now()->format('Y-m-d_His') . '.pdf';

                    return response()->streamDownload(
                        fn() => print ($pdf->output()),
                        $filename
                    );
                }),

            // ======================================================
            // CREATE NEW RKO
            // ======================================================
            Actions\CreateAction::make(),
        ];
    }
}
