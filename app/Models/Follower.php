<?php

namespace App\Models;

use Carbon\Carbon;
use App\Actions\SendEmail;
use Laravel\Sanctum\HasApiTokens;
use App\Mail\Admin\PasswordResets;
use Illuminate\Support\Facades\URL;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\MailTemplates\Models\MailTemplate;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Follower extends User implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, HasRoles;
    use BelongsToTenant, Impersonate;

    protected $table      = "followers";
    protected $guard_name = 'web';
    protected $fillable   = [
        'id',
        'uuid',
        'name',
        'email',
        'password',
        'country',
        'country_code',
        'dial_code',
        'phone',
        'created_by',
        'email_verified_at',
        'phone_verified_at',
        'dp',
        'type',
        'active_status',
        'bio',
        'stripe_cus_id',
        'social_url_ig',
        'social_url_fb',
        'social_url_x',
        'isGuest',
        'plan_id',
        'plan_expired_date',
        'chat_user_id',
        'group_id',
        'chat_status',
    ];
    protected $hidden = [
        'password',
        'remeberToken',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    public function loginSecurity()
    {
        return $this->hasOne('App\Models\LoginSecurity');
    }
    public function purchase(): HasMany
    {
        return $this->hasMany(Purchase::class, 'follower_id');
    }
    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }
    public function slots(): BelongsToMany
    {
        return $this->belongsToMany(Slots::class, 'slot_follower', 'follower_id', 'slot_id');
    }
    public function purchasePost(): HasMany
    {
        return $this->hasMany(PurchasePost::class);
    }
    public function post(): HasMany
    {
        return $this->hasMany(Post::class);
    }
    public function influencer()
    {
        return $this->belongsToMany(User::class, 'follows');
    }
    public function currentLanguage()
    {
        return $this->lang;
    }
    public function hasVerifiedPhone()
    {
        return ! is_null($this->phone_verified_at);
    }
    public function likePost(): HasMany
    {
        return $this->hasMany(LikePost::class);
    }
    public function pushToken(): HasOne
    {
        return $this->hasOne(PushToken::class, 'follower_id');
    }
    public function plan(): HasOne
    {
        return $this->hasOne(Plan::class, 'id', 'plan_id');
    }

    public function sendPasswordResetNotification($token)
    {
        if (tenant()) {
            if (MailTemplate::where('mailable', PasswordResets::class)->first()) {
                $url = URL::temporarySignedRoute(
                    'password.reset',
                    \Illuminate\Support\Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                    [
                        'token' => $token,
                    ]
                );
                SendEmail::dispatch($this->email, new PasswordResets($this, $url));
            }
        }
    }

     public function subscriptions()
    {
        return $this->hasMany(ClientSubscription::class, 'follower_id');
    }


      public function hasActiveOnlineSubscription()
    {
        // Get the single active subscription
        $subscription = $this->subscriptions()
            ->where('status', 'active')
            ->latest()
            ->first();

        // No active subscription found
        if (!$subscription) {
            return false;
        }

        // Ensure it’s tied to an instructor (optional safety check)
        if (empty($subscription->influencer_id)) {
            return false;
        }

        // Check that the plan includes online lessons
        $plan = $subscription->plan ?? null;

        return $plan && $plan->lesson_limit !== 0;
    }

    /**
     * Get number of remaining free online lessons in the current subscription cycle.
     *
     * @param  int|null  $instructorId
     * @return int
     */
    public function getRemainingFreeOnlineLessons($instructorId = null)
    {
        // Fetch the single active subscription
        $subscription = $this->subscriptions()
            ->where('status', 'active');

            if($instructorId != null){
                $subscription = $subscription->where('influencer_id', $instructorId);
            }


              $subscription = $subscription
            ->latest()
            ->first();

        // No active subscription or missing plan
        if (!$subscription || !$subscription->plan) {
            return 0;
        }

        $plan = $subscription->plan;

        // Unlimited lessons
        if ($plan->lesson_limit == -1) {
            return PHP_INT_MAX; // or return null to represent "unlimited"
        }

        // Calculate the active billing cycle based on subscription start date
        $startDate = Carbon::parse($subscription->created_at);
        $now = Carbon::now();

        $cycleStart = $startDate->copy()->addMonthsNoOverflow(
            $startDate->diffInMonths($now)
        );
        $cycleEnd = $cycleStart->copy()->addMonth();

        // Get instructor ID from subscription
        $instructorId = $instructorId == null ? $subscription->influencer_id : $instructorId;

        // Count online lessons used in this current cycle
        $usedCount = Purchase::where('follower_id', $this->id)
            ->where('influencer_id', $instructorId)
            ->where('status', 'complete')
            // ->where('type', 'online')
            ->whereBetween('created_at', [$cycleStart, $cycleEnd])
            ->count();

        // Remaining lessons = total allowed - used
        $remaining = max(0, $plan->lesson_limit - $usedCount);

        return $remaining;
    }
}
