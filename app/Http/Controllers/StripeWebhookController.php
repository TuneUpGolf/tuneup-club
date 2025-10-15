<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\ClientSubscription;
use Illuminate\Support\Facades\Log;
use App\Models\StripeConnectedAccount;
use App\Models\ClientSubscriptionDetail;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info('🔔 Stripe Webhook received!');

        $sigHeader = $request->header('Stripe-Signature');
        $payload = $request->all();
        $accountId = $payload['account'] ?? null;
        $eventType = $payload['type'] ?? 'unknown';

        $stripe_account_id = StripeConnectedAccount::where('stripe_account_id', $request->account)->first();
        $tenant = Tenant::find($stripe_account_id->tenant_id); // or however you store tenant IDs

        tenancy()->initialize($tenant); // 🔁 switch context to this tenant

        // Now you're “inside” the tenant’s DB
        $users = User::where('stripe_account_id', $stripe_account_id->stripe_account_id)->first();

        // Do your work in this tenant's DB...
        // Log::info($users);

        tenancy()->end();

        Log::info("🎯 Event Type: {$eventType}");

        // Handle events you care about
        switch ($eventType) {
            case 'invoice.payment_succeeded':
                $subscriptionId = $payload['data']['object']['subscription'];
                $invoiceId = $payload['data']['object']['id'];
                $paymentIntentId = $payload['data']['object']['payment_intent'] ?? null;

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
                Log::info('invoice payment');
                break;

            case 'invoice.payment_failed':
                $subscriptionId = $payload['data']['object']['subscription'];
                $invoiceId = $payload['data']['object']['id'];
                $paymentIntentId = $payload['data']['object']['payment_intent'] ?? null;

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

                break;

            case 'customer.subscription.deleted':
                $subscriptionId = $payload['data']['object']['id'];
                tenancy()->initialize($tenant);

                ClientSubscription::where('stripe_subscription_id', $subscriptionId)
                    ->update(['status' => 'canceled']);

                tenancy()->end();
                break;
        }

        return response()->json(['status' => 'success']);
    }
}
