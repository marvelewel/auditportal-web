<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'id_unik', 
        'no_dokumen', 
        'perihal_dokumen', 
        'jenis_dokumen',
        'tanggal_terbit', 
        'dept_author', 
        'ruang_lingkup', 
        'status', 
        'file_dokumen',
    ];

    protected $casts = [
        'tanggal_terbit' => 'date',
        // 'ruang_lingkup' => 'array' // Opsional: Jika ingin simpan sebagai JSON. Saat ini kita simpan sebagai string koma (GD, GJ) via Resource.
    ];

    /**
     * LOGIKA AUTO GENERATE ID UNIK
     * Dijalankan otomatis saat data sedang dibuat (creating)
     */
    protected static function booted()
    {
        static::creating(function ($memo) {
            // Hanya generate jika id_unik belum diisi
            if (empty($memo->id_unik)) {
                
                // 1. Tentukan Prefix
                $prefixMap = [
                    'MEMO' => 'MEM', 
                    'PNP' => 'PNP', 
                    'MEKANISME' => 'MEK',
                    'LNI' => 'LNI', 
                    'BERITA_ACARA' => 'BA',
                    'GUIDANCE' => 'GUI', 
                    'OTHERS' => 'OTH',
                ];
                $prefix = $prefixMap[$memo->jenis_dokumen] ?? 'DOC';

                // 2. Ambil data terakhir dengan jenis yang sama untuk cek nomor urut
                // Kita gunakan latest() agar aman walau ada data yang dihapus
                $lastRecord = self::where('jenis_dokumen', $memo->jenis_dokumen)
                    ->latest('created_at') // Atau latest('id') jika auto increment
                    ->first();

                $nextNumber = 1;

                if ($lastRecord && $lastRecord->id_unik) {
                    // Format ID di database: PREFIX-001
                    // Kita pecah string berdasarkan strip "-"
                    $parts = explode('-', $lastRecord->id_unik);
                    
                    // Ambil angka di bagian belakang, lalu tambah 1
                    if (isset($parts[1]) && is_numeric($parts[1])) {
                        $nextNumber = intval($parts[1]) + 1;
                    }
                }

                // 3. Format Final: PREFIX-00X
                $memo->id_unik = $prefix . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}