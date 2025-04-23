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

class MercadoController extends Controller
{
    public function mercadoPrepare(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = \Auth::user();
        if (Auth::user()->type == 'Admin') {
            $mercadoMode        = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('mercado_mode');
            });
            $mercadoAccessToken = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('mercado_access_token');
            });
            $plan               = tenancy()->central(function ($tenant) use ($planID) {
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
                $resData['total_price'] = $price;
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                return $resData;
            });
        } else {
            $mercadoMode        = UtilityFacades::getsettings('mercado_mode');
            $mercadoAccessToken = UtilityFacades::getsettings('mercado_access_token');
            $plan               = Plan::find($planID);
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
        \MercadoPago\SDK::setAccessToken($mercadoAccessToken);
        try {
            $preference         = new \MercadoPago\Preference();
            // Create an item in the preference
            $item               = new \MercadoPago\Item();
            $item->title        = "Plan : " . $plan->name;
            $item->quantity     = 1;
            $item->unit_price   = $resData['total_price'];
            $preference->items  = array($item);
            $successUrl         = route('mercado.payment.callback', [Crypt::encrypt(['order_id' => $resData['order_id'], 'coupon' => $resData['coupon'], 'flag' => 'success'])]);
            $failureUrl         = route('mercado.payment.callback', [Crypt::encrypt(['order_id' => $resData['order_id'], 'coupon' => $resData['coupon'], 'flag' => 'failure'])]);
            $pendingUrl         = route('mercado.payment.callback', [Crypt::encrypt(['order_id' => $resData['order_id'], 'coupon' => $resData['coupon'], 'flag' => 'pending'])]);

            $preference->back_urls = array(
                "success" => $successUrl,
                "failure" => $failureUrl,
                "pending" => $pendingUrl,
            );
            $preference->auto_return = "approved";
            $preference->save();
            if ($mercadoMode == 'live') {
                $redirectUrl = $preference->init_point;
                return redirect($redirectUrl);
            } else {
                $redirectUrl = $preference->sandbox_init_point;
                return redirect($redirectUrl);
            }
        } catch (Exception $e) {
            return redirect()->back()->with('failed', __('Something went wrong.'));
        }
    }

    public function mercadoCallback(Request $request, $data)
    {
        $data   = Crypt::decrypt($data);
        if ($data['flag'] == 'success') {
            if (Auth::user()->type == 'Admin') {
                $order  = tenancy()->central(function ($tenant) use ($data, $request) {
                    $datas                  = Order::find($data['order_id']);
                    $datas->status          = 1;
                    $datas->payment_id      = $request->payment_id;
                    $datas->payment_type    = 'mercadopago';
                    $datas->update();
                    $user       = User::find($tenant->id);
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
                    return redirect()->route('plans.index')->with('status', 'Payment successfull.');
                });
            } else {
                $datas                  = Order::find($data['order_id']);
                $datas->status          = 1;
                $datas->payment_id      = $request->payment_id;
                $datas->payment_type    = 'mercadopago';
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
                return redirect()->route('plans.index')->with('status', 'Payment successfull.');
            }
        } else {
            if (Auth::user()->type == 'Admin') {
                $central    = tenancy()->central(function ($tenant) use ($data) {
                    $order                  = Order::find($data['order_id']);
                    $order->status          = 2;
                    $order->payment_type    = 'mercadopago';
                    $order->update();
                    return redirect()->route('plans.index')->with('errors', __('Payment failed.'));
                });
            } else {
                $order                  = Order::find($data['order_id']);
                $order->status          = 2;
                $order->payment_type    = 'mercadopago';
                $order->update();
                return redirect()->route('plans.index')->with('errors', __('Payment failed.'));
            }
        }
        return redirect()->route('plans.index')->with('status', 'Payment successfull.');
    }
}
