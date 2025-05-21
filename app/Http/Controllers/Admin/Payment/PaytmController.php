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
use Paytm\JsCheckout\Facades\Paytm;

class PaytmController extends Controller
{
    public function pay(Request $request)
    {
        request()->validate([
            'mobile_number'     => 'required|numeric|digits:10',
        ]);
        $planID     = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $payment    = Paytm::with('receive');
        $authUser   = Auth::user();
        if ($authUser->type == 'Admin') {
            $plan       = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $resData    =  tenancy()->central(function ($tenant) use ($plan, $request, $authUser) {
                $couponId       = '0';
                $couponCode     = null;
                $discountValue  = null;
                $price          = $plan->price;
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
                $resData['user_id']     = $tenant->id;
                $resData['email']       = $authUser->email;
                $resData['total_price'] = $price;
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                return $resData;
            });
        } else {
            $plan           =  Plan::find($planID);
            $couponId       = '0';
            $couponCode     = null;
            $discountValue  = null;
            $price      = $plan->price;
            $coupons    = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();
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
                'user_id'           => $authUser->id,
                'amount'            => $price,
                'discount_amount'   => $discountValue,
                'coupon_code'       => $couponCode,
                'status'            => 0,
            ]);
            $resData['user_id']     = $authUser->id;
            $resData['email']       = $authUser->email;
            $resData['total_price'] = $price;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
        }
        $payment->prepare([
            'order'         => rand(),
            'user'          => $resData['user_id'],
            'mobile_number' => $request->mobile_number,
            'email'         => $resData['email'],
            'amount'        =>  $resData['total_price'], // amount will be paid in INR.
            'callback_url'  => route('paypaytm.callback', ['coupon' => $resData['coupon'], 'order_id' => $resData['order_id']]) // callback URL
        ]);
        $response   =  $payment->receive();  // initiate a new payment
        return $response;
    }

    public function paymentCallback(Request $request)
    {
        if (Auth::user()->type == 'Admin') {
            $order  = tenancy()->central(function ($tenant) use ($request) {
                $transaction    = Paytm::with('receive');
                $response       = $transaction->response();
                $orderId        = $request->order_id; // return a order id
                if ($transaction->isSuccessful()) {
                    $datas                  = Order::find($orderId);
                    $datas->status          = 1;
                    $datas->payment_id      = $transaction->getTransactionId();
                    $datas->payment_type    = 'paytm';
                    $datas->update();
                    $coupons    = Coupon::find($request->coupon);
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
                    $plan           = Plan::find($datas->plan_id);
                    $user->plan_id  = $plan->id;
                    if ($plan->durationtype == 'Month' && $plan->id != '1') {
                        $user->plan_expired_date = Carbon::now()->addMonths($plan->duration)->isoFormat('YYYY-MM-DD');
                    } elseif ($plan->durationtype == 'Year' && $plan->id != '1') {
                        $user->plan_expired_date = Carbon::now()->addYears($plan->duration)->isoFormat('YYYY-MM-DD');
                    } else {
                        $user->plan_expired_date = null;
                    }
                    $user->save();
                } else if ($transaction->isFailed()) {
                    $data               = Order::find($orderId);
                    $data->status       = 2;
                    $data->payment_id   = $transaction->getTransactionId();
                    $data->payment_type = 'paytm';
                    $data->update();
                    return redirect()->route('plans.index')->with('errors', __('Transaction failed.'));
                } else {
                    return redirect()->route('plans.index')->with('warning', __('Transaction in prossesing.'));
                }
            });
        } else {
            $transaction    = Paytm::with('receive');
            $response       = $transaction->response();
            $orderId        = $transaction->getOrderId();
            $orderId        = $request->order_id;
            if ($transaction->isSuccessful()) {
                $datas                  = Order::find($orderId);
                $datas->status          = 1;
                $datas->payment_id      = $transaction->getTransactionId();
                $datas->payment_type    = 'paytm';
                $datas->update();
                $user       = User::find(Auth::user()->id);
                $coupons    = Coupon::find($request->coupon);
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
                $plan           = Plan::find($datas->plan_id);
                $user->plan_id  = $plan->id;
                if ($plan->durationtype == 'Month' && $plan->id != '1') {
                    $user->plan_expired_date = Carbon::now()->addMonths($plan->duration)->isoFormat('YYYY-MM-DD');
                } elseif ($plan->durationtype == 'Year' && $plan->id != '1') {
                    $user->plan_expired_date = Carbon::now()->addYears($plan->duration)->isoFormat('YYYY-MM-DD');
                } else {
                    $user->plan_expired_date = null;
                }
                $user->save();
            } else if ($transaction->isFailed()) {
                $data               = Order::find($orderId);
                $data->status       = 2;
                $data->payment_id   = $transaction->getTransactionId();
                $data->payment_type = 'paytm';
                $data->update();
                return redirect()->route('plans.index')->with('errors', __('Transaction failed.'));
            } else {
                return redirect()->route('plans.index')->with('warning', __('Transaction in prossesing.'));
            }
        }
        return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
    }
}
