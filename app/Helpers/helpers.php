<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Models\InfluencerSubscription;

if (!function_exists('mark_user_online')) {
    /**
     * Mark user as online (30 minutes)
     */
    function mark_user_online($userId = null): void
    {
        if (app()->environment('local', 'testing')) {
            return;
        }

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
        if (app()->environment('local', 'testing')) {
            return;
        }
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
        if (app()->environment('local', 'testing')) {
            return;
        }
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


if (!function_exists('checkInstructorSubscription')) {
    function checkInstructorSubscription()
    {
          $user = Auth::user();
        // Allow through if no user or not Instructor
        if (!$user || $user->type !== 'Influencer') {
            return false;
        }
        // dd($user);

        // Allow if instructor has no subscription plan set (e.g. free access or trial)
        if (is_null($user->subscription_plan_id)) {
            return false;
        }



        // ✅ Check central subscription safely
        $subscription = tenancy()->central(function () use ($user) {
            return InfluencerSubscription::where('influencer_id', $user->id)
                ->where('tenant_id', $user->tenant_id)
                ->where('plan_id', $user->subscription_plan_id)
                ->where('status', 'active')
                ->first();
        });

        if (!$subscription) {
            return true;
        }

        return false;
    }

    
}