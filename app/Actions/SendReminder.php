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
                $slot->load('student');
                $slot->load('lesson');
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $slot?->date_time);
                $messageStudent = __(
                    'Reminder! you have an upcoming booking for ' . $date->toDayDateTimeString() . ' with ' . $slot?->lesson?->user?->name . ' for the in-person lesson ' . $slot?->lesson?->lesson_name
                );
                $messageInstructor = __('Reminder! you have an upcoming booking for ' . $date->toDayDateTimeString() . ' with ' . $slot?->student?->name . ' for the in-person lesson ' . $slot?->lesson?->lesson_name);

                if (isset($slot?->student?->pushToken?->token))
                    SendPushNotification::dispatch($slot?->student?->pushToken?->token, 'Lesson Reminder', $messageStudent);
                if (isset($slot?->lesson?->user?->pushToken?->token))
                    SendPushNotification::dispatch($slot?->lesson?->user?->pushToken?->token, 'Lesson Reminder', $messageInstructor);

                $userPhone = Str::of($slot->student['dial_code'])->append($slot->student['phone'])->value();
                $userPhone = str_replace(array('(', ')'), '', $userPhone);
                $instructorPhone = Str::of($slot->lesson->user['dial_code'])->append($slot->lesson->user['phone'])->value();
                $instructorPhone = str_replace(array('(', ')'), '', $instructorPhone);

                SendSMS::dispatch($userPhone, $messageStudent);
                SendSMS::dispatch($instructorPhone, $messageInstructor);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
