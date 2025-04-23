<?php

namespace App\Actions;

use Exception;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;
use Lorisleiva\Actions\Concerns\AsAction;

class SendPushNotification
{
    use AsAction;

    public function handle($tokens, $title, $body)
    {
        try {
            if (empty($tokens)) {
                throw new Exception('No Expo token(s) found.');
            }

            // Ensure tokens is always an array
            $tokens = is_array($tokens) ? $tokens : [$tokens];

            $expo = new Expo();
            $message = (new ExpoMessage([
                'title' => $title,
                'body'  => $body,
            ]))
                ->setChannelId('default')
                ->setBadge(0)
                ->playSound()
                ->setContentAvailable(true);

            foreach ($tokens as $token) {
                $expo->send($message)->to($token)->push();
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
