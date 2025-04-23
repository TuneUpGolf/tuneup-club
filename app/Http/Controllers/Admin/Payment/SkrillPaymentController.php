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
use Obydul\LaraSkrill\SkrillClient;
use Obydul\LaraSkrill\SkrillRequest;
use Illuminate\Http\RedirectResponse;

class SkrillPaymentController extends Controller
{
    public function planPayWithSkrill(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if (Auth::user()->type == 'Admin') {
            $plan           = tenancy()->central(function ($tenant) use ($planID) {
                return Plan::find($planID);
            });
            $skrillEmail    = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('skrill_email');
            });
            $currency       = tenancy()->central(function ($tenant) {
                return UtilityFacades::getValByName('currency');
            });
            $resData        =  tenancy()->central(function ($tenant) use ($plan, $request) {
                $orderID        = time();
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
                    'txn_id'            => '',
                    'payment_type'      => 'skrill',
                    'payment_id'        => $orderID,
                ]);
                $resData['total_price'] = $price;
                $resData['coupon']      = $couponId;
                $resData['order_id']    = $data->id;
                $resData['payment_id']  = $orderID;
                return $resData;
            });
        } else {
            $plan           = Plan::find($planID);
            $skrillEmail    = UtilityFacades::getsettings('skrill_email');
            $currency       = UtilityFacades::getValByName('currency');
            $orderID        = time();
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
                'txn_id'            => '',
                'payment_type'      => 'skrill',
                'payment_id'        => $orderID,
            ]);
            $resData['total_price'] = $price;
            $resData['coupon']      = $couponId;
            $resData['order_id']    = $data->id;
            $resData['payment_id']  = $orderID;
        }

        $tranId                 = md5(date('Y-m-d') . strtotime('Y-m-d H:i:s') . 'user_id');
        $skill                  = new SkrillRequest();
        $skill->pay_to_email    = $skrillEmail;
        $skill->return_url      = route('plan.skrill', Crypt::encrypt(['order_id' => $resData['order_id'], 'payment_id' => $resData['payment_id'], 'amount' => $resData['total_price'], 'tansaction_id' => MD5($tranId), 'payment_frequency' => $request->skrill_payment_frequency, 'coupon_id' => $resData['coupon'], 'plan_id' => $plan->id, 'firstname' => $authUser->name, 'email' => $authUser->email, 'status' => 'successfull']));
        $skill->cancel_url      = route('plan.skrill', Crypt::encrypt(['order_id' => $resData['order_id'], 'payment_id' => $resData['payment_id'], 'amount' => $resData['total_price'], 'tansaction_id' => MD5($tranId), 'payment_frequency' => $request->skrill_payment_frequency, 'coupon_id' => $resData['coupon'], 'plan_id' => $plan->id, 'firstname' => $authUser->name, 'email' => $authUser->email, 'status' => 'failed']));
        $skill->transaction_id  = MD5($tranId); // generate transaction id
        $skill->amount          = $resData['total_price'];
        $skill->currency        = $currency;
        $skill->language        = 'EN';
        $skill->prepare_only    = '1';
        $skill->merchant_fields = 'site_name, customer_email';
        $skill->site_name       = Auth::user()->name;
        $skill->customer_email  = Auth::user()->email;
        $client                 = new SkrillClient($skill);
        $sid                    = $client->generateSID();
        $jsonSID                = json_decode($sid);
        if ($jsonSID != null && $jsonSID->code == "BAD_REQUEST") {
            return redirect()->back()->with('errors', 'You dont have enough money in your balance. Make a deposit to your Skrill account or choose another payment option.');
        }
        $redirectUrl    = $client->paymentRedirectUrl($sid);
        if ($tranId) {
            $data   = [
                'amount'    => $resData['total_price'],
                'trans_id'  => MD5($request['transaction_id']),
                'currency'  => $currency,
            ];
            session()->put('skrill_data', $data);
        }
        try {
            return  new RedirectResponse($redirectUrl);
        } catch (\Exception $e) {
            return redirect()->route('plans.index')->with('errors', __('Transaction has been failed!'));
        }
    }

    public function getPayWithSkrillCallback(Request $request, $data)
    {
        $data = Crypt::decrypt($data);
        if (Auth::user()->type == 'Admin') {
            $order = tenancy()->central(function ($tenant) use ($request, $data) {
                if ($request->status == 'successfull') {
                    if (session()->has('skrill_data')) {
                        $getData                = session()->get('skrill_data');
                        $orderID                = time();
                        $datas                  = Order::find($data['order_id']);
                        $datas->status          = 1;
                        $datas->payment_type    = 'skrill';
                        $datas->payment_id      = $orderID;
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
                        return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
                    } else {
                        $order                  = Order::find($data['order_id']);
                        $order->status          = 2;
                        $order->payment_type    = 'skrill';
                        $order->update();
                        return redirect()->route('plans.index')->with('errors', __('Transaction has been failed!'));
                    }
                } else {
                    $order                  = Order::find($data['order_id']);
                    $order->status          = 2;
                    $order->payment_type    = 'skrill';
                    $order->update();
                    return redirect()->route('plans.index')->with('errors', __('Opps something went wrong.'));
                }
            });
        } else {
            if ($request->status == 'successfull') {
                if (session()->has('skrill_data')) {
                    $getData                = session()->get('skrill_data');
                    $orderID                = time();
                    $datas                  = Order::find($data['order_id']);
                    $datas->status          = 1;
                    $datas->payment_type    = 'skrill';
                    $datas->payment_id      = $orderID;
                    $datas->update();
                    $coupons    = Coupon::find($data['coupon_id']);
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
                    $order->payment_type    = 'skrill';
                    $order->update();
                    return redirect()->route('plans.index')->with('errors', __('Transaction has been failed!'));
                }
            } else {
                $order                  = Order::find($data['order_id']);
                $order->status          = 2;
                $order->payment_type    = 'skrill';
                $order->update();
                return redirect()->route('plans.index')->with('errors', __('Opps something went wrong.'));
            }
        }
        return redirect()->route('plans.index');
    }
}
