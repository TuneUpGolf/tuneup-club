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
                $slots = Slots::whereHas('follower') // Ensure slot has followers
                    ->whereBetween('date_time', [$now->format('Y-m-d H:i:s'), $now->addHour()->format('Y-m-d H:i:s')])
                    ->get();

                try {
                    foreach ($slots as $slot) {
                        $slot->load('followers');   // Load all followers
                        $slot->load('lesson.user'); // Load lesson influencer

                        $date           = Carbon::createFromFormat('Y-m-d H:i:s', $slot->date_time);
                        $lessonName     = $slot->lesson?->lesson_name;
                        $influencer     = $slot->lesson?->user;
                        $influencerName = $influencer?->name;

                        foreach ($slot->followers as $follower) {
                            $followerName    = $follower->pivot->friend_name ?? $follower->name; // Show friend name if available
                            $messageFollower = __(
                                "Hey {$followerName} Reminder! You have an upcoming booking for {$date->toDayDateTimeString()} with {$influencerName} for the in-person lesson {$lessonName}."
                            );

                            // Send Push Notification to Follower
                            if (isset($follower->pushToken?->token)) {
                                SendPushNotification::dispatch($follower->pushToken->token, 'Lesson Reminder', $messageFollower);
                            }

                            // Send SMS to Follower
                            $followerPhone = Str::of($follower->dial_code)->append($follower->phone)->value();
                            $followerPhone = str_replace(['(', ')'], '', $followerPhone);
                            SendSMS::dispatch($followerPhone, $messageFollower);
                        }

                        // Notify Influencer
                        if ($influencer) {
                            $followerNames     = $slot->followers->pluck('name')->join(', ');
                            $messageInfluencer = __(
                                "Reminder! You have an upcoming booking for {$date->toDayDateTimeString()} with followers: {$followerNames} for the in-person lesson {$lessonName}."
                            );

                            // Send Push Notification to Influencer
                            if (isset($influencer->pushToken?->token)) {
                                SendPushNotification::dispatch($influencer->pushToken->token, 'Lesson Reminder', $messageInfluencer);
                            }

                            // Send SMS to Influencer
                            $influencerPhone = Str::of($influencer->dial_code)->append($influencer->phone)->value();
                            $influencerPhone = str_replace(['(', ')'], '', $influencerPhone);
                            SendSMS::dispatch($influencerPhone, $messageInfluencer);
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
