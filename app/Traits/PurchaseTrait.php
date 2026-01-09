<?php

namespace App\Traits;

use App\Actions\SendPushNotification;
use App\Actions\SendSMS;
use App\Models\Lesson;
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
            // $convertedAmount    = $purchase?->total_amount * 100;


            
            // Calculate platform fee percentage (e.g., 10%)
            $platformPercent = $application_fee_percentage; // This should come from your config/settings

            // Calculate the actual amount the instructor should receive (base price)
            $basePrice = $purchase->total_amount * 100; // Or however you calculate the base amount

            // Initialize variables
            $convertedAmount = $basePrice;
            $applicationFeeAmount = 0;

            // **Scenario 1: Instructor pays both fees**
            if (
                $influencer?->stripe_transaction_fee == 'instructor' &&
                $influencer?->stripe_tuneup_percentage_fee == 'instructor'
            ) {
                // Student pays: 300
                $convertedAmount = $basePrice; // 300
                $platformFeeAmount = $basePrice * ($platformPercent / 100); // 30
                $applicationFeeAmount = $platformFeeAmount; // Direct fee in cents

                // No Stripe fee recovery needed as instructor pays it
            }

            // **Scenario 2: Student pays Stripe fee, Instructor pays Platform fee**
            elseif (
                $influencer?->stripe_transaction_fee == 'student' &&
                $influencer?->stripe_tuneup_percentage_fee == 'instructor'
            ) {
                // Student pays: 300 + Stripe fees
                $stripePerc = 0.029;       // 2.9%
                $stripeFixed = 30;         // $0.30 → 30 cents

                $gross = ($basePrice + $stripeFixed) / (1 - $stripePerc);
                $convertedAmount = round($gross); // ~309

                // Platform fee is 10% of 300 = 30 (paid by instructor)
                $platformFeeAmount = $basePrice * ($platformPercent / 100); // 30
                $applicationFeeAmount = $platformFeeAmount;
            }

            // **Scenario 3: Student pays Platform fee, Instructor pays Stripe fee**
            elseif (
                $influencer?->stripe_transaction_fee == 'instructor' &&
                $influencer?->stripe_tuneup_percentage_fee == 'student'
            ) {
                // Student pays: 300 + Platform fee (10% of 300 = 30)
                $convertedAmount = $basePrice * (1 + ($platformPercent / 100)); // 300 + 30 = 330
                $convertedAmount = round($convertedAmount);

                // Platform fee is 10% of 300 = 30
                // Since student is paying it, it becomes part of the total
                $platformFeeAmount = $basePrice * ($platformPercent / 100); // 30
                $applicationFeeAmount = $platformFeeAmount;

                // No Stripe fee recovery needed as instructor pays it
            }

            // **Scenario 4: Student pays both fees**
            elseif (
                $influencer?->stripe_transaction_fee == 'student' &&
                $influencer?->stripe_tuneup_percentage_fee == 'student'
            ) {
                // First: Add platform fee to base price
                $priceWithPlatformFee = $basePrice * (1 + ($platformPercent / 100)); // 300 + 30 = 330

                // Then: Add Stripe fees on top
                $stripePerc = 0.029;       // 2.9%
                $stripeFixed = 30;         // $0.30 → 30 cents

                $gross = ($priceWithPlatformFee + $stripeFixed) / (1 - $stripePerc);
                $convertedAmount = round($gross); // ~339

                // Platform fee is 10% of 300 = 30
                $platformFeeAmount = $basePrice * ($platformPercent / 100); // 30
                $applicationFeeAmount = $platformFeeAmount;
            }

            // Convert to cents for Stripe
            $convertedAmount = round($convertedAmount); // Already in cents if amount is in dollars
            $applicationFeeAmount = round($applicationFeeAmount);

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
            // Prepare session data
            $sessionData = [
                'line_items' => [[
                    'price_data' => [
                        'currency' => $influencerCurrency,
                        'product_data' => [
                            'name' => "$purchase->id " . "$purchase->influencer_id" . "$purchase->lesson_id",
                        ],
                        'unit_amount' => $convertedAmount,
                    ],
                    'quantity' => 1,
                ]],
                'payment_intent_data' => [
                    'application_fee_amount' => $applicationFeeAmount,
                    // 'transfer_data' => ['destination' => $accountId],
                ],
                'mode' => 'payment',
                'allow_promotion_codes' => true,
                'customer' => Auth::user()?->stripe_cus_id ?? null,
                'success_url' => route(
                    
                    'purchase-success',
                    $success_params
                ) . '&stripe_session={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('purchase-cancel', $cancel_params),
            ];

            // if (!$isInstructorUSA) {
            //     $sessionData['payment_intent_data']['on_behalf_of'] = $accountId;
            // }

            // Create the session
            $session = Session::create($sessionData, [
                'stripe_account' => $influencer->stripe_account_id,
            ]);


            // if ($influencerCurrency !== $application_currency) {
            //     $exchangeRates   = \Stripe\ExchangeRate::retrieve($influencerCurrency);
            //     $conversionRate  = $exchangeRates['rates'][$application_currency] ?? 1;
            //     $convertedAmount = round($convertedAmount / $conversionRate);
            // }

            // // $applicationFeeAmount = round(($application_fee_percentage / 100) * $convertedAmount);

            //  $applicationFeeAmount = round(($application_fee_percentage / 100) * $convertedAmount);

            // if ($influencer?->stripe_transaction_fee != 'instructor') { //keep instructor as it is in db
            //     // 🎯 Add Stripe fee recovery here
            //     $stripePerc = 0.029;       // 2.9%
            //     $stripeFixed = 30;         // $0.30 → 30 cents
            //     $gross = ($convertedAmount + $stripeFixed) / (1 - $stripePerc);
            //     $convertedAmount = round($gross);
            // }

            //  if ($influencer?->stripe_tuneup_percentage_fee != 'instructor') {
            //     $convertedAmount += $applicationFeeAmount;
            // }

            // $success_params = [
            //     'purchase_id' => $purchase->id,
            //     'redirect'    => $redirect,
            //     'user_id'     => Auth::user()->id,
            // ];

            // $cancel_params = [
            //     'purchase_id' => $purchase->id,
            //     'redirect'    => $redirect,
            //     'user_id'     => Auth::user()->id,
            // ];

            // if ($slot_id) {
            //     $success_params['slot_id'] = $slot_id;
            // }

            // $purchase->load('influencer');

            // $sessionData = [
            //     'line_items'          => [[
            //         'price_data' => [
            //             'currency'     => $influencerCurrency,
            //             'product_data' => [
            //                 'name' => "$purchase->id " . "$purchase->influencer_id" . "$purchase->lesson_id",
            //             ],
            //             'unit_amount'  => $convertedAmount,
            //         ],
            //         'quantity'   => 1,
            //     ]],
            //     'payment_intent_data' => [
            //         'application_fee_amount' => $applicationFeeAmount,
            //         'transfer_data'          => ['destination' => $accountId],
            //     ],
            //     'mode'                => 'payment',
            //     'customer'            => Auth::user()?->stripe_cus_id ?? null,
            //     'success_url'         => route('purchase-success', $success_params),
            //     'cancel_url'          => route('purchase-cancel', $cancel_params),
            // ];

            // if (! $isinfluencerUSA) {
            //     $sessionData['payment_intent_data']['on_behalf_of'] = $accountId;
            // }

            // if (
            //     $influencer?->active_status &&
            //     ! empty($account->id) &&
            //     // $account->charges_enabled &&
            //     ! empty($account->capabilities['card_payments'])
            //     // $account->capabilities['card_payments'] === 'active'
            // ) {
            //     $session = Session::create($sessionData);
            // } else {
            //     throw new Exception('There is a problem with booking lessons for this influencer. Kindly contact admin.');
            // }

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

    public function createSessionForPaymentNew($lesson_id)
    {
        try {
            $tenantId = tenancy()->tenant->id;
            tenancy()->central(function () use (&$application_fee_percentage, &$application_currency, $tenantId) {
                $userData = User::where('tenant_id', $tenantId)
                    ->select('application_fee_percentage', 'currency')
                    ->first();
                $application_fee_percentage = $userData?->application_fee_percentage;
                $application_currency = $userData?->currency ?? 'usd';
            });

            $lesson = Lesson::find($lesson_id);
            $instructor = $lesson?->user;
            $isInstructorUSA = $instructor?->country == 'United States';

            Stripe::setApiKey(config('services.stripe.secret'));

            $accountId = $instructor?->stripe_account_id;
            $account = Account::retrieve($accountId);
            $instructorCurrency = $account?->default_currency ?? 'usd';
            $convertedAmount = $lesson?->lesson_price * 100;

            // Convert currency if needed
            if ($instructorCurrency !== $application_currency) {
                $exchangeRates = \Stripe\ExchangeRate::retrieve($instructorCurrency);
                $conversionRate = $exchangeRates['rates'][$application_currency] ?? 1;
                $convertedAmount = round($convertedAmount / $conversionRate);
            }

            $applicationFeeAmount = round(($application_fee_percentage / 100) * $convertedAmount);
            $success_url = route('purchase.checkout', [
                'lesson_id' => $lesson_id,
                'user_id' => Auth::id(),
            ]) . '&session_id={CHECKOUT_SESSION_ID}';
            // Create session first (without success/cancel URL)
            $session = \Stripe\Checkout\Session::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => $instructorCurrency,
                        'product_data' => [
                            'name' => "{$instructor->id}-{$lesson->id}",
                        ],
                        'unit_amount' => $convertedAmount,
                    ],
                    'quantity' => 1,
                ]],
                'payment_intent_data' => [
                    'application_fee_amount' => $applicationFeeAmount,
                    'transfer_data' => ['destination' => $accountId],
                ],
                'mode' => 'payment',
                'customer' => Auth::user()?->stripe_cus_id ?? null,
                'success_url' => $success_url,
                'cancel_url' => route('purchase-cancel', [
                    'lesson_id' => $lesson_id,
                    'user_id' => Auth::id(),
                ]),
            ]);

            return $session;
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        }
    }
}
