<?php

namespace App\Http\Controllers\Admin;

use Stripe\Price;
use Stripe\Stripe;
use Stripe\Product;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Follower;
use Illuminate\Http\Request;
use App\Facades\UtilityFacades;
use App\Models\ClientSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\SubscriptionService;
use App\Models\StripeConnectedAccount;
use App\DataTables\Admin\PlanDataTable;
use Illuminate\Support\Facades\Validator;
use Stripe\Subscription as StripeSubscription;

class PlanController extends Controller
{
    public function index(PlanDataTable $dataTable)
    {
        if (Auth::user()->can('manage-plan')) {
            if (Auth::user()->type == 'Admin') {
                $plans = tenancy()->central(function ($tenant) {
                    return Plan::all();
                });
                $user = tenancy()->central(function ($tenant) {
                    return User::find($tenant->id);
                });
                return view('admin.plans.index', compact('user', 'plans'));
            } else {
                $plans = Plan::where('tenant_id', null)->get();
                $user  = User::find(Auth::user()->id);
                return view('admin.plans.index', compact('user', 'plans'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function myPlan(Request $request)
    {
        // PlanDataTable $dataTable
        if (Auth::user()->can('manage-plan')) {
            if (Auth::user()->type != 'Follower') {


                return view('admin.plans.my-plans');
                // return $dataTable->render('admin.plans.my-plans');
            } else {
                $plans = Plan::where('tenant_id', null)->get();
                $user  = User::where('tenant_id', tenant('id'))->where('type', 'Admin')->first();
                return view('admin.plans.index', compact('user', 'plans'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
    public function myPlanData(Request $request)
    {
        if ($request->ajax()) {
            $model = new Plan();
            $plans = $model->newQuery()->withCount([
                'orders as buyers_count' => function ($q) {
                    $q->join('followers', 'orders.follower_id', '=', 'followers.id')
                        ->whereNotNull('followers.plan_expired_date')
                        ->whereDate('followers.plan_expired_date', '>=', now()->toDateString())
                        ->whereColumn('followers.plan_id', 'orders.plan_id')
                        ->select(DB::raw('COUNT(DISTINCT orders.follower_id)'));
                },
            ])->where('influencer_id', auth()->user()->id)->orderBy('column_order', 'asc')->get();
            return datatables()
                ->of($plans)
                ->addIndexColumn()
                ->editColumn('name', function ($plan) {
                    $url = route('plans.buyers', $plan->id);
                    return '<a href="#" class="js-plan-buyers" data-plan-id="' . $plan->id . '" data-url="' . $url . '">' . e($plan->name) . '</a>';
                })

                ->editColumn('created_at', function ($request) {
                    return UtilityFacades::date_time_format($request->created_at);
                })
                ->editColumn('duration', function (plan $plan) {
                    return $plan->duration . ' ' . $plan->durationtype;
                })
                ->editColumn('active_status', function (plan $plan) {
                    $checked = ($plan->active_status == 1) ? 'checked' : '';
                    $status   = '<label class="form-switch">
                             <input class="form-check-input chnageStatus" name="custom-switch-checkbox" ' . $checked . ' data-id="' . $plan->id . '" data-url="' . route('myplan.status', $plan->id) . '" type="checkbox">
                             </label>';
                    return $status;
                })
                ->addColumn('action', function (plan $plan) {
                    return view('admin.plans.action', compact('plan'));
                })
                ->editColumn('price', function (plan $plan) {
                    return UtilityFacades::amount_format($plan->price);
                })
                ->addColumn('purchases_count', function (Plan $plan) {
                    $client_subscription_count = ClientSubscription::where('plan_id', $plan->id)->count();
                    return $client_subscription_count ?? 0;
                })
                ->rawColumns(['action', 'active_status', 'name'])
                ->make(true);
        }
    }
    public function reorder(Request $request)
    {
    // dd($request->all());
        foreach ($request->order as $item) {
            \App\Models\Plan::where('id', $item['id'])
                ->update(['column_order' => $item['position']]);
        }

        return response()->json(['success' => true]);
    }
    public function createMyPlan()
    {
        if (Auth::user()->can('create-plan')) {
            if (Auth::user()->is_stripe_connected == 0) {
                return back()->with('failed', 'Stripe account not connected');
            }
            return view('admin.plans.create');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    // public function store(Request $request)
    // {

    //     if (Auth::user()->can('create-plan')) {
    //         request()->validate([
    //             'name'         => 'required|unique:plans,name|max:50',
    //             'price'        => 'required',
    //             'price_year'   => 'required',
    //             'price_quarter'=> 'required',
    //             // 'duration'     => 'required',
    //             // 'durationtype' => 'required',
    //             'max_users'    => 'required',
    //             'lesson_limit' => 'required|integer',
    //         ]);
    //         // $paymentTypes = UtilityFacades::getpaymenttypes();

    //         // if (! $paymentTypes) {
    //         //     return redirect()->back()->with('errors', __('Please select at least one payment type from Settings > Payment Settings.'))->withInput();
    //         // }

    //         $influencerId = Auth::user()->type === Role::ROLE_INFLUENCER ? Auth::user()->id : null;
    //         $tenantId     = Auth::user()->type === Role::ROLE_INFLUENCER ? tenant()->id : null;

    //         // if ($influencerId) {
    //         //     $exists = Plan::where('influencer_id', $influencerId)
    //         //         ->where('is_chat_enabled', $request->chat == '1' ? 1 : 0)
    //         //         ->where('is_feed_enabled', $request->feed == '1' ? 1 : 0)
    //         //         ->exists();

    //         //     if ($exists) {
    //         //         return redirect()->route('plans.myplan')->with('failed', __('You already have a plan with the same chat and feed settings.'));
    //         //     }
    //         // }
    //         $user  = Auth::user();
    //         // if (empty($user->stripe_account_id)) {
    //         //     return redirect()->route('plans.myplan')
    //         //         ->with('failed', __('Please first connect your Stripe account.'));
    //         // }

    //         $instructor = $influencerId ? User::find($influencerId) : null;

    //         // $serviceplane = SubscriptionService::createStripePlan($request, $user);
    //         $stripeAccountId = $instructor->stripe_account_id ?? null;

    //         Stripe::setApiKey(config('services.stripe.secret'));

    //         $product = Product::create([
    //             'name' => $request->name,
    //             'description' => $request->description,
    //         ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

    //         $convertedAmount = $request->price;

    //         if ($instructor?->stripe_transaction_fee != 'instructor') {
    //             // 🎯 Add Stripe fee recovery here
    //             $stripePerc = 0.029;       // 2.9%
    //             $stripeFixed = 0.30;         // $0.30 → 30 cents
    //             $gross = ($convertedAmount + $stripeFixed) / (1 - $stripePerc);
    //             $convertedAmount = round($gross);
    //         }

    //         $interval = 'month';
    //         $interval_count = 1;

    //         if (strtolower($request->durationtype) == 'quarter') {
    //             $interval_count = 3;
    //         } elseif (strtolower($request->durationtype) == 'year') {
    //             $interval = 'year';
    //         }
    //         // 2️⃣ Create a Recurring Price
    //         $price = Price::create([
    //             'unit_amount' => round($convertedAmount * 100), // Stripe expects cents
    //             'currency' => 'usd',
    //             'recurring' => [
    //                 'interval' => $interval,
    //                 'interval_count' => $interval_count,
    //             ],
    //             'product' => $product->id,
    //         ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

    //         $this->saveCentralizedStripeData($stripeAccountId, tenant()->id);

    //         Plan::create([
    //             'name'            => $request->name,
    //             'price'           => $request->price,
    //             // 'duration'        => $request->duration,
    //             'durationtype'    => $request->durationtype,
    //             'tenant_id'       => $tenantId,
    //             'max_users'       => $request->max_users,
    //             'description'     => $_POST['description'],
    //             'is_chat_enabled' => $request->chat == '1' ? 1 : 0,
    //             'is_feed_enabled' => $request->feed == '1' ? 1 : 0,
    //             'influencer_id'   => $influencerId,
    //             // 'stripe_product_id' => $serviceplane['product_id'] ?? null,
    //             // 'stripe_price_id' => $serviceplane['price_id'] ?? null,
    //             'stripe_product_id' => $product->id, // store Stripe IDs!
    //             'stripe_price_id'   => $price->id,
    //             'lesson_limit' => $request->lesson_limit,
    //         ]);
    //         return redirect()->route('plans.myplan')->with('success', __('Plan created successfully.'));
    //     } else {
    //         return redirect()->back()->with('failed', __('Permission denied.'));
    //     }
    // }

    // public function store(Request $request)
    // {
    //     if (Auth::user()->can('create-plan')) {
    //         request()->validate([
    //             'name'           => 'required|unique:plans,name|max:50',
    //             'price'          => 'required|numeric|min:0',
    //             'price_quarter'  => 'required|numeric|min:0',
    //             'price_year'     => 'required|numeric|min:0',
    //             'max_users'      => 'required|integer|min:1',
    //             'lesson_limit'   => 'required|integer',
    //             'description'    => 'nullable|string',
    //         ]);

    //         $influencerId = Auth::user()->type === Role::ROLE_INFLUENCER ? Auth::user()->id : null;
    //         $tenantId     = Auth::user()->type === Role::ROLE_INFLUENCER ? tenant()->id : null;

    //         // Optional: Check for existing plans with same chat/feed settings
    //         // if ($influencerId) {
    //         //     $exists = Plan::where('influencer_id', $influencerId)
    //         //         ->where('is_chat_enabled', $request->chat == '1' ? 1 : 0)
    //         //         ->where('is_feed_enabled', $request->feed == '1' ? 1 : 0)
    //         //         ->exists();
    //         // 
    //         //     if ($exists) {
    //         //         return redirect()->route('plans.myplan')->with('failed', __('You already have a plan with the same chat and feed settings.'));
    //         //     }
    //         // }

    //         $user = Auth::user();

    //         // if (empty($user->stripe_account_id)) {
    //         //     return redirect()->route('plans.myplan')
    //         //         ->with('failed', __('Please first connect your Stripe account.'));
    //         // }

    //         $instructor = $influencerId ? User::find($influencerId) : null;
    //         $stripeAccountId = $instructor->stripe_account_id ?? null;

    //         Stripe::setApiKey(config('services.stripe.secret'));

    //         // Create Stripe product
    //         $product = Product::create([
    //             'name' => $request->name,
    //             'description' => $request->description ?? '',
    //         ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

    //         // Function to calculate price with Stripe fee recovery
    //         $calculatePriceWithFee = function ($amount, $instructor) {
    //             $convertedAmount = $amount;

    //             if ($instructor && $instructor->stripe_transaction_fee != 'instructor') {
    //                 $stripePerc = 0.029;  // 2.9%
    //                 $stripeFixed = 0.30;  // $0.30
    //                 $gross = ($convertedAmount + $stripeFixed) / (1 - $stripePerc);
    //                 $convertedAmount = round($gross, 2);
    //             }

    //             return round($convertedAmount * 100); // Convert to cents
    //         };

    //         // Create Monthly Price
    //         $monthlyAmount = $calculatePriceWithFee($request->price, $instructor);
    //         $monthlyPrice = Price::create([
    //             'unit_amount' => $monthlyAmount,
    //             'currency' => 'usd',
    //             'recurring' => [
    //                 'interval' => 'month',
    //                 'interval_count' => 1,
    //             ],
    //             'product' => $product->id,
    //         ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

    //         // Create Quarterly Price
    //         $quarterlyAmount = $calculatePriceWithFee($request->price_quarter, $instructor);
    //         $quarterlyPrice = Price::create([
    //             'unit_amount' => $quarterlyAmount,
    //             'currency' => 'usd',
    //             'recurring' => [
    //                 'interval' => 'month',
    //                 'interval_count' => 3,
    //             ],
    //             'product' => $product->id,
    //         ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

    //         // Create Yearly Price
    //         $yearlyAmount = $calculatePriceWithFee($request->price_year, $instructor);
    //         $yearlyPrice = Price::create([
    //             'unit_amount' => $yearlyAmount,
    //             'currency' => 'usd',
    //             'recurring' => [
    //                 'interval' => 'year',
    //                 'interval_count' => 1,
    //             ],
    //             'product' => $product->id,
    //         ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

    //         // Save centralized Stripe data if needed
    //         $this->saveCentralizedStripeData($stripeAccountId, tenant()->id);

    //         // Create Plan record
    //         Plan::create([
    //             'name'                 => $request->name,
    //             'price'                => $request->price,
    //             'price_quarter'        => $request->price_quarter,
    //             'price_year'           => $request->price_year,
    //             'tenant_id'            => $tenantId,
    //             'max_users'            => $request->max_users,
    //             'description'          => $request->description ?? '',
    //             'is_chat_enabled'      => $request->chat == '1' ? 1 : 0,
    //             'is_feed_enabled'      => $request->feed == '1' ? 1 : 0,
    //             'influencer_id'        => $influencerId,
    //             'stripe_product_id'    => $product->id,
    //             'stripe_price_id'      => $monthlyPrice->id,       // Default monthly price
    //             'stripe_price_quarter_id' => $quarterlyPrice->id, // Quarterly price
    //             'stripe_price_year_id' => $yearlyPrice->id,       // Yearly price
    //             'lesson_limit'         => $request->lesson_limit,
    //         ]);

    //         return redirect()->route('plans.myplan')->with('success', __('Plan created successfully.'));
    //     } else {
    //         return redirect()->back()->with('failed', __('Permission denied.'));
    //     }
    // }

    public function store(Request $request)
    {
        if (Auth::user()->can('create-plan')) {
            request()->validate([
                'name'           => 'required|unique:plans,name|max:50',
                'price'          => 'nullable|numeric|min:0',
                'price_quarter'  => 'nullable|numeric|min:0',
                'price_year'     => 'nullable|numeric|min:0',
                'max_users'      => 'required|integer|min:1',
                'lesson_limit'   => 'required|integer',
                'description'    => 'required|string',
            ]);

            // Add custom validation to ensure at least one price is provided
            $validator = Validator::make($request->all(), []);

            $validator->after(function ($validator) use ($request) {
                // Check if all prices are empty
                if (empty($request->price) && empty($request->price_quarter) && empty($request->price_year)) {
                    $validator->errors()->add(
                        'price',
                        __('At least one price (monthly, quarterly, or yearly) must be provided.')
                    );
                    $validator->errors()->add(
                        'price_quarter',
                        __('At least one price (monthly, quarterly, or yearly) must be provided.')
                    );
                    $validator->errors()->add(
                        'price_year',
                        __('At least one price (monthly, quarterly, or yearly) must be provided.')
                    );
                }
            });

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $instructorId = Auth::user()->type === Role::ROLE_INFLUENCER ? Auth::user()->id : null;
            $tenantId     = Auth::user()->type === Role::ROLE_INFLUENCER ? tenant()->id : null;

            // Get application fee settings
            $tenantId = tenancy()->tenant->id;
            tenancy()->central(function () use (&$application_fee_percentage, &$application_currency, $tenantId) {
                $userData = User::where('tenant_id', $tenantId)
                    ->select('application_fee_percentage', 'currency')
                    ->first();
                $application_fee_percentage = $userData?->application_fee_percentage;
                $application_currency = $userData?->currency ?? 'usd';
            });

            $instructor = $instructorId ? User::find($instructorId) : null;
            $stripeAccountId = $instructor->stripe_account_id ?? null;

            // Check if instructor has Stripe account
            if ($instructorId && empty($stripeAccountId)) {
                return redirect()->route('plans.myplan')
                    ->with('failed', __('Please first connect your Stripe account.'));
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            // Create Stripe product
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description ?? '',
            ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

            // Function to calculate price with Stripe fee recovery and application fee
            $calculatePriceWithFees = function ($amount, $instructor) use ($application_fee_percentage) {
                $convertedAmount = $amount;

                // Add Stripe fee recovery if not paid by instructor
                if ($instructor && $instructor->stripe_transaction_fee != 'instructor') {
                    $stripePerc = 0.029;  // 2.9%
                    $stripeFixed = 0.30;  // $0.30
                    $gross = ($convertedAmount + $stripeFixed) / (1 - $stripePerc);
                    $convertedAmount = round($gross, 2);
                }

                // Add application fee if not paid by instructor
                if ($instructor && $instructor->stripe_tuneup_percentage_fee != 'instructor') {
                    $applicationFeeAmount = round(($application_fee_percentage / 100) * $convertedAmount, 2);
                    $convertedAmount += $applicationFeeAmount;
                }

                return round($convertedAmount * 100); // Convert to cents
            };

            // Get currency from tenant settings
            $currency = 'usd';

            // Create Monthly Price (always required)
            $monthlyPrice = null;
            if ($request->has('price') && $request->price > 0) {
                $monthlyAmount = $calculatePriceWithFees($request->price, $instructor);
                $monthlyPrice = Price::create([
                    'unit_amount' => $monthlyAmount,
                    'currency' => $currency,
                    'recurring' => [
                        'interval' => 'month',
                        'interval_count' => 1,
                    ],
                    'product' => $product->id,
                ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);
            }

            // Create Quarterly Price if provided
            $quarterlyPrice = null;
            if ($request->has('price_quarter') && $request->price_quarter > 0) {
                $quarterlyAmount = $calculatePriceWithFees($request->price_quarter, $instructor);
                $quarterlyPrice = Price::create([
                    'unit_amount' => $quarterlyAmount,
                    'currency' => $currency,
                    'recurring' => [
                        'interval' => 'month',
                        'interval_count' => 3,
                    ],
                    'product' => $product->id,
                ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);
            }

            // Create Yearly Price if provided
            $yearlyPrice = null;
            if ($request->has('price_year') && $request->price_year > 0) {
                $yearlyAmount = $calculatePriceWithFees($request->price_year, $instructor);
                $yearlyPrice = Price::create([
                    'unit_amount' => $yearlyAmount,
                    'currency' => $currency,
                    'recurring' => [
                        'interval' => 'year',
                        'interval_count' => 1,
                    ],
                    'product' => $product->id,
                ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);
            }

            // Save centralized Stripe data
            $this->saveCentralizedStripeData($stripeAccountId, tenant()->id);

            // Create Plan record
            Plan::create([
                'name'                     => $request->name,
                'price'                    => $request->price,
                'price_quarter'            => $request->price_quarter,
                'price_year'               => $request->price_year,
                'tenant_id'                => $tenantId,
                'max_users'                => $request->max_users,
                // 'description'              => $request->description ?? '',
                'description'              => $_POST['description'] ?? '',
                'is_chat_enabled'          => $request->chat == '1' ? 1 : 0,
                'is_feed_enabled'          => $request->feed == '1' ? 1 : 0,
                'influencer_id'            => $instructorId,
                'stripe_product_id'        => $product->id,
                'stripe_price_id'          => $monthlyPrice?->id,
                'stripe_price_quarter_id'  => $quarterlyPrice?->id,
                'stripe_price_year_id'     => $yearlyPrice?->id,
                'lesson_limit'             => $request->lesson_limit,
                // 'currency'                 => $currency,
            ]);

            return redirect()->route('plans.myplan')->with('success', __('Plan created successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }


    private function saveCentralizedStripeData($stripeAccountId, $tenant_id)
    {
        tenancy()->central(function () use ($stripeAccountId, $tenant_id) {
            $exists = StripeConnectedAccount::where('stripe_account_id', $stripeAccountId)
                ->where('tenant_id', $tenant_id)
                ->exists();

            if (! $exists) {
                StripeConnectedAccount::create([
                    'tenant_id' => $tenant_id,
                    'stripe_account_id' => $stripeAccountId,
                ]);
            }
        });
    }

    public function editMyplan($id)
    {
        if (Auth::user()->can('edit-plan')) {
            $plan = Plan::find($id);
            return view('admin.plans.edit', compact('plan'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }


    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit-plan')) {
            request()->validate([
                'name'           => 'required|max:50',
                'price'          => 'nullable|numeric|min:0',
                'price_quarter'  => 'nullable|numeric|min:0',
                'price_year'     => 'nullable|numeric|min:0',
                'max_users'      => 'required|integer|min:1',
                'lesson_limit'   => 'required|integer',
                'description'    => 'nullable|string',
            ]);

            // Add custom validation to ensure at least one price is provided
            $validator = Validator::make($request->all(), []);

            $validator->after(function ($validator) use ($request) {
                // Check if all prices are empty
                if (empty($request->price) && empty($request->price_quarter) && empty($request->price_year)) {
                    $validator->errors()->add(
                        'price',
                        __('At least one price (monthly, quarterly, or yearly) must be provided.')
                    );
                    $validator->errors()->add(
                        'price_quarter',
                        __('At least one price (monthly, quarterly, or yearly) must be provided.')
                    );
                    $validator->errors()->add(
                        'price_year',
                        __('At least one price (monthly, quarterly, or yearly) must be provided.')
                    );
                }
            });

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }


            $plan = Plan::findOrFail($id);

            $instructorId = Auth::user()->type === Role::ROLE_INFLUENCER ? Auth::user()->id : null;
            $tenantId     = Auth::user()->type === Role::ROLE_INFLUENCER ? tenant()->id : null;

            // Get application fee settings
            $tenantId = tenancy()->tenant->id;
            tenancy()->central(function () use (&$application_fee_percentage, &$application_currency, $tenantId) {
                $userData = User::where('tenant_id', $tenantId)
                    ->select('application_fee_percentage', 'currency')
                    ->first();
                $application_fee_percentage = $userData?->application_fee_percentage;
                $application_currency = $userData?->currency ?? 'usd';
            });

            $instructor = $instructorId ? User::find($instructorId) : null;
            $stripeAccountId = $instructor->stripe_account_id ?? null;

            // Check if instructor has Stripe account
            if ($instructorId && empty($stripeAccountId)) {
                return redirect()->route('plans.myplan')
                    ->with('failed', __('Please first connect your Stripe account.'));
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                // Function to calculate price with Stripe fee recovery and application fee
                $calculatePriceWithFees = function ($amount, $instructor) use ($application_fee_percentage) {
                    $convertedAmount = $amount;

                    // Add Stripe fee recovery if not paid by instructor
                    if ($instructor && $instructor->stripe_transaction_fee != 'instructor') {
                        $stripePerc = 0.029;  // 2.9%
                        $stripeFixed = 0.30;  // $0.30
                        $gross = ($convertedAmount + $stripeFixed) / (1 - $stripePerc);
                        $convertedAmount = round($gross, 2);
                    }

                    // Add application fee if not paid by instructor
                    if ($instructor && $instructor->stripe_tuneup_percentage_fee != 'instructor') {
                        $applicationFeeAmount = round(($application_fee_percentage / 100) * $convertedAmount, 2);
                        $convertedAmount += $applicationFeeAmount;
                    }

                    return round($convertedAmount * 100); // Convert to cents
                };

                // Get currency from tenant settings
                $currency = 'usd';

                /**
                 * 1️⃣ Update or Create Stripe Product
                 */
                if ($plan->stripe_product_id) {
                    $product = Product::update(
                        $plan->stripe_product_id,
                        [
                            'name' => $request->name,
                            'description' => $request->description ?? '',
                        ],
                        $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                    );
                } else {
                    $product = Product::create(
                        [
                            'name' => $request->name,
                            'description' => $request->description ?? '',
                        ],
                        $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                    );
                    $plan->stripe_product_id = $product->id;
                }

                /**
                 * 2️⃣ Create new Stripe Prices (old prices are archived automatically)
                 */

                // Update Monthly Price (always required)
                if ($plan->stripe_price_id) {
                    // Archive old monthly price
                    // try {
                    //     Price::update(
                    //         $plan->stripe_price_id,
                    //         ['active' => false],
                    //         $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                    //     );
                    // } catch (\Exception $e) {
                    //     // Price might already be archived or doesn't exist
                    // }
                }

                if ($request->has('price_quarter') && $request->price_quarter > 0) {

                    // Create new monthly price
                    $monthlyAmount = $calculatePriceWithFees($request->price, $instructor);
                    $monthlyPrice = Price::create([
                        'unit_amount' => $monthlyAmount,
                        'currency' => $currency,
                        'recurring' => [
                            'interval' => 'month',
                            'interval_count' => 1,
                        ],
                        'product' => $plan->stripe_product_id,
                    ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

                    $plan->stripe_price_id = $monthlyPrice->id;
                }

                // Update Quarterly Price if provided
                if ($request->has('price_quarter') && $request->price_quarter > 0) {
                    if ($plan->stripe_price_quarter_id) {
                        // Archive old quarterly price
                        // try {
                        //     Price::update(
                        //         $plan->stripe_price_quarter_id,
                        //         ['active' => false],
                        //         $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                        //     );
                        // } catch (\Exception $e) {
                        //     // Price might already be archived or doesn't exist
                        // }
                    }

                    // Create new quarterly price
                    $quarterlyAmount = $calculatePriceWithFees($request->price_quarter, $instructor);
                    $quarterlyPrice = Price::create([
                        'unit_amount' => $quarterlyAmount,
                        'currency' => $currency,
                        'recurring' => [
                            'interval' => 'month',
                            'interval_count' => 3,
                        ],
                        'product' => $plan->stripe_product_id,
                    ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

                    $plan->stripe_price_quarter_id = $quarterlyPrice->id;
                } else {
                    // If quarterly price is removed, archive existing one
                    if ($plan->stripe_price_quarter_id) {
                        // try {
                        //     Price::update(
                        //         $plan->stripe_price_quarter_id,
                        //         ['active' => false],
                        //         $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                        //     );
                        // } catch (\Exception $e) {
                        //     // Price might already be archived or doesn't exist
                        // }
                        // $plan->stripe_price_quarter_id = null;
                    }
                }

                // Update Yearly Price if provided
                if ($request->has('price_year') && $request->price_year > 0) {
                    // if ($plan->stripe_price_year_id) {
                    //     // Archive old yearly price
                    //     try {
                    //         Price::update(
                    //             $plan->stripe_price_year_id,
                    //             ['active' => false],
                    //             $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                    //         );
                    //     } catch (\Exception $e) {
                    //         // Price might already be archived or doesn't exist
                    //     }
                    // }

                    // Create new yearly price
                    $yearlyAmount = $calculatePriceWithFees($request->price_year, $instructor);
                    $yearlyPrice = Price::create([
                        'unit_amount' => $yearlyAmount,
                        'currency' => $currency,
                        'recurring' => [
                            'interval' => 'year',
                            'interval_count' => 1,
                        ],
                        'product' => $plan->stripe_product_id,
                    ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

                    $plan->stripe_price_year_id = $yearlyPrice->id;
                } else {
                    // If yearly price is removed, archive existing one
                    // if ($plan->stripe_price_year_id) {
                    //     try {
                    //         Price::update(
                    //             $plan->stripe_price_year_id,
                    //             ['active' => false],
                    //             $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                    //         );
                    //     } catch (\Exception $e) {
                    //         // Price might already be archived or doesn't exist
                    //     }
                    //     $plan->stripe_price_year_id = null;
                    // }
                }

                // Save centralized Stripe data
                $this->saveCentralizedStripeData($stripeAccountId, tenant()->id);

                /**
                 * 3️⃣ Update Local Plan Data
                 */
                $plan->name                     = $request->name;
                $plan->price                    = $request->price;
                $plan->price_quarter            = $request->price_quarter;
                $plan->price_year               = $request->price_year;
                $plan->max_users                = $request->max_users;
                // $plan->description              = $request->description ?? '';
                $plan->description              = $_POST["description"] ?? '';
                $plan->is_chat_enabled          = $request->chat == '1' ? 1 : 0;
                $plan->is_feed_enabled          = $request->feed == '1' ? 1 : 0;
                $plan->tenant_id                = $tenantId;
                $plan->influencer_id            = $instructorId;
                $plan->lesson_limit             = $request->lesson_limit;
                // $plan->currency                 = $currency;

                $plan->save();

                return redirect()->route('plans.myplan')->with('success', __('Plan updated successfully.'));
            } catch (\Exception $e) {
                return redirect()->back()->with('failed', __('Stripe Error: ') . $e->getMessage());
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    // public function update(Request $request, $id)
    // {
    //     $user = Auth::user();

    //     // Check if user has Stripe account (only for Influencers)
    //     if (Auth::user()->type == 'Influencer' && empty($user->stripe_account_id)) {
    //         return redirect()->route('plans.myplan')
    //             ->with('failed', __('Please first connect your Stripe account.'));
    //     }

    //     if (Auth::user()->can('edit-plan')) {
    //         request()->validate([
    //             'name'           => 'required|max:50|unique:plans,name,' . $id,
    //             'price'          => 'required|numeric|min:0',
    //             'price_quarter'  => 'required|numeric|min:0',
    //             'price_year'     => 'required|numeric|min:0',
    //             'max_users'      => 'required|integer|min:1',
    //             'lesson_limit'   => 'required|integer',
    //             'description'    => 'nullable|string',
    //         ]);

    //         $plan = Plan::find($id);

    //         // Determine influencer ID if applicable
    //         $influencerId = Auth::user()->type === Role::ROLE_INFLUENCER ? Auth::user()->id : null;
    //         $instructor = $influencerId ? User::find($influencerId) : null;
    //         $stripeAccountId = $instructor->stripe_account_id ?? null;

    //         // Initialize Stripe
    //         Stripe::setApiKey(config('services.stripe.secret'));

    //         try {
    //             /**
    //              * 1️⃣ Update Stripe Product
    //              */
    //             if ($plan->stripe_product_id) {
    //                 $product = Product::update(
    //                     $plan->stripe_product_id,
    //                     [
    //                         'name' => $request->name,
    //                         'description' => $request->description ?? '',
    //                     ],
    //                     $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
    //                 );
    //             } else {
    //                 // This should not happen, but handle it just in case
    //                 $product = Product::create(
    //                     [
    //                         'name' => $request->name,
    //                         'description' => $request->description ?? '',
    //                     ],
    //                     $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
    //                 );
    //                 $plan->stripe_product_id = $product->id;
    //             }

    //             /**
    //              * 2️⃣ Function to calculate price with Stripe fee recovery
    //              */
    //             $calculatePriceWithFee = function ($amount, $instructor) {
    //                 $convertedAmount = $amount;

    //                 if ($instructor && $instructor->stripe_transaction_fee != 'instructor') {
    //                     $stripePerc = 0.029;  // 2.9%
    //                     $stripeFixed = 0.30;  // $0.30
    //                     $gross = ($convertedAmount + $stripeFixed) / (1 - $stripePerc);
    //                     $convertedAmount = round($gross, 2);
    //                 }

    //                 return round($convertedAmount * 100); // Convert to cents
    //             };

    //             /**
    //              * 3️⃣ Archive old prices and create new ones
    //              */
    //             // Monthly Price
    //             if ($plan->stripe_price_id) {
    //                 // Archive old monthly price
    //                 try {
    //                     $oldPrice = Price::update(
    //                         $plan->stripe_price_id,
    //                         ['active' => false],
    //                         $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
    //                     );
    //                 } catch (\Exception $e) {
    //                     // Price might not exist, continue anyway
    //                 }
    //             }

    //             $monthlyAmount = $calculatePriceWithFee($request->price, $instructor);
    //             $monthlyPrice = Price::create([
    //                 'unit_amount' => $monthlyAmount,
    //                 'currency' => 'usd',
    //                 'recurring' => [
    //                     'interval' => 'month',
    //                     'interval_count' => 1,
    //                 ],
    //                 'product' => $plan->stripe_product_id,
    //             ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

    //             // Quarterly Price
    //             if ($plan->stripe_price_quarter_id) {
    //                 // Archive old quarterly price
    //                 try {
    //                     $oldQuarterlyPrice = Price::update(
    //                         $plan->stripe_price_quarter_id,
    //                         ['active' => false],
    //                         $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
    //                     );
    //                 } catch (\Exception $e) {
    //                     // Price might not exist, continue anyway
    //                 }
    //             }

    //             $quarterlyAmount = $calculatePriceWithFee($request->price_quarter, $instructor);
    //             $quarterlyPrice = Price::create([
    //                 'unit_amount' => $quarterlyAmount,
    //                 'currency' => 'usd',
    //                 'recurring' => [
    //                     'interval' => 'month',
    //                     'interval_count' => 3,
    //                 ],
    //                 'product' => $plan->stripe_product_id,
    //             ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

    //             // Yearly Price
    //             if ($plan->stripe_price_year_id) {
    //                 // Archive old yearly price
    //                 try {
    //                     $oldYearlyPrice = Price::update(
    //                         $plan->stripe_price_year_id,
    //                         ['active' => false],
    //                         $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
    //                     );
    //                 } catch (\Exception $e) {
    //                     // Price might not exist, continue anyway
    //                 }
    //             }

    //             $yearlyAmount = $calculatePriceWithFee($request->price_year, $instructor);
    //             $yearlyPrice = Price::create([
    //                 'unit_amount' => $yearlyAmount,
    //                 'currency' => 'usd',
    //                 'recurring' => [
    //                     'interval' => 'year',
    //                     'interval_count' => 1,
    //                 ],
    //                 'product' => $plan->stripe_product_id,
    //             ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

    //             /**
    //              * 4️⃣ Update Plan record
    //              */
    //             $plan->name = $request->name;
    //             $plan->price = $request->price;
    //             $plan->price_quarter = $request->price_quarter;
    //             $plan->price_year = $request->price_year;
    //             $plan->max_users = $request->max_users;
    //             $plan->description = $request->description ?? '';
    //             $plan->is_chat_enabled = $request->chat == '1' ? 1 : 0;
    //             $plan->is_feed_enabled = $request->feed == '1' ? 1 : 0;
    //             $plan->lesson_limit = $request->lesson_limit;
    //             $plan->stripe_price_id = $monthlyPrice->id;
    //             $plan->stripe_price_quarter_id = $quarterlyPrice->id;
    //             $plan->stripe_price_year_id = $yearlyPrice->id;

    //             // For Super Admin or non-influencer users
    //             if (Auth::user()->type != 'Influencer') {
    //                 $plan->tenant_id = tenant()->id;
    //                 $plan->influencer_id = null;
    //             }

    //             $plan->save();

    //             // Save centralized Stripe data if needed (for influencers)
    //             if ($influencerId) {
    //                 $this->saveCentralizedStripeData($stripeAccountId, tenant()->id);
    //             }

    //             return redirect()->route('plans.myplan')->with('success', __('Plan updated successfully.'));
    //         } catch (\Exception $e) {
    //             return redirect()->back()
    //                 ->with('failed', __('Stripe Error: ') . $e->getMessage())
    //                 ->withInput();
    //         }
    //     } else {
    //         return redirect()->back()->with('failed', __('Permission denied.'));
    //     }
    // }

    // public function update(Request $request, $id)
    // {
    //     $user  = Auth::user();
    //     if (empty($user->stripe_account_id)) {
    //         return redirect()->route('plans.myplan')
    //             ->with('failed', __('Please first connect your Stripe account.'));
    //     }
    //     if (Auth::user()->can('edit-plan')) {
    //         // if (Auth::user()->type == 'Super Admin') {
    //         //     request()->validate([
    //         //         'name'        => 'required|max:50|unique:plans,name,' . $id,
    //         //         'price'       => 'required',
    //         //         'duration'    => 'required',
    //         //         'description' => 'max:100',
    //         //     ]);
    //         //     $plan               = Plan::find($id);
    //         //     $serviceplane = SubscriptionService::updateStripePlan($request, $user, $plan);
    //         //     $plan->name         = $request->input('name');
    //         //     $plan->price        = $request->input('price');
    //         //     $plan->duration     = $request->input('duration');
    //         //     $plan->durationtype = $request->input('durationtype');
    //         //     $plan->description  = $request->input('description');
    //         //     $plan->stripe_product_id = $serviceplane['product_id'];
    //         //     $plan->stripe_price_id = $serviceplane['price_id'];
    //         //     $plan->save();
    //         // } else {


    //         request()->validate([
    //             'name'      => 'required|max:50|unique:plans,name,' . $id,
    //             'price'     => 'required',
    //             'duration'  => 'required',
    //             'max_users' => 'required',
    //             'lesson_limit' => 'required|integer',

    //         ]);
    //         $plan                  = Plan::find($id);

    //         $instructorId = Auth::user()->type === Role::ROLE_INFLUENCER ? Auth::user()->id : null;


    //         $instructor = $instructorId ? User::find($instructorId) : null;
    //         $stripeAccountId = $instructor->stripe_account_id ?? null;

    //         // 🔹 Initialize Stripe
    //         Stripe::setApiKey(config('services.stripe.secret'));

    //         try {
    //             /**
    //              * 1️⃣ Update or Create Stripe Product
    //              */
    //             if ($plan->stripe_product_id) {
    //                 $product = Product::update(
    //                     $plan->stripe_product_id,
    //                     [
    //                         'name' => $request->name,
    //                         'description' => $request->description,
    //                     ],
    //                     $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
    //                 );
    //             } else {
    //                 $product = Product::create(
    //                     [
    //                         'name' => $request->name,
    //                         'description' => $request->description,
    //                     ],
    //                     $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
    //                 );
    //                 $plan->stripe_product_id = $product->id;
    //             }


    //             $convertedAmount = $request->price;
    //             if ($instructor?->stripe_transaction_fee != 'instructor') {
    //                 // 🎯 Add Stripe fee recovery here
    //                 $stripePerc = 0.029;       // 2.9%
    //                 $stripeFixed = 0.30;         // $0.30 → 30 cents
    //                 $gross = ($convertedAmount + $stripeFixed) / (1 - $stripePerc);
    //                 $convertedAmount = round($gross);
    //             }

    //             $interval = 'month';
    //             $interval_count = 1;

    //             if (strtolower($request->durationtype) == 'quarter') {
    //                 $interval_count = 3;
    //             } elseif (strtolower($request->durationtype) == 'year') {
    //                 $interval = 'year';
    //             }


    //             $price = Price::create(
    //                 [
    //                     'unit_amount' => round($convertedAmount * 100),
    //                     'currency' => 'usd',
    //                     'recurring' => [
    //                         'interval' => $interval,
    //                         'interval_count' => $interval_count,
    //                     ],
    //                     'product' => $plan->stripe_product_id,
    //                 ],
    //                 $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
    //             );

    //             $plan->stripe_price_id = $price->id;
    //         } catch (\Exception $e) {
    //             return redirect()->back()->with('failed', __('Stripe Error: ') . $e->getMessage());
    //         }

    //         $plan->name            = $request->input('name');
    //         $plan->price           = $request->input('price');
    //         // $plan->duration        = $request->input('duration');
    //         $plan->durationtype    = $request->input('durationtype');
    //         $plan->max_users       = $request->input('max_users');
    //         $plan->description     = $_POST['description'];
    //         $plan->is_chat_enabled = $request->input('chat') ? true : false;
    //         $plan->is_feed_enabled = $request->input('feed') ? true : false;
    //         $plan->lesson_limit    = $request->lesson_limit;
    //         // $plan->stripe_product_id = $serviceplane['product_id'];
    //         // $plan->stripe_price_id = $serviceplane['price_id'];
    //         $plan->save();
    //         // }
    //         return redirect()->route('plans.myplan')->with('success', __('Plan updated successfully.'));
    //     } else {
    //         return redirect()->back()->with('failed', __('Permission denied.'));
    //     }
    // }

    public function destroy($id)
    {
        $user  = Auth::user();
        if (empty($user->stripe_account_id)) {
            return redirect()->route('plans.myplan')
                ->with('failed', __('Please first connect your Stripe account.'));
        }
        if (Auth::user()->can('delete-plan')) {
            $plan = Plan::find($id);
            $serviceplane = SubscriptionService::deleteStripePlan($user, $plan);

            if ($plan->id != 1) {
                $planExistInOrder = Order::where('plan_id', $plan->id)->first();
                if (empty($planExistInOrder)) {
                    $plan->delete();
                    return redirect()->route('plans.myplan')->with('success', __('Plan deleted successfully.'));
                } else {
                    return redirect()->back()->with('failed', __('Can not delete this plan because its purchased by users.'));
                }
            } else {
                return redirect()->back()->with('failed', __('Can not delete this plan because its free plan.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function planStatus(Request $request, $id)
    {
        $plan       = Plan::find($id);
        $planStatus = ($request->value == "true") ? 1 : 0;
        if ($plan) {
            $plan->active_status = $planStatus;
            $plan->save();
        }
        return response()->json([
            'is_success' => true,
            'message'    => __('Plan status changed successfully.'),
        ]);
    }

    public function payment($code, $subscription)
    {
        // $plan_id = \Illuminate\Support\Facades\Crypt::decrypt($code);
        $plan_id = $code;




        if (Auth::user()->type == 'Admin') {
            $plan = tenancy()->central(function ($tenant) use ($plan_id) {
                return Plan::find($plan_id);
            });
            $paymentTypes = tenancy()->central(function ($tenant) {
                return UtilityFacades::getpaymenttypes();
            });
            $adminPaymentSetting = UtilityFacades::getadminplansetting();
        } else {
            $plan                = Plan::find($plan_id);
            $paymentTypes        = UtilityFacades::getpaymenttypes();
            $adminPaymentSetting = UtilityFacades::getplansetting();
            $default_sub = [];

            $default_sub[$plan->stripe_price_id] = [
                'duration' => 'monthly',
                'price' => $plan->price,
                'key' => $plan->stripe_price_id
            ];

            $default_sub[$plan->stripe_price_quarter_id] = [
                'duration' => 'quarterly',
                'price' => $plan->price_quarter,
                'key' => $plan->stripe_price_id
            ];

            $default_sub[$plan->stripe_price_year_id] = [
                'duration' => 'yearly',
                'price' => $plan->price_year,
                'key' => $plan->stripe_price_year_id
            ];
        }
        // Overriding as curent stripe is handle through env
        $paymentTypes["stripe"] =  "Stripe";
        $adminPaymentSetting["stripesetting"] =  "on";
        $adminPaymentSetting["stripe_key"] =  config('services.stripe.key');
        $adminPaymentSetting["stripe_secret"] =  config('services.stripe.secret');
        $adminPaymentSetting["stripe_description"] =  "";
        if ($plan) {
            return view('admin.plans.payment', compact('plan', 'adminPaymentSetting', 'paymentTypes', 'default_sub', 'subscription'));
        } else {
            return redirect()->back()->with('errors', __('Plan deleted successfully.'));
        }
    }

    /**
     * Return buyers for a given plan (followers who currently have or had the plan).
     *
     * @param int $planId
     * @return \Illuminate\Http\JsonResponse
     */
    public function buyers($planId)
    {
        $this->authorize('manage-plan');

        $plan = Plan::with(['orders' => function ($q) use ($planId) {
            $q->with(['orderFollower:id,name,email,plan_expired_date'])
                ->whereHas('orderFollower', function ($qq) use ($planId) {
                    $qq->whereNotNull('plan_expired_date')
                        ->whereDate('plan_expired_date', '>=', now()->toDateString())
                        ->where('plan_id', $planId);
                });
        }])->findOrFail($planId);

        $buyers = $plan->orders
            ->map(function ($order) {
                $f = $order->orderFollower;
                if (!$f) {
                    return null;
                }
                return [
                    'id' => $f->id,
                    'name' => $f->name,
                    'email' => $f->email,
                    'plan_expired_date' => date('Y-m-d', strtotime($f->plan_expired_date)),
                ];
            })
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        return response()->json(['data' => $buyers]);
    }

    public function cancelPlan($encrptedPlanid)
    {
        // Plan id
        $plan_id  = \Illuminate\Support\Facades\Crypt::decrypt($encrptedPlanid);

        // Student can only cancel at the moment
        if (!(auth('follower')->user())) {
            return redirect()->back()->with('failed', 'Unauthorized');
        }

        // user id
        $user_id = auth('follower')->user()->id;

        // Student Subscription
        $student_subscription = ClientSubscription::where('plan_id', $plan_id)->where('follower_id', $user_id)->latest()->first();

        // dd($student_subscription);
        // Subscription Check
        if (!$student_subscription) {
            Log::error("Plan id: " . $plan_id . " User id: " . $user_id . " subscription not found");
            return redirect()->back()->with('failed', 'Something went wrong');
        }

        // 🔹 Initialize Stripe for the connected account
        Stripe::setApiKey(config('services.stripe.secret')); // your platform secret key

        // tenant_id likely corresponds to the connected account ID
        $influencer_id = User::find($student_subscription->influencer_id);
        // dd($instructor_id, $student_subscription->instructor_id, );
        $connectedAccountId = $influencer_id->stripe_account_id;

        try {
            $stripeSubscription = StripeSubscription::retrieve(
                $student_subscription->stripe_subscription_id,
                ['stripe_account' => $connectedAccountId]
            );

            // Cancel immediately (no waiting for end of period)
            $stripeSubscription->cancel(
                ['invoice_now' => true, 'prorate' => false],
                ['stripe_account' => $connectedAccountId]
            );
        } catch (\Exception $stripeError) {
            Log::error("Stripe cancellation failed for connected account {$connectedAccountId}: " . $stripeError->getMessage());
            return redirect()->back()->with('failed', 'Unable to cancel subscription on Stripe.');
        }

        // 🔹 Update your local database
        $student_subscription->update([
            'status' => 'cancelled',
        ]);

        Follower::find($user_id)->update(['plan_id' => null]);

        return redirect()->back()->with('success', 'Subscription cancelled successfully.');
    }
}
