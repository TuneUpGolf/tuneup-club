<?php

namespace App\Mail;

namespace App\Mail\Admin;


use Spatie\MailTemplates\TemplateMailable;

class RequestDemoMail extends TemplateMailable
{

    public $name;
    public $email;
    public $phone_number;
    public $clubName;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($requestDemo)
    {
        //
        $this->name = $requestDemo['name'];
        $this->email = $requestDemo['email'];
        $this->phone_number = $requestDemo['phone'];
        $this->clubName = $requestDemo['clubName'];
    }

    public function build()
    {
        return $this->html($this->buildView()); // Ensure HTML is sent
    }

    public function getHtmlLayout(): string
    {
        return view('mails.layout')->render();
    }
}
