<?php
namespace App\Models;

use App\Actions\SendEmail;
use App\Mail\Admin\PasswordResets;
use App\Mail\Superadmin\PasswordReset;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\MailTemplates\Models\MailTemplate;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    use BelongsToTenant, Impersonate;

    protected $fillable = [
        'id',
        'uuid',
        'name',
        'email',
        'password',
        'type',
        'tenant_id',
        'plan_id',
        'created_by',
        'address',
        'country',
        'country_code',
        'dial_code',
        'phone',
        'email_verified_at',
        'phone_verified_at',
        'logo',
        'bio',
        'sub_price',
        'golf_course',
        'service_fee',
        'experience',
        'social_url_ig',
        'social_url_fb',
        'social_url_x',
        'avg_rate',
        'stripe_account_id',
        'application_fee_percentage',
        'is_stripe_connected',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    public static function getStripeCurrencies(): array
    {
        return [
            'usd',
            'eur',
            'gbp',
            'aud',
            'cad',
            'jpy',
            'sgd',
            'chf',
            'sek',
            'nok',
            'dkk',
            'hkd',
            'mxn',
            'nzd',
            'brl',
            'inr',
            'zar',
            'cny',
            'krw',
            'idr',
            'myr',
            'php',
            'thb',
            'vnd',
        ];
    }

    public static function getCurrencySymbol($currency)
    {
        $currencySymbols = [
            // Dollar-based currencies with country initials
            'usd' => '$',
            'aud' => '$',
            'cad' => '$',
            'nzd' => '$',
            'sgd' => '$',
            'hkd' => '$',
            'twd' => '$',
            'mxn' => '$',
            'cop' => '$',

            // Other major currencies
            'eur' => '€',
            'gbp' => '£',
            'jpy' => '¥',
            'cny' => '¥',
            'inr' => '₹',
            'brl' => 'R$',
            'zar' => 'R',
            'chf' => 'CHF',
            'sek' => 'kr',
            'nok' => 'kr',
            'dkk' => 'kr',
            'pln' => 'zł',
            'huf' => 'Ft',
            'czk' => 'Kč',
            'ron' => 'lei',
            'rub' => '₽',
            'thb' => '฿',
            'idr' => 'Rp',
            'myr' => 'RM',
            'php' => '₱',
            'krw' => '₩',
            'vnd' => '₫',
            'ils' => '₪',
            'aed' => 'د.إ',
            'sar' => '﷼',
            'egp' => 'E£',
            'bhd' => '.د.ب',
            'qar' => 'ر.ق',
            'omr' => '﷼',
            'kwd' => 'د.ك',

        ];

        return $currencySymbols[strtolower($currency)] ?? '$';
    }

    public function setCurrencyAttribute($value)
    {
        if (in_array($value, self::getStripeCurrencies())) {
            $this->attributes['currency'] = strtolower($value);
        } else {
            $this->attributes['currency'] = 'usd'; // Fallback
        }
    }

    public function loginSecurity()
    {
        return $this->hasOne('App\Models\LoginSecurity');
    }
    public function currentLanguage()
    {
        return $this->lang;
    }

    public function getAvatarImageAttribute()
    {
        if (tenant('id') == null) {
            $avatar = File::exists(Storage::path($this->avatar)) ? Storage::url($this->avatar) : Storage::url('avatar/avatar.png');
        } else {
            if (config('filesystems.default') == 'local') {
                $avatar = File::exists(Storage::path($this->avatar)) ? Storage::url(tenant('id') . '/' . $this->avatar) : Storage::url('avatar/avatar.png');
            } else {
                $avatar = File::exists(Storage::path($this->avatar)) ? Storage::url($this->avatar) : Storage::url('avatar/avatar.png');
            }
        }
        return $avatar;
    }

    public function assignPlan($planID)
    {
        $plan = Plan::find($planID);
        if ($plan) {
            $users     = User::where('tenant_id', tenant('id'))->where('type', '!=', 'Admin')->get();
            $userCount = 0;

            foreach ($users as $user) {
                $userCount++;
                $user->active_status = ($plan->max_users == -1 || $userCount <= $plan->max_users) ? 1 : 0;
                $user->save();
            }
            $this->plan_id = $plan->id;
            if ($plan->durationtype == 'Month' && $planID != '1') {
                $this->plan_expired_date = Carbon::now()->addMonths($plan->duration)->isoFormat('YYYY-MM-DD');
            } elseif ($plan->durationtype == 'Year' && $planID != '1') {
                $this->plan_expired_date = Carbon::now()->addYears($plan->duration)->isoFormat('YYYY-MM-DD');
            } else {
                $this->plan_expired_date = null;
            }
            $this->save();
            return ['is_success' => true];
        } else {
            return [
                'is_success' => false,
                'errors' => __('Plan is deleted.'),
            ];
        }
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
        } else {
            if (MailTemplate::where('mailable', PasswordReset::class)->first()) {
                $url = URL::temporarySignedRoute(
                    'password.reset',
                    \Illuminate\Support\Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                    [
                        'token' => $token,
                    ]
                );
                SendEmail::dispatch($this->email, new PasswordReset($this, $url));
            }
        }
    }

    public function hasVerifiedPhone()
    {
        return !is_null($this->phone_verified_at);
    }

    public function lastCodeRemainingSeconds()
    {
        $temp = UserCode::where('user_id', $this->id)->first();
        if (isset($temp)) {
            $seconds = $temp->updated_at->diffInSeconds(Carbon::now());
            if ($seconds > 60) {
                return 60;
            } else {
                return 60 - $seconds;
            }
        } else {
            return 60;
        }
    }
    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'created_by');
    }

    public function post(): HasMany
    {
        return $this->hasMany(Post::class, 'influencer_id');
    }

    public function purchase(): HasMany
    {
        return $this->hasMany(Purchase::class, 'influencer_id');
    }
    public function likePost(): HasMany
    {
        return $this->hasMany(LikePost::class, 'influencer_id');
    }
    public function reportUser(): HasMany
    {
        return $this->hasMany(ReportUser::class, 'influencer_id');
    }
    public function review(): HasMany
    {
        return $this->hasMany(Review::class, 'influencer_id');
    }
    public function pushToken(): HasOne
    {
        return $this->hasOne(PushToken::class, 'influencer_id');
    }
}
