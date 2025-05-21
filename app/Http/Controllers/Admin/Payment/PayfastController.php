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

class PayfastController extends Controller
{
    public function payfastPrepare(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if ($authUser->type == 'Admin') {
            $payfastMerchantId  = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('payfast_merchant_id');
            });
            $payfastMerchantKey = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('payfast_merchant_key');
            });
            $plan               = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $payfastSignature   = tenancy()->central(function ($tenant) use ($planID) {
                return UtilityFacades::getsettings('payfast_signature');
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
                if (!$request->amount) {
                    $data = Order::create([
                        'plan_id'           => $plan->id,
                        'user_id'           => $tenant->id,
                        'amount'            => $price,
                        'discount_amount'   => $discountValue,
                        'coupon_code'       => $couponCode,
                        'status'            => 0,
                    ]);
                } else {
                    $data   = Order::find($request->order_id);
                }
                $resData['total_price']     = $price;
                $resData['discount_amount'] = $discountValue;
                $resData['coupon']          = $couponId;
                $resData['order_id']        = $data->id;
                return $resData;
            });
        } else {
            $payfastMerchantId  = UtilityFacades::getsettings('payfast_merchant_id');
            $payfastMerchantKey =  UtilityFacades::getsettings('payfast_merchant_key');
            $payfastSignature   = UtilityFacades::getsettings('payfast_signature');
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
                        $price  = $plan->price;
                    }
                    $couponId   = $coupons->id;
                }
            }
            if (!$request->amount) {
                $data = Order::create([
                    'plan_id'           => $plan->id,
                    'user_id'           => $authUser->id,
                    'amount'            => $price,
                    'discount_amount'   => $discountValue,
                    'coupon_code'       => $couponCode,
                    'status'            => 0,
                ]);
            } else {
                $data   = Order::find($request->order_id);
            }
            $resData['total_price']     = $price;
            $resData['discount_amount'] = $discountValue;
            $resData['coupon']          = $couponId;
            $resData['order_id']        = $data->id;
        }
        $success = Crypt::encrypt([
            'plan_id'           => $plan->id,
            'order_id'          => $resData['order_id'],
            'coupon'            => $resData['coupon'],
            'discount_amount'   => $resData['discount_amount'],
            'plan_amount'       => $resData['total_price'],
            'flag'              => 'success',
        ]);
        $error = Crypt::encrypt([
            'plan_id'           => $plan->id,
            'discount_amount'   => $resData['discount_amount'],
            'order_id'          => $resData['order_id'],
            'coupon'            => $resData['coupon'],
            'plan_amount'       => $resData['total_price'],
            'flag'              => 'error',
        ]);
        $data = array(
            // Merchant details
            'merchant_id'   => $payfastMerchantId,
            'merchant_key'  => $payfastMerchantKey,
            'return_url'    => route('payfast.payment.callback', $success),
            'cancel_url'    => route('payfast.payment.callback', $error),
            'notify_url'    => route('payfast.payment.callback', $success),
            // Buyer details
            'name_first'    => $authUser->name,
            'name_last'     => '',
            'email_address' => $authUser->email,
            // Transaction details
            'm_payment_id'  => $resData['order_id'], //Unique payment ID to pass through to notify_url
            'amount'        =>  number_format(sprintf('%.2f', $resData['total_price']), 2, '.', ''),
            'item_name'     => $plan->name,
        );
        $passphrase         = $payfastSignature;
        $signature          = $this->generateSignature($data, $passphrase);
        $data['signature']  = $signature;
        $htmlForm = '';
        foreach ($data as $name => $value) {
            $htmlForm .= '<input name="' . $name . '" type="hidden" value=\'' . $value . '\' />';
        }
        return response()->json([
            'success'   => true,
            'inputs'    => $htmlForm,
        ]);
    }

    public function generateSignature($data, $passPhrase = null)
    {
        $pfOutput   = '';
        foreach ($data as $key => $val) {
            if ($val !== '') {
                $pfOutput .= $key . '=' . urlencode(trim($val)) . '&';
            }
        }
        $getString  = substr($pfOutput, 0, -1);
        if ($passPhrase !== null) {
            $getString .= '&passphrase=' . urlencode(trim($passPhrase));
        }
        return md5($getString);
    }

    public function payfastCallback(Request $request, $data)
    {
        $data   = Crypt::decrypt($data);
        if ($data['flag'] == 'success') {
            if (Auth::user()->type == 'Admin') {
                $order  = tenancy()->central(function ($tenant) use ($data, $request) {
                    $coupons    = Coupon::find($data['coupon']);
                    $datas      = Order::find($data['order_id']);
                    $datas->status          = 1;
                    $datas->payment_id      = $request->signature;
                    $datas->amount          = $data['plan_amount'];
                    $datas->discount_amount = $data['discount_amount'];
                    $datas->coupon_code     = ($coupons) ? $coupons->code : null;
                    $datas->payment_type    = 'payfast';
                    $datas->update();
                    $user   = User::find($tenant->id);
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
                });
                return redirect()->route('plans.index')->with('status', __('Payment successfully done.'));
            } else {
                $coupons                = Coupon::find($data['coupon']);
                $datas                  = Order::find($data['order_id']);
                $datas->status          = 1;
                $datas->payment_id      = $request->signature;
                $datas->amount          = $data['plan_amount'];
                $datas->discount_amount = $data['discount_amount'];
                $datas->coupon_code     = ($coupons) ? $coupons->code : null;
                $datas->payment_type    = 'payfast';
                $datas->update();
                $user                   = User::find(Auth::user()->id);
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
        } else {
            if (Auth::user()->type == 'Admin') {
                $central    = tenancy()->central(function ($tenant) use ($data) {
                    $coupons                = Coupon::find($data['coupon']);
                    $order                  = Order::find($data['order_id']);
                    $order->status          = 2;
                    $order->amount          = $data['plan_amount'];
                    $order->discount_amount = $data['discount_amount'];
                    $order->coupon_code     = ($coupons) ? $coupons->code : null;
                    $order->payment_type    = 'payfast';
                    $order->update();
                });
                return redirect()->route('plans.index')->with('errors', __('Payment failed.'));
            } else {
                $coupons    = Coupon::find($data['coupon']);
                $order      = Order::find($data['order_id']);
                $order->status          = 2;
                $order->amount          = $data['plan_amount'];
                $order->discount_amount = $data['discount_amount'];
                $order->coupon_code     = ($coupons) ? $coupons->code : null;
                $order->payment_type    = 'payfast';
                $order->update();
                return redirect()->route('plans.index')->with('errors', __('Payment failed.'));
            }
        }
    }
}
