<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Facades\UtilityFacades;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use GuzzleHttp\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BenefitPaymentController extends Controller
{
    public function initiatePayment(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if (Auth::user()->type == 'Admin') {
            $secretKey  = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('benefit_secret_key');
            });
            $plan       = tenancy()->central(function ($tenant) use ($planID) {
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
            $secretKey      = UtilityFacades::getsettings('benefit_secret_key');
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
            $data   = Order::create([
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
        $userData = [
            "amount"                => $resData['total_price'],
            "currency"              => UtilityFacades::getsettings('currency'),     // BHD
            "customer_initiated"    => true,
            "threeDSecure"          => true,
            "save_card"             => false,
            "description"           => " Plan - " . $plan->name,
            "metadata"              => ["udf1" => "Metadata 1"],
            "reference"             => ["transaction" => "txn_01", "order" => "ord_01"],
            "receipt"               => ["email" => true, "sms" => true],
            "customer"              => ["first_name" => $authUser->name, "middle_name" => "", "last_name" => "", "email" => $authUser->email, "phone" => ["country_code" => 965, "number" => 51234567]],
            "source"                => ["id" => "src_bh.benefit"],
            "post"                  => ["url" => "https://webhook.site/fd8b0712-d70a-4280-8d6f-9f14407b3bbd"],
            "redirect"              => ["url" => route('benefit.callback', ['order_id' => $resData['order_id'], 'plan_id' => $plan->id, 'amount' => $resData['total_price'], 'coupon' => $resData['coupon']])],
        ];
        $responseData   = json_encode($userData);
        $client         = new Client();
        try {
            $response   = $client->request('POST', 'https://api.tap.company/v2/charges', [
                'body'      => $responseData,
                'headers'   => [
                    'Authorization' => 'Bearer ' . $secretKey,
                    'accept'        => 'application/json',
                    'content-type'  => 'application/json',
                ],
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->with('errors', 'Currency Not Supported.Contact To Your Site Admin');
        }
        $data   = $response->getBody();
        $res    = json_decode($data);
        return redirect($res->transaction->url);
    }

    public function callBack(Request $request)
    {
        if (Auth::user()->type == 'Admin') {
            $secretKey  = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('benefit_secret_key');
            });
            $order      = tenancy()->central(function ($tenant) use ($request, $secretKey) {
                $orderID    = strtoupper(str_replace('.', '', uniqid('', true)));
                $post       = $request->all();
                $client     = new Client();
                $response   = $client->request('GET', 'https://api.tap.company/v2/charges/' . $post['tap_id'], [
                    'headers'   => [
                        'Authorization' => 'Bearer ' . $secretKey,
                        'accept'        => 'application/json',
                    ],
                ]);
                $json       = $response->getBody();
                $data       = json_decode($json);
                $statusCode = $data->gateway->response->code;
                if ($statusCode == '00') {
                    $datas                  = Order::find($request['order_id']);
                    $datas->payment_id      = $orderID;
                    $datas->status          = 1;
                    $datas->payment_type    = 'benefit';
                    $datas->update();
                    $coupons    = Coupon::find($request['coupon']);
                    $user       = User::find($tenant->id);
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
                    $plan           = Plan::find($request['plan_id']);
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
                    $order                  = Order::find($request['order_id']);
                    $order->status          = 2;
                    $order->payment_type    = 'benefit';
                    $order->update();
                    return redirect()->route('plans.index')->with('errors', __('Opps something went wrong.'));
                }
            });
            return redirect()->route('plans.index');
        } else {
            $secretKey  = UtilityFacades::getsettings('benefit_secret_key');
            $orderID    = strtoupper(str_replace('.', '', uniqid('', true)));
            $post       = $request->all();
            $client     = new Client();
            $response   = $client->request('GET', 'https://api.tap.company/v2/charges/' . $post['tap_id'], [
                'headers'   => [
                    'Authorization' => 'Bearer ' . $secretKey,
                    'accept'        => 'application/json',
                ],
            ]);
            $json = $response->getBody();
            $data = json_decode($json);
            $statusCode = $data->gateway->response->code;
            if ($statusCode == '00') {
                $datas                  = Order::find($request['order_id']);
                $datas->payment_id      = $orderID;
                $datas->status          = 1;
                $datas->payment_type    = 'benefit';
                $datas->update();
                $coupons    = Coupon::find($request['coupon']);
                $user       = User::find(Auth::user()->id);
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
                $plan           = Plan::find($request['plan_id']);
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
                $order                  = Order::find($request['order_id']);
                $order->status          = 2;
                $order->payment_type    = 'benefit';
                $order->update();
                return redirect()->route('plans.index')->with('errors', __('Opps something went wrong.'));
            }
            return redirect()->route('plans.index');
        }
    }
}
