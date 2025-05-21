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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalController extends Controller
{
    public function processTransaction(Request $request)
    {
        $currency   = UtilityFacades::getsettings('currency');
        if (Auth::user()->type == 'Admin') {
            $pro_detials = tenancy()->central(function ($tenant) use ($request) {
                return Plan::find($request->p_plan_id);
            });
        } else {
            $pro_detials =  Plan::find($request->p_plan_id);
        }
        $provider       = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken    = $provider->getAccessToken();
        $response       = $provider->createOrder([
            "intent"                => "CAPTURE",
            "application_context"   => [
                'return_url' => route('pay.success.transaction', Crypt::encrypt(['product_name' => $pro_detials->name, 'price' => $pro_detials->price, 'user_id' => $request->r_user_id, 'currency' => $pro_detials->currency, 'product_id' => $request->p_plan_id, 'order_id' => $request->p_order_id])),
                'cancel_url' => route('pay.cancel.transaction', Crypt::encrypt(['product_name' => $pro_detials->name, 'price' => $pro_detials->price, 'user_id' => $request->r_user_id, 'currency' => $pro_detials->currency, 'product_id' => $request->p_plan_id, 'order_id' => $request->p_order_id])),

            ],
            "purchase_units"        => [
                0 => [
                    "amount"    => [
                        "currency_code" => $currency,
                        "value"         => $pro_detials->price,
                    ]
                ]
            ]
        ]);
        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
            return redirect()->back()->with('failed',  __('Something went wrong.'));
        } else {
            return redirect()->back()->with('failed',  __('Something went wrong.'));
        }
    }

    public function processTransactionAdmin(Request $request)
    {
        $authuUser  = Auth::user();
        $planID     = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        if ($authuUser->type == 'Admin') {
            $currency   = tenancy()->central(function ($tenant) use ($planID) {
                return UtilityFacades::getsettings('currency');
            });
            $plan       = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $resData    =  tenancy()->central(function ($tenant) use ($plan, $request) {
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
                        $discountType   = $coupons->discount_type;
                        $discountValue  =  UtilityFacades::calculateDiscount($price, $discount, $discountType);
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
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                return $resData;
            });
        } else {
            $currency       = UtilityFacades::getsettings('currency');
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
                    $discountType   = $coupons->discount_type;
                    $discountValue  =  UtilityFacades::calculateDiscount($price, $discount, $discountType);
                    $price          = $price - $discountValue;
                    if ($price < 0) {
                        $price      = $plan->price;
                    }
                    $couponId       = $coupons->id;
                }
            }
            $data = Order::create([
                'plan_id'           => $plan->id,
                'user_id'           => $authuUser->id,
                'amount'            => $price,
                'discount_amount'   => $discountValue,
                'coupon_code'       => $couponCode,
                'status'            => 0,
            ]);
            $resData['total_price'] = $price;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
        }
        $provider       = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken    = $provider->getAccessToken();
        $response       = $provider->createOrder([
            "intent"                => "CAPTURE",
            "application_context"   => [
                'return_url' => route('pay.success.transaction', Crypt::encrypt(['coupon' => $resData['coupon'], 'product_name' => $plan->name, 'price' => $resData['total_price'], 'user_id' => $authuUser->id, 'currency' => $plan->currency, 'coupon' => $resData['coupon'], 'product_id' => $plan->id, 'order_id' => $resData['order_id']])),
                'cancel_url' => route('pay.cancel.transaction', Crypt::encrypt(['coupon' => $resData['coupon'], 'product_name' => $plan->name, 'price' => $resData['total_price'], 'user_id' => $authuUser->id, 'currency' => $plan->currency, 'coupon' => $resData['coupon'], 'product_id' => $plan->id, 'order_id' => $resData['order_id']])),

            ],
            "purchase_units"        => [
                0 => [
                    "amount"    => [
                        "currency_code" => $currency,
                        "value"         => $resData['total_price'],
                    ]
                ]
            ]
        ]);
        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
            return redirect()->back()->with('failed',  __('Something went wrong.'));
        } else {

            return redirect()->back()->with('failed',  __('Something went wrong.'));
        }
    }

    public function successTransaction($data, Request $request)
    {
        $data   = Crypt::decrypt($data);
        if (Auth::user()->type == 'Admin') {
            $order  = tenancy()->central(function ($tenant) use ($request, $data) {
                $datas                  = Order::find($data['order_id']);
                $datas->status          = 1;
                $datas->payment_id      = $request['PayerID'];
                $datas->payment_type    = 'paypal';
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
                $plan           = Plan::find($datas['plan_id']);
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
            $datas->payment_id      = $request['PayerID'];
            $datas->payment_type    = 'paypal';
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
            $plan           = Plan::find($datas['plan_id']);
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
        return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
    }

    public function cancelTransaction($data)
    {
        $data   = Crypt::decrypt($data);
        if (Auth::user()->type == 'Admin') {
            $order  = tenancy()->central(function ($tenant) use ($data) {
                $data               = Order::find($data['order_id']);
                $data->status       = 2;
                $data->payment_type = 'paypal';
                $data->update();
            });
        } else {
            $data               = Order::find($data['order_id']);
            $data->status       = 2;
            $data->payment_type = 'paypal';
            $data->update();
        }
        return redirect()->route('plans.index')->with('failed', __('Payment canceled.'));
    }
}
