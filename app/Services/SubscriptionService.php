<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Product;
use Stripe\Price;
use App\Facades\UtilityFacades;


class SubscriptionService
{

    public static function createStripePlan($request, $user)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $product = null; // ✅ define before try to avoid undefined variable

        try {
            // ✅ Create Product
            $product = \Stripe\Product::create([
                'name' => $request->name,
            ], [
                'stripe_account' => $user->stripe_account_id,
            ]);

            // ✅ Create Price
            $price = \Stripe\Price::create([
                'unit_amount' => $request->price * 100,
                'currency'    => 'usd',
                'recurring'   => [
                    'interval' => strtolower($request->durationtype), // e.g. month/year
                ],
                'product'     => $product->id,
            ], [
                'stripe_account' => $user->stripe_account_id,
            ]);

            return [
                'success'    => true,
                'product_id' => $product->id,
                'price_id'   => $price->id,
            ];
        } catch (\Exception $e) {


            // ✅ Return error response
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    public static function updateStripePlan($request, $user, $plan)
    {
        Stripe::setApiKey(config('services.stripe.secret'));


        try {
            // ✅ Update product (only name/description can be changed)
            $product = Product::update(
                $plan->stripe_product_id,
                ['name' => $request->name],
                ['stripe_account' => $user->stripe_account_id]
            );

            // ✅ Create a new Price (Stripe does not allow editing old one)
            $price = Price::create([
                'unit_amount' => $request->price * 100,
                'currency' => 'usd',
                'recurring' => [
                    'interval' => strtolower($request->durationtype),
                    'interval_count' => (int) $request->duration,
                ],
                'product' => $product->id,
            ], [
                'stripe_account' => $user->stripe_account_id,
            ]);

            // ❌ "Delete" old price = mark inactive
            if (!empty($plan->stripe_price_id)) {
                Price::update(
                    $plan->stripe_price_id,
                    ['active' => false],
                    ['stripe_account' => $user->stripe_account_id]
                );
            }

            // ✅ Save new IDs in DB
            $plan->update([
                'stripe_product_id' => $product->id,
                'stripe_price_id'   => $price->id,
            ]);

            return [
                'success'    => true,
                'product_id' => $product->id,
                'price_id'   => $price->id,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public static function deleteStripePlan($user, $plan)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            // 1. Deactivate all Prices
            $prices = \Stripe\Price::all(
                ['product' => $plan->stripe_product_id, 'limit' => 100],
                ['stripe_account' => $user->stripe_account_id]
            );

            foreach ($prices->data as $price) {
                \Stripe\Price::update(
                    $price->id,
                    ['active' => false],
                    ['stripe_account' => $user->stripe_account_id]
                );
            }

            // 2. Archive the Product (instead of delete)
            $archivedProduct = \Stripe\Product::update(
                $plan->stripe_product_id,
                ['active' => false],
                ['stripe_account' => $user->stripe_account_id]
            );

            return [
                'success' => true,
                'product' => $archivedProduct,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
