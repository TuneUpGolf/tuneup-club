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

class CashFreeController extends Controller
{
    public function cashfreePayment(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if (Auth::user()->type == 'Admin') {
            $cashfreeAppId      = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('cashfree_app_id');
            });
            $cashfreeSecretKey  = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('cashfree_secret_key');
            });
            $cashfreeMode       = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('cashfree_mode');
            });
            $plan               = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });

            $resData    =  tenancy()->central(function ($tenant) use ($plan, $request, $authUser) {
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

                $resData['total_price'] = $price;
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                return $resData;
            });
        } else {
            $cashfreeAppId      = UtilityFacades::getsettings('cashfree_app_id');
            $cashfreeSecretKey  = UtilityFacades::getsettings('cashfree_secret_key');
            $cashfreeMode       = UtilityFacades::getsettings('cashfree_mode');
            $plan               = Plan::find($planID);

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
            $resData['total_price'] = $price;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
        }
        try {
            $url        = ($cashfreeMode == 'sandbox') ? 'https://sandbox.cashfree.com/pg/orders' : 'https://sandbox.cashfree.com/pg/orders';
            $headers    = array(
                "Content-Type: application/json",
                "x-api-version: 2022-01-01",
                "x-client-id: " . $cashfreeAppId,
                "x-client-secret: " . $cashfreeSecretKey
            );
            $data   = json_encode([
                'order_id'          =>  'order_' . rand(1111111111, 9999999999),
                'order_amount'      => $resData['total_price'],
                "order_currency"    => 'INR',
                "order_name"        => $plan->name,
                "customer_details"  => [
                    "customer_id"       => 'customer_' . $authUser->id,
                    "customer_name"     => $authUser->name,
                    "customer_email"    => $authUser->email,
                    "customer_phone"    => '+' . $authUser->dial_code . $authUser->phone,
                ],
                "order_meta" => [
                    "return_url"    => route('cashfree.payment.callback') . '?order_id={order_id}&order_token={order_token}&order=' . Crypt::encrypt($resData['order_id']) . '?&order_ids=' . $resData['order_id'] . '&coupon_id=' . $resData['coupon'] . '&plan_id=' . $planID . '',
                ]
            ]);
            $curl   = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $resp   = curl_exec($curl);
            curl_close($curl);
            return redirect()->to(json_decode($resp)->payment_link);
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        }
    }

    public function cashfreeCallback(Request $request)
    {
        if (Auth::user()->type == 'Admin') {
            $authUser           = Auth::user();
            $cashfreeAppId      = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('cashfree_app_id');
            });
            $cashfreeSecretKey  = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('cashfree_secret_key');
            });
            $cashfreeMode       = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('cashfree_mode');
            });
            $cashfree_url       = tenancy()->central(function ($tenant) use ($cashfreeMode) {
                return ($cashfreeMode == 'sandbox') ? 'https://sandbox.cashfree.com/pg/orders' : 'https://sandbox.cashfree.com/pg/orders';
            });

            $client     = new \GuzzleHttp\Client();
            $response   = $client->request('GET', $cashfree_url . '/' . $request->get('order_id') . '/settlements', [
                'headers'   => [
                    'accept'            => 'application/json',
                    'x-api-version'     => '2022-09-01',
                    "x-client-id"       => $cashfreeAppId,
                    "x-client-secret"   => $cashfreeSecretKey
                ],
            ]);
            $respons        = json_decode($response->getBody());
            if ($respons->order_id && $respons->cf_payment_id != NULL) {
                $response   = $client->request('GET', $cashfree_url . '/' . $respons->order_id . '/payments/' . $respons->cf_payment_id . '', [
                    'headers'   => [
                        'accept'            => 'application/json',
                        'x-api-version'     => '2022-09-01',
                        'x-client-id'       => $cashfreeAppId,
                        'x-client-secret'   => $cashfreeSecretKey,
                    ],
                ]);
                $info       = json_decode($response->getBody());
                if ($info->payment_status == "SUCCESS") {
                    $order  = tenancy()->central(function ($tenant) use ($request, $info, $authUser) {
                        $datas                  = Order::find($request['order_ids']);
                        $datas->status          = 1;
                        $datas->payment_id      = $info->cf_payment_id;
                        $datas->payment_type    = 'cashfree';
                        $datas->update();
                        $coupons    = Coupon::where('code', $datas->coupon_code)->where('is_active', '1')->first();
                        $user       = User::find($authUser->id);
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
                    return redirect()->route('plans.index')->with('success', __('Payment successfully.'));
                } else {
                    $order  = tenancy()->central(function ($tenant) use ($request, $info) {
                        $order                  = Order::find($request['order_ids']);
                        $order->status          = 2;
                        $order->payment_id      = $info->cf_payment_id;
                        $order->payment_type    = 'cashfree';
                        $order->save();
                    });
                    return redirect()->route('plans.index')->with('failed', __('Opps something wents wrong.'));
                }
            } else {
                $order  = tenancy()->central(function ($tenant) use ($request) {
                    $order                  = Order::find($request['order_ids']);
                    $order->status          = 2;
                    $order->payment_type    = 'cashfree';
                    $order->save();
                });
                return redirect()->route('plans.index')->with('errors', __('Payment Failed.'));
            }
            return redirect()->route('plans.index')->with('success', __('Payment successfully.'));
        } else {
            $authUser           = Auth::user();
            $cashfreeAppId      = UtilityFacades::getsettings('cashfree_app_id');
            $cashfreeSecretKey  = UtilityFacades::getsettings('cashfree_secret_key');
            $cashfreeMode       = UtilityFacades::getsettings('cashfree_mode');
            $cashfree_url       = ($cashfreeMode == 'sandbox') ? 'https://sandbox.cashfree.com/pg/orders' : 'https://sandbox.cashfree.com/pg/orders';

            $client     = new \GuzzleHttp\Client();
            $response   = $client->request('GET', $cashfree_url . '/' . $request->get('order_id') . '/settlements', [
                'headers'   => [
                    'accept'            => 'application/json',
                    'x-api-version'     => '2022-09-01',
                    "x-client-id"       => $cashfreeAppId,
                    "x-client-secret"   => $cashfreeSecretKey
                ],
            ]);
            $respons        = json_decode($response->getBody());
            if ($respons->order_id && $respons->cf_payment_id != NULL) {
                $response   = $client->request('GET', $cashfree_url . '/' . $respons->order_id . '/payments/' . $respons->cf_payment_id . '', [
                    'headers'   => [
                        'accept'            => 'application/json',
                        'x-api-version'     => '2022-09-01',
                        'x-client-id'       => $cashfreeAppId,
                        'x-client-secret'   => $cashfreeSecretKey,
                    ],
                ]);
                $info   = json_decode($response->getBody());
                if ($info->payment_status == "SUCCESS") {
                    $datas                  = Order::find($request->order_ids);
                    $datas->status          = 1;
                    $datas->payment_id      = $info->cf_payment_id;
                    $datas->payment_type    = 'cashfree';
                    $datas->update();
                    $coupons    = Coupon::where('code', $datas->coupon_code)->where('is_active', '1')->first();
                    $user       = User::find($authUser->id);
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
                    return redirect()->route('plans.index')->with('success', __('Payment successfully.'));
                } else {
                    $order                  = Order::find($request->order_ids);
                    $order->status          = 2;
                    $order->payment_id      = $info->cf_payment_id;
                    $order->payment_type    = 'cashfree';
                    $order->save();
                    return redirect()->route('plans.index')->with('failed', __('Opps something wents wrong.'));
                }
            } else {
                $order                  = Order::find($request->order_ids);
                $order->status          = 2;
                $order->payment_type    = 'cashfree';
                $order->save();
                return redirect()->route('plans.index')->with('errors', __('Payment Failed.'));
            }
            return redirect()->route('plans.index')->with('success', __('Payment successfully.'));
        }
    }
}
