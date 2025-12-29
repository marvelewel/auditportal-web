<?php

namespace App\Http\Controllers;

use App\Models\AuditRKO;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintNotulenController extends Controller
{
    public function download(AuditRKO $record)
    {
        // 1. Ambil data Finding dan urutkan
        // Kita butuh grouping berdasarkan Jenis Temuan (Compliance, Substantive, etc)
        $findings = $record->findings()
            ->with('followups') // Eager load followups biar kenceng
            ->orderBy('created_at')
            ->get();

        // 2. Grouping Manual sesuai urutan Kategori (A, B, C, D)
        // Mapping: 'COMPLIANCE' => 'A', 'SUBSTANTIVE' => 'B', ...
        $groupedFindings = [
            'A' => [
                'title' => 'Compliance',
                'items' => $findings->where('jenis_temuan', 'COMPLIANCE')
            ],
            'B' => [
                'title' => 'Substantive',
                'items' => $findings->where('jenis_temuan', 'SUBSTANTIVE')
            ],
            'C' => [
                'title' => 'Others',
                'items' => $findings->where('jenis_temuan', 'OTHERS')
            ],
            'D' => [
                'title' => 'Information', // Jika ada jenis INFORMATION (Opsional)
                'items' => $findings->where('jenis_temuan', 'INFORMATION') // Sesuaikan jika ada
            ],
        ];

        // 3. Load View PDF
        $pdf = Pdf::loadView('pdf.rko-notulen', [
            'rko' => $record,
            'groupedFindings' => $groupedFindings,
            'today' => now()->translatedFormat('d F Y'),
        ])->setPaper('a4', 'landscape'); // Landscape agar tabel muat banyak

        // 4. Stream/Download (Nama file disesuaikan)
        $fileName = 'Notulen_' . str_replace(' ', '_', $record->nama_obyek_pemeriksaan) . '.pdf';
        
        return $pdf->stream($fileName);
    }
}