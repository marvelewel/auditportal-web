<?php

namespace App\Traits;

use App\Services\ActivityLogger;

/**
 * ============================================================
 * HAS ACTIVITY LOG TRAIT
 * ============================================================
 * 
 * Attach this trait to any Eloquent model that needs audit logging.
 * It automatically logs CREATE, UPDATE, and DELETE events.
 * 
 * Usage:
 *   class Finding extends Model
 *   {
 *       use HasActivityLog;
 *   }
 * 
 * Customization:
 *   Override these properties in your model:
 *   - $logOnlyDirty: Only log when there are actual changes (default: true)
 *   - $logUnguarded: Log all attributes regardless of $guarded (default: false)
 *   - $dontLogAttributes: Array of attributes to exclude from logging
 */
trait HasActivityLog
{
    /**
     * Boot the trait
     */
    public static function bootHasActivityLog(): void
    {
        // Log CREATE events
        static::created(function ($model) {
            if ($model->shouldLogActivity('created')) {
                ActivityLogger::created($model);
            }
        });

        // Log UPDATE events
        static::updated(function ($model) {
            if ($model->shouldLogActivity('updated') && $model->wasChanged()) {
                ActivityLogger::updated($model);
            }
        });

        // Log DELETE events
        static::deleted(function ($model) {
            if ($model->shouldLogActivity('deleted')) {
                ActivityLogger::deleted($model);
            }
        });
    }

    /**
     * Determine if activity should be logged
     */
    protected function shouldLogActivity(string $event): bool
    {
        // Check if logging is disabled for this model
        if (property_exists($this, 'disableActivityLog') && $this->disableActivityLog) {
            return false;
        }

        // Check if specific events are disabled
        if (property_exists($this, 'dontLogEvents')) {
            if (in_array($event, $this->dontLogEvents)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get attributes that should not be logged
     */
    public function getExcludedLogAttributes(): array
    {
        $default = [
            'password',
            'remember_token',
            'updated_at',
        ];

        if (property_exists($this, 'dontLogAttributes')) {
            return array_merge($default, $this->dontLogAttributes);
        }

        return $default;
    }
}
