<?php
namespace App\Mail;

namespace App\Mail\Admin;

use App\Models\Purchase;
use Spatie\MailTemplates\TemplateMailable;

class VideoAdded extends TemplateMailable
{

    public $follower_name;
    public $name;
    public $link;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Purchase $purchase)
    {
        //
        $this->name          = $purchase?->lesson?->user?->name;
        $this->link          = route('purchase.feedback.index', ['purchase_id' => $purchase->id]);
        $this->follower_name = $purchase->follower->name;
    }
    public function build()
    {
        return $this->html($this->buildView()); // Ensure HTML is sent
    }

    public function getHtmlLayout(): string
    {
        return view('mails.layout', ['data' => [$this->follower_name, $this->name, $this->link]])->render();
    }
}
