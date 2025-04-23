<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Facades\UtilityFacades;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCoupon;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeController extends Controller
{
    public function stripe()
    {
        $view =  view('payment.PaymentStripe');
        return ['html' => $view->render()];
    }

    public function connectStripe(Request $request)
    {
        try {
            $request->validate([
                'influencer_id' => 'required',
            ]);

            $instructor = User::find($request->influencer_id);

            Stripe::setApiKey(config('services.stripe.secret'));
            $stripeClient = new StripeClient(config('services.stripe.secret'));

            if (empty($instructor->stripe_account_id)) {
                $account = $stripeClient->accounts->create([
                    'type' => 'standard',
                    'email' => $instructor->email,
                ]);
                $instructor->stripe_account_id = $account->id;
                $instructor->save();
            }

            $accountLink = $stripeClient->accountLinks->create([
                'account'     => $instructor->stripe_account_id,
                'refresh_url' => route('stripe.refresh', ['influencer_id' => $instructor->id]),
                'return_url'  => route('stripe-redirect-create', ['account_id' => $instructor->stripe_account_id, 'influencer_id' => $instructor->id]),
                'type'        => 'account_onboarding',
            ]);
            return redirect($accountLink->url);
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        };
    }

    public function refreshAccountLink(Request $request)
    {
        try {
            $request->validate([
                'influencer_id' => 'required',
            ]);
            $instructor = User::find($request->influencer_id);
            Stripe::setApiKey(config('services.stripe.secret'));
            $stripeClient = new StripeClient(config('services.stripe.secret'));

            if (empty($instructor->stripe_account_id)) {
                $account = $stripeClient->accounts->create([
                    'type' => 'standard',
                    'email' => $instructor->email,
                ]);
                $instructor->stripe_account_id = $account->id;
                $instructor->save();
            }

            $accountLink = $stripeClient->accountLinks->create([
                'account'     => $instructor->stripe_account_id,
                'refresh_url' => route('stripe.refresh', ['influencer_id' => $instructor->id]),
                'return_url'  => route('stripe-redirect-create', ['account_id' => $instructor->stripe_account_id, 'influencer_id' => $instructor->id]),
                'type'        => 'account_onboarding',
            ]);
            return redirect($accountLink->url);
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        };
    }

    public function redirectFromCreate(Request $request)
    {
        try {
            $request->validate([
                'account_id'    => 'required',
                'influencer_id' => 'required',
            ]);
            $instructor = User::where('id', $request->get('influencer_id'))->first();
            if (! empty($instructor->stripe_account_id)) {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                $stripeClient = new \Stripe\StripeClient(config('services.stripe.secret'));
                $account      = $stripeClient->accounts->retrieve($instructor->stripe_account_id);

                if ($account && $account->id) {
                    $isVerified = false;

                    if (isset($account->charges_enabled) && $account->charges_enabled) {
                        $isVerified = true;
                    }

                    if (isset($account->payouts_enabled) && $account->payouts_enabled) {
                        $isVerified = true;
                    }

                    // Save the account ID and verification status
                    $instructor->stripe_account_id = $instructor->stripe_account_id;
                    $instructor->is_stripe_connected = $isVerified;
                }
                $instructor->save();
            }
            return redirect()->route('home')->with('success', __('Stripe Connect Integrated Successfully'));
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        };
    }

    public function stripePostPending(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if ($authUser->type == 'Admin') {
            $plan   = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $resData =  tenancy()->central(function ($tenant) use ($plan, $request) {
                $couponId = '0';
                $price = $plan->price;
                $couponCode = null;
                $discountValue = null;
                $coupons = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();
                if ($coupons) {
                    $couponCode     = $coupons->code;
                    $usedCoupun     = $coupons->used_coupon();
                    if ($coupons->limit == $usedCoupun) {
                        $resData['errors'] = __('This coupon code has expired.');
                    } else {
                        $discount       = $coupons->discount;
                        $discount_type  = $coupons->discount_type;
                        $discountValue  =  UtilityFacades::calculateDiscount($price, $discount, $discount_type);
                        $price          = $price - $discountValue;
                        if ($price < 0) {
                            $price      = $plan->price;
                        }
                        $couponId       = $coupons->id;
                    }
                }
                $data = Order::create([
                    'plan_id'           => $plan->id,
                    'user_id'           => $tenant->id,
                    'amount'            => $price,
                    'discount_amount'   => $discountValue,
                    'coupon_code'       => $couponCode,
                    'status'            => 0,
                ]);

                $resData['total_price'] = $price;
                $resData['plan_id']     = $plan->id;
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                return $resData;
            });
            return $resData;
        } else {
            $plan           =  Plan::find($planID);
            $couponId       = '0';
            $price          = $plan->price;
            $couponCode     = null;
            $discountValue  = null;
            $coupons        = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();
            if ($coupons) {
                $couponCode     = $coupons->code;
                $usedCoupun     = $coupons->used_coupon();
                if ($coupons->limit == $usedCoupun) {
                    $resData['errors'] = __('This coupon code has expired.');
                } else {
                    $discount       = $coupons->discount;
                    $discount_type  = $coupons->discount_type;
                    $discountValue  =  UtilityFacades::calculateDiscount($price, $discount, $discount_type);
                    $price          = $price - $discountValue;
                    if ($price < 0) {
                        $price      = $plan->price;
                    }
                    $couponId       = $coupons->id;
                }
            }
            $data = Order::create([
                'plan_id'           => $plan->id,
                'user_id'           => $authUser->id,
                'amount'            => $price,
                'discount_amount'   => $discountValue,
                'coupon_code'       => $couponCode,
                'status'            => 0,
            ]);

            $resData['total_price'] = $price;
            $resData['plan_id']     = $plan->id;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
            return $resData;
        }
    }
    public function stripeSession(Request $request)
    {
        if (Auth::user()->type != 'Admin') {
            Stripe::setApiKey(UtilityFacades::getsettings('stripe_secret'));
            $currency       = UtilityFacades::getsettings('currency');
        } else {
            $currency       = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('currency');
            });
            $stripe_secret  = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('stripe_secret');
            });
            Stripe::setApiKey($stripe_secret);
        }
        if (!empty($request->createCheckoutSession)) {
            if (Auth::user()->type == 'Admin') {
                $planDetails   = tenancy()->central(function ($tenant) use ($request) {
                    return Plan::find($request->plan_id);
                });
            } else {
                $planDetails   =  Plan::find($request->plan_id);
            }
            try {
                $checkout_session = \Stripe\Checkout\Session::create([
                    'payment_method_types'  => ['card'],
                    'line_items'            => [[
                        'price_data'    => [
                            'currency'      => $currency,
                            'product_data'  => [
                                'name'      => $planDetails->name,
                                'metadata'  => [
                                    'plan_id'           => $request->plan_id,
                                    'domainrequest_id'  => $request->domainrequest_id
                                ]
                            ],
                            'unit_amount'   => $request->amount * 100,
                        ],
                        'quantity'      => 1,
                    ]],
                    'mode'          => 'payment',
                    'success_url'   => route('stripe.success.pay', Crypt::encrypt([
                        'coupon'    => $request->coupon,
                        'plan_id'   => $planDetails->id,
                        'price'     => $request->amount,
                        'user_id'   => Auth::user()->id,
                        'order_id'  => $request->order_id,
                        'type'      => 'stripe'
                    ])),
                    'cancel_url'    => route('stripe.cancel.pay', Crypt::encrypt([
                        'coupon'    => $request->coupon,
                        'plan_id'   => $planDetails->id,
                        'price'     => $request->amount,
                        'user_id'   => Auth::user()->id,
                        'order_id'  => $request->order_id,
                        'type'      => 'stripe'
                    ])),
                ]);
            } catch (Exception $e) {
                $api_error  = $e->getMessage();
            }
            if (empty($api_error) && $checkout_session) {
                $response   = [
                    'status'    => 1,
                    'message'   => 'Checkout session created successfully.',
                    'sessionId' => $checkout_session->id
                ];
            } else {
                $response = [
                    'status'    => 0,
                    'error'     => [
                        'message' => 'Checkout session creation failed. ' . $api_error
                    ]
                ];
            }
        }
        return response()->json($response);
    }

    function paymentPending(Request $request)
    {
        if (Auth::user()->type == 'Admin') {
            $user   = User::find(Auth::user()->id);
            $order  = tenancy()->central(function ($tenant) use ($request, $user) {
                $data['plan_details']   = Plan::find($request->plan_id);
                $user                   = User::where('email', $user->email)->first();
                $data['order']  = Order::create([
                    'plan_id'   => $request->plan_id,
                    'user_id'   => $user->id,
                    'amount'    => $data['plan_details']->price,
                    'status'    => 0,
                ]);
                return $data;
            });
            $response = array(
                'status'            => 0,
                'order_id'          => $order['order']->id,
                'amount'            => $order['order']->amount,
                'plan_name'         => $order['plan_details']->name,
                'currency'          => $request->currency,
                'currency_symbol'   => $request->currency_symbol,
            );
            echo json_encode($response);
            die;
        } else {
            $user = User::find(Auth::user()->id); {
                $planDetails    = Plan::find($request->plan_id);
                $user           = User::where('email', $user->email)->first();
                $data           = Order::create([
                    'plan_id'   => $request->plan_id,
                    'user_id'   => Auth::user()->id,
                    'amount'    => $planDetails->price,
                    'status'    => 0,
                ]);
            }
            $response = array(
                'status'            => 0,
                'order_id'          => $data->id,
                'amount'            => $planDetails->price,
                'plan_name'         => $planDetails->name,
                'currency'          => $request->currency,
                'currency_symbol'   => $request->currency_symbol,
            );
            echo json_encode($response);
            die;
        }
    }

    function paymentCancel($data)
    {
        $data = Crypt::decrypt($data);
        if (Auth::user()->type == 'Admin') {
            $order  = tenancy()->central(function ($tenant) use ($data) {
                $datas                  = Order::find($data['order_id']);
                $datas->status          = 2;
                $datas->payment_type    = 'stripe';
                $datas->update();
            });
        } else {
            $datas                  = Order::find($data['order_id']);
            $datas->status          = 2;
            $datas->payment_type    = 'stripe';
            $datas->update();
        }
        return redirect()->route('plans.index')->with('errors', __('Payment canceled.'));
    }

    function paymentSuccess($data)
    {
        $data   = Crypt::decrypt($data);
        if (Auth::user()->type == 'Admin') {
            $order = tenancy()->central(function ($tenant) use ($data) {
                $datas                  = Order::find($data['order_id']);
                $datas->status          = 1;
                $datas->payment_type    = 'stripe';
                $datas->update();
                $coupons    = Coupon::find($data['coupon']);
                $user       = User::find($tenant->id);
                if (!empty($coupons)) {
                    $userCoupon         = new UserCoupon();
                    $userCoupon->user   = $user->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order  = $datas->id;
                    $userCoupon->save();
                    $usedCoupun         = $coupons->used_coupon();
                    if ($coupons->limit <= $usedCoupun) {
                        $coupons->is_active = 0;
                        $coupons->save();
                    }
                }
                $plan           = Plan::find($data['plan_id']);
                $user->plan_id  = $plan->id;
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
            $datas                  = Order::find($data['order_id']);
            $datas->status          = 1;
            $datas->payment_type    = 'stripe';
            $datas->update();
            $user       = User::find(Auth::user()->id);
            $coupons    = Coupon::find($data['coupon']);
            if (!empty($coupons)) {
                $userCoupon         = new UserCoupon();
                $userCoupon->user   = $user->id;
                $userCoupon->coupon = $coupons->id;
                $userCoupon->order  = $datas->id;
                $userCoupon->save();
                $usedCoupun         = $coupons->used_coupon();
                if ($coupons->limit <= $usedCoupun) {
                    $coupons->is_active = 0;
                    $coupons->save();
                }
            }
            $plan           = Plan::find($data['plan_id']);
            $user->plan_id  = $plan->id;
            if ($plan->durationtype == 'Month' && $plan->id != '1') {
                $user->plan_expired_date = Carbon::now()->addMonths($plan->duration)->isoFormat('YYYY-MM-DD');
            } elseif ($plan->durationtype == 'Year' && $plan->id != '1') {
                $user->plan_expired_date = Carbon::now()->addYears($plan->duration)->isoFormat('YYYY-MM-DD');
            } else {
                $user->plan_expired_date = null;
            }
            $user->save();
        }
        return redirect()->route('plans.index')->with('status', __('Payment successfully!'));
    }
}
