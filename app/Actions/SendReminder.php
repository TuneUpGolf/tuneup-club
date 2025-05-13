<?php
namespace App\Actions;

use App\Actions\SendPushNotification;
use App\Actions\SendSMS;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsJob;

class SendReminder
{

    use AsJob;

    public function handle($slots)
    {

        try {
            foreach ($slots as $slot) {
                $slot->load('follower');
                $slot->load('lesson');
                $date            = Carbon::createFromFormat('Y-m-d H:i:s', $slot?->date_time);
                $messageFollower = __(
                    'Reminder! you have an upcoming booking for ' . $date->toDayDateTimeString() . ' with ' . $slot?->lesson?->user?->name . ' for the in-person lesson ' . $slot?->lesson?->lesson_name
                );
                $messageInfluencer = __('Reminder! you have an upcoming booking for ' . $date->toDayDateTimeString() . ' with ' . $slot?->follower?->name . ' for the in-person lesson ' . $slot?->lesson?->lesson_name);

                if (isset($slot?->follower?->pushToken?->token)) {
                    SendPushNotification::dispatch($slot?->follower?->pushToken?->token, 'Lesson Reminder', $messageFollower);
                }

                if (isset($slot?->lesson?->user?->pushToken?->token)) {
                    SendPushNotification::dispatch($slot?->lesson?->user?->pushToken?->token, 'Lesson Reminder', $messageInfluencer);
                }

                $userPhone       = Str::of($slot->follower['dial_code'])->append($slot->follower['phone'])->value();
                $userPhone       = str_replace(['(', ')'], '', $userPhone);
                $influencerPhone = Str::of($slot->lesson->user['dial_code'])->append($slot->lesson->user['phone'])->value();
                $influencerPhone = str_replace(['(', ')'], '', $influencerPhone);

                SendSMS::dispatch($userPhone, $messageFollower);
                SendSMS::dispatch($influencerPhone, $messageInfluencer);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
