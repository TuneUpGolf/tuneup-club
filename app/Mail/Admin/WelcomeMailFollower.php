<?php

namespace App\Mail;

namespace App\Mail\Admin;


use Spatie\MailTemplates\TemplateMailable;

class WelcomeMailFollower extends TemplateMailable
{

    public $name;
    public $link;
    public $password;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $password)
    {
        //
        $this->name = $user->name;
        $this->link = url('/login');
        $this->password = $password;
    }

    public function build()
    {
        return $this->html($this->buildView()); // Ensure HTML is sent
    }


    public function getHtmlLayout(): string
    {
        return view('mails.layout', ['data' => $this->name, $this->password, $this->link])->render();
    }
}
