<?php

namespace App\Actions;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\TenantAwareJob;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;

class SendTendPuchNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, TenantAwareJob;

    protected $tokens;
    protected $title;
    protected $body;

    public function __construct($tokens, $title, $body)
    {
        $this->tokens = $tokens;
        $this->title  = $title;
        $this->body   = $body;
    }

    public function handle()
    {
        // 👇 tenant() is automatically initialized for you when using TenantAwareJob
        $tokens = is_array($this->tokens) ? $this->tokens : [$this->tokens];

        $expo = new Expo();
        $message = (new ExpoMessage([
            'title' => $this->title,
            'body'  => $this->body,
        ]))->playSound()->setChannelId('default');

        foreach ($tokens as $token) {
            $expo->send($message)->to($token)->push();
        }
    }
}
