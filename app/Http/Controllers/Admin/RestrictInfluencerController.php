<?php

namespace App\Http\Controllers\Admin;

use Stripe\Stripe;
use App\Models\Plan;
use App\Models\Influencer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\InfluencerSubscription;
use Stripe\Checkout\Session as StripeSession;

class RestrictInfluencerController extends Controller
{
    public function subscription_inactive()
    {
        $user = Auth::user();

        $subscription = tenancy()->central(function () use ($user) {
            return Plan::find($user->subscription_id);
        });

        return view('admin.restrict.influencer', compact('subscription'));
    }



    public function subscription_inactive_purchase()
    {
        $user = Auth::user();

        // 🔸 Validate that user has a subscription plan
        if (is_null($user->subscription_plan_id)) {
            return redirect()->back()->with('error', __('No subscription plan found.'));
        }

        // 🔸 Create Stripe checkout session on CENTRAL DB
        $checkoutSession = tenancy()->central(function () use ($user) {

            $plan = Plan::find($user->subscription_plan_id);

            if (!$plan || !$plan->stripe_price_id) {
                abort(404, 'Plan not found or not configured for Stripe.');
            }

            // ✅ Set your Stripe secret key
            Stripe::setApiKey(config('services.stripe.secret'));

            // ✅ Create the checkout session
            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'mode' => 'subscription',
                'line_items' => [[
                    'price' => $plan->stripe_price_id,
                    'quantity' => 1,
                ]],
                'customer_email' => $user->email,
                'success_url' => route('instructor.stripe.success.pay', Crypt::encrypt([
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'user_id' => $user->id,
                    'type' => 'stripe',
                ])) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('instructor.stripe.cancel.pay', Crypt::encrypt([
                    'plan_id' => $plan->id,
                    'price' => $plan->price,
                    'user_id' => $user->id,
                    'type' => 'stripe',
                ])),
            ]);

            return $session;
        });

        // 🔸 Redirect user to Stripe Checkout page
        if ($checkoutSession && isset($checkoutSession->url)) {
            return redirect($checkoutSession->url);
        }

        // 🚫 Fallback: if Stripe session could not be created
        return redirect()->back()->with('error', __('Unable to create Stripe checkout session.'));
    }

    public function instructor_stripe_success_pay($data)
    {
        $session_id = request('session_id');

        // ✅ Remove session id as other data is encrypted
        if (strpos($data, '?') !== false) {
            $data = explode('?', $data)[0];
        }

        // Then decrypt encrypted data
        $data = Crypt::decrypt($data);
        $user = Auth::user();
        Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::retrieve($session_id);
        $tenant_id = tenant('id');

        tenancy()->central(function () use ($session, $user, $tenant_id) {
            $subscription_id = $session->subscription;
            $customer_id = $session->customer ?? null;

            InfluencerSubscription::create([
                'plan_id' => $user->subscription_plan_id,
                'influencer_id' => $user->id ?? null,
                'tenant_id' => $tenant_id,
                'stripe_customer_id' => $customer_id,
                'stripe_subscription_id' => $subscription_id,
                'status' => 'active',
            ]);
        });

        return redirect()->route('home')->with('success', 'Subscription Success');
    }
}
