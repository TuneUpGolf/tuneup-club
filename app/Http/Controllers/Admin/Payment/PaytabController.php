<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Facades\UtilityFacades;
use Paytabscom\Laravel_paytabs\Facades\paypage;
use App\Models\Plan;
use App\Models\UserCoupon;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PaytabController extends Controller
{
    public function planPayWithPaytab(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = \Auth::user();
        if (Auth::user()->type == 'Admin') {
            $plan       = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $resData    =  tenancy()->central(function ($tenant) use ($plan, $request, $authUser) {
                config([
                    'paytabs.profile_id' => UtilityFacades::getsettings('paytab_profile_id'),
                    'paytabs.server_key' => UtilityFacades::getsettings('paytab_server_key'),
                    'paytabs.region' =>  UtilityFacades::getsettings('paytab_region'),
                    'paytabs.currency' => UtilityFacades::getsettings('currency'), // 'INR'
                ]);
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
                ]);
                $resData['user_id']     = $authUser->id;
                $resData['total_price'] = $price;
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                return $resData;
            });
        } else {
            $plan   = Plan::find($planID);
            config([
                'paytabs.profile_id'    => UtilityFacades::getsettings('paytab_profile_id'),
                'paytabs.server_key'    => UtilityFacades::getsettings('paytab_server_key'),
                'paytabs.region'        =>  UtilityFacades::getsettings('paytab_region'),
                'paytabs.currency'      => UtilityFacades::getsettings('currency'),  // 'INR'
            ]);
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
            ]);
            $resData['user_id']     = $authUser->id;
            $resData['total_price'] = $price;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
        }
        $customerName   = isset($resData['user_id']) ? $resData['user_id'] : "";
        $pay    = paypage::sendPaymentCode('all')
            ->sendTransaction('sale')
            ->sendCart(1, $resData['total_price'], 'plan payment')
            ->sendCustomerDetails('', '', '', '', '', '', '', '', '')
            ->sendURLs(
                route('plan.paytab.success', ['success' => 1, 'data' => $resData, 'plan_id' => $plan->id, 'amount' => $resData['total_price'], 'coupon' => $resData['coupon']]),
                route('plan.paytab.success', ['success' => 0, 'data' => $resData, 'plan_id' => $plan->id, 'amount' => $resData['total_price'], 'coupon' => $resData['coupon']])
            )
            ->sendLanguage('en')
            ->sendFramed(false)
            ->create_pay_page();
        return $pay;
    }

    public function paytabGetPayment(Request $request)
    {
        if (Auth::user()->type == 'Admin') {
            $order  = tenancy()->central(function ($tenant) use ($request) {
                if ($request->respMessage == "Authorised") {
                    $datas                  = Order::find($request->data['order_id']);
                    $datas->status          = 1;
                    $datas->payment_id      = $request->payment_id;
                    $datas->payment_type    = 'paytab';
                    $datas->update();
                    $coupons    = Coupon::find($request->data['coupon']);
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
                    return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
                } else {
                    $order                  = Order::find($request->data['order_id']);
                    $order->status          = 2;
                    $order->payment_id      = $request->transaction_id;
                    $order->payment_type    = 'paytab';
                    $order->update();
                    return redirect()->back()->with('failed', __('Payment failed.'));
                }
            });
        } else {
            if ($request->respMessage == "Authorised") {
                $datas                  = Order::find($request->data['order_id']);
                $datas->status          = 1;
                $datas->payment_id      = $request->payment_id;
                $datas->payment_type    = 'paytab';
                $datas->update();
                $coupons    = Coupon::find($request->data['coupon']);
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
                $plan   = Plan::find($request->plan_id);
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
                $order                  = Order::find($request->data['order_id']);
                $order->status          = 2;
                $order->payment_id      = $request->transaction_id;
                $order->payment_type    = 'paytab';
                $order->update();
                return redirect()->back()->with('failed', __('Payment failed.'));
            }
        }
        return redirect()->route('plans.index');
    }
}
