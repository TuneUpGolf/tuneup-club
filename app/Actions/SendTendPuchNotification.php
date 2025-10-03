<?php

namespace App\Actions;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;

class SendTendPuchNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, Queueable;

    protected $tokens;
    protected $title;
    protected $body;
    protected $tenantId;

    /**
     * Create a new job instance.
     */
    public function __construct($tokens, $title, $body, $tenantId = null)
    {
        $this->tokens   = $tokens;
        $this->title    = $title;
        $this->body     = $body;
        $this->tenantId = $tenantId ?? tenant('id'); // ✅ capture current tenant id
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // ✅ Restore tenant context if available
        if ($this->tenantId) {
            tenancy()->initialize($this->tenantId);
        }

        $tokens = is_array($this->tokens) ? $this->tokens : [$this->tokens];

        $expo = new Expo();
        $message = (new ExpoMessage([
            'title' => $this->title,
            'body'  => $this->body,
        ]))
            ->playSound()
            ->setChannelId('default')
            ->setBadge(0);

        foreach ($tokens as $token) {
            $expo->send($message)->to($token)->push();
        }
    }
}
