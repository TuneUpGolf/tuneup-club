<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Follower;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCoupon;
use App\Services\ChatService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Stripe\Account;
use Stripe\Checkout\Session;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\Product;
use Stripe\Checkout\Session as StripeSession;
use Stripe\StripeClient;

class StripeController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    public function stripe()
    {
        $view = view('payment.PaymentStripe');
        return ['html' => $view->render()];
    }

    public function connectStripe(Request $request)
    {
        try {
            $request->validate([
                'influencer_id' => 'required',
            ]);

            $influencer = User::find($request->influencer_id);


            Stripe::setApiKey(config('services.stripe.secret'));
            $stripeClient = new StripeClient(config('services.stripe.secret'));

            if (empty($influencer->stripe_account_id)) {
                $account = $stripeClient->accounts->create([
                    'type'  => 'standard',
                    'email' => $influencer->email,
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                ]);
                $influencer->stripe_account_id = $account->id;
                $influencer->save();
            }

            $accountLink = $stripeClient->accountLinks->create([
                'account'     => $influencer->stripe_account_id,
                'refresh_url' => route('stripe.refresh', ['influencer_id' => $influencer->id]),
                'return_url'  => route('stripe-redirect-create', ['account_id' => $influencer->stripe_account_id, 'influencer_id' => $influencer->id]),
                'type'        => 'account_onboarding',
            ]);
            return redirect($accountLink->url);
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        }
    }

    public function refreshAccountLink(Request $request)
    {
        try {
            $request->validate([
                'influencer_id' => 'required',
            ]);

            $influencer = User::find($request->influencer_id);

            Stripe::setApiKey(config('services.stripe.secret'));
            $stripeClient = new StripeClient(config('services.stripe.secret'));

            if (empty($influencer->stripe_account_id)) {
                $account = $stripeClient->accounts->create([
                    'type'  => 'standard',
                    'email' => $influencer->email,
                ]);
                $influencer->stripe_account_id = $account->id;
                $influencer->save();
            }

            $accountLink = $stripeClient->accountLinks->create([
                'account'     => $influencer->stripe_account_id,
                'refresh_url' => route('stripe.refresh', ['influencer_id' => $influencer->id]),
                'return_url'  => route('stripe-redirect-create', ['account_id' => $influencer->stripe_account_id, 'influencer_id' => $influencer->id]),
                'type'        => 'account_onboarding',
            ]);
            return redirect($accountLink->url);
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        }
    }

    public function redirectFromCreate(Request $request)
    {
        try {
            $request->validate([
                'account_id'    => 'required',
                'influencer_id' => 'required',
            ]);
            $influencer = User::where('id', $request->get('influencer_id'))->first();
            if (! empty($influencer->stripe_account_id)) {

                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $stripeClient = new \Stripe\StripeClient(config('services.stripe.secret'));
                $account      = $stripeClient->accounts->retrieve($influencer->stripe_account_id);

                if ($account && $account->id) {
                    $isVerified = false;

                    if (isset($account->charges_enabled) && $account->charges_enabled) {
                        $isVerified = true;
                    }

                    if (isset($account->payouts_enabled) && $account->payouts_enabled) {
                        $isVerified = true;
                    }

                    // Save the account ID and verification status
                    $influencer->stripe_account_id   = $influencer->stripe_account_id;
                    $influencer->is_stripe_connected = $isVerified;
                }
                $influencer->save();
            }
            return redirect()->route('home')->with('success', __('Stripe Connect Integrated Successfully'));
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        }
    }

    public function stripePostPending(Request $request)
    {

        $planID   = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser = Auth::user();

        if ($authUser->type == 'Admin') {
            $plan = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::with(['influencer'])->find($planID);
            });
            $resData = tenancy()->central(function ($tenant) use ($plan, $request) {
                $couponId      = '0';
                $price         = $plan->price;
                $couponCode    = null;
                $discountValue = null;
                $coupons       = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();
                if ($coupons) {
                    $couponCode = $coupons->code;
                    $usedCoupun = $coupons->used_coupon();
                    if ($coupons->limit == $usedCoupun) {
                        $resData['errors'] = __('This coupon code has expired.');
                    } else {
                        $discount      = $coupons->discount;
                        $discount_type = $coupons->discount_type;
                        $discountValue = UtilityFacades::calculateDiscount($price, $discount, $discount_type);
                        $price         = $price - $discountValue;
                        if ($price < 0) {
                            $price = $plan->price;
                        }
                        $couponId = $coupons->id;
                    }
                }
                $data = Order::create([
                    'plan_id'         => $plan->id,
                    'user_id'         => $tenant->id,
                    'amount'          => $price,
                    'discount_amount' => $discountValue,
                    'coupon_code'     => $couponCode,
                    'status'          => 0,
                ]);

                $resData['total_price'] = $price;
                $resData['plan_id']     = $plan->id;
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                $resData['stripe_account_id']    = $plan->influencer?->stripe_account_id;
                $resData['stripe_product_id']     = $plan->stripe_product_id;
                $resData['stripe_price_id']     = $plan->stripe_price_id;
                return $resData;
            });
            return $resData;
        } else {

            if ($authUser->type == 'Follower') {
                $authUserId = 0;
                $followerId = $authUser->id;
            } else {
                $authUserId = $authUser->id;
                $followerId = null;
            }
            $followerId    = $authUser->type == 'Follower' ? $authUser->id : null;
            $plan          = Plan::with(['influencer'])->find($planID);

            if ($plan->is_chat_enabled && is_null($authUser->chat_user_id)) {
                return response()->json([
                    'error' => 'Chat user ID is required to proceed with the payment.'
                ]);
            }

            $couponId      = '0';
            $price         = $plan->price;
            $couponCode    = null;
            $discountValue = null;
            $coupons       = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();
            if ($coupons) {
                $couponCode = $coupons->code;
                $usedCoupun = $coupons->used_coupon();
                if ($coupons->limit == $usedCoupun) {
                    $resData['errors'] = __('This coupon code has expired.');
                } else {
                    $discount      = $coupons->discount;
                    $discount_type = $coupons->discount_type;
                    $discountValue = UtilityFacades::calculateDiscount($price, $discount, $discount_type);
                    $price         = $price - $discountValue;
                    if ($price < 0) {
                        $price = $plan->price;
                    }
                    $couponId = $coupons->id;
                }
            }
            $data = Order::create([
                'plan_id'         => $plan->id,
                'user_id'         => $authUserId,
                'amount'          => $price,
                'discount_amount' => $discountValue,
                'coupon_code'     => $couponCode,
                'status'          => 0,
                'follower_id'     => $followerId,
            ]);

            $resData['total_price'] = $price;
            $resData['plan_id']     = $plan->id;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
            $resData['stripe_account_id']    = $plan->influencer?->stripe_account_id;
            $resData['stripe_product_id']     = $plan->stripe_product_id;
            $resData['stripe_price_id']     = $plan->stripe_price_id;
            // dd($resData);
            return $resData;
        }
    }
    public function stripeSession(Request $request)
    {

        // ✅ Get Plan with Influencer
        if (Auth::user()->type === 'Admin') {
            $planDetails = tenancy()->central(function ($tenant) use ($request) {
                return Plan::with(['influencer'])->find($request->plan_id);
            });
        } else {
            $planDetails = Plan::with(['influencer'])->find($request->plan_id);
        }

        if (! $planDetails || empty($planDetails->influencer?->stripe_account_id)) {
            return response()->json([
                'status' => 0,
                'error'  => ['message' => 'Plan or connected account not found.']
            ], 404);
        }

        // ✅ Always use your platform secret key

        Stripe::setApiKey(config('services.stripe.secret'));

        $account_id = $planDetails->influencer->stripe_account_id;
        $platformAccount = \Stripe\Account::retrieve();

        // Get connected account (influencer’s account)
        $destinationAccount = \Stripe\Account::retrieve($account_id);


        // (Optional) verify price exists inside the connected account
        try {
            $price  = Price::retrieve($planDetails->stripe_price_id, ['stripe_account' => $account_id]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'error'  => ['message' => 'Price not found in connected account: ' . $e->getMessage()]
            ], 404);
        }

        $platform_fee = UtilityFacades::getsettings('application_fee_percentage');
        if (empty($platform_fee) || !is_numeric($platform_fee) || $platform_fee < 0 || $platform_fee > 100) {
            return response()->json([
                'status' => 0,
                'error'  => ['message' => 'Platform fee is not set or invalid in settings. It should be between 0 and 100.']
            ], 404);
        }

        $response = [];

        // ✅ Create checkout session
        if ($request->has('createCheckoutSession')) {
            try {
                $checkout_session = Session::create([
                    'payment_method_types' => ['card'],
                    'mode' => 'subscription',
                    'line_items' => [[
                        'price'    => $planDetails->stripe_price_id,
                        'quantity' => 1,
                    ]],
                    'success_url' => route('stripe.success.pay', Crypt::encrypt([
                        'coupon'   => $request->coupon,
                        'plan_id'  => $planDetails->id,
                        'price'    => $request->amount,
                        'user_id'  => Auth::id(),
                        'order_id' => $request->order_id,
                        'stripe_account_id' => $account_id,
                        'type'     => 'stripe',
                    ])) . '&session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('stripe.cancel.pay', Crypt::encrypt([
                        'coupon'   => $request->coupon,
                        'plan_id'  => $planDetails->id,
                        'price'    => $request->amount,
                        'user_id'  => Auth::id(),
                        'order_id' => $request->order_id,
                        'type'     => 'stripe',
                    ])),
                    'metadata' => [
                        'plan_id' => $request->plan_id,
                        'user_id' => Auth::id(),
                    ],
                    'subscription_data' => [
                        'application_fee_percent' => $platform_fee, // Use the platform fee from settings

                    ]
                ], [
                    // 👇 THIS IS THE IMPORTANT FIX
                    'stripe_account' => $account_id
                ]);





                $response = [
                    'status'    => 1,
                    'message'   => 'Checkout session created successfully.',
                    'sessionId' => $checkout_session->id,
                    'url'       => $checkout_session->url,
                ];
            } catch (\Exception $e) {
                $response = [
                    'status' => 0,
                    'error'  => ['message' => 'Checkout session creation failed. ' . $e->getMessage()],
                ];
            }
        }

        return response()->json($response);
    }


    public function paymentPending(Request $request)
    {
        if (Auth::user()->type == 'Admin') {
            $user  = User::find(Auth::user()->id);
            $order = tenancy()->central(function ($tenant) use ($request, $user) {
                $data['plan_details'] = Plan::find($request->plan_id);
                $user                 = User::where('email', $user->email)->first();
                $data['order']        = Order::create([
                    'plan_id' => $request->plan_id,
                    'user_id' => $user->id,
                    'amount'  => $data['plan_details']->price,
                    'status'  => 0,
                ]);
                return $data;
            });
            $response = [
                'status'          => 0,
                'order_id'        => $order['order']->id,
                'amount'          => $order['order']->amount,
                'plan_name'       => $order['plan_details']->name,
                'currency'        => $request->currency,
                'currency_symbol' => $request->currency_symbol,
            ];
            echo json_encode($response);
            die;
        } else {
            $user = User::find(Auth::user()->id); {
                $planDetails = Plan::find($request->plan_id);
                $user        = User::where('email', $user->email)->first();
                $data        = Order::create([
                    'plan_id' => $request->plan_id,
                    'user_id' => Auth::user()->id,
                    'amount'  => $planDetails->price,
                    'status'  => 0,
                ]);
            }
            $response = [
                'status'          => 0,
                'order_id'        => $data->id,
                'amount'          => $planDetails->price,
                'plan_name'       => $planDetails->name,
                'currency'        => $request->currency,
                'currency_symbol' => $request->currency_symbol,
            ];
            echo json_encode($response);
            die;
        }
    }

    public function paymentCancel($data)
    {
        if (strpos($data, '&session_id=') !== false) {
            [$encrypted, $sessionId] = explode('&session_id=', $data, 2);
        } else {
            $encrypted = $data;
            $sessionId = null;
        }
        $data = Crypt::decrypt($data);
        if (Auth::user()->type == 'Admin') {
            $order = tenancy()->central(function ($tenant) use ($data) {
                $datas               = Order::find($data['order_id']);
                $datas->status       = 2;
                $datas->payment_type = 'stripe';
                $datas->update();
            });
        } else {
            $datas               = Order::find($data['order_id']);
            $datas->status       = 2;
            $datas->payment_type = 'stripe';
            $datas->update();
        }
        return redirect()->route('plans.index')->with('errors', __('Payment canceled.'));
    }

    public function paymentSuccess($data)
    {

        if (strpos($data, '&session_id=') !== false) {
            [$encrypted, $sessionId] = explode('&session_id=', $data, 2);
        } else {
            $encrypted = $data;
            $sessionId = null;
        }
        $data = Crypt::decrypt($encrypted);

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::retrieve($sessionId, [
            'stripe_account' => $data['stripe_account_id'] // only if connected accounts
        ]);

        $subscription = \Stripe\Subscription::retrieve($session->subscription, [
            'stripe_account' => $data['stripe_account_id']
        ]);

        $latestInvoice = \Stripe\Invoice::retrieve($subscription->latest_invoice, [
            'stripe_account' => $data['stripe_account_id']
        ]);
        $platform_fee  = UtilityFacades::getsettings('application_fee_percentage');
        $totalAmount    = $latestInvoice->total / 100;       // total charged
        $taxAmount      = $latestInvoice->tax / 100 ?? 0;    // Stripe invoice tax
        $platformAmount = ($totalAmount * $platform_fee) / 100; // your fee %
        $netAmount      = $totalAmount - $platformAmount - $taxAmount;

        $superAdmin = DB::connection('mysql')->table('users')
            ->where('type', 'Super Admin')
            ->first();

        if ($superAdmin) {
            DB::connection('mysql')->table('users')
                ->where('id', $superAdmin->id)
                ->update([
                    'service_earning' => $superAdmin->service_earning + $platformAmount,
                ]);
        }
        \Log::info('Stripe Payment Success - Super Admin', (array) $superAdmin);



        if (Auth::user()->type == 'Admin') {
            $order = tenancy()->central(function ($tenant) use ($data,  $sessionId, $subscription, $taxAmount, $platformAmount, $netAmount) {
                $datas               = Order::find($data['order_id']);
                $datas->status       = 1;
                $datas->payment_type = 'stripe';
                $datas->checkout_session_id = $sessionId;
                $datas->subscription_id     = $subscription->id;
                $datas->tax_amount     = $taxAmount;
                $datas->platform_amount = $platformAmount;
                $datas->net_amount      = $netAmount;
                $datas->update();
                $coupons = Coupon::find($data['coupon']);
                $user    = User::find($tenant->id);
                if (! empty($coupons)) {
                    $userCoupon         = new UserCoupon();
                    $userCoupon->user   = $user->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order  = $datas->id;
                    $userCoupon->save();
                    $usedCoupun = $coupons->used_coupon();
                    if ($coupons->limit <= $usedCoupun) {
                        $coupons->is_active = 0;
                        $coupons->save();
                    }
                }
                $plan          = Plan::find($data['plan_id']);
                $user->plan_id = $plan->id;
                if ($plan->durationtype == 'Month' && $plan->id != '1') {
                    $user->plan_expired_date = Carbon::now()->addMonths($plan->duration)->isoFormat('YYYY-MM-DD');
                } elseif ($plan->durationtype == 'Year' && $plan->id != '1') {
                    $user->plan_expired_date = Carbon::now()->addYears($plan->duration)->isoFormat('YYYY-MM-DD');
                } else {
                    $user->plan_expired_date = null;
                }
                $user->save();
            });
        } else {
            $datas               = Order::find($data['order_id']);
            $datas->status       = 1;
            $datas->payment_type = 'stripe';
            $datas->checkout_session_id = $sessionId;
            $datas->subscription_id     = $subscription->id;
            $datas->tax_amount     = $taxAmount;
            $datas->platform_amount = $platformAmount;
            $datas->net_amount      = $netAmount;
            $datas->update();
            $currentUser = Auth::user();
            $userType    = $currentUser->type;

            $user    = $userType === 'Follower' ? Follower::find($currentUser->id) : User::find($currentUser->id);
            $coupons = Coupon::find($data['coupon']);
            if (! empty($coupons)) {
                $userCoupon = new UserCoupon();
                if ($userType == 'Follower') {
                    $userCoupon->follower = $user->id;
                } else {
                    $userCoupon->user = $user->id;
                }
                $userCoupon->coupon = $coupons->id;
                $userCoupon->order  = $datas->id;
                $userCoupon->save();
                $usedCoupun = $coupons->used_coupon();
                if ($coupons->limit <= $usedCoupun) {
                    $coupons->is_active = 0;
                    $coupons->save();
                }
            }
            $plan          = Plan::find($data['plan_id']);
            $user->plan_id = $plan->id;
            if ($plan->durationtype == 'Month' && $plan->id != '1') {
                $planExpiredDate = Carbon::now()->addMonths($plan->duration)->isoFormat('YYYY-MM-DD');
                $user->plan_expired_date = $planExpiredDate;
            } elseif ($plan->durationtype == 'Year' && $plan->id != '1') {
                $planExpiredDate = Carbon::now()->addYears($plan->duration)->isoFormat('YYYY-MM-DD');
                $user->plan_expired_date = $planExpiredDate;
            } else {
                $user->plan_expired_date = null;
            }
            if ($plan->is_chat_enabled) {
                $this->chatService->updateUser($user->chat_user_id, 'plan_expired_date', $planExpiredDate, $user->email);
                $groupId = $this->chatService->createGroup($user->chat_user_id, $user->follows->first()?->influencer->chat_user_id);
                if ($groupId) {
                    $user->group_id = $groupId;
                }
            }
            $user->save();
        }
        if ($userType == 'Follower') {
            return redirect()->route('home')->with('status', __('Payment successfully!'));
        } else {
            return redirect()->route('plans.index')->with('status', __('Payment successfully!'));
        }
    }
    public function handleStripeWebhook(Request $request)
    {
        \Log::info('Stripe Webhook Received', $request->all());
        $payload = $request->getContent();

        $sig_header = $request->server('HTTP_STRIPE_SIGNATURE');
        $endpoint_secret = config('services.stripe.webhook.secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\Exception $e) {
            return response('Webhook error: ' . $e->getMessage(), 400);
        }

        if ($event->type === 'checkout.session.completed') {
            \Log::info('Stripe Checkout Session Completed', $event->data->object);
            $session = $event->data->object;
            if ($session) {
                $old_order = Order::where('checkout_session_id', $session->id)->first();
                // ✅ read metadata
                $plan_id  = $old_order->plan_id;
                $user_id  = $old_order->user_id;
                $order_id = $old_order->id;

                // ✅ amount in cents, convert to normal
                $amount   = $session->amount_total / 100;

                Order::create([
                    'user_id'      => $user_id,
                    'plan_id'      => $plan_id,
                    'amount'       => $amount,
                    'payment_type' => 'stripe',
                    'status'       => 1,
                    'order_ref'    => $session->id,
                ]);
            }
        }

        if ($event->type === 'invoice.payment_failed') {
            \Log::info('Stripe Invoice Payment Failed', $event->data->object);
            $session = $event->data->object;
            $old_order = Order::where('checkout_session_id', $session->id)->first();
            \App\Models\Order::create([
                'user_id'      => $old_order->user_id ?? null,
                'plan_id'      => $old_order->plan_id ?? null,
                'amount'       => $session->amount_due / 100,
                'payment_type' => 'stripe',
                'status'       => -1,
                'order_ref'    => $session->id,
            ]);
        }

        return response('Webhook handled', 200);
    }
}
