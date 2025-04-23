<?php

namespace App\Models;

use App\Actions\SendEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Mail\Admin\PasswordResets;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\MailTemplates\Models\MailTemplate;

class Student extends User implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, HasRoles;
    use BelongsToTenant, Impersonate;

    protected $table = "students";
    protected $guard_name = 'web';
    protected $fillable = [
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
        return $this->hasMany(Purchase::class, 'student_id');
    }
    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }
    public function slots(): BelongsToMany
    {
        return $this->belongsToMany(Slots::class, 'slot_student', 'student_id', 'slot_id');
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
        return !is_null($this->phone_verified_at);
    }
    public function likePost(): HasMany
    {
        return $this->hasMany(LikePost::class);
    }
    public function pushToken(): HasOne
    {
        return $this->hasOne(PushToken::class, 'student_id');
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
}
