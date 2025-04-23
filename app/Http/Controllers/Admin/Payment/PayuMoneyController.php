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

class PayuMoneyController extends Controller
{
    public function PayUmoneyPayment(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if (Auth::user()->type == 'Admin') {
            $payumoneyMode          = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('payumoney_mode');
            });
            $payumoneyMerchantKey   = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('payumoney_merchant_key');
            });
            $payumoneySaltKey       = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('payumoney_salt_key');
            });
            $currency               = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('currency');
            });
            $plan                   = tenancy()->central(function ($tenant) use ($planID) {
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
                        $resData['errors'] = 'This coupon code has expired.';
                    } else {
                        $discount       = $coupons->discount;
                        $discountType   = $coupons->discount_type;
                        $discountValue  = UtilityFacades::calculateDiscount($price, $discount, $discountType);
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
                $resData['coupon']      = $couponId;
                $resData['plan_id']     = $plan->id;
                $resData['order_id']    = $data->id;
                return $resData;
            });
        } else {
            $payumoneyMode          = UtilityFacades::getsettings('payumoney_mode');
            $payumoneyMerchantKey   = UtilityFacades::getsettings('payumoney_merchant_key');
            $payumoneySaltKey       = UtilityFacades::getsettings('payumoney_salt_key');
            $currency               = UtilityFacades::getsettings('currency');
            $plan                   = Plan::find($planID);
            $couponId               = '0';
            $couponCode             = null;
            $discountValue          = null;
            $price                  = $plan->price;
            $coupons                = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();
            if ($coupons) {
                $couponCode     = $coupons->code;
                $usedCoupun     = $coupons->used_coupon();
                if ($coupons->limit == $usedCoupun) {
                    $resData['errors'] = 'This coupon code has expired.';
                } else {
                    $discount       = $coupons->discount;
                    $discountType   = $coupons->discount_type;
                    $discountValue  = UtilityFacades::calculateDiscount($price, $discount, $discountType);
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
            $resData['coupon']      = $couponId;
            $resData['plan_id']     = $plan->id;
            $resData['order_id']    = $data->id;
        }
        $txnId          = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
        $amount         = $resData['total_price'];
        $hashString     = $payumoneyMerchantKey . '|' . $txnId . '|' . $amount . '|' . $plan->name . '|' . $authUser->name . '|' . $authUser->email . '|' . '||||||||||' . $payumoneySaltKey;
        $hash           = strtolower(hash('sha512', $hashString));
        $payuUrl        = 'https://test.payu.in/_payment';  // For production environment, change it to 'https://secure.payumoney.com/_payment'
        $paymentData    = [
            'key'           => $payumoneyMerchantKey,
            'txnid'         => $txnId,
            'amount'        => $resData['total_price'],
            'productinfo'   => $plan->name,
            'firstname'     => $authUser->name,
            'email'         => $authUser->email,
            'coupon'        => $resData['coupon'],
            'hash'          => $hash,
            'surl'          => route('payu.success', Crypt::encrypt(['key' => $payumoneyMerchantKey, 'productinfo' => $plan->name, 'firstname' => $authUser->name, 'email' => $authUser->email,  'txnid' => $txnId,  'order_id' => $resData['order_id'], 'user_id' => $authUser->id, 'coupon' => $resData['coupon'], 'plan_id' => $plan->id, 'currency' => $currency, 'payment_type' => 'payumoney', 'status' => 'successfull'])),
            'furl'          => route('payu.failure', Crypt::encrypt(['key' => $payumoneyMerchantKey, 'productinfo' => $plan->name, 'firstname' => $authUser->name, 'email' => $authUser->email,  'txnid' => $txnId, 'order_id' => $resData['order_id'], 'user_id' => $authUser->id, 'coupon' => $resData['coupon'], 'plan_id' => $plan->id, 'currency' => $currency, 'payment_type' => 'payumoney', 'status' => 'failed'])),
        ];
        return view('superadmin.request-domain.payumoney-redirect', compact('payuUrl', 'paymentData'));
    }

    public function payuSuccess($data)
    {
        $data   = Crypt::decrypt($data);
        if (\Auth::user()->type == 'Admin') {
            $order = tenancy()->central(function ($tenant) use ($data) {
                $datas                  = Order::find($data['order_id']);
                $datas->status          = 1;
                $datas->payment_id      = $data['txnid'];
                $datas->payment_type    = 'payumoney';
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
            $datas->payment_id      = $data['txnid'];
            $datas->payment_type    = 'payumoney';
            $datas->update();
            $user       = User::find(Auth::user()->id);
            $coupons    = Coupon::find($data['coupon']);
            if (!empty($coupons)) {
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
        return redirect()->route('plans.index')->with('success', __('Payment successfully.'));
    }

    public function payuFailure(Request $request)
    {
        $order                  = Order::find($request['order_id']);
        $order->status          = 2;
        $order->payment_type    = 'payumoney';
        $order->update();
        return redirect()->route('plans.index')->with('errors', __('Payment Failed.'));
    }
}
