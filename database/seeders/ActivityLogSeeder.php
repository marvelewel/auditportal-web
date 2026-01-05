<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * ============================================================
 * ACTIVITY LOG SEEDER
 * ============================================================
 * 
 * Seeds sample activity logs for testing purposes.
 * Run with: php artisan db:seed --class=ActivityLogSeeder
 */
class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing users or create sample ones
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        $sampleLogs = [
            // Auth logs
            [
                'log_name' => ActivityLog::LOG_AUTH,
                'event' => ActivityLog::EVENT_LOGIN,
                'description' => 'User logged in successfully',
                'subject_type' => 'App\Models\User',
            ],
            [
                'log_name' => ActivityLog::LOG_AUTH,
                'event' => ActivityLog::EVENT_LOGOUT,
                'description' => 'User logged out',
                'subject_type' => 'App\Models\User',
            ],
            [
                'log_name' => ActivityLog::LOG_AUTH,
                'event' => ActivityLog::EVENT_LOGIN_FAILED,
                'description' => 'Failed login attempt for email: test@example.com',
                'subject_type' => null,
            ],

            // Audit RKO logs
            [
                'log_name' => ActivityLog::LOG_AUDIT,
                'event' => ActivityLog::EVENT_CREATE,
                'description' => 'Created new AuditRKO: Pemeriksaan Operasional Departemen IT',
                'subject_type' => 'App\Models\AuditRKO',
                'properties' => [
                    'attributes' => [
                        'nama_obyek_pemeriksaan' => 'Pemeriksaan Operasional Departemen IT',
                        'pic_audit' => 'John Doe',
                        'departemen_auditee' => 'IT',
                        'status_audit' => 'PLANNING',
                    ],
                ],
            ],
            [
                'log_name' => ActivityLog::LOG_AUDIT,
                'event' => ActivityLog::EVENT_STATUS_CHANGE,
                'description' => 'Updated AuditRKO: Pemeriksaan Operasional Departemen IT',
                'subject_type' => 'App\Models\AuditRKO',
                'properties' => [
                    'old' => ['status_audit' => 'PLANNING'],
                    'attributes' => ['status_audit' => 'IN_PROGRESS'],
                ],
            ],

            // Finding logs
            [
                'log_name' => ActivityLog::LOG_AUDIT,
                'event' => ActivityLog::EVENT_CREATE,
                'description' => 'Created new Finding: Ketidaksesuaian SOP Backup Data',
                'subject_type' => 'App\Models\Finding',
                'properties' => [
                    'attributes' => [
                        'jenis_temuan' => 'MAJOR',
                        'kategori' => 'Compliance',
                        'deskripsi_temuan' => 'Ditemukan ketidaksesuaian dalam pelaksanaan SOP backup data',
                        'status_finding' => 'OPEN',
                    ],
                ],
            ],
            [
                'log_name' => ActivityLog::LOG_AUDIT,
                'event' => ActivityLog::EVENT_UPDATE,
                'description' => 'Updated Finding: Ketidaksesuaian SOP Backup Data',
                'subject_type' => 'App\Models\Finding',
                'properties' => [
                    'old' => ['pic_auditee' => 'Jane Doe'],
                    'attributes' => ['pic_auditee' => 'Bob Smith'],
                ],
            ],

            // Follow-up logs
            [
                'log_name' => ActivityLog::LOG_AUDIT,
                'event' => ActivityLog::EVENT_CREATE,
                'description' => 'Created new Followup: Perbaikan prosedur backup',
                'subject_type' => 'App\Models\Followup',
                'properties' => [
                    'attributes' => [
                        'keterangan' => 'Telah dilakukan perbaikan prosedur backup sesuai rekomendasi',
                        'tanggal_fu' => now()->subDays(5)->toDateString(),
                    ],
                ],
            ],
            [
                'log_name' => ActivityLog::LOG_AUDIT,
                'event' => ActivityLog::EVENT_UPLOAD,
                'description' => 'Uploaded file evidence_backup.pdf to Followup',
                'subject_type' => 'App\Models\Followup',
                'properties' => [
                    'file_name' => 'evidence_backup.pdf',
                ],
            ],

            // Memo logs
            [
                'log_name' => ActivityLog::LOG_AUDIT,
                'event' => ActivityLog::EVENT_CREATE,
                'description' => 'Created new Memo: MEM-001',
                'subject_type' => 'App\Models\Memo',
                'properties' => [
                    'attributes' => [
                        'no_dokumen' => 'MEMO/2025/001',
                        'perihal_dokumen' => 'Pengumuman Audit Internal Q1',
                        'jenis_dokumen' => 'MEMO',
                        'status' => 'DRAFT',
                    ],
                ],
            ],
            [
                'log_name' => ActivityLog::LOG_AUDIT,
                'event' => ActivityLog::EVENT_STATUS_CHANGE,
                'description' => 'Updated Memo: MEM-001 - Status changed',
                'subject_type' => 'App\Models\Memo',
                'properties' => [
                    'old' => ['status' => 'DRAFT'],
                    'attributes' => ['status' => 'PUBLISHED'],
                ],
            ],
        ];

        $this->command->info('Seeding activity logs...');

        foreach ($sampleLogs as $index => $log) {
            $user = $users->random();
            $createdAt = now()->subMinutes(rand(1, 10080)); // Random time in last 7 days

            ActivityLog::create([
                'id' => Str::uuid(),
                'log_name' => $log['log_name'],
                'event' => $log['event'],
                'description' => $log['description'],
                'subject_id' => Str::uuid(),
                'subject_type' => $log['subject_type'],
                'subject_identifier' => $log['description'],
                'causer_id' => $log['event'] === ActivityLog::EVENT_LOGIN_FAILED ? null : $user->id,
                'causer_type' => $log['event'] === ActivityLog::EVENT_LOGIN_FAILED ? null : get_class($user),
                'causer_name' => $log['event'] === ActivityLog::EVENT_LOGIN_FAILED ? null : $user->name,
                'causer_role' => $log['event'] === ActivityLog::EVENT_LOGIN_FAILED ? null : $user->role,
                'causer_ip' => '192.168.1.' . rand(1, 254),
                'causer_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
                'properties' => $log['properties'] ?? null,
                'created_at' => $createdAt,
            ]);
        }

        $this->command->info('Created ' . count($sampleLogs) . ' activity logs.');
    }
}
