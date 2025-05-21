<?php

namespace App\Actions;

use Error;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;
use Spatie\MailTemplates\Models\MailTemplate;

class SendEmail
{
    use AsAction;

    public function handle($email, Mailable $mailable)
    {
        try {
            $emailEnabled = \App\Facades\UtilityFacades::getsettings('enable_email_notification') == "on";

            if (MailTemplate::where('mailable', get_class($mailable))->first() && $emailEnabled) {
                Mail::mailer('smtp')->to($email)->send($mailable);
            }
        } catch (Error $e) {
            return response($e->getMessage());
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
