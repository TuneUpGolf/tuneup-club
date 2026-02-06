<?php

namespace App\Http\Middleware\Tenant;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InfluencerSubscription;
use App\Models\InstructorSubscription;

class RestrictInfluencer
{
    protected $except = [
        'impersonate.leave',
        'impersonate.*', // You can use wildcards
        'subscription.inactive', // Also exclude the inactive page itself
    ];
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
        if (!$subscription && !$this->hasValidFreeTrial($user)) {
            
            return redirect()
                ->route('subscription.inactive')
                ->with('error', __('Your subscription is not active. Please renew to continue.'));
        }

        // ✅ All checks passed → continue the request
        return $next($request);
    }

    private function hasValidFreeTrial($user)
    {
        // Agar days_limit null hai ya 0 hai, to trial nahi hai
        if (empty($user->days_limit) || $user->days_limit <= 0) {
            return false;
        }

        // Agar start_login_date null hai, to abhi trial start karo
        if (empty($user->start_login_date)) {
            $user->start_login_date = Carbon::now();
            $user->save();
            return true; // Trial start ho gaya
        }

        $startDate = Carbon::parse($user->start_login_date);
        $endDate = $startDate->copy()->addDays($user->days_limit);
        $now = Carbon::now();

        // Check karo agar trial expire ho gaya hai
        if ($now->greaterThan($endDate)) {
            // ❌ Trial khatam ho gaya
            return false;
        }

        // ✅ Trial abhi bhi valid hai
        return true;
    }
}
