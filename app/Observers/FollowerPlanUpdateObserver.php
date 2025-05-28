<?php

namespace App\Observers;

use App\Actions\SendEmail;
use App\Mail\Admin\FollowerSubscribedMail;
use App\Models\Follower;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;

class FollowerPlanUpdateObserver
{

    /**
     * Handle the Follower "updating" event.
     *
     * @param  \App\Models\Follower  $follower
     * @return void
     */
    public function updating(Follower $follower)
    {
        $influencer = User::where('type', Role::ROLE_INFLUENCER)->first();
        if ($influencer) {
            SendEmail::dispatch(
                $influencer->email,
                new FollowerSubscribedMail(
                    $influencer,
                    $follower,
                    Plan::where('id', $follower->plan_id)->first()
                )
            );
        }
    }
}
