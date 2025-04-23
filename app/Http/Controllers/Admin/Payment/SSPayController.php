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

class SSPayController extends Controller
{
    public function initPayment(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = \Auth::user();
        if (Auth::user()->type == 'Admin') {
            $sspayCategoryCode  = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('sspay_category_code');
            });
            $sspaySecretKey     = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('sspay_secret_key');
            });
            $sspayDescription   = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('sspay_description');
            });
            $plan               = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });

            $resData            =  tenancy()->central(function ($tenant) use ($plan, $request, $authUser) {
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
                        $discount_type  = $coupons->discount_type;
                        $discountValue  = UtilityFacades::calculateDiscount($price, $discount, $discount_type);
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
            $sspayCategoryCode  = UtilityFacades::getsettings('sspay_category_code');
            $sspaySecretKey     = UtilityFacades::getsettings('sspay_secret_key');
            $sspayDescription   = UtilityFacades::getsettings('sspay_description');
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
                    $discount_type  = $coupons->discount_type;
                    $discountValue  = UtilityFacades::calculateDiscount($price, $discount, $discount_type);
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
            $someData = array(
                'userSecretKey'             => $sspaySecretKey,
                'categoryCode'              => $sspayCategoryCode,
                'billName'                  => $plan->name,
                'billDescription'           => $plan->description,
                'billPriceSetting'          => 1,
                'billPayorInfo'             => 1,
                'billAmount'                => round($resData['total_price'] * 100, 2),
                'billReturnUrl'             => route('sspay.payment.callback') . '?&order=' . $resData['order_id'] . '&coupon_id=' . $resData['coupon'] . '&plan_id=' . $planID . '&status=failed' . '',
                'billCallbackUrl'           => route('sspay.payment.callback') . '?&order=' . $resData['order_id'] . '&coupon_id=' . $resData['coupon'] . '&plan_id=' . $planID . '&status=successfull' . '',
                'billExternalReferenceNo'   => 'AFR341DFI',
                'billTo'                    => $authUser->name,
                'billEmail'                 => $authUser->email,
                'billPhone'                 => $authUser->phone,
                'billSplitPayment'          => 0,
                'billSplitPaymentArgs'      => '',
                'billPaymentChannel'        => '0',
                'billDisplayMerchant'       => 1,
                'billContentEmail'          => $sspayDescription,
                'billChargeToCustomer'      => 1
            );

            $curl       = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_URL, 'https://sspay.my' . '/index.php/api/createBill');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $someData);
            $result     = curl_exec($curl);
            $info       = curl_getinfo($curl);
            curl_close($curl);
            $obj        = json_decode($result);
            $url        = 'https://sspay.my' . '/index.php/api/runBill';
            $billCode   = $obj[0]->BillCode;

            $someData   = array(
                'userSecretKey'         =>  $sspaySecretKey,
                'billCode'              => $billCode,
                'billpaymentAmount'     => round($resData['total_price'] * 100, 2),
                'billpaymentPayorName'  => $authUser->name,
                'billpaymentPayorPhone' => $authUser->phone,
                'billpaymentPayorEmail' => $authUser->email,
                'billBankID'            => 'TEST0021'
            );

            $curl       = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $someData);
            $result     = curl_exec($curl);
            curl_getinfo($curl);
            curl_close($curl);
            $obj        = json_decode($result);

            return redirect()->to('https://sspay.my' . '/' . $billCode);
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        }
    }

    public function sspayCallback(Request $request)
    {
        if ($request->status_id == 1) {
            if (Auth::user()->type == 'Admin') {
                $order = tenancy()->central(function ($tenant) use ($request) {
                    $datas                  = Order::find($request['order']);
                    $datas->status          = 1;
                    $datas->payment_id      = $request['transaction_id'];
                    $datas->payment_type    = 'sspay';
                    $datas->update();
                    $user       = User::find(Auth::user()->id);
                    $coupons    = Coupon::find($datas['coupon_id']);
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
                $datas                  = Order::find($request['order']);
                $datas->status          = 1;
                $datas->payment_id      = $request['transaction_id'];
                $datas->payment_type    = 'sspay';
                $datas->update();
                $user       = User::find(Auth::user()->id);
                $coupons    = Coupon::find($datas['coupon_id']);
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
                return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
            }
        } else if ($request->status_id == 3) {
            if (Auth::user()->type == 'Admin') {
                $order = tenancy()->central(function ($tenant) use ($request) {
                    $order                  = Order::find($request['order']);
                    $order->status          = 2;
                    $order->payment_id      = $request['transaction_id'];
                    $order->payment_type    = 'sspay';
                    $order->save();
                });
                return redirect()->route('plans.index')->with('errors', __('Payment failed.'));
            } else {
                $order                  = Order::find($request['order']);
                $order->status          = 2;
                $order->payment_id      = $request['transaction_id'];
                $order->payment_type    = 'sspay';
                $order->save();
                return redirect()->route('plans.index')->with('errors', __('Payment failed.'));
            }
        } else {
            return redirect()->route('plans.index')->with('errors', __('Payment pending.'));
        }
    }
}
