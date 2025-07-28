<?php

namespace App\Mail\Admin;

use App\Models\Purchase;
use Spatie\MailTemplates\TemplateMailable;

class PurchaseFeedback extends TemplateMailable
{

    public $name;
    public $influencer;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Purchase $purchase)
    {
        $this->name = $purchase->follower->name;
        $this->influencer = $purchase->lesson->user->name;
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
