<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Facades\UtilityFacades;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserCoupon;
use Carbon\Carbon;

class AamarpayController extends Controller
{
    public function planPayWithAamarpay(Request $request)
    {
        $planID     = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser   = Auth::user();
        $url        = 'https://sandbox.aamarpay.com/request.php';
        if (Auth::user()->type == 'Admin') {
            $plan                   = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $aamarpayStoreId        = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('aamarpay_store_id');
            });
            $aamarpaySignatureKey   = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('aamarpay_signature_key');
            });
            $aamarpayDescription    = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('aamarpay_description');
            });
            $currency               = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('currency');
            });
            $resData =  tenancy()->central(function ($tenant) use ($plan, $request) {
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
            $plan                   = Plan::find($planID);
            $aamarpayStoreId        = UtilityFacades::getsettings('aamarpay_store_id');
            $aamarpaySignatureKey   = UtilityFacades::getsettings('aamarpay_signature_key');
            $aamarpayDescription    = UtilityFacades::getsettings('aamarpay_description');
            $currency               = UtilityFacades::getsettings('currency');
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
        if (Auth::user()->phone == null) {
            return redirect()->back()->with('failed', __('Please add phone number to your profile.'));
        }
        try {
            $orderID    = strtoupper(str_replace('.', '', uniqid('', true)));
            $fields     = array(
                'store_id'      => $aamarpayStoreId,
                'amount'        => $resData['total_price'],
                'payment_type'  => '',
                'currency'      => $currency,
                'tran_id'       => $orderID,
                'cus_name'      => $authUser->name,
                'cus_email'     => $authUser->email,
                'cus_add1'      => '',
                'cus_add2'      => '',
                'cus_city'      => '',
                'cus_state'     => '',
                'cus_postcode'  => '',
                'cus_country'   => $authUser->country,
                'cus_phone'     => $authUser->phone,
                'success_url'   => route('plan.aamarpay', Crypt::encrypt(['response' => 'success', 'coupon_id' => $resData['coupon'], 'plan_id' => $plan->id, 'price' => $resData['total_price'], 'order_id' => $resData['order_id']])),
                'fail_url'      => route('plan.aamarpay', Crypt::encrypt(['response' => 'failure', 'coupon_id' => $resData['coupon'], 'plan_id' => $plan->id, 'price' => $resData['total_price'], 'order_id' => $resData['order_id']])),
                'cancel_url'    => route('plan.aamarpay', Crypt::encrypt(['response' => 'cancel', 'coupon_id' => $resData['coupon'], 'plan_id' => $plan->id, 'price' => $resData['total_price'], 'order_id' => $resData['order_id']])),
                'signature_key' => $aamarpaySignatureKey,
                'desc'          => $aamarpayDescription,
            );
            $fieldsString   = http_build_query($fields);
            $ch             = curl_init();
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $urlForward     = str_replace('"', '', stripslashes(curl_exec($ch)));
            curl_close($ch);
            $this->redirect_to_merchant($urlForward);
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e);
        }
    }

    function redirect_to_merchant($url)
    {
        $token = csrf_token();
?>
        <html xmlns="http://www.w3.org/1999/xhtml">

        <head>
            <script type="text/javascript">
                function closethisasap() {
                    document.forms["redirectpost"].submit();
                }
            </script>
        </head>

        <body onLoad="closethisasap();">
            <form name="redirectpost" method="post" action="<?php echo 'https://sandbox.aamarpay.com/' . $url; ?>">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
        </body>

        </html>
<?php
        exit;
    }

    public function getPaymentAamarpayStatus($data)
    {
        $data       = Crypt::decrypt($data);
        if (Auth::user()->type == 'Admin') {
            $order  = tenancy()->central(function ($tenant) use ($data) {
                $orderID    = strtoupper(str_replace('.', '', uniqid('', true)));
                if ($data['response'] == "success") {
                    $datas                  = Order::find($data['order_id']);
                    $datas->payment_id      = $orderID;
                    $datas->status          = 1;
                    $datas->payment_type    = 'aamarpay';
                    $datas->update();
                    $coupons    = Coupon::find($data['coupon_id']);
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
                    return redirect()->route('plans.index')->with('success', __('Plan activated Successfully.'));
                } elseif ($data['response'] == "cancel") {
                    $order                  = Order::find($data['order_id']);
                    $order->status          = 2;
                    $order->payment_type    = 'aamarpay';
                    $order->update();
                    return redirect()->route('plans.index')->with('errors', __('Your payment is cancel'));
                } else {
                    $order                  = Order::find($data['order_id']);
                    $order->status          = 2;
                    $order->payment_type    = 'aamarpay';
                    $order->update();
                    return redirect()->route('plans.index')->with('errors', __('Your Transaction is fail please try again'));
                }
            });
            return redirect()->route('plans.index');
        } else {
            $orderID    = strtoupper(str_replace('.', '', uniqid('', true)));
            $user       = Auth::user();
            if ($data['response'] == "success") {
                $datas                  = Order::find($data['order_id']);
                $datas->payment_id      = $orderID;
                $datas->status          = 1;
                $datas->payment_type    = 'aamarpay';
                $datas->update();
                $user       = User::find(Auth::user()->id);
                $coupons    = Coupon::find($data['coupon_id']);
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
                return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
            } elseif ($data['response'] == "cancel") {
                $order                  = Order::find($data['order_id']);
                $order->payment_id      = $orderID;
                $order->status          = 2;
                $order->payment_type    = 'aamarpay';
                $order->update();
                return redirect()->route('plans.index')->with('errors', __('Your payment is cancel'));
            } else {
                $order                  = Order::find($data['order_id']);
                $order->payment_id      = $orderID;
                $order->status          = 2;
                $order->payment_type    = 'aamarpay';
                $order->update();
                return redirect()->route('plans.index')->with('errors', __('Your Transaction is fail please try again'));
            }
        }
    }
}
