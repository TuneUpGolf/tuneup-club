<?php

namespace App\Actions;

use Exception;
use Lorisleiva\Actions\Concerns\AsAction;

use Twilio\Rest\Client;

class SendSMS
{

    use AsAction;

    public function handle($recepient, $message)
    {
        try {
            $smsEnabled = \App\Facades\UtilityFacades::getsettings('enable_sms_notification') == "on";
            if ($smsEnabled) {
                $client = new Client(config('services.twilio.sid'), config('services.twilio.token'));
                $client->messages->create($recepient, [
                    'from'
                    => config('services.twilio.phone'),
                    'body' => $message
                ]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
