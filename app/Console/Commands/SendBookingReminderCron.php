<?php

namespace App\Console\Commands;

use App\Actions\SendPushNotification;
use App\Actions\SendSMS;
use App\Models\Slots;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Stancl\Tenancy\Concerns\HasATenantsOption;

class SendBookingReminderCron extends Command
{
    use HasATenantsOption;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking --tenants=*';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Booked Slots Reminder Prior to the session';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        tenancy()->runForMultiple(
            $this->option('tenants'),
            function ($tenant) {
                $this->line("Tenant: {$tenant['id']}");
                $now = Carbon::now();

                // Get slots in the next hour
                $slots = Slots::whereHas('student') // Ensure slot has students
                    ->whereBetween('date_time', [$now->format('Y-m-d H:i:s'), $now->addHour()->format('Y-m-d H:i:s')])
                    ->get();

                try {
                    foreach ($slots as $slot) {
                        $slot->load('students'); // Load all students
                        $slot->load('lesson.user'); // Load lesson instructor

                        $date = Carbon::createFromFormat('Y-m-d H:i:s', $slot->date_time);
                        $lessonName = $slot->lesson?->lesson_name;
                        $instructor = $slot->lesson?->user;
                        $instructorName = $instructor?->name;

                        foreach ($slot->students as $student) {
                            $studentName = $student->pivot->friend_name ?? $student->name; // Show friend name if available
                            $messageStudent = __(
                                "Hey {$studentName} Reminder! You have an upcoming booking for {$date->toDayDateTimeString()} with {$instructorName} for the in-person lesson {$lessonName}."
                            );

                            // Send Push Notification to Student
                            if (isset($student->pushToken?->token)) {
                                SendPushNotification::dispatch($student->pushToken->token, 'Lesson Reminder', $messageStudent);
                            }

                            // Send SMS to Student
                            $studentPhone = Str::of($student->dial_code)->append($student->phone)->value();
                            $studentPhone = str_replace(['(', ')'], '', $studentPhone);
                            SendSMS::dispatch($studentPhone, $messageStudent);
                        }

                        // Notify Instructor
                        if ($instructor) {
                            $studentNames = $slot->students->pluck('name')->join(', ');
                            $messageInstructor = __(
                                "Reminder! You have an upcoming booking for {$date->toDayDateTimeString()} with students: {$studentNames} for the in-person lesson {$lessonName}."
                            );

                            // Send Push Notification to Instructor
                            if (isset($instructor->pushToken?->token)) {
                                SendPushNotification::dispatch($instructor->pushToken->token, 'Lesson Reminder', $messageInstructor);
                            }

                            // Send SMS to Instructor
                            $instructorPhone = Str::of($instructor->dial_code)->append($instructor->phone)->value();
                            $instructorPhone = str_replace(['(', ')'], '', $instructorPhone);
                            SendSMS::dispatch($instructorPhone, $messageInstructor);
                        }
                    }
                } catch (\Exception $e) {
                    return throw new Exception($e->getMessage(), $e->getCode());
                }
            }
        );

        return Command::SUCCESS;
    }
}
