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
use CoinGate\CoinGate;
use Illuminate\Support\Facades\Crypt;

class CoingateController extends Controller
{
    public function coingatePrepare(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if (Auth::user()->type == 'Admin') {
            $coingateEnvironment    = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('coingate_environment');
            });
            $coingateAuthToken      = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('coingate_auth_token');
            });
            $currency               = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('currency');
            });
            $plan                   = tenancy()->central(function ($tenant) use ($planID) {
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
            $coingateEnvironment    = UtilityFacades::getsettings('coingate_environment');
            $coingateAuthToken      = UtilityFacades::getsettings('coingate_auth_token');
            $currency               = UtilityFacades::getsettings('currency');
            $plan           = Plan::find($planID);
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
                'user_id'           => $authUser->id,
                'amount'            => $price,
                'discount_amount'   => $discountValue,
                'coupon_code'       => $couponCode,
                'status'            => 0,
            ]);
            $resData['total_price'] = $price;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
        }
        CoinGate::config(
            array(
                'environment'               => $coingateEnvironment,
                'auth_token'                => $coingateAuthToken,
                'curlopt_ssl_verifypeer'    => FALSE
            )
        );
        $params = array(
            'order_id'          => rand(),
            'price_amount'      => $resData['total_price'],
            'price_currency'    => $currency,
            'receive_currency'  => $currency,
            'callback_url'      => route('coingate.payment.callback', Crypt::encrypt(['order_id' => $resData['order_id'], 'coupon_id' => $resData['coupon'], 'plan_id' => $planID])),
            'cancel_url'        => route('coingate.payment.callback', Crypt::encrypt(['order_id' => $resData['order_id'], 'coupon_id' => $resData['coupon'], 'plan_id' => $planID, 'status' => 'failed'])),
            'success_url'       => route('coingate.payment.callback', Crypt::encrypt(['order_id' => $resData['order_id'], 'coupon_id' => $resData['coupon'], 'plan_id' => $planID, 'status' => 'successfull'])),
        );
        $order  = \CoinGate\Merchant\Order::create($params);
        if ($order) {
            if (Auth::user()->type == 'Admin') {
                $central_order = tenancy()->central(function ($tenant) use ($order, $resData) {
                    $paymentId              = Order::find($resData['order_id']);
                    $paymentId->payment_id  = $order->id;
                    $paymentId->update();
                });
            } else {
                $paymentId              = Order::find($resData['order_id']);
                $paymentId->payment_id  = $order->id;
                $paymentId->update();
            }
            return redirect($order->payment_url);
        } else {
            return redirect()->back()->with('errors', __('Opps something went wrong.'));
        }
    }

    public function coingateCallback($data)
    {
        $data       = Crypt::decrypt($data);
        if (Auth::user()->type == 'Admin') {
            $order  = tenancy()->central(function ($tenant) use ($data) {
                if ($data['status'] == 'successfull') {
                    $datas                  = Order::find($data['order_id']);
                    $datas->status          = 1;
                    $datas->payment_type    = 'coingate';
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
                } else {
                    $order                  = Order::find($data['order_id']);
                    $order->status          = 2;
                    $order->payment_type    = 'coingate';
                    $order->update();
                    return redirect()->route('plans.index')->with('errors', __('Opps something went wrong.'));
                }
            });
        } else {
            if ($data['status'] == 'successfull') {
                $datas                  = Order::find($data['order_id']);
                $datas->status          = 1;
                $datas->payment_type    = 'coingate';
                $datas->update();
                $user                   = User::find(Auth::user()->id);
                $coupons                = Coupon::find($data['coupon_id']);
                if (!empty($coupons)) {
                    $userCoupon         = new UserCoupon();
                    $userCoupon->user   = $user->id;
                    $userCoupon->coupon = $coupons->id;
                    $userCoupon->order  = $datas->id;
                    $userCoupon->save();
                    $usedCoupun     = $coupons->used_coupon();
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
                $order->payment_type    = 'coingate';
                $order->update();
                return redirect()->route('plans.index')->with('errors', __('Opps something went wrong.'));
            }
        }
        return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
    }
}
