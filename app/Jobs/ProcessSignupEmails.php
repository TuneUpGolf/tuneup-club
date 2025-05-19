<?php
namespace App\Jobs;

use App\Actions\SendEmail;
use App\Mail\Admin\WelcomeMailFollower;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;
use Stancl\Tenancy\Tenancy;

class ProcessSignupEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tenantId;
    public $user;

    public function __construct($user, string $tenantId)
    {
        $this->user = $user;
        $this->tenantId = $tenantId;
    }

    public function handle(Tenancy $tenancy): void
    {
        $tenant = Tenant::find($this->tenantId);
        $tenancy->initialize($tenant);

        SendEmail::dispatch($this->user->email, new WelcomeMailFollower($this->user, ''));
    }
}
