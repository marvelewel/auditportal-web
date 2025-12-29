<!DOCTYPE html>
<html>
<head>
    <title>Notulen Meeting Audit</title>
    <style>
        /* 1. SETTING HALAMAN AGAR MUAT BANYAK (MARGIN TIPIS) */
        @page {
            margin: 1cm 1cm 1.5cm 1cm; /* Atas Kanan Bawah Kiri */
        }
        
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 9pt; /* Ukuran Font Standar Dokumen Padat */
            color: #000; 
            line-height: 1.2; /* Spasi antar baris diperkecil */
        }

        /* HEADER */
        .header-table { width: 100%; border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
        .logo-box { 
            width: 35px; height: 35px; background: #D4AF37; color: white; 
            font-weight: bold; text-align: center; line-height: 35px; font-size: 18px;
            font-family: serif; border-radius: 4px;
        }
        .header-title { font-size: 11pt; font-weight: bold; }
        .header-sub { font-size: 8pt; font-style: italic; text-align: right; vertical-align: bottom; }

        /* TITLE DOKUMEN */
        .doc-title { 
            text-align: center; font-weight: bold; font-size: 12pt; 
            margin-bottom: 10px; text-transform: uppercase; border-bottom: 1px double #999; 
            padding-bottom: 5px; display: inline-block; width: 100%;
        }

        /* INFO TABLE (RKO DATA) */
        .info-table { width: 100%; margin-bottom: 10px; font-size: 9pt; }
        .info-table td { padding: 2px 0; vertical-align: top; } /* Padding vertikal dikurangi */

        /* MAIN TABLE (ISI NOTULEN) */
        .main-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .main-table th { 
            border: 1px solid #000; padding: 4px; 
            background-color: #e5e7eb; /* Abu-abu agak gelap biar kontras */
            font-weight: bold; text-align: center; font-size: 9pt;
        }
        .main-table td { 
            border: 1px solid #000; padding: 4px; 
            vertical-align: top; text-align: justify;
        }

        /* KOLOM LEBAR (PROPORSIONAL) */
        .col-no { width: 25px; text-align: center; }
        .col-finding { width: 32%; } /* Paling lebar */
        .col-cause { width: 20%; }
        .col-action { width: 30%; }
        .col-pic { width: 8%; text-align: center; }
        .col-time { width: 10%; text-align: center; }

        /* HELPER STYLE - RISK TAG */
        .risk-tag {
            display: block; text-align: right; font-weight: bold; 
            font-size: 8pt; margin-top: 6px; font-style: italic;
        }
        .risk-major { color: #b91c1c; } /* Merah Gelap */
        .risk-minor { color: #b45309; } /* Orange Gelap */
        .risk-obs { color: #1e3a8a; }   /* Biru Gelap */

        .section-header { background-color: #f3f4f6; font-weight: bold; }
        
        /* FOLLOW UP STYLE */
        .fu-list { 
            margin-top: 6px; font-size: 8.5pt; color: #374151; 
            border-top: 1px dashed #d1d5db; padding-top: 4px; 
        }
        .fu-item { margin-bottom: 2px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="40"><div class="logo-box">W</div></td>
            <td style="vertical-align: middle;">
                <span class="header-title">DEPARTEMEN INTERNAL AUDIT</span><br>
                <span style="font-size: 8pt;">PT Wismilak Inti Makmur Tbk.</span>
            </td>
            <td class="header-sub">
                Pasti Ada Cara Yang Lebih Baik
            </td>
        </tr>
    </table>

    <div class="doc-title">NOTULEN MEETING PEMBAHASAN DAFTAR TEMUAN SEMENTARA</div>

    <table class="info-table">
        <tr>
            <td width="15%"><b>Hari, Tanggal</b></td>
            <td width="2%">:</td>
            <td width="35%">{{ \Carbon\Carbon::parse($rko->tanggal_mulai)->translatedFormat('l, d F Y') }}</td>
            
            <td width="15%"><b>Peserta</b></td>
            <td width="2%">:</td>
            <td width="31%">
                {{ $rko->pic_audit }} (Auditor), 
                {{-- Ambil PIC unik dari temuan untuk list peserta --}}
                @php 
                    $pics = $rko->findings->pluck('pic_auditee')->unique()->implode(', ');
                @endphp
                {{ $pics ? $pics : 'Tim ' . $rko->departemen_auditee }}
            </td>
        </tr>
        <tr>
            <td><b>Objek</b></td>
            <td>:</td>
            <td colspan="4">{{ $rko->nama_obyek_pemeriksaan }} (Dept. {{ $rko->departemen_auditee }})</td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th class="col-no">No.</th>
                <th class="col-finding">Finding List / Temuan</th>
                <th class="col-cause">Akar Penyebab</th>
                <th class="col-action">Tindakan Perbaikan & Pencegahan</th>
                <th class="col-pic">PIC</th>
                <th class="col-time">Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedFindings as $code => $group)
                @if($group['items']->count() > 0)
                    <tr class="section-header">
                        <td class="col-no">{{ $code }}.</td>
                        <td colspan="5">{{ $group['title'] }}</td>
                    </tr>

                    @foreach($group['items'] as $index => $finding)
                        <tr>
                            <td style="text-align: center;">{{ $loop->iteration }}.</td>
                            
                            <td>
                                {!! nl2br(e($finding->deskripsi_temuan)) !!}
                                
                                @php
                                    $riskLabel = match($finding->kategori) {
                                        'MAJOR' => '(Ma)',
                                        'MINOR' => '(Mi)',
                                        'OBSERVASI' => '(Obs)',
                                        default => ''
                                    };
                                    $riskClass = match($finding->kategori) {
                                        'MAJOR' => 'risk-major',
                                        'MINOR' => 'risk-minor',
                                        'OBSERVASI' => 'risk-obs',
                                        default => ''
                                    };
                                @endphp
                                @if($riskLabel)
                                    <span class="risk-tag {{ $riskClass }}">{{ $riskLabel }}</span>
                                @endif
                            </td>

                            <td>
                                {!! nl2br(e($finding->akar_penyebab ?? '-')) !!}
                            </td>

                            <td>
                                <b>Rekomendasi:</b><br>
                                {!! nl2br(e($finding->tindakan_perbaikan ?? '-')) !!}

                                @if($finding->followups->count() > 0)
                                    <div class="fu-list">
                                        <b>Progress:</b><br>
                                        @foreach($finding->followups as $fu)
                                            <div class="fu-item">
                                                <span style="font-weight: bold; font-size: 8pt;">[{{ \Carbon\Carbon::parse($fu->tanggal_fu)->format('d/m/y') }}]</span> 
                                                {{ $fu->keterangan }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            <td style="text-align: center;">
                                {{ $finding->pic_auditee }}
                            </td>

                            <td style="text-align: center;">
                                {{ \Carbon\Carbon::parse($finding->due_date)->format('d-M-y') }}
                            </td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
            
            @if($rko->findings->count() == 0)
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">- Belum ada temuan -</td>
                </tr>
            @endif
        </tbody>
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $x = 750; // Posisi X (Kanan) untuk Landscape A4
            $y = 570; // Posisi Y (Bawah)
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $font = $fontMetrics->get_font("Helvetica", "italic");
            $size = 8;
            $color = array(0,0,0);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>

</body>
</html>