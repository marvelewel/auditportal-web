<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * ============================================================
 * ACTIVITY LOG MODEL - ENTERPRISE AUDIT TRAIL
 * ============================================================
 * 
 * This model is IMMUTABLE by design:
 * - No update/delete operations allowed
 * - All audit logs are permanent records
 * 
 * Implements the 3W Principle:
 * - WHO   → causer relationship
 * - WHAT  → event, description, properties
 * - WHEN  → created_at
 */
class ActivityLog extends Model
{
    use HasUuids;

    // ======================================================
    // TABLE CONFIGURATION
    // ======================================================
    protected $table = 'activity_logs';
    public $incrementing = false;
    protected $keyType = 'string';

    // No updated_at column - logs are immutable
    public $timestamps = false;

    const CREATED_AT = 'created_at';

    // ======================================================
    // MASS ASSIGNMENT
    // ======================================================
    protected $fillable = [
        'log_name',
        'event',
        'description',
        'subject_id',
        'subject_type',
        'subject_identifier',
        'causer_id',
        'causer_type',
        'causer_name',
        'causer_role',
        'causer_ip',
        'causer_agent',
        'properties',
        'created_at',
    ];

    // ======================================================
    // CASTS
    // ======================================================
    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    // ======================================================
    // EVENT TYPE CONSTANTS
    // ======================================================
    public const EVENT_CREATE = 'CREATE';
    public const EVENT_UPDATE = 'UPDATE';
    public const EVENT_DELETE = 'DELETE';
    public const EVENT_LOGIN = 'LOGIN';
    public const EVENT_LOGOUT = 'LOGOUT';
    public const EVENT_LOGIN_FAILED = 'LOGIN_FAILED';
    public const EVENT_STATUS_CHANGE = 'STATUS_CHANGE';
    public const EVENT_UPLOAD = 'UPLOAD';
    public const EVENT_DOWNLOAD = 'DOWNLOAD';
    public const EVENT_VIEW = 'VIEW';
    public const EVENT_APPROVE = 'APPROVE';
    public const EVENT_REJECT = 'REJECT';

    // ======================================================
    // LOG NAME CONSTANTS
    // ======================================================
    public const LOG_AUTH = 'auth';
    public const LOG_AUDIT = 'audit';
    public const LOG_SYSTEM = 'system';

    // ======================================================
    // RELATIONSHIPS
    // ======================================================

    /**
     * WHO - The user who caused the activity
     */
    public function causer(): MorphTo
    {
        return $this->morphTo('causer');
    }

    /**
     * WHAT - The subject/entity that was affected
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('subject');
    }

    // ======================================================
    // HELPER METHODS
    // ======================================================

    /**
     * Get the old values from properties
     */
    public function getOldAttribute(): ?array
    {
        return $this->properties['old'] ?? null;
    }

    /**
     * Get the new values from properties
     */
    public function getAttributesChangedAttribute(): ?array
    {
        return $this->properties['attributes'] ?? null;
    }

    /**
     * Get formatted changes for display
     */
    public function getFormattedChanges(): array
    {
        $changes = [];
        $old = $this->old ?? [];
        $new = $this->attributes_changed ?? [];

        foreach ($new as $key => $newValue) {
            $oldValue = $old[$key] ?? null;
            $changes[] = [
                'field' => $this->formatFieldName($key),
                'old' => $this->formatValue($oldValue),
                'new' => $this->formatValue($newValue),
            ];
        }

        return $changes;
    }

    /**
     * Format field name for display
     */
    protected function formatFieldName(string $field): string
    {
        return ucwords(str_replace('_', ' ', $field));
    }

    /**
     * Format value for display
     */
    protected function formatValue($value): string
    {
        if (is_null($value)) {
            return '-';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Get event badge color for UI
     */
    public function getEventColor(): string
    {
        return match ($this->event) {
            self::EVENT_CREATE => 'success',
            self::EVENT_UPDATE => 'warning',
            self::EVENT_DELETE => 'danger',
            self::EVENT_LOGIN => 'info',
            self::EVENT_LOGOUT => 'gray',
            self::EVENT_LOGIN_FAILED => 'danger',
            self::EVENT_STATUS_CHANGE => 'warning',
            self::EVENT_UPLOAD => 'info',
            self::EVENT_DOWNLOAD => 'info',
            self::EVENT_APPROVE => 'success',
            self::EVENT_REJECT => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get event icon for UI
     */
    public function getEventIcon(): string
    {
        return match ($this->event) {
            self::EVENT_CREATE => 'heroicon-o-plus-circle',
            self::EVENT_UPDATE => 'heroicon-o-pencil-square',
            self::EVENT_DELETE => 'heroicon-o-trash',
            self::EVENT_LOGIN => 'heroicon-o-arrow-right-on-rectangle',
            self::EVENT_LOGOUT => 'heroicon-o-arrow-left-on-rectangle',
            self::EVENT_LOGIN_FAILED => 'heroicon-o-x-circle',
            self::EVENT_STATUS_CHANGE => 'heroicon-o-arrow-path',
            self::EVENT_UPLOAD => 'heroicon-o-arrow-up-tray',
            self::EVENT_DOWNLOAD => 'heroicon-o-arrow-down-tray',
            self::EVENT_APPROVE => 'heroicon-o-check-circle',
            self::EVENT_REJECT => 'heroicon-o-x-circle',
            default => 'heroicon-o-information-circle',
        };
    }

    /**
     * Get human-readable subject type name
     */
    public function getSubjectTypeName(): string
    {
        if (!$this->subject_type) {
            return '-';
        }

        $map = [
            'App\Models\AuditRKO' => 'Audit RKO',
            'App\Models\Finding' => 'Finding',
            'App\Models\Followup' => 'Follow-Up',
            'App\Models\FollowUp' => 'Follow-Up',
            'App\Models\Memo' => 'Memo',
            'App\Models\User' => 'User',
        ];

        return $map[$this->subject_type] ?? class_basename($this->subject_type);
    }

    // ======================================================
    // QUERY SCOPES
    // ======================================================

    public function scopeByUser($query, $userId)
    {
        return $query->where('causer_id', $userId);
    }

    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeByLogName($query, string $logName)
    {
        return $query->where('log_name', $logName);
    }

    public function scopeBySubjectType($query, string $type)
    {
        return $query->where('subject_type', $type);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
