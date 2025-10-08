<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class InfluncerServices
{
    // Your service methods here
    public static function addAndUpdateInfluncerPlan($amount, $id, $name)
    {
        \Log::info('addAndUpdateInfluncerPlan called with amount: ' . $amount . ', id: ' . $id . ', name: ' . $name);
        $influncer = DB::table('influencer_plan')->where('influencer_id', $id)->first();
        if (floatval($amount) <= 0) {
            return;
        }
        if ($influncer && floatval($influncer->price) == floatval($amount)) {
            return;
        }
        Stripe::setApiKey(config('services.stripe.secret'));

        if ($influncer) {
            if (!empty($influncer->price_id)) {
                try {
                    Price::update($influncer->price_id, ['active' => false]);
                } catch (\Exception $e) {
                    \Log::info('Failed to deactivate old Stripe price: ' . $e->getMessage());
                }
            }

            // 2️⃣ Create a new Stripe price for the updated amount
            $price = Price::create([
                'unit_amount' => $amount * 100,
                'currency' => 'usd',
                'recurring' => ['interval' => 'month'],
                'product' => $influncer->product_id,
            ]);

            // 3️⃣ Update influencer plan in DB
            DB::table('influencer_plan')
                ->where('influencer_id', $id)
                ->update([
                    'price' => $amount,
                    'price_id' => $price->id,
                    'updated_at' => now(),
                ]);
        } else {
            // 🆕 Create new product and price for new influencer
            $product = Product::create([
                'name' =>  $name . ' ' . 'Influencer Plan',
            ]);

            $price = Price::create([
                'unit_amount' => $amount * 100,
                'currency' => 'usd',
                'recurring' => ['interval' => 'month'],
                'product' => $product->id,
            ]);

            DB::table('influencer_plan')->insert([
                'influencer_id' => $id,
                'product_id' => $product->id,
                'price_id' => $price->id,
                'price' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public static function subscribeInfluncerPlan($userId)
    {
        // 1️⃣ Fetch influencer plan from DB
        $influencer = DB::table('influencer_plan')->where('influencer_id', $userId)->first();
        if (!$influencer) {
            return ['error' => 'No influencer plan found'];
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // 2️⃣ Create Stripe Checkout Session
        $session = Session::create([
            'mode' => 'subscription',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $influencer->price_id,
                'quantity' => 1,
            ]],
            'metadata' => [
                'user_id' => $userId,
            ],
            'success_url' => route('influencer.payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('influencer.payment.cancel'),
        ]);

        return ['url' => $session->url];
    }

    /**
     * ✅ Handle Stripe Checkout success callback
     */
    public static function handleSuccess($sessionId)
    {
        \Log::info('handleSuccess called with sessionId: ' . $sessionId);
        Stripe::setApiKey(config('services.stripe.secret'));
        $session = \Stripe\Checkout\Session::retrieve($sessionId);
        \Log::info('Retrieved session: ' . json_encode($session));
        if (!$session) {
            return ['error' => 'Invalid session ID'];
        }

        $subscriptionId = $session->subscription;
        \Log::info('Retrieved subscription ID: ' . $subscriptionId);
        $customerId = $session->customer;

        // Retrieve subscription info
        $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        \Log::info('Retrieved subscription: ' . json_encode($subscription));
        // Get user ID from DB (if you stored it in metadata)
        $userId = $session->metadata->user_id ?? null;
        \Log::info('Retrieved user ID from metadata: ' . $userId);

        if (!$userId) return ['error' => 'User not found in session metadata'];

        $influencer = DB::table('influencer_plan')->where('influencer_id', $userId)->first();
        \Log::info('Fetched influencer plan: ' . json_encode($influencer));
        if ($influencer) {

            DB::table('influencer_subscription')->insert([
                'influencer_id' => $userId,
                'subscription_id' => $subscriptionId,
                'price_id' => $influencer->price_id,
                'product_id' => $influencer->product_id,
                'price' => $influencer->price,
                'status' => $subscription->status ?? 'active',
                'end_date' => $subscription->current_period_end
                    ? date('Y-m-d H:i:s', $subscription->current_period_end)
                    : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $superAdmin = DB::connection('mysql')->table('users')
                ->where('type', 'Super Admin')
                ->first();

            if ($superAdmin) {
                DB::connection('mysql')->table('users')
                    ->where('id', $superAdmin->id)
                    ->update([
                        'service_earning' => $superAdmin->service_earning + $influencer->price,
                    ]);
            }
            \Log::info('Stripe Checkout session completed: ' . $sessionId);
        }

        return ['success' => true];
    }

    public static function handleCancel()
    {
        \Log::info('handleCancel called');
        return ['cancelled' => true, 'message' => 'Payment was cancelled'];
    }
}
