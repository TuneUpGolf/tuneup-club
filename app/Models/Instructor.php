<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Illuminate\Notifications\Notifiable;

class Instructor extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, HasRoles;
    use BelongsToTenant;

    protected $table = "instructors";
    protected $guard_name = 'web';

    protected $fillable = [
        'id',
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


    public function follows(): HasMany
    {
        return $this->hasMany(Follow::class);
    }

    public function students()
    {
        return $this->belongsToMany(Follower::class, 'follows', 'instructor_id', 'follower_id');
    }

    public function currentLanguage()
    {
        return $this->lang;
    }
}
