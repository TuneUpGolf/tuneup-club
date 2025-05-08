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

class ToyyibpayController extends Controller
{
    public function charge(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authuser  = Auth::user();
        if ($authuser->type == 'Admin') {
            $secretKey      = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('toyyibpay_secret_key');
            });
            $categoryCode   = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('toyyibpay_category_code');
            });
            $description    = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('toyyibpay_description');
            });
            $plan           = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $resData        =  tenancy()->central(function ($tenant) use ($plan, $request) {
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
                $resData['plan_id']     = $plan->id;
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                return $resData;
            });
        } else {
            $secretKey      = UtilityFacades::getsettings('toyyibpay_secret_key');
            $categoryCode   = UtilityFacades::getsettings('toyyibpay_category_code');
            $description    = UtilityFacades::getsettings('toyyibpay_description');
            $plan           =  Plan::find($planID);
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
                'user_id'           => $authuser->id,
                'amount'            => $price,
                'discount_amount'   => $discountValue,
                'coupon_code'       => $couponCode,
                'status'            => 0,
            ]);

            $resData['total_price'] = $price;
            $resData['plan_id']     = $plan->id;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
        }

        try {
            $amount             = $resData['total_price'];
            $billName           = $plan->name;
            $billExpiryDate     = Carbon::now()->addDays(3);
            $billContentEmail   = $plan->description;
            $billReturnUrl      = route('toyyibpay.payment.callback',  [$plan->id, $resData['order_id'], $resData['coupon']]);
            $billCallbackUrl    = route('toyyibpay.payment.callback', [$plan->id, $resData['order_id'], $resData['coupon']]);
            $someData = array(
                'userSecretKey'             => $secretKey,
                'categoryCode'              => $categoryCode,
                'billName'                  => $billName,
                'billDescription'           => $description,
                'billPriceSetting'          => 1,
                'billPayorInfo'             => 1,
                'billAmount'                => 100 * $amount,
                'billReturnUrl'             => $billReturnUrl,
                'billCallbackUrl'           => $billCallbackUrl,
                'billExternalReferenceNo'   => 'AFR341DFI',
                'billTo'                    => $authuser->name,
                'billEmail'                 => $authuser->email,
                'billPhone'                 => '0194342411',
                'billSplitPayment'          => 0,
                'billSplitPaymentArgs'      => '',
                'billPaymentChannel'        => '0',
                'billContentEmail'          => $billContentEmail,
                'billChargeToCustomer'      => 1,
                'billExpiryDate'            => $billExpiryDate,
                'billExpiryDays'            => 3
            );
            $curl       = curl_init();
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_URL, 'https://toyyibpay.com/index.php/api/createBill');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $someData);
            $result     = curl_exec($curl);
            $info       = curl_getinfo($curl);
            curl_close($curl);
            $obj        = json_decode($result);
            return redirect('https://toyyibpay.com/' . $obj[0]->BillCode);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function toyyibpayCallback(Request $request, $planID, $orderId, $couponId)
    {
        if ($request->status_id == 1) {
            if (Auth::user()->type == 'Admin') {
                $order = tenancy()->central(function ($tenant) use ($request, $orderId, $couponId, $planID) {
                    $datas = Order::find($orderId);
                    $datas->status = 1;
                    $datas->payment_id = $request->transaction_id;
                    $datas->payment_type = 'toyyibpay';
                    $datas->update();
                    $coupons = Coupon::find($couponId);
                    $user = User::find($tenant->id);
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
                    $plan = Plan::find($planID);
                    $user->plan_id = $plan->id;
                    if ($plan->durationtype == 'Month' && $plan->id != '1') {
                        $user->plan_expired_date = Carbon::now()->addMonths($plan->duration)->isoFormat('YYYY-MM-DD');
                    } elseif ($plan->durationtype == 'Year' && $plan->id != '1') {
                        $user->plan_expired_date = Carbon::now()->addYears($plan->duration)->isoFormat('YYYY-MM-DD');
                    } else {
                        $user->plan_expired_date = null;
                    }
                    $user->save();
                });
                return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
            } else {
                $datas = Order::find($orderId);
                $datas->status = 1;
                $datas->payment_id = $request->transaction_id;
                $datas->payment_type = 'toyyibpay';
                $datas->update();
                $user = User::find(Auth::user()->id);
                $coupons = Coupon::find($couponId);
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
                $plan = Plan::find($request->plan_id);
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
        } else if ($request->status_id == 3) {
            if (Auth::user()->type == 'Admin') {
                $central = tenancy()->central(function ($tenant) use ($orderId,$request) {
                    $order = Order::find($orderId);
                    $order->status = 2;
                    $order->payment_id = $request->transaction_id;
                    $order->payment_type = 'toyyibpay';
                    $order->update();
                });
                return redirect()->route('plans.index')->with('errors', __('Payment failed.'));
            } else {
                $order = Order::find($orderId);
                $order->status = 2;
                $order->payment_id = $request->transaction_id;
                $order->payment_type = 'toyyibpay';
                $order->update();
                return redirect()->route('plans.index')->with('errors', __('Payment failed.'));
            }
        } else {
            return redirect()->route('plans.index')->with('errors', __('Payment pending.'));
        }
    }
}
