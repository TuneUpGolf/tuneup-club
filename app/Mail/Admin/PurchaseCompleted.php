<?php

namespace App\Mail;

namespace App\Mail\Admin;

use App\Models\Purchase;
use Spatie\MailTemplates\TemplateMailable;

class PurchaseCompleted extends TemplateMailable
{

    public $name;
    public $id;
    public $amount;
    public $lesson_name;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Purchase $purchase)
    {
        //
        $this->name = $purchase->follower->name;
        $this->id = $purchase->id;
        $this->amount = $purchase->total_amount;
        $this->lesson_name = $purchase->lesson ? $purchase->lesson->lesson_name : 'N/A';
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
