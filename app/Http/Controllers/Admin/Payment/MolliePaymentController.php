<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Facades\UtilityFacades;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;
use App\Models\Order;
use App\Models\UserCoupon;
use App\Models\User;
use Carbon\Carbon;

class MolliePaymentController extends Controller
{
    public function planPayWithMollie(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if (Auth::user()->type == 'Admin') {
            $mollieApiKey   = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('mollie_api_key');
            });
            $currency       = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('currency');
            });
            $plan           = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $resData    =  tenancy()->central(function ($tenant) use ($plan, $request, $authUser) {
                $orderID        = time();
                $couponId       = '0';
                $price          = $plan->price;
                $couponCode     = null;
                $discountValue  = null;
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
                    'payment_id'        => $orderID,
                ]);
                $resData['total_price'] = $price;
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                $resData['plan_id']     = $plan->id;
                $resData['payment_id']  = $orderID;
                return $resData;
            });
        } else {
            $mollieApiKey   = UtilityFacades::getsettings('mollie_api_key');
            $currency       = UtilityFacades::getsettings('currency');
            $plan           = Plan::find($planID);
            $orderID        = time();
            $couponId       = '0';
            $price          = $plan->price;
            $couponCode     = null;
            $discountValue  = null;
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
                'payment_id'        => $orderID,
            ]);
            $resData['total_price'] = $price;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
            $resData['plan_id']     = $plan->id;
            $resData['payment_id']  = $orderID;
        }
        try {
            $mollie  = new \Mollie\Api\MollieApiClient();
            $mollie->setApiKey($mollieApiKey);
            $payment = $mollie->payments->create(
                [
                    "amount"    => [
                        "currency"  => $currency,
                        "value"     => number_format((float)$resData['total_price'], 2, '.', ''),
                    ],
                    "description" => "payment for product",
                    "redirectUrl" => route('plan.mollie', ['plan_id' => $resData['plan_id'], 'order_id' => $resData['order_id'], 'payment_id' => $resData['payment_id'], 'payment_frequency=' . $request->mollie_payment_frequency, 'coupon_id=' . $resData['coupon'], 'status' => 'successfull']),
                ]
            );
            session()->put('mollie_payment_id', $payment->id);
            return redirect($payment->getCheckoutUrl())->with('payment_id', $payment->id);
        } catch (\Exception $e) {
            return redirect()->route('plans.index')->with('errors', __($e->getMessage()));
        }
    }

    public function getPaymentStatus(Request $request)
    {
        if (Auth::user()->type == 'Admin') {
            $order      = tenancy()->central(function ($tenant) use ($request) {
                $datas  = Order::find($request->order_id);
                $datas->status          = 1;
                $datas->payment_type    = 'mollie';
                $datas->update();
                $coupons    = Coupon::find($request->coupon_id);
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
                $plan           = Plan::find($request->plan_id);
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
            $datas                  = Order::find($request->order_id);
            $datas->status          = 1;
            $datas->payment_type    = 'mollie';
            $datas->update();
            $coupons    = Coupon::find($request->coupon_id);
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
            $plan           = Plan::find($request->plan_id);
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
}
