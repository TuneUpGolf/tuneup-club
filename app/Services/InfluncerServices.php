<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class InfluncerServices
{
    // Your service methods here
    public static function addAndUpdateInfluncerPlan($amount, $id, $name)
    {
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
                    \Log::warning('Failed to deactivate old Stripe price: ' . $e->getMessage());
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
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return;
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // Search for Stripe customer by email
        $customers = \Stripe\Customer::all(['email' => $user->email, 'limit' => 1]);
        if (count($customers->data) > 0) {
            $stripeCustomerId = $customers->data[0]->id;
        } else {
            // Create customer if not found
            $customer = \Stripe\Customer::create([
                'name'  => $user->name,
                'email' => $user->email,
            ]);
            $stripeCustomerId = $customer->id;
        }

        $influncer = DB::table('influencer_plan')->where('influencer_id', $userId)->first();
        if (!$influncer) {
            return;
        }

        $subscription = \Stripe\Subscription::create([
            'customer' => $stripeCustomerId,
            'items' => [[
                'price' => $influncer->price_id,
            ]],
        ]);
        DB::table('influencer_subscription')->insert([
            'influencer_id' => $userId,
            'subscription_id' => $subscription->id,
            'price_id' => $influncer->price_id,
            'product_id' => $influncer->product_id,
            'price' => $influncer->price,
            'status' => 'active',
            'end_date' => $subscription->current_period_end ? date('Y-m-d H:i:s', $subscription->current_period_end) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
