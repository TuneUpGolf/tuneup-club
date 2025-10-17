<?php

namespace App\Http\Middleware\Tenant;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InfluencerSubscription;

class RestrictInfluencer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $user = Auth::user();
        // Allow through if no user or not Instructor
        if (!$user || $user->type !== 'Influencer') {
            return $next($request);
        }
        // dd($user);

        // Allow if instructor has no subscription plan set (e.g. free access or trial)
        if (is_null($user->subscription_plan_id)) {
            return $next($request);
        }

        // ✅ Check central subscription safely
        $subscription = tenancy()->central(function () use ($user) {
            return InfluencerSubscription::where('influencer_id', $user->id)
                ->where('tenant_id', $user->tenant_id)
                ->where('plan_id', $user->subscription_plan_id)
                ->where('status', 'active')
                ->first();
        });

        // 🚫 If no active central subscription found → block access
        if (!$subscription) {
            return redirect()
                ->route('subscription.inactive')
                ->with('error', __('Your subscription is not active. Please renew to continue.'));
        }

        // ✅ All checks passed → continue the request
        return $next($request);
    }
}
