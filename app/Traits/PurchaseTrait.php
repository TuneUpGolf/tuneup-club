<?php
namespace App\Traits;

use App\Actions\SendPushNotification;
use App\Actions\SendSMS;
use App\Models\Purchase;
use App\Models\Slots;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stripe\Account;
use Stripe\Checkout\Session;
use Stripe\Stripe;

trait PurchaseTrait
{

    public function sendSlotNotification(Slots $slot, string $notificationType, ?string $followerMessageTemplate = null, ?string $influencerMessageTemplate = null, ?Follower $specificFollower = null)
    {
        $slot->load(['follower', 'lesson']);
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $slot->date_time)->toDayDateTimeString();

        if ($specificFollower) {
            $personalizedMessage = str_replace(
                [':name'],
                [$slot->lesson->user->name],
                $followerMessageTemplate
            );

            if (isset($specificFollower->pushToken->token)) {
                SendPushNotification::dispatch($specificFollower->pushToken->token, $notificationType, $personalizedMessage);
            }

            $followerPhone = Str::of($specificFollower->dial_code)->append($specificFollower->phone)->value();
            SendSMS::dispatch($followerPhone, $personalizedMessage);
        } else {

            $influencer = $slot->lesson->user;

            // Format messages for influencer
            $messageinfluencer = __($influencerMessageTemplate, [
                'date' => $date,
            ]);

            // Notify all followers who booked the slot
            if (isset($followerMessageTemplate)) {
                foreach ($slot->follower as $follower) {
                    $messageFollower = __($followerMessageTemplate, [
                        'influencer' => $influencer?->name,
                        'lesson'     => $slot->lesson->lesson_name,
                        'date'       => $date,
                    ]);

                    // Send push notification to followers
                    if (! empty($follower->pushToken?->token) && ! $follower->pivot->isFriend) {
                        SendPushNotification::dispatch($follower->pushToken->token, $notificationType, $messageFollower);
                    }

                    // Send SMS to followers (if they have valid phone numbers)
                    if (! empty($follower->dial_code) && ! empty($follower->phone) && ! $follower->pivot->isFriend) {
                        $userPhone = Str::of($follower->dial_code)->append($follower->phone)->value();
                        $userPhone = str_replace(['(', ')'], '', $userPhone);
                        SendSMS::dispatch($userPhone, $messageFollower);
                    }
                }
            }

            if (isset($influencerMessageTemplate)) {
                // Send push notification to influencer
                if (! empty($influencer->pushToken?->token)) {
                    SendPushNotification::dispatch($influencer->pushToken->token, $notificationType, $messageinfluencer);
                }

                // Send SMS to influencer (if they have a valid phone number)
                if (! empty($influencer->dial_code) && ! empty($influencer->phone)) {
                    $influencerPhone = Str::of($influencer->dial_code)->append($influencer->phone)->value();
                    $influencerPhone = str_replace(['(', ')'], '', $influencerPhone);
                    SendSMS::dispatch($influencerPhone, $messageinfluencer);
                }
            }
        }
    }

    public function createSessionForPayment(Purchase $purchase, $redirect, $slot_id = null)
    {
        try {
            $tenantId = tenancy()->tenant->id;
            tenancy()->central(function () use (&$application_fee_percentage, &$application_currency, $tenantId) {
                $userData = User::where('tenant_id', $tenantId)
                    ->select('application_fee_percentage', 'currency')
                    ->first();
                $application_fee_percentage = $userData?->application_fee_percentage;
                $application_currency       = $userData?->currency ?? 'usd';
            });

            $influencer      = $purchase?->influencer;
            $isinfluencerUSA = $influencer?->country == 'United States';

            Stripe::setApiKey(config('services.stripe.secret'));

            $accountId = $influencer?->stripe_account_id;
            $account   = Account::retrieve($accountId);

            $influencerCurrency = $account?->default_currency ?? 'usd';
            $convertedAmount    = $purchase?->total_amount * 100;

            if ($influencerCurrency !== $application_currency) {
                $exchangeRates   = \Stripe\ExchangeRate::retrieve($influencerCurrency);
                $conversionRate  = $exchangeRates['rates'][$application_currency] ?? 1;
                $convertedAmount = round($convertedAmount / $conversionRate);
            }

            $applicationFeeAmount = round(($application_fee_percentage / 100) * $convertedAmount);

            $success_params = [
                'purchase_id' => $purchase->id,
                'redirect'    => $redirect,
                'user_id'     => Auth::user()->id,
            ];

            $cancel_params = [
                'purchase_id' => $purchase->id,
                'redirect'    => $redirect,
                'user_id'     => Auth::user()->id,
            ];

            if ($slot_id) {
                $success_params['slot_id'] = $slot_id;
            }

            $purchase->load('influencer');

            $sessionData = [
                'line_items'          => [[
                    'price_data' => [
                        'currency'     => $influencerCurrency,
                        'product_data' => [
                            'name' => "$purchase->id " . "$purchase->influencer_id" . "$purchase->lesson_id",
                        ],
                        'unit_amount'  => $convertedAmount,
                    ],
                    'quantity'   => 1,
                ]],
                'payment_intent_data' => [
                    'application_fee_amount' => $applicationFeeAmount,
                    'transfer_data'          => ['destination' => $accountId],
                ],
                'mode'                => 'payment',
                'customer'            => Auth::user()?->stripe_cus_id ?? null,
                'success_url'         => route('purchase-success', $success_params),
                'cancel_url'          => route('purchase-cancel', $cancel_params),
            ];

            if (! $isinfluencerUSA) {
                $sessionData['payment_intent_data']['on_behalf_of'] = $accountId;
            }

            if (
                $influencer?->active_status &&
                ! empty($account->id) &&
                $account->charges_enabled &&
                ! empty($account->capabilities['card_payments']) &&
                $account->capabilities['card_payments'] === 'active'
            ) {
                $session = Session::create($sessionData);
            } else {
                throw new Exception('There is a problem with booking lessons for this influencer. Kindly contact admin.');
            }

            if (! empty($session?->id)) {
                $purchase->session_id = $session->id;
                $purchase->save();
            }

            return $session;
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        }
    }

    public function confirmPurchaseWithRedirect(Request $request, bool $returnJson = false)
    {
        try {
            $request->validate([
                'purchase_id' => 'required',
            ]);

            $purchase = Purchase::find($request?->purchase_id);

            if ($purchase && Auth::user()->can('create-purchases') && ! ! $purchase->influencer->is_stripe_connected) {

                $session = $this->createSessionForPayment($purchase, true);

                if (empty($session->url)) {
                    throw new \Exception('Failed to generate payment link');
                }

                return $returnJson
                ? response()->json(['payment_url' => $session->url], 200)
                : redirect($session->url);
            }

            throw new \Exception('Failed to generate payment link');
        } catch (\Exception $e) {

            \Log::error('Payment link generation failed: ' . $e->getMessage());

            if ($returnJson) {
                return response()->json(['error' => 'Failed to generate payment link, please try again later.'], 500);
            }

            return redirect()->back()->withErrors(['failed' => 'Failed to generate payment link, please try again later.' . $e->getMessage()]);
        }
    }
}
