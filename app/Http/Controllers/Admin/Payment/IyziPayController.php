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
use Illuminate\Support\Facades\Http;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;
use Iyzipay\Options;

class IyziPayController extends Controller
{
    public function initiatePayment(Request $request)
    {
        $planID    = \Illuminate\Support\Facades\Crypt::decrypt($request->plan_id);
        $authUser  = Auth::user();
        if (Auth::user()->type == 'Admin') {
            $iyzipayKey     = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('iyzipay_key');
            });
            $iyzipaySecret  = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('iyzipay_secret');
            });
            $iyzipayMode    = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('iyzipay_mode');
            });
            $currency       = tenancy()->central(function ($tenant) {
                return UtilityFacades::getsettings('currency');
            });
            $plan           = tenancy()->central(function ($tenant) use ($planID) {
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
                        $discount_type  = $coupons->discount_type;
                        $discountValue  =  UtilityFacades::calculateDiscount($price, $discount, $discount_type);
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
            // set your Iyzico API credentials
            try {
                $setBaseUrl     = ($iyzipayMode == 'sandbox') ? 'https://sandbox-api.iyzipay.com' : 'https://api.iyzipay.com';
                $options        = new Options();
                $options->setApiKey($iyzipayKey);
                $options->setSecretKey($iyzipaySecret);
                $options->setBaseUrl($setBaseUrl); // or "https://api.iyzipay.com" for production
                $ipAddress      = Http::get('https://ipinfo.io/?callback=')->json();
                $address        = ($authUser->address) ? $authUser->address : 'Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1';
                // create a new payment request
                $request        = new CreateCheckoutFormInitializeRequest();
                $request->setLocale("en");
                $request->setPrice($resData['total_price']);
                $request->setPaidPrice($resData['total_price']);
                $request->setCurrency($currency);
                $request->setCallbackUrl(route('iyzipay.payment.callback'));
                $request->setEnabledInstallments(array(1));
                $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
                $buyer          = new \Iyzipay\Model\Buyer();
                $buyer->setId($authUser->id);
                $buyer->setName(explode(' ', $authUser->name)[0]);
                $buyer->setSurname(explode(' ', $authUser->name)[1]);
                $buyer->setGsmNumber("+" . $authUser->dial_code . $authUser->phone);
                $buyer->setEmail($authUser->email);
                $buyer->setIdentityNumber(rand(0, 999999));
                $buyer->setLastLoginDate("2023-03-05 12:43:35");
                $buyer->setRegistrationDate("2023-04-21 15:12:09");
                $buyer->setRegistrationAddress($address);
                $buyer->setIp($ipAddress['ip']);
                $buyer->setCity($ipAddress['city']);
                $buyer->setCountry($ipAddress['country']);
                $buyer->setZipCode($ipAddress['postal']);
                $request->setBuyer($buyer);
                $shippingAddress    = new \Iyzipay\Model\Address();
                $shippingAddress->setContactName($authUser->name);
                $shippingAddress->setCity($ipAddress['city']);
                $shippingAddress->setCountry($ipAddress['country']);
                $shippingAddress->setAddress($address);
                $shippingAddress->setZipCode($ipAddress['postal']);
                $request->setShippingAddress($shippingAddress);
                $billingAddress     = new \Iyzipay\Model\Address();
                $billingAddress->setContactName($authUser->name);
                $billingAddress->setCity($ipAddress['city']);
                $billingAddress->setCountry($ipAddress['country']);
                $billingAddress->setAddress($address);
                $billingAddress->setZipCode($ipAddress['postal']);
                $request->setBillingAddress($billingAddress);
                $basketItems        = array();
                $firstBasketItem    = new \Iyzipay\Model\BasketItem();
                $firstBasketItem->setId("BI101");
                $firstBasketItem->setName("Binocular");
                $firstBasketItem->setCategory1("Collectibles");
                $firstBasketItem->setCategory2("Accessories");
                $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
                $firstBasketItem->setPrice($resData['total_price']);
                $basketItems[0]         = $firstBasketItem;
                $request->setBasketItems($basketItems);
                $checkoutFormInitialize = CheckoutFormInitialize::create($request, $options);
                return redirect()->to($checkoutFormInitialize->getpaymentPageUrl());
            } catch (\Exception $e) {
                return redirect()->route('plans.index')->with('errors', $e->getMessage());
            }
        } else {
            $iyzipayKey     = UtilityFacades::getsettings('iyzipay_key');
            $iyzipaySecret  = UtilityFacades::getsettings('iyzipay_secret');
            $iyzipayMode    =  UtilityFacades::getsettings('iyzipay_mode');
            $currency       = UtilityFacades::getsettings('currency');
            $plan           = Plan::find($planID);
            $couponId       = '0';
            $price          = $plan->price;
            $couponCode     = null;
            $discountValue  = null;
            $coupons    = Coupon::where('code', $request->coupon)->where('is_active', '1')->first();
            if ($coupons) {
                $couponCode     = $coupons->code;
                $usedCoupun     = $coupons->used_coupon();
                if ($coupons->limit == $usedCoupun) {
                    $resData['errors'] = __('This coupon code has expired.');
                } else {
                    $discount       = $coupons->discount;
                    $discount_type  = $coupons->discount_type;
                    $discountValue  =  UtilityFacades::calculateDiscount($price, $discount, $discount_type);
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
            // set your Iyzico API credentials
            try {
                $setBaseUrl         = ($iyzipayMode == 'sandbox') ? 'https://sandbox-api.iyzipay.com' : 'https://api.iyzipay.com';
                $options            = new Options();
                $options->setApiKey($iyzipayKey);
                $options->setSecretKey($iyzipaySecret);
                $options->setBaseUrl($setBaseUrl); // or "https://api.iyzipay.com" for production
                $ipAddress          = Http::get('https://ipinfo.io/?callback=')->json();
                $address            = ($authUser->address) ? $authUser->address : 'Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1';
                // create a new payment request
                $request            = new CreateCheckoutFormInitializeRequest();
                $request->setLocale("en");
                $request->setPrice($resData['total_price']);
                $request->setPaidPrice($resData['total_price']);
                $request->setCurrency($currency);
                $request->setCallbackUrl(route('iyzipay.payment.callback'));
                $request->setEnabledInstallments(array(1));
                $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
                $buyer              = new \Iyzipay\Model\Buyer();
                $buyer->setId($authUser->id);
                $buyer->setName(explode(' ', $authUser->name)[0]);
                $buyer->setSurname(explode(' ', $authUser->name)[1]);
                $buyer->setGsmNumber("+" . $authUser->dial_code . $authUser->phone);
                $buyer->setEmail($authUser->email);
                $buyer->setIdentityNumber(rand(0, 999999));
                $buyer->setLastLoginDate("2023-03-05 12:43:35");
                $buyer->setRegistrationDate("2023-04-21 15:12:09");
                $buyer->setRegistrationAddress($address);
                $buyer->setIp($ipAddress['ip']);
                $buyer->setCity($ipAddress['city']);
                $buyer->setCountry($ipAddress['country']);
                $buyer->setZipCode($ipAddress['postal']);
                $request->setBuyer($buyer);
                $shippingAddress        = new \Iyzipay\Model\Address();
                $shippingAddress->setContactName($authUser->name);
                $shippingAddress->setCity($ipAddress['city']);
                $shippingAddress->setCountry($ipAddress['country']);
                $shippingAddress->setAddress($address);
                $shippingAddress->setZipCode($ipAddress['postal']);
                $request->setShippingAddress($shippingAddress);
                $billingAddress         = new \Iyzipay\Model\Address();
                $billingAddress->setContactName($authUser->name);
                $billingAddress->setCity($ipAddress['city']);
                $billingAddress->setCountry($ipAddress['country']);
                $billingAddress->setAddress($address);
                $billingAddress->setZipCode($ipAddress['postal']);
                $request->setBillingAddress($billingAddress);
                $basketItems            = array();
                $firstBasketItem        = new \Iyzipay\Model\BasketItem();
                $firstBasketItem->setId("BI101");
                $firstBasketItem->setName("Binocular");
                $firstBasketItem->setCategory1("Collectibles");
                $firstBasketItem->setCategory2("Accessories");
                $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL);
                $firstBasketItem->setPrice($resData['total_price']);
                $basketItems[0]         = $firstBasketItem;
                $request->setBasketItems($basketItems);
                $checkoutFormInitialize = CheckoutFormInitialize::create($request, $options);
                return redirect()->to($checkoutFormInitialize->getpaymentPageUrl());
            } catch (\Exception $e) {
                return redirect()->route('plans.index')->with('errors', $e->getMessage());
            }
        }
    }

    public function iyzipayCallback(Request $request)
    {
        if (Auth::user()->type == 'Admin') {
            $order  = tenancy()->central(function ($tenant) use ($request) {
                $datas                  = Order::orderBy('id', 'desc')->first();
                $datas->status          = 1;
                $datas->payment_type    = 'iyzipay';
                $datas->payment_id      = $request->token;
                $datas->update();
                $coupons    = Coupon::where('code', $datas->coupon_code)->where('is_active', '1')->first();
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
        } else {
            $datas                  = Order::orderBy('id', 'desc')->first();
            $datas->status          = 1;
            $datas->payment_type    = 'iyzipay';
            $datas->payment_id      = $request->token;
            $datas->update();
            $coupons    = Coupon::where('code', $datas->coupon_code)->where('is_active', '1')->first();
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
        }
        return redirect()->route('plans.index')->with('status', __('Payment successfully.'));
    }
}
