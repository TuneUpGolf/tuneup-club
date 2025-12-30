<?php

namespace App\Mail\Admin;


use Spatie\MailTemplates\TemplateMailable;

class ClientSubscriptionToInstructorMail extends TemplateMailable
{

    public $student_name;
    public $instructor_name;
    public $plan_name;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($student_name, $instructor_name, $plan_name)
    {
        //
        $this->student_name = $student_name;
        $this->instructor_name = $instructor_name;
        $this->plan_name = $plan_name;
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
