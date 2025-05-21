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
use Easebuzz\Easebuzz;

class EasebuzzPaymentController extends Controller
{
    public function planPayWithEasebuzz(Request $request)
    {
        $planID    = Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if (Auth::user()->type == 'Admin') {
            $plan                   = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $easebuzzMerchantKey    = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('easebuzz_merchant_key');
            });
            $easebuzzSalt           = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('easebuzz_salt');
            });
            $resData    =  tenancy()->central(function ($tenant) use ($plan, $request) {
                $couponId       = '0';
                $couponCode     = null;
                $discountValue  = null;
                $price          = $plan->price;
                $coupons        = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();
                if ($coupons) {
                    $couponCode     = $coupons->code;
                    $usedCoupun     = $coupons->used_coupon();
                    if ($coupons->limit == $usedCoupun) {
                        $resData['error'] = __('This coupon code has expired.');
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
                    'txnid'             => uniqid(),
                    'plan_id'           => $plan->id,
                    'user_id'           => $tenant->id,
                    'amount'            => $price,
                    'discount_amount'   => $discountValue,
                    'coupon_code'       => $couponCode,
                    'status'            => 0,
                ]);
                $resData['total_price'] = $price;      //  number_format($resData['total_price'], 2, '.', '')
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                $resData['plan_id']     = $plan->id;
                $resData['txnid']       = uniqid();
                // return $resData;
                // success call
                $datas                  = Order::find($resData['order_id']);
                $datas->status          = 1;
                $datas->payment_id      = $resData['txnid'];
                $datas->payment_type    = 'easebuzz';
                $datas->update();
                $coupons    = Coupon::find($resData['coupon']);
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
            $plan                   =  Plan::find($planID);
            $easebuzzMerchantKey    = UtilityFacades::getsettings('easebuzz_merchant_key');
            $easebuzzSalt           = UtilityFacades::getsettings('easebuzz_salt');
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
                'txnid'             => uniqid(),
                'plan_id'           => $plan->id,
                'user_id'           => $authUser->id,
                'amount'            => $price,
                'discount_amount'   => $discountValue,
                'coupon_code'       => $couponCode,
                'status'            => 0,
            ]);
            $resData['total_price'] = $price;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
            $resData['plan_id']     = $plan->id;
            $resData['txnid']       = uniqid();
            // success call
            $datas                  = Order::find($resData['order_id']);
            $datas->status          = 1;
            $datas->payment_id      = $resData['txnid'];
            $datas->payment_type    = 'easebuzz';
            $datas->update();
            $coupons    = Coupon::find($resData['coupon']);
            $user       = User::find(Auth::user()->id);
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
            $plan   = Plan::find($data['plan_id']);
            $user->plan_id = $plan->id;
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
        $easebuzz       = new Easebuzz($easebuzzMerchantKey, $easebuzzSalt, 'test');
        $paymentData    = array(
            'txnid'         => $resData['txnid'],
            'order_id'      =>  $resData['order_id'],
            'amount'        => number_format($resData['total_price'], 2, '.', ''),
            'firstname'     => $authUser->name,
            'email'         => $authUser->email,
            "phone"         => $authUser->phone,
            "productinfo"   => "Laptop",
            'surl'          => route('plan.easebuzz.callback', Crypt::encrypt(['order_id' => $resData['order_id'], 'txnid' => $resData['txnid'], 'coupon_id' => $resData['coupon'], 'plan_id' => $planID, 'status' => 'successfull'])),
            'furl'          => route('plan.easebuzz.callback', Crypt::encrypt(['order_id' => $resData['order_id'], 'txnid' => $resData['txnid'], 'coupon_id' => $resData['coupon'], 'plan_id' => $planID, 'status' => 'failed'])),
        );
        $paymentPageUrl     = $easebuzz->initiatePaymentAPI($paymentData);
        return redirect($paymentPageUrl);
    }

    public function planWithEasebuzzCallback($data)
    {
        $data       = Crypt::decrypt($data);
        if (Auth::user()->type == 'Admin') {
            $order  = tenancy()->central(function ($tenant) use ($data) {
                if ($data['status'] == 'successfull') {
                    $datas                  = Order::find($data['order_id']);
                    $datas->status          = 1;
                    $datas->payment_id      = $data['txnid'];
                    $datas->payment_type    = 'easebuzz';
                    $datas->update();
                    $coupons    = Coupon::find($data['coupon_id']);
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
                    $plan   = Plan::find($data['plan_id']);
                    $user->plan_id = $plan->id;
                    if ($plan->durationtype == 'Month' && $plan->id != '1') {
                        $user->plan_expired_date = Carbon::now()->addMonths($plan->duration)->isoFormat('YYYY-MM-DD');
                    } elseif ($plan->durationtype == 'Year' && $plan->id != '1') {
                        $user->plan_expired_date = Carbon::now()->addYears($plan->duration)->isoFormat('YYYY-MM-DD');
                    } else {
                        $user->plan_expired_date = null;
                    }
                    $user->save();
                    return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
                } else {
                    $order                  = Order::find($data['order_id']);
                    $order->status          = 2;
                    $order->payment_type    = 'easebuzz';
                    $order->update();
                    return redirect()->route('plans.index')->with('errors', __('Opps something went wrong.'));
                }
            });
        } else {
            if ($data['status'] == 'successfull') {
                $datas                  = Order::find($data['order_id']);
                $datas->status          = 1;
                $datas->payment_id      = $data['txnid'];
                $datas->payment_type    = 'easebuzz';
                $datas->update();
                $coupons    = Coupon::find($data['coupon_id']);
                $user       = User::find(Auth::user()->id);
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
                return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
            } else {
                $order                  = Order::find($data['order_id']);
                $order->status          = 2;
                $order->payment_type    = 'easebuzz';
                $order->update();
                return redirect()->route('plans.index')->with('errors', __('Opps something went wrong.'));
            }
        }
        return redirect()->route('plans.index');
    }
}
