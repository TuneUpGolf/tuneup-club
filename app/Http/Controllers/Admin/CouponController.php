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
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

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
        if (!Auth::user()->can('create-coupon')) {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }

        $stripe_account_id = Auth::user()->stripe_account_id;

        if (!$stripe_account_id) {
            return redirect()->back()->with('failed', __('Stripe connected account not found.'));
        }

        // Validation
        $request->validate([
            'icon_input' => 'required|in:manual,auto',
            'manualCode' => 'required_if:icon_input,manual|string|max:50',
            'autoCode' => 'required_if:icon_input,auto|string|max:50',
            'discount' => 'required|numeric|min:0.01|max:100',
            'discount_type' => 'required|in:percentage,flat',
            'limit' => 'required|integer|min:1|max:10000',
        ]);

        // Determine final code
        $code = strtoupper(
            $request->icon_input === 'manual'
            ? $request->manualCode
            : $request->autoCode
        );

        if (empty($code)) {
            return back()->with('failed', __('Coupon code is required.'));
        }

        if (Coupon::where('code', $code)->exists()) {
            return back()->with('failed', __('This coupon code already exists.'));
        }

        $couponData = [
            'code' => $code,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
            'limit' => $request->limit,
            'used_count' => 0,
            'is_active' => true,
        ];

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            // 1️⃣ Create Stripe Coupon
            $couponParams = [
                'duration' => 'once',
                'name' => "Discount {$request->discount}" . ($request->discount_type === 'percentage' ? '%' : '$') . " - {$code}",
                'metadata' => [
                    'created_by' => 'your-app',
                ],
            ];

            if ($request->discount_type === 'percentage') {
                $couponParams['percent_off'] = (float) $request->discount;
            } else {
                $couponParams['amount_off'] = (int) ($request->discount * 100);
                $couponParams['currency'] = 'usd'; // change to your currency
            }

            $stripeCoupon = $stripe->coupons->create(
                $couponParams,
                ['stripe_account' => $stripe_account_id]
            );

            // 2️⃣ Create Promotion Code
            $promotionCode = $stripe->promotionCodes->create([
                'coupon' => $stripeCoupon->id,
                'code' => $code,
                'active' => true,
                'max_redemptions' => (int) $request->limit,
            ], [
                'stripe_account' => $stripe_account_id
            ]);

            // Save to DB
            $couponData['stripe_coupon_id'] = $stripeCoupon->id;
            $couponData['stripe_promo_id'] = $promotionCode->id;

            $coupon = Coupon::create($couponData);

            // Optional: update metadata with local ID
            $stripe->coupons->update(
                $stripeCoupon->id,
                ['metadata' => ['local_id' => $coupon->id]],
                ['stripe_account' => $stripe_account_id]
            );

            return redirect()
                ->route('coupon.index')
                ->with('success', __('Coupon created successfully!'));

        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error - Coupon Creation', [
                'message' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode(),
                'account_id' => $stripe_account_id,
                'code_attempt' => $code,
            ]);

            $errorMessage = 'Stripe error: ' . ($e->getUserMessage() ?? $e->getMessage());

            return back()->withInput()->with('failed', $errorMessage);

        } catch (\Exception $e) {
            Log::error('Coupon Creation General Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('failed', 'Something went wrong. Please try again.');
        }
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
        if (!Auth::user()->can('edit-coupon')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $user = Auth::user();
        $stripe_account_id = $user->stripe_account_id;

        if (!$stripe_account_id) {
            return redirect()->back()->with('error', __('Stripe account not found.'));
        }

        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'discount' => 'required|numeric|min:0',
            'discount_type' => 'required|in:percentage,flat',
            'limit' => 'required|integer|min:1',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($coupon->id),
            ],
        ]);

        $newCode = strtoupper(trim($validated['code']));
        $newDiscount = $validated['discount'];
        $newDiscountType = $validated['discount_type'];
        $newMaxRedemptions = (int) $validated['limit'];

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // ── 1. Load current Stripe objects (if they exist) ───────────────────────
            $currentCoupon = null;
            $currentPromo = null;

            if ($coupon->stripe_coupon_id) {
                try {
                    $currentCoupon = \Stripe\Coupon::retrieve(
                        $coupon->stripe_coupon_id,
                        ['stripe_account' => $stripe_account_id]
                    );
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    // Coupon probably deleted or invalid → we'll create new
                }
            }

            if ($coupon->stripe_promo_id) {
                try {
                    $currentPromo = \Stripe\PromotionCode::retrieve(
                        $coupon->stripe_promo_id,
                        ['stripe_account' => $stripe_account_id]
                    );
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    // Promo probably deleted → we'll create new
                }
            }

            // ── 2. Determine if we need a new Coupon ─────────────────────────────────
            $needsNewCoupon = true;
            $stripeCoupon = null;

            if ($currentCoupon) {
                if ($newDiscountType === 'percentage') {
                    $needsNewCoupon = ($currentCoupon->percent_off != $newDiscount);
                } else {
                    $needsNewCoupon = ($currentCoupon->amount_off != (int) round($newDiscount * 100));
                }
            }

            // ── 3. Create or reuse Coupon ────────────────────────────────────────────
            if ($needsNewCoupon) {
                $couponParams = [
                    'duration' => 'once',
                    'currency' => 'usd',
                ];

                if ($newDiscountType === 'percentage') {
                    $couponParams['percent_off'] = $newDiscount;
                } else {
                    $couponParams['amount_off'] = (int) round($newDiscount * 100);
                }

                $stripeCoupon = \Stripe\Coupon::create(
                    $couponParams,
                    ['stripe_account' => $stripe_account_id]
                );

                // Optional: mark old coupon as deprecated
                if ($currentCoupon) {
                    try {
                        \Stripe\Coupon::update($currentCoupon->id, [
                            'metadata' => [
                                'status' => 'replaced',
                                'replaced_at' => now()->toIso8601String(),
                            ]
                        ], ['stripe_account' => $stripe_account_id]);
                    } catch (\Throwable $t) {
                        // silent fail
                    }
                }
            } else {
                $stripeCoupon = $currentCoupon;
            }

            // ── 4. Handle Promotion Code ─────────────────────────────────────────────
            $promoParams = [
                'coupon' => $stripeCoupon->id,
                'code' => $newCode,
                'max_redemptions' => $newMaxRedemptions,
            ];

            $createNewPromo = true;
            $stripePromo = null;

            // Case 1: Same code + promo still active → try to update allowed fields
            if ($currentPromo && $currentPromo->active && $currentPromo->code === $newCode) {
                try {
                    $stripePromo = \Stripe\PromotionCode::update(
                        $currentPromo->id,
                        [
                            'max_redemptions' => $newMaxRedemptions,
                            // metadata if you want
                        ],
                        ['stripe_account' => $stripe_account_id]
                    );
                    $createNewPromo = false;
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    // Update failed → fallback to create new
                }
            }

            // Always deactivate old promo code if it exists (safety first)
            if ($currentPromo) {
                try {
                    \Stripe\PromotionCode::update(
                        $currentPromo->id,
                        ['active' => false],
                        ['stripe_account' => $stripe_account_id]
                    );
                } catch (\Throwable $t) {
                    \Log::warning('Failed to deactivate old promo code', [
                        'promo_id' => $currentPromo->id,
                        'error' => $t->getMessage()
                    ]);
                }
            }

            // Case 2: Create new promotion code (most common when code changes)
            if ($createNewPromo) {
                $stripePromo = \Stripe\PromotionCode::create(
                    $promoParams,
                    ['stripe_account' => $stripe_account_id]
                );
            }

            // ── 5. Update local database ─────────────────────────────────────────────
            $coupon->update([
                'code' => $newCode,
                'discount' => $newDiscount,
                'discount_type' => $newDiscountType,
                'limit' => $newMaxRedemptions,
                'stripe_coupon_id' => $stripeCoupon->id,
                'stripe_promo_id' => $stripePromo->id,
            ]);

            return redirect()->route('coupon.index')
                ->with('success', __('Coupon updated successfully'));

        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error('Stripe coupon/promocode update failed', [
                'coupon_id' => $coupon->id,
                'error' => $e->getMessage(),
                'stripe_code' => $e->getStripeCode() ?? 'unknown'
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', __('Failed to update on Stripe: ') . $e->getMessage());
        } catch (\Throwable $e) {
            \Log::critical('Critical error during coupon update', [
                'coupon_id' => $coupon->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', __('Something went seriously wrong. Please contact support.'));
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
