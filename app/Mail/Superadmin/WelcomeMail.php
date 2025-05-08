<?php

namespace App\Mail;

namespace App\Mail\Superadmin;

use Spatie\MailTemplates\TemplateMailable;

class WelcomeMail extends TemplateMailable
{

    public $name;
    public $link;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($username, $link)
    {
        //
        $this->name = $username;
        $this->link = $link;
    }

    public function build()
    {
        return $this->html($this->buildView()); // Ensure HTML is sent
    }


    public function getHtmlLayout(): string
    {
        return view('mails.layout', ['data' => $this->name, $this->link])->render();
    }
}
