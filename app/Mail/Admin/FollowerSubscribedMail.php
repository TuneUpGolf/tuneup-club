<?php

namespace App\Mail;

namespace App\Mail\Admin;


use Spatie\MailTemplates\TemplateMailable;

class FollowerSubscribedMail extends TemplateMailable
{
    public $influencer;
    public $follower;
    public $subscriberEmail;
    public $plan;
    public $datetime;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($influencer, $follower, $plan)
    {
        $this->influencer = $influencer->name;
        $this->follower = $follower->name;
        $this->subscriberEmail = $follower->email;
        $this->plan = $plan->name;
        $this->datetime = now()->format('F j, Y g:i A');
    }

    public function build()
    {
        return $this->html($this->buildView());
    }


    public function getHtmlLayout(): string
    {
        return view(
            'mails.layout',
            [
                'influencer' => $this->influencer,
                'follower' => $this->follower,
                'subscriberEmail' => $this->subscriberEmail,
                'plan' => $this->plan,
                'datetime' => $this->datetime,
            ]
        )->render();
    }
}
