<?php

namespace App\Http\Controllers\Admin;

use Error;
use Exception;
use Stripe\Stripe;
use Stripe\Account;
use App\Models\Post;
use App\Models\User;
use App\Models\Follower;
use App\Models\PurchasePost;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Models\PurchaseAlbum;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PurchasePostController extends Controller
{
    public function purchasePost(Request $request)
    {
        $request->validate([
            'post_id' => 'required',
        ]);

        try {
            $post         = Post::where('paid', true)->where('id', $request->post_id)->where('status', 'active')->first();
            $purchasePost = PurchasePost::firstOrCreate(
                [
                    'follower_id' => Auth::user()->id,
                    'post_id'     => $post->id,
                ],
                [
                    'active_status' => false,
                ]
            );

            Stripe::setApiKey(config('services.stripe.secret'));

            $tenantId = tenancy()->tenant->id;
            tenancy()->central(function () use (&$application_fee_percentage, &$application_currency, $tenantId) {
                $userData = User::where('tenant_id', $tenantId)
                    ->select('application_fee_percentage', 'currency')
                    ->first();
                $application_fee_percentage = $userData?->application_fee_percentage;
                $application_currency = $userData?->currency ?? 'usd';
            });

            $instructor = $post->influencer;


            // $totalPrice = $post->price * 100;
            // $applicationFeeAmount = round(($application_fee_percentage / 100) * $totalPrice);

            // if ($instructor?->stripe_transaction_fee != 'instructor') {
            //     $stripePerc = 0.029;       // 2.9%
            //     $stripeFixed = 30;         // $0.30 → 30 cents
            //     $gross = ($totalPrice + $stripeFixed) / (1 - $stripePerc);
            //     $totalPrice = round($gross);
            // }
            // $currency = 'usd';
            // $minimumCents = 0.50;
            // $priceInCents = (int) round($totalPrice * 100);
            // $finalAmountInCents = max($priceInCents, $minimumCents);
            // $finalPrice = $finalAmountInCents / 100;

            // if ($instructor?->stripe_tuneup_percentage_fee != 'instructor') {
            //     $totalPrice += $applicationFeeAmount;
            // }


            // $accountId = $instructor?->stripe_account_id;
            // $account = Account::retrieve($accountId);

            // $sessionData = [
            //     'line_items' => [[
            //         'price_data' => [
            //             'currency' => $currency,
            //             'product_data' => [
            //                 'name' => "$post->title",
            //             ],
            //             'unit_amount' => $totalPrice,
            //         ],
            //         'quantity' => 1,
            //     ]],
            //     'payment_intent_data' => [
            //         'application_fee_amount' => $applicationFeeAmount,
            //         'transfer_data' => ['destination' => $accountId],
            //     ],
            //     'customer' => Auth::user()?->stripe_cus_id ?? null,
            //     'mode' => 'payment',
            //     'success_url' => route('purchase-post-success', [
            //         'purchase_post_id' => $purchasePost?->id,
            //         'student_id' => Auth::user()->id,
            //         'redirect' => $request->redirect
            //     ]),
            //     'cancel_url' => route('subscription-unsuccess'),
            // ];

            // //   if (!$isInstructorUSA) {
            // //     $sessionData['payment_intent_data']['on_behalf_of'] = $accountId;
            // // }

            // // Also add the same account verification logic as the first function
            // // dd(!empty($account->capabilities['card_payments']), $instructor?->active_status, !empty($account->id));
            // if (
            //     $instructor?->active_status &&
            //     !empty($account->id) &&
            //     !empty($account->capabilities['card_payments'])
            // ) {
            //     $session = Session::create($sessionData);
            // } else {
            //     throw new Exception('There is a problem with purchasing this post. Kindly contact admin.');
            // }


              $isInstructorUSA = $instructor?->country == 'United States';

            // Platform fee percentage
            $platformPercent = $application_fee_percentage; // e.g., 10

            // Calculate base price in cents
            $basePrice = $post->price * 100;

            // Initialize variables
            $convertedAmount = $basePrice;
            $applicationFeeAmount = 0;

            // **Scenario 1: Instructor pays both fees**
            if (
                $instructor?->stripe_transaction_fee == 'instructor' &&
                $instructor?->stripe_tuneup_percentage_fee == 'instructor'
            ) {
                // Student pays: base price only
                $convertedAmount = $basePrice;
                $platformFeeAmount = $basePrice * ($platformPercent / 100);
                $applicationFeeAmount = $platformFeeAmount;

                // No Stripe fee recovery needed as instructor pays it
            }

            // **Scenario 2: Student pays Stripe fee, Instructor pays Platform fee**
            elseif (
                $instructor?->stripe_transaction_fee == 'student' &&
                $instructor?->stripe_tuneup_percentage_fee == 'instructor'
            ) {
                // Student pays: base price + Stripe fees
                $stripePerc = 0.029;       // 2.9%
                $stripeFixed = 30;         // $0.30 → 30 cents

                $gross = ($basePrice + $stripeFixed) / (1 - $stripePerc);
                $convertedAmount = round($gross);

                // Platform fee is X% of base price (paid by instructor)
                $platformFeeAmount = $basePrice * ($platformPercent / 100);
                $applicationFeeAmount = $platformFeeAmount;
            }

            // **Scenario 3: Student pays Platform fee, Instructor pays Stripe fee**
            elseif (
                $instructor?->stripe_transaction_fee == 'instructor' &&
                $instructor?->stripe_tuneup_percentage_fee == 'student'
            ) {
                // Student pays: base price + Platform fee
                $convertedAmount = $basePrice * (1 + ($platformPercent / 100));
                $convertedAmount = round($convertedAmount);

                // Platform fee is X% of base price
                $platformFeeAmount = $basePrice * ($platformPercent / 100);
                $applicationFeeAmount = $platformFeeAmount;

                // No Stripe fee recovery needed as instructor pays it
            }

            // **Scenario 4: Student pays both fees**
            elseif (
                $instructor?->stripe_transaction_fee == 'student' &&
                $instructor?->stripe_tuneup_percentage_fee == 'student'
            ) {
                // First: Add platform fee to base price
                $priceWithPlatformFee = $basePrice * (1 + ($platformPercent / 100));

                // Then: Add Stripe fees on top
                $stripePerc = 0.029;       // 2.9%
                $stripeFixed = 30;         // $0.30 → 30 cents

                $gross = ($priceWithPlatformFee + $stripeFixed) / (1 - $stripePerc);
                $convertedAmount = round($gross);

                // Platform fee is X% of base price
                $platformFeeAmount = $basePrice * ($platformPercent / 100);
                $applicationFeeAmount = $platformFeeAmount;
            }

            // Round to nearest integer (cents)
            $convertedAmount = round($convertedAmount);
            $applicationFeeAmount = round($applicationFeeAmount);

            // Apply minimum amount check
            $currency = 'usd';
            $minimumCents = 0.50;
            $finalAmountInCents = max($convertedAmount, $minimumCents);

            // Convert back to dollars for display if needed
            $finalPrice = $finalAmountInCents / 100;

            // Retrieve instructor's Stripe account
            $accountId = $instructor?->stripe_account_id;
            $account = Account::retrieve($accountId);

            // Prepare session data
            $sessionData = [
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $post->title,
                        ],
                        'unit_amount' => $finalAmountInCents, // Use the calculated final amount
                    ],
                    'quantity' => 1,
                ]],
                'payment_intent_data' => [
                    'application_fee_amount' => $applicationFeeAmount,
                    // 'transfer_data' => ['destination' => $accountId],
                ],
                'customer' => Auth::user()?->stripe_cus_id ?? null,
                'mode' => 'payment',
                'success_url' => route('purchase-post-success', [
                    'purchase_post_id' => $purchasePost?->id,
                    'follower_id' => Auth::user()->id,
                    'redirect' => $request->redirect
                ]),
                'cancel_url' => route('subscription-unsuccess'),
            ];

            // Apply on_behalf_of for non-US instructors
            // if (!$isInstructorUSA) {
            //     $sessionData['payment_intent_data']['on_behalf_of'] = $accountId;
            // }

            // Verify account and create session
            if (
                $instructor?->active_status &&
                !empty($account->id) &&
                !empty($account->capabilities['card_payments'])
            ) {
                $session = Session::create($sessionData, [
                    'stripe_account' => $instructor->stripe_account_id,
                ]);
            } else {
                throw new Exception('There is a problem with purchasing this post. Kindly contact admin.');
            }

            if (!empty($session?->id)) {
                $purchasePost->session_id = $session?->id;
                $purchasePost->save();
            }

            if ($request->redirect == 1) {
                return response($session->url);
            }
            return redirect($session->url);

            // if (! empty($session?->id)) {
            //     $purchasePost->session_id = $session?->id;
            //     $purchasePost->save();
            // }

            // if ($session->payment_status === 'paid') {
            //     $purchasePost->active_status = true;
            //     $purchasePost->save();
            // }

            // if ($request->redirect == 1) {
            //     return response($session->url);
            // }
            // return redirect($session->url);
        } catch (Error $e) {
            return response($e, 419);
        }
    }

    public function purchasePostSuccess(Request $request)
    {
        $purchasePost = PurchasePost::find($request->query('purchase_post_id'));
        try {
            if (! ! $purchasePost) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = Session::retrieve($purchasePost->session_id, [
                    'stripe_account' => $purchasePost->post->influencer->stripe_account_id,
                ]);

                if ($session->payment_status == "paid") {
                    $purchasePost->active_status = true;
                    $purchasePost->session_id    = $session->id;
                    $purchasePost->save();
                    $follower = Follower::find($request->query('follower_id'));
                    if (! isset($follower->stripe_cus_id)) {
                        $follower->stripe_cus_id = $session->customer;
                        $follower->save();
                    }
                }

                if ($request->redirect == 1) {
                    return response('Post Purchased Successfully');
                }

                return redirect()->back()->with('success', 'Post Purchased Successfully');
            }
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        }
    }

    public function purchaseAllbumsSuccess(Request $request)
    {
        $purchasePost = PurchaseAlbum::find($request->query('purchase_post_id'));

        try {
            if (!!$purchasePost) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session  = Session::retrieve($purchasePost->session_id, [
                    'stripe_account' => $purchasePost->albumCategory->instructor->stripe_account_id,
                ]);

                if ($session->payment_status == "paid") {
                    $purchasePost->active_status = true;
                    $purchasePost->session_id = $session->id;
                    $purchasePost->save();
                    $student = Follower::find($request->query('student_id'));
                    if (!isset($student->stripe_cus_id)) {
                        $student->stripe_cus_id = $session->customer;
                        $student->save();
                    }
                }

                if ($request->redirect == 1) {
                    return response('Post Purchased Successfully');
                }

                return redirect()->route('home')->with('success', 'Post Purchased Successfully');
            }
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        };
    }
}
