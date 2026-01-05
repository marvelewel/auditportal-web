<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Request;

/**
 * ============================================================
 * AUTHENTICATION EVENT LISTENER
 * ============================================================
 * 
 * Listens to Laravel's authentication events and logs them.
 * - Login: Successful login
 * - Logout: User logged out
 * - Failed: Failed login attempt
 */
class AuthenticationLogListener
{
    /**
     * Handle successful login
     */
    public function handleLogin(Login $event): void
    {
        /** @var User|null $user */
        $user = $event->user;

        if (!$user instanceof User) {
            return;
        }

        ActivityLogger::auth(
            ActivityLog::EVENT_LOGIN,
            "User {$user->name} ({$user->email}) logged in successfully",
            $user,
            [
                'guard' => $event->guard,
            ]
        );
    }

    /**
     * Handle logout
     */
    public function handleLogout(Logout $event): void
    {
        /** @var User|null $user */
        $user = $event->user;

        if ($user instanceof User) {
            ActivityLogger::auth(
                ActivityLog::EVENT_LOGOUT,
                "User {$user->name} ({$user->email}) logged out",
                $user
            );
        }
    }

    /**
     * Handle failed login attempt
     */
    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'unknown';

        // Create log without user (since login failed)
        ActivityLog::create([
            'log_name' => ActivityLog::LOG_AUTH,
            'event' => ActivityLog::EVENT_LOGIN_FAILED,
            'description' => "Failed login attempt for email: {$email}",
            'subject_id' => null,
            'subject_type' => null,
            'subject_identifier' => $email,
            'causer_id' => null,
            'causer_type' => null,
            'causer_name' => null,
            'causer_role' => null,
            'causer_ip' => Request::ip(),
            'causer_agent' => Request::userAgent(),
            'properties' => [
                'attempted_email' => $email,
                'guard' => $event->guard,
            ],
            'created_at' => now(),
        ]);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe($events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
        ];
    }
}
