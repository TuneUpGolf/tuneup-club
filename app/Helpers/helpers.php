<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

if (!function_exists('mark_user_online')) {
    /**
     * Mark user as online (30 minutes)
     */
    function mark_user_online($userId = null): void
    {
        if (!$userId && Auth::check()) {
            $userId = Auth::id();
        }

        if ($userId) {
            Redis::setex("user:online:{$userId}", 1800, 'online'); // 30 minutes
        }
    }
}

if (!function_exists('extend_user_online')) {
    /**
     * Extend user online status to 30 minutes from now
     * Call this on every authenticated request
     */
    function extend_user_online($userId = null): void
    {
        if (!$userId && Auth::check()) {
            $userId = Auth::id();
        }

        if ($userId) {
            $key = "user:online:{$userId}";

            // If key exists, extend it to 30 minutes from now
            if (Redis::exists($key)) {
                Redis::expire($key, 1800); // Reset to 30 minutes
            } else {
                // If key doesn't exist, create it
                Redis::setex($key, 1800, 'online');
            }
        }
    }
}

if (!function_exists('mark_user_offline')) {
    /**
     * Mark user as offline (remove key)
     */
    function mark_user_offline($userId = null): void
    {
        if (!$userId && Auth::check()) {
            $userId = Auth::id();
        }

        if ($userId) {
            Redis::del("user:online:{$userId}");
        }
    }
}

if (!function_exists('is_user_online')) {
    /**
     * Check if user is online (key exists)
     */
    function is_user_online($userId): bool
    {
        return (bool) Redis::exists("user:online:{$userId}");
    }
}