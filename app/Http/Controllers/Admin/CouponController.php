<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\CouponDataTable;
use App\DataTables\Admin\UserCouponDatatable;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Stripe\Stripe;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Stripe\Coupon as StripeCoupon;
use Stripe\PromotionCode;

class CouponController extends Controller
{
    public function index(CouponDataTable $dataTable)
    {
        if (\Auth::user()->can('manage-coupon')) {
            $totalCoupon = Coupon::count();
            $expieredCoupon = Coupon::where('is_active', '0')->count();
            $totalUsedCoupon = UserCoupon::count();
            $totalUseAmount = Order::where('status', 1)->sum('discount_amount');
            return $dataTable->render('admin.coupon.index', compact('totalCoupon', 'expieredCoupon', 'totalUsedCoupon', 'totalUseAmount'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create-coupon')) {
            return view('admin.coupon.create');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }


public function store(Request $request)
{
    if (!\Auth::user()->can('create-coupon')) {
        return redirect()->back()->with('failed', __('Permission denied.'));
    }

    // 1️⃣ Basic validation
    $request->validate([
        'icon_input'     => 'required',
        'discount'       => 'required|numeric|min:1',
        'discount_type'  => 'required|in:percentage,flat',
        'limit'          => 'required|integer|min:1',
    ]);

    // 2️⃣ Resolve coupon code
    $code = $request->icon_input === 'manual'
        ? strtoupper($request->manualCode)
        : strtoupper($request->autoCode);

    if (empty($code)) {
        return back()->with('failed', __('Coupon code is required.'));
    }

    // 3️⃣ DB uniqueness check
    $request->merge(['code' => $code]);
    $request->validate([
        'code' => 'unique:coupons,code',
    ]);

    // 4️⃣ Save local coupon
    $coupon = Coupon::create([
        'discount'       => $request->discount,
        'discount_type'  => $request->discount_type,
        'limit'          => $request->limit,
        'code'           => $code,
    ]);

    // 5️⃣ Create Stripe Coupon + Promotion Code
    try {
        Stripe::setApiKey(config('services.stripe.secret'));

        /** 🔴 IMPORTANT FIX #1
         *  Do NOT send currency for percentage coupons
         */
        $stripeCouponData = [
            'duration' => 'once',
        ];

        if ($request->discount_type === 'percentage') {
            $stripeCouponData['percent_off'] = $request->discount;
        } else {
            $stripeCouponData['amount_off'] = $request->discount * 100;
            $stripeCouponData['currency']   = 'usd'; // MUST match product currency
        }

        $stripeCoupon = StripeCoupon::create($stripeCouponData);

        /** 🔴 IMPORTANT FIX #2
         *  Promotion code MUST be active
         */
        $promotionCode = PromotionCode::create([
            'coupon'         => $stripeCoupon->id,
            'code'           => $code,
            'max_redemptions'=> $request->limit,
            'active'         => true,
        ]);

        // Save Stripe IDs
        $coupon->update([
            'stripe_coupon_id' => $stripeCoupon->id,
            'stripe_promo_id'  => $promotionCode->id,
        ]);

    } catch (\Exception $e) {
        return back()->with('failed', 'Stripe error: ' . $e->getMessage());
    }

    return redirect()
        ->route('coupon.index')
        ->with('success', __('Coupon created successfully.'));
}


    public function show(UserCouponDatatable $dataTable)
    {
        if (\Auth::user()->can('show-coupon')) {
            return $dataTable->render('admin.coupon.show');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (\Auth::user()->can('edit-coupon')) {
            $coupon = Coupon::find($id);
            return view('admin.coupon.edit', compact('coupon'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    // public function update(Request $request, $id)
    // {
    //     if (\Auth::user()->can('edit-coupon')) {
    //         request()->validate([
    //             'discount' => 'required',
    //             'discount_type' => 'required',
    //             'limit' => 'required',
    //             'code'           => [
    //             'required',
    //             Rule::unique('coupons', 'code')->ignore($id),
    //         ],
    //         ]);
    //         $coupon                 = Coupon::find($id);
    //         $coupon->discount       = $request->discount;
    //         $coupon->discount_type  = $request->discount_type;
    //         $coupon->limit          = $request->limit;
    //         $coupon->code           = $request->code;
    //         $coupon->save();
    //         return redirect()->route('coupon.index')
    //             ->with('success',  __('Coupon updated successfully'));
    //     } else {
    //         return redirect()->back()->with('failed', __('Permission denied.'));
    //     }
    // }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit-coupon')) {
            $coupon = Coupon::find($id);

            if (!$coupon) {
                return redirect()->back()->with('error', __('Coupon not found.'));
            }

            // Validate request
            $request->validate([
                'discount' => 'required|numeric|min:0',
                'discount_type' => 'required|in:percentage,flat',
                'limit' => 'required|integer|min:1',
                'code' => 'required|unique:coupons,code,' . $id,
            ]);

            // Store old values for Stripe reference
            $oldStripeCouponId = $coupon->stripe_coupon_id;
            $oldStripePromoId = $coupon->stripe_promo_id;
            $oldCode = $coupon->code;

            // Update local coupon
            $coupon->discount = $request->discount;
            $coupon->discount_type = $request->discount_type;
            $coupon->limit = $request->limit;
            $coupon->code = strtoupper($request->code);

            // 🟢 Update Stripe coupon and promotion code
            try {
                Stripe::setApiKey(config('services.stripe.secret'));

                // Check if Stripe IDs exist
                if ($oldStripeCouponId && $oldStripePromoId) {
                    try {
                        // 1️⃣ Update existing promotion code (if only code changed)
                        if ($oldCode !== $coupon->code) {
                            try {
                                $promoCode = PromotionCode::retrieve($oldStripePromoId);
                                $promoCode->code = strtoupper($coupon->code);
                                $promoCode->save();

                                // Update coupon with new promo ID if needed
                                $coupon->stripe_promo_id = $promoCode->id;
                            } catch (\Exception $e) {
                                // If can't update, create new promotion code
                                $promotionCode = PromotionCode::create([
                                    'coupon' => $oldStripeCouponId,
                                    'code' => strtoupper($coupon->code),
                                    'max_redemptions' => $request->limit,
                                ]);
                                $coupon->stripe_promo_id = $promotionCode->id;
                            }
                        }

                        // 2️⃣ Update max redemptions on promotion code
                        try {
                            $promoCode = PromotionCode::retrieve($coupon->stripe_promo_id);
                            $promoCode->max_redemptions = $request->limit;
                            $promoCode->save();
                        } catch (\Exception $e) {
                            // Log error but continue
                            \Log::error('Failed to update Stripe promotion code redemptions: ' . $e->getMessage());
                        }

                        // 3️⃣ Create a new coupon if discount type/amount changed significantly
                        // Note: Stripe coupons cannot be updated once created
                        // We need to create a new one and update the promotion code
                        $currentCoupon = StripeCoupon::retrieve($oldStripeCouponId);

                        // Check if discount type or amount changed
                        $needsNewCoupon = false;
                        if ($request->discount_type === 'percentage') {
                            if ($currentCoupon->percent_off != $request->discount) {
                                $needsNewCoupon = true;
                            }
                        } else {
                            // For flat discount, convert to cents
                            $newAmountCents = $request->discount * 100;
                            if ($currentCoupon->amount_off != $newAmountCents) {
                                $needsNewCoupon = true;
                            }
                        }

                        if ($needsNewCoupon) {
                            // Create new Stripe coupon
                            $stripeCouponData = [
                                'duration' => 'once',
                                'currency' => 'usd',
                            ];

                            if ($request->discount_type === 'percentage') {
                                $stripeCouponData['percent_off'] = $request->discount;
                            } else {
                                $stripeCouponData['amount_off'] = $request->discount * 100; // Convert to cents
                                $stripeCouponData['currency'] = 'usd';
                            }

                            $newStripeCoupon = StripeCoupon::create($stripeCouponData);

                            // Update promotion code to use new coupon
                            try {
                                $promoCode = PromotionCode::retrieve($coupon->stripe_promo_id);
                                $promoCode->coupon = $newStripeCoupon->id;
                                $promoCode->save();
                            } catch (\Exception $e) {
                                // If can't update, create new promotion code
                                $newPromotionCode = PromotionCode::create([
                                    'coupon' => $newStripeCoupon->id,
                                    'code' => strtoupper($coupon->code),
                                    'max_redemptions' => $request->limit,
                                ]);
                                $coupon->stripe_promo_id = $newPromotionCode->id;
                            }

                            $coupon->stripe_coupon_id = $newStripeCoupon->id;

                            // Optionally expire old coupon
                            try {
                                $currentCoupon = StripeCoupon::retrieve($oldStripeCouponId);
                                $currentCoupon->delete();
                            } catch (\Exception $e) {
                                // Ignore if already deleted
                            }
                        }

                    } catch (\Exception $e) {
                        \Log::error('Stripe update error: ' . $e->getMessage());

                        // If Stripe update fails, create new coupon and promotion code
                        $stripeCouponData = [
                            'duration' => 'once',
                            'currency' => 'usd',
                        ];

                        if ($request->discount_type === 'percentage') {
                            $stripeCouponData['percent_off'] = $request->discount;
                        } else {
                            $stripeCouponData['amount_off'] = $request->discount * 100;
                        }

                        $newStripeCoupon = StripeCoupon::create($stripeCouponData);

                        $newPromotionCode = PromotionCode::create([
                            'coupon' => $newStripeCoupon->id,
                            'code' => strtoupper($coupon->code),
                            'max_redemptions' => $request->limit,
                        ]);

                        $coupon->stripe_coupon_id = $newStripeCoupon->id;
                        $coupon->stripe_promo_id = $newPromotionCode->id;
                    }
                } else {
                    // No existing Stripe IDs, create new ones
                    $stripeCouponData = [
                        'duration' => 'once',
                        'currency' => 'usd',
                    ];

                    if ($request->discount_type === 'percentage') {
                        $stripeCouponData['percent_off'] = $request->discount;
                    } else {
                        $stripeCouponData['amount_off'] = $request->discount * 100;
                    }

                    $stripeCoupon = StripeCoupon::create($stripeCouponData);

                    $promotionCode = PromotionCode::create([
                        'coupon' => $stripeCoupon->id,
                        'code' => strtoupper($coupon->code),
                        'max_redemptions' => $request->limit,
                    ]);

                    $coupon->stripe_coupon_id = $stripeCoupon->id;
                    $coupon->stripe_promo_id = $promotionCode->id;
                }

            } catch (\Exception $e) {
                \Log::error('Stripe error in coupon update: ' . $e->getMessage());

                // Save coupon locally even if Stripe fails, but notify user
                $coupon->save();

                return redirect()->route('coupon.index')
                    ->with('warning', __('Coupon updated locally, but there was an issue with Stripe: ') . $e->getMessage());
            }

            $coupon->save();

            return redirect()->route('coupon.index')
                ->with('success', __('Coupon updated successfully'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('delete-coupon')) {
            $coupon = Coupon::find($id);
            $coupon->delete();
            return redirect()->route('coupon.index')
                ->with('success', __('Coupon deleted successfully'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function uploadCsv()
    {
        return view('admin.coupon.upload-coupon');
    }

    public function uploadCsvStore(Request $request)
    {
        request()->validate([
            'file' => 'required|file|mimes:csv'
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file;
            $fileName = time() . '.' . $file->extension();
            $path = $file->storeAs('/coupon', $fileName);
            $data = array_map('str_getcsv', file(Storage::path($path)));
            array_shift($data);
            foreach ($data as $val) {
                $coupon = new Coupon();
                $coupon->discount_type = $val[0];
                $coupon->code = $val[1];
                $coupon->discount = $val[2];
                $coupon->limit = $val[3];
                $coupon->is_active = 1;
                $coupon->save();
            }
        }
        return redirect()->route('coupon.index')
            ->with('success', __('Coupon created successfully.'));
    }

    public function massCreate()
    {
        if (\Auth::user()->can('mass-create-coupon')) {
            return view('admin.coupon.mass-create');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function massCreateStore(Request $request)
    {
        if (\Auth::user()->can('mass-create-coupon')) {
            request()->validate([
                'discount' => 'required',
                'discount_type' => 'required',
                'mass_create' => 'required',
                'limit' => 'required',
            ]);
            $massCreate = $request->mass_create;
            for ($i = 1; $i <= $massCreate; $i++) {
                $coupon = new Coupon();
                $coupon->discount = $request->discount;
                $coupon->discount_type = $request->discount_type;
                $coupon->limit = $request->limit;
                $coupon->code = strtoupper(Str::random(10));
                $coupon->save();
            }
            return redirect()->route('coupon.index')->with('success', __('Coupon created successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function couponStatus(Request $request, $id)
    {
        $coupon = Coupon::find($id);
        $couponStatus = ($request->value == "true") ? 1 : 0;
        if ($coupon) {
            $coupon->is_active = $couponStatus;
            $coupon->save();
        }
        return response()->json([
            'is_success' => true,
            'message' => __('Coupon status changed successfully.')
        ]);
    }

    public function applyCoupon(Request $request)
    {
        if (Auth::user()->type == 'Admin') {
            $plan = tenancy()->central(function ($tenant) use ($request) {
                return Plan::find(\Illuminate\Support\Facades\Crypt::decrypt($request->plan_id));
            });
            if ($plan && $request->coupon != '') {
                $originalPrice = UtilityFacades::amount_format($plan->price);
                $coupons = tenancy()->central(function ($tenant) use ($request) {
                    return Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                });
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    if ($coupons->limit == $usedCoupun) {
                        return response()->json([
                            'is_success' => false,
                            'final_price' => $originalPrice,
                            'price' => number_format($plan->price, 2),
                            'message' => __(__('This coupon code has expired.')),
                        ]);
                    } else {
                        $discountType = $coupons->discount_type;
                        $discountValue = UtilityFacades::calculateDiscount($plan->price, $coupons->discount, $discountType);
                        $planPrice = $plan->price - $discountValue;
                        $price = UtilityFacades::amount_format($plan->price - $discountValue);
                        $discountValue = '-' . UtilityFacades::amount_format($discountValue);
                        if ($planPrice < 0) {
                            return response()->json([
                                'is_success' => false,
                                'discount_price' => UtilityFacades::amount_format(0),
                                'currency_symbol' => UtilityFacades::getsettings('currency_symbol'),
                                'final_price' => UtilityFacades::amount_format($plan->price),
                                'price' => number_format($plan->price, 2),
                                'message' => __('Price is negetive please enter currect coupon code.'),
                            ]);
                        } else {
                            return response()->json([
                                'is_success' => true,
                                'discount_price' => $discountValue,
                                'currency_symbol' => UtilityFacades::getsettings('currency_symbol'),
                                'final_price' => $price,
                                'price' => number_format($planPrice, 2),
                                'message' => __('Coupon code has applied successfully.'),
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        'is_success' => false,
                        'final_price' => $originalPrice,
                        'price' => number_format($plan->price, 2),
                        'message' => __('This coupon code is invalid or has expired.'),
                    ]);
                }
            }
        } else {
            $plan = Plan::find(\Illuminate\Support\Facades\Crypt::decrypt($request->plan_id));
            if ($plan && $request->coupon != '') {
                $originalPrice = UtilityFacades::amount_format($plan->price);
                $coupons = Coupon::where('code', strtoupper($request->coupon))->where('is_active', '1')->first();
                if (!empty($coupons)) {
                    $usedCoupun = $coupons->used_coupon();
                    if ($coupons->limit == $usedCoupun) {
                        return response()->json([
                            'is_success' => false,
                            'final_price' => $originalPrice,
                            'price' => number_format($plan->price, 2),
                            'message' => __(__('This coupon code has expired.')),
                        ]);
                    } else {
                        $discountType = $coupons->discount_type;
                        $discountValue = UtilityFacades::calculateDiscount($plan->price, $coupons->discount, $discountType);
                        $planPrice = $plan->price - $discountValue;
                        $price = UtilityFacades::amount_format($plan->price - $discountValue);
                        $discountValue = '-' . UtilityFacades::amount_format($discountValue);
                        if ($planPrice < 0) {
                            return response()->json([
                                'is_success' => false,
                                'discount_price' => UtilityFacades::amount_format(0),
                                'currency_symbol' => UtilityFacades::getsettings('currency_symbol'),
                                'final_price' => UtilityFacades::amount_format($plan->price),
                                'price' => number_format($plan->price, 2),
                                'message' => __('Price is negetive please enter currect coupon code.'),
                            ]);
                        } else {
                            return response()->json([
                                'is_success' => true,
                                'discount_price' => $discountValue,
                                'currency_symbol' => UtilityFacades::getsettings('currency_symbol'),
                                'final_price' => $price,
                                'price' => number_format($planPrice, 2),
                                'message' => __('Coupon code has applied successfully.'),
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        'is_success' => false,
                        'final_price' => $originalPrice,
                        'price' => number_format($plan->price, 2),
                        'message' => __('This coupon code is invalid or has expired.'),
                    ]);
                }
            }
        }
    }
}
