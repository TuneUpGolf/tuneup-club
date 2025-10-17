<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\ClientSubscription;
use Illuminate\Support\Facades\Log;
use App\Models\InfluencerSubscription;
use App\Models\StripeConnectedAccount;
use App\Models\ClientSubscriptionDetail;
use App\Models\InfluencerSubscriptionDetail;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info('🔔 Stripe Webhook received!');

        $sigHeader = $request->header('Stripe-Signature');
        $payload = $request->all();
        $accountId = $payload['account'] ?? null;
        $eventType = $payload['type'] ?? 'unknown';

        if ($accountId) {
            $stripe_account_id = StripeConnectedAccount::where('stripe_account_id', $request->account)->first();
            $tenant = Tenant::find($stripe_account_id->tenant_id); // or however you store tenant IDs

            tenancy()->initialize($tenant); // 🔁 switch context to this tenant

            // Now you're “inside” the tenant’s DB
            $users = User::where('stripe_account_id', $stripe_account_id->stripe_account_id)->first();

            // Do your work in this tenant's DB...
            // Log::info($users);

            tenancy()->end();
        }

        Log::info("🎯 Event Type: {$eventType}");

        // Handle events you care about
        switch ($eventType) {
            case 'invoice.payment_succeeded':
                $subscriptionId = $payload['data']['object']['subscription'];
                $invoiceId = $payload['data']['object']['id'];
                $paymentIntentId = $payload['data']['object']['payment_intent'] ?? null;

                if ($accountId) {
                    tenancy()->initialize($tenant);

                    $student_subscription = ClientSubscription::where('stripe_subscription_id', $subscriptionId)
                        ->update(['status' => 'active']);

                    if ($student_subscription) {
                        $student_subscription->update(['status' => 'active']);

                        // Create a subscription detail record
                        ClientSubscriptionDetail::create([
                            'client_subscription_id' => $student_subscription->id,
                            'invoice_id' => $invoiceId,
                            'payment_intent_id' => $paymentIntentId,
                        ]);

                        Log::info('Invoice payment succeeded', [
                            'subscription_id' => $subscriptionId,
                            'invoice_id' => $invoiceId,
                            'payment_intent_id' => $paymentIntentId,
                            'tenant_id' => $tenant->id,
                        ]);
                    } else {
                        Log::warning('Client subscription not found for successful payment', [
                            'subscription_id' => $subscriptionId,
                            'invoice_id' => $invoiceId,
                        ]);
                    }

                    tenancy()->end();
                } else {
                    // 🔸 MAIN ACCOUNT HANDLING (Instructor subscriptions)
                    $instructorSub = InfluencerSubscription::where('stripe_subscription_id', $subscriptionId)->first();



                    if ($instructorSub) {

                        InfluencerSubscriptionDetail::create([
                            'influencer_subscription_id' => $instructorSub->id,
                            'invoice_id' => $invoiceId,
                            'payment_intent_id' => $paymentIntentId,
                        ]);

                        $instructorSub->update(['status' => 'active']);
                        Log::info('✅ Instructor subscription payment succeeded (Main Account)', [
                            'subscription_id' => $subscriptionId,
                            'invoice_id' => $invoiceId,
                        ]);
                    } else {
                        Log::warning('⚠️ Instructor subscription not found (Main Account)', [
                            'subscription_id' => $subscriptionId,
                        ]);
                    }
                }
                Log::info('invoice payment');
                break;

            case 'invoice.payment_failed':
                $subscriptionId = $payload['data']['object']['subscription'];
                $invoiceId = $payload['data']['object']['id'];
                $paymentIntentId = $payload['data']['object']['payment_intent'] ?? null;

                if ($accountId) {
                    tenancy()->initialize($tenant);

                    $subscription = ClientSubscription::where('stripe_subscription_id', $subscriptionId)->first();
                    if ($subscription && $subscription->status !== 'past_due') {
                        $subscription->update(['status' => 'past_due']);
                        Log::info('Subscription updated to past_due due to payment failure', [
                            'subscription_id' => $subscriptionId,
                            'invoice_id' => $invoiceId,
                            'payment_intent_id' => $paymentIntentId,
                            'tenant_id' => $tenant->id,
                        ]);
                    } else {
                        Log::info('Subscription already past_due or not found', [
                            'subscription_id' => $subscriptionId,
                            'invoice_id' => $invoiceId,
                            'payment_intent_id' => $paymentIntentId,
                            'tenant_id' => $tenant->id,
                        ]);
                    }
                    tenancy()->end();
                } else {
                    // 🔸 MAIN ACCOUNT PAYMENT FAILURE (Instructor)
                    $instructorSub = InfluencerSubscription::where('stripe_subscription_id', $subscriptionId)->first();
                    if ($instructorSub && $instructorSub->status !== 'past_due') {
                        $instructorSub->update(['status' => 'past_due']);
                        Log::info('Instructor subscription marked past_due', [
                            'subscription_id' => $subscriptionId,
                            'invoice_id' => $invoiceId,
                        ]);
                    }
                }
                break;

            case 'customer.subscription.deleted':
                $subscriptionId = $payload['data']['object']['id'];
                if ($accountId) {
                    tenancy()->initialize($tenant);

                    ClientSubscription::where('stripe_subscription_id', $subscriptionId)
                        ->update(['status' => 'canceled']);

                    tenancy()->end();
                } else {
                    // 🔸 MAIN ACCOUNT SUBSCRIPTION CANCELED
                    InfluencerSubscription::where('stripe_subscription_id', $subscriptionId)
                        ->update(['status' => 'canceled']);
                    Log::info('Instructor subscription canceled (Main Account)', [
                        'subscription_id' => $subscriptionId,
                    ]);
                }
                break;
        }

        return response()->json(['status' => 'success']);
    }
}
