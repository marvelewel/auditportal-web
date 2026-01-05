<!DOCTYPE html>
<html>

<head>
    <title>RKO List Report</title>
    <style>
        /* ============================================================
           SETTING HALAMAN (LANDSCAPE, MARGIN TIPIS)
           ============================================================ */
        @page {
            size: A4 landscape;
            margin: 1cm 1cm 1.5cm 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.3;
        }

        /* ============================================================
           HEADER SECTION
           ============================================================ */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }

        .logo-box {
            width: 35px;
            height: 35px;
            background: #D4AF37;
            color: white;
            font-weight: bold;
            text-align: center;
            line-height: 35px;
            font-size: 18px;
            font-family: serif;
            border-radius: 4px;
        }

        .header-title {
            font-size: 11pt;
            font-weight: bold;
        }

        .header-sub {
            font-size: 8pt;
            font-style: italic;
            text-align: right;
            vertical-align: bottom;
        }

        /* ============================================================
           DOCUMENT TITLE
           ============================================================ */
        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .date-range {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 5px;
            background: #f3f4f6;
            border: 1px solid #000;
        }

        /* ============================================================
           FILTER INFO
           ============================================================ */
        .filter-info {
            font-size: 8pt;
            margin-bottom: 8px;
            color: #333;
        }

        /* ============================================================
           MAIN TABLE (EXCEL-LIKE)
           ============================================================ */
        .main-table {
            width: 100%;
            border-collapse: collapse;
        }

        .main-table th {
            border: 1px solid #000;
            padding: 6px 4px;
            background-color: #e5e7eb;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }

        .main-table td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: middle;
            font-size: 9pt;
        }

        /* COLUMN WIDTHS */
        .col-no {
            width: 4%;
            text-align: center;
        }

        .col-obyek {
            width: 22%;
        }

        .col-dept {
            width: 10%;
            text-align: center;
        }

        .col-pic {
            width: 12%;
        }

        .col-tanggal {
            width: 10%;
            text-align: center;
        }

        .col-status {
            width: 8%;
            text-align: center;
        }

        .col-major {
            width: 7%;
            text-align: center;
        }

        .col-minor {
            width: 7%;
            text-align: center;
        }

        .col-obs {
            width: 8%;
            text-align: center;
        }

        .col-fu {
            width: 8%;
            text-align: center;
        }

        /* STATUS STYLES */
        .status-plan {
            color: #374151;
        }

        .status-ongoing {
            color: #b45309;
            font-weight: bold;
        }

        .status-closed {
            color: #065f46;
            font-weight: bold;
        }

        /* FINDING COUNT STYLES */
        .count-major {
            color: #b91c1c;
            font-weight: bold;
        }

        .count-minor {
            color: #b45309;
            font-weight: bold;
        }

        .count-obs {
            color: #1e3a8a;
            font-weight: bold;
        }

        .count-fu {
            color: #065f46;
            font-weight: bold;
        }

        /* ============================================================
           FOOTER
           ============================================================ */
        .footer-table {
            width: 100%;
            margin-top: 10px;
            font-size: 8pt;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        /* EMPTY STATE */
        .empty-row td {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>

<body>

    <!-- ============================================================
         HEADER
         ============================================================ -->
    <table class="header-table">
        <tr>
            <td width="40">
                <div class="logo-box">W</div>
            </td>
            <td style="vertical-align: middle;">
                <span class="header-title">DEPARTEMEN INTERNAL AUDIT</span><br>
                <span style="font-size: 8pt;">PT Wismilak Inti Makmur Tbk.</span>
            </td>
            <td class="header-sub">
                Pasti Ada Cara Yang Lebih Baik
            </td>
        </tr>
    </table>

    <!-- ============================================================
         DOCUMENT TITLE
         ============================================================ -->
    <div class="doc-title">DAFTAR RENCANA KERJA OBSERVASI (RKO)</div>
    <div class="date-range">PERIODE: {{ strtoupper($fromDate) }} SAMPAI {{ strtoupper($untilDate) }}</div>

    <!-- ============================================================
         FILTER INFO
         ============================================================ -->
    @if($filters['departemen'] !== 'Semua' || $filters['status'] !== 'Semua')
        <div class="filter-info">
            <strong>Filter:</strong>
            @if($filters['departemen'] !== 'Semua')
                Departemen: {{ $filters['departemen'] }}
            @endif
            @if($filters['status'] !== 'Semua')
                @if($filters['departemen'] !== 'Semua') | @endif
                Status: {{ $filters['status'] }}
            @endif
        </div>
    @endif

    <!-- ============================================================
         MAIN TABLE (EXCEL-LIKE FORMAT)
         ============================================================ -->
    <table class="main-table">
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-obyek">Obyek Pemeriksaan</th>
                <th class="col-dept">Departemen</th>
                <th class="col-pic">PIC Audit</th>
                <th class="col-tanggal">Tgl. Mulai</th>
                <th class="col-status">Status</th>
                <th class="col-major">Major</th>
                <th class="col-minor">Minor</th>
                <th class="col-obs">Observasi</th>
                <th class="col-fu">Follow-Up</th>
            </tr>
        </thead>
        <tbody>
            @if($rkoData->isEmpty())
                <tr class="empty-row">
                    <td colspan="10">- Tidak ada data RKO yang sesuai dengan filter -</td>
                </tr>
            @else
                @foreach($rkoData as $index => $item)
                    <tr>
                        <td class="col-no">{{ $index + 1 }}</td>
                        <td class="col-obyek">{{ $item['rko']->nama_obyek_pemeriksaan }}</td>
                        <td class="col-dept">{{ $item['rko']->departemen_auditee }}</td>
                        <td class="col-pic">{{ $item['rko']->pic_audit }}</td>
                        <td class="col-tanggal">{{ \Carbon\Carbon::parse($item['rko']->tanggal_mulai)->format('d-M-Y') }}</td>
                        <td class="col-status">
                            @php
                                $statusClass = match ($item['rko']->status_audit) {
                                    'PLAN' => 'status-plan',
                                    'ONGOING' => 'status-ongoing',
                                    'CLOSED' => 'status-closed',
                                    default => ''
                                };
                            @endphp
                            <span class="{{ $statusClass }}">{{ $item['rko']->status_audit }}</span>
                        </td>
                        <td class="col-major">
                            <span class="count-major">{{ $item['finding_counts']['MAJOR'] }}</span>
                        </td>
                        <td class="col-minor">
                            <span class="count-minor">{{ $item['finding_counts']['MINOR'] }}</span>
                        </td>
                        <td class="col-obs">
                            <span class="count-obs">{{ $item['finding_counts']['OBSERVASI'] }}</span>
                        </td>
                        <td class="col-fu">
                            <span class="count-fu">{{ $item['total_followups'] }}</span>
                        </td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- ============================================================
         SUMMARY ROW
         ============================================================ -->
    @if($rkoData->isNotEmpty())
        @php
            $totalMajor = $rkoData->sum(fn($item) => $item['finding_counts']['MAJOR']);
            $totalMinor = $rkoData->sum(fn($item) => $item['finding_counts']['MINOR']);
            $totalObs = $rkoData->sum(fn($item) => $item['finding_counts']['OBSERVASI']);
            $totalFU = $rkoData->sum(fn($item) => $item['total_followups']);
        @endphp
        <table class="main-table" style="margin-top: -1px;">
            <tr style="background-color: #d1d5db; font-weight: bold;">
                <td colspan="6" style="text-align: right; padding: 6px; border: 1px solid #000;">
                    <strong>TOTAL ({{ $rkoData->count() }} RKO)</strong>
                </td>
                <td class="col-major" style="border: 1px solid #000;">
                    <span class="count-major">{{ $totalMajor }}</span>
                </td>
                <td class="col-minor" style="border: 1px solid #000;">
                    <span class="count-minor">{{ $totalMinor }}</span>
                </td>
                <td class="col-obs" style="border: 1px solid #000;">
                    <span class="count-obs">{{ $totalObs }}</span>
                </td>
                <td class="col-fu" style="border: 1px solid #000;">
                    <span class="count-fu">{{ $totalFU }}</span>
                </td>
            </tr>
        </table>
    @endif

    <!-- ============================================================
         FOOTER
         ============================================================ -->
    <table class="footer-table">
        <tr>
            <td style="text-align: left;">
                Dicetak oleh: <strong>{{ $generatedBy }}</strong> | {{ $generatedAt }}
            </td>
            <td style="text-align: right;">
                Total RKO: <strong>{{ $rkoData->count() }}</strong> data
            </td>
        </tr>
    </table>

    <!-- PAGE NUMBER -->
    <script type="text/php">
        if (isset($pdf)) {
            $x = 750;
            $y = 570;
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $font = $fontMetrics->get_font("Helvetica", "italic");
            $size = 8;
            $color = array(0,0,0);
            $pdf->page_text($x, $y, $text, $font, $size, $color);
        }
    </script>

</body>

</html>