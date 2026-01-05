<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * ============================================================
 * ACTIVITY LOGGER SERVICE
 * ============================================================
 * 
 * Centralized service for logging all activities.
 * This is the single source of truth for creating audit logs.
 * 
 * Usage:
 *   ActivityLogger::log($model, 'CREATE', 'Created new finding', ['attributes' => [...]])
 *   ActivityLogger::auth('LOGIN', 'User logged in successfully')
 */
class ActivityLogger
{
    /**
     * ============================================================
     * LOG AN ACTIVITY
     * ============================================================
     * 
     * @param Model|null $subject The entity being acted upon
     * @param string $event The action type (CREATE, UPDATE, DELETE, etc.)
     * @param string $description Human-readable description
     * @param array $properties Additional data (old, attributes, etc.)
     * @param string $logName Log category (auth, audit, system)
     */
    public static function log(
        ?Model $subject,
        string $event,
        string $description,
        array $properties = [],
        string $logName = ActivityLog::LOG_AUDIT
    ): ActivityLog {
        // Get current user
        $user = Auth::user();

        // Get subject identifier for human-readable reference
        $subjectIdentifier = null;
        if ($subject) {
            $subjectIdentifier = self::getSubjectIdentifier($subject);
        }

        // Create the log entry
        return ActivityLog::create([
            'log_name' => $logName,
            'event' => $event,
            'description' => $description,
            'subject_id' => $subject?->getKey(),
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_identifier' => $subjectIdentifier,
            'causer_id' => $user?->id,
            'causer_type' => $user ? get_class($user) : null,
            'causer_name' => $user?->name,
            'causer_role' => $user?->role,
            'causer_ip' => Request::ip(),
            'causer_agent' => Request::userAgent(),
            'properties' => !empty($properties) ? $properties : null,
            'created_at' => now(),
        ]);
    }

    /**
     * ============================================================
     * LOG AUTHENTICATION EVENTS
     * ============================================================
     */
    public static function auth(
        string $event,
        string $description,
        ?User $user = null,
        array $properties = []
    ): ActivityLog {
        $user = $user ?? Auth::user();

        return ActivityLog::create([
            'log_name' => ActivityLog::LOG_AUTH,
            'event' => $event,
            'description' => $description,
            'subject_id' => $user?->id,
            'subject_type' => $user ? get_class($user) : null,
            'subject_identifier' => $user?->email,
            'causer_id' => $user?->id,
            'causer_type' => $user ? get_class($user) : null,
            'causer_name' => $user?->name,
            'causer_role' => $user?->role,
            'causer_ip' => Request::ip(),
            'causer_agent' => Request::userAgent(),
            'properties' => !empty($properties) ? $properties : null,
            'created_at' => now(),
        ]);
    }

    /**
     * ============================================================
     * LOG CREATE EVENT
     * ============================================================
     */
    public static function created(Model $model, ?string $customDescription = null): ActivityLog
    {
        $modelName = class_basename($model);
        $identifier = self::getSubjectIdentifier($model);

        $description = $customDescription
            ?? "Created new {$modelName}" . ($identifier ? ": {$identifier}" : '');

        return self::log(
            $model,
            ActivityLog::EVENT_CREATE,
            $description,
            [
                'attributes' => self::getLoggableAttributes($model),
            ]
        );
    }

    /**
     * ============================================================
     * LOG UPDATE EVENT
     * ============================================================
     */
    public static function updated(Model $model, ?string $customDescription = null): ActivityLog
    {
        $modelName = class_basename($model);
        $identifier = self::getSubjectIdentifier($model);
        $changes = $model->getChanges();
        $original = [];

        // Get original values for changed fields
        foreach (array_keys($changes) as $key) {
            $original[$key] = $model->getOriginal($key);
        }

        // Check if this is a status change
        $statusFields = ['status_audit', 'status_finding', 'status'];
        $statusChanged = !empty(array_intersect(array_keys($changes), $statusFields));

        $event = $statusChanged
            ? ActivityLog::EVENT_STATUS_CHANGE
            : ActivityLog::EVENT_UPDATE;

        $description = $customDescription
            ?? "Updated {$modelName}" . ($identifier ? ": {$identifier}" : '');

        // Filter out sensitive data
        $filteredChanges = self::filterSensitiveData($changes);
        $filteredOriginal = self::filterSensitiveData($original);

        return self::log(
            $model,
            $event,
            $description,
            [
                'old' => $filteredOriginal,
                'attributes' => $filteredChanges,
            ]
        );
    }

    /**
     * ============================================================
     * LOG DELETE EVENT
     * ============================================================
     */
    public static function deleted(Model $model, ?string $customDescription = null): ActivityLog
    {
        $modelName = class_basename($model);
        $identifier = self::getSubjectIdentifier($model);

        $description = $customDescription
            ?? "Deleted {$modelName}" . ($identifier ? ": {$identifier}" : '');

        return self::log(
            $model,
            ActivityLog::EVENT_DELETE,
            $description,
            [
                'old' => self::filterSensitiveData(self::getLoggableAttributes($model)),
            ]
        );
    }

    /**
     * ============================================================
     * LOG UPLOAD EVENT
     * ============================================================
     */
    public static function uploaded(Model $model, string $fileName, ?string $customDescription = null): ActivityLog
    {
        $modelName = class_basename($model);
        $identifier = self::getSubjectIdentifier($model);

        $description = $customDescription
            ?? "Uploaded file '{$fileName}' to {$modelName}" . ($identifier ? ": {$identifier}" : '');

        return self::log(
            $model,
            ActivityLog::EVENT_UPLOAD,
            $description,
            [
                'file_name' => $fileName,
            ]
        );
    }

    /**
     * ============================================================
     * HELPER: Get subject identifier for human-readable reference
     * ============================================================
     */
    protected static function getSubjectIdentifier(Model $model): ?string
    {
        // Check for common identifier fields
        $identifierFields = [
            'nama_obyek_pemeriksaan',  // AuditRKO
            'deskripsi_temuan',        // Finding
            'keterangan',              // FollowUp
            'nomor_memo',              // Memo
            'name',                    // User
            'title',                   // Generic
            'email',                   // User fallback
        ];

        foreach ($identifierFields as $field) {
            if (isset($model->{$field}) && !empty($model->{$field})) {
                $value = $model->{$field};
                // Truncate if too long
                return strlen($value) > 100 ? substr($value, 0, 100) . '...' : $value;
            }
        }

        return $model->getKey();
    }

    /**
     * ============================================================
     * HELPER: Get loggable attributes (exclude sensitive/internal)
     * ============================================================
     */
    protected static function getLoggableAttributes(Model $model): array
    {
        $attributes = $model->getAttributes();
        return self::filterSensitiveData($attributes);
    }

    /**
     * ============================================================
     * HELPER: Filter out sensitive data
     * ============================================================
     */
    protected static function filterSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'api_token',
            'secret_key',
        ];

        return array_filter($data, function ($key) use ($sensitiveFields) {
            return !in_array($key, $sensitiveFields);
        }, ARRAY_FILTER_USE_KEY);
    }
}
