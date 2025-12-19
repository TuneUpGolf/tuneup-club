<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use App\Events\TenantNotificationEvent;
use App\Models\Follower;

class MessageNotifyController extends Controller
{
    public function handleNotification(Request $request)
    {
        // 🔹 Log the full incoming payload for debugging
        Log::info('Incoming Notification:', $request->all());

        // 🔹 Validate input
        $data = $request->validate([
            'tenant_id'       => 'nullable|string',
            'sender_id'       => 'nullable|string',
            'sender_email'    => 'nullable|email',
            'receiver_id'     => 'nullable|string',
            'receiver_email'  => 'nullable|email',
            'message'         => 'nullable|string',
            'group_id'        => 'nullable|string',
            'type'            => 'nullable|string',
            'status'          => 'nullable|string',
            'sent_at'         => 'nullable|date',
        ]);
        Log::info("Validation Passed");
        // 🔹 Initialize tenant context if using multi-tenancy
        if (function_exists('tenancy')) {
            tenancy()->initialize($data['tenant_id']);
        }

        // 🔹 Helper closure to find a person in User or Student
        $findPerson = function ($id, $email) {
            $person = User::where('id', $id)->where('email', $email)->first();
            if ($person) {
                $person->role = 'influencer'; // or use $person->role if User table has it
            } else {
                $person = Follower::where('id', $id)->where('email', $email)->first();
                if ($person) {
                    $person->role = 'follower';
                }
            }
            return $person;
        };

        // 🔹 Find sender and receiver
        $sender = $findPerson($data['sender_id'], $data['sender_email']);
        $receiver = $findPerson($data['receiver_id'], $data['receiver_email']);

        if (!$receiver) {
            Log::warning('Receiver not found', ['receiver_id' => $data['receiver_id']]);
            return response()->json(['status' => 'error', 'message' => 'Receiver not found'], 404);
        }

        if (!$sender) {
            Log::warning('Sender not found', ['sender_id' => $data['sender_id']]);
            return response()->json(['status' => 'error', 'message' => 'Sender not found'], 404);
        }
        // Mail::raw("New message from {$sender->name}: {$data['message']}", function ($msg) use ($receiver) {
        //     $msg->to($receiver->email)
        //         ->subject('You have a new chat message');
        // });
        // 🔹 Send notification based on user status


        $redisKey = "user:{$receiver->id}:online";
        $redisValue = Redis::get($redisKey);

        // If key doesn't exist OR value is not 'offline', user is online
        // $isOnline = ($redisValue !== 'offline');
         $isOnline = is_user_online($receiver->id);

        Log::info('Redis online status check:', [
            'user_id' => $receiver->id,
            'redis_key' => $redisKey,
            'redis_value' => $redisValue,
            'is_online' => $isOnline
        ]);


        if (!$isOnline) {
            // Send email to offline users
            Mail::raw("New message from {$sender->name}: {$data['message']}", function ($msg) use ($receiver) {
                $msg->to($receiver->email)
                    ->subject('You have a new chat message');
            });

            Log::info('📧 Email notification sent to offline receiver', [
                'receiver_email' => $receiver->email,
            ]);
        } else {
            event(new TenantNotificationEvent(
                $data['tenant_id'],
                $receiver->id,
                $data['message'],
                $receiver->role ?? 'student', // role included for channel name
                $sender->name,
            ));
            // For online users, you might push a WebSocket or real-time alert
            Log::info('💬 Online user notification handled (no email)', [
                'receiver_id' => $receiver->id,
            ]);
        }

        Log::info('📨 Message notification processed successfully', $data);

        if (function_exists('tenancy')) {
            tenancy()->end();
        }

        return response()->json(['status' => 'success', 'message' => 'Notification processed']);
    }

}
