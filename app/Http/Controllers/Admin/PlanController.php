<?php

namespace App\Http\Controllers\Admin;

use Stripe\Price;
use Stripe\Stripe;
use Stripe\Product;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Facades\UtilityFacades;
use App\Models\ClientSubscription;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\SubscriptionService;
use App\Models\StripeConnectedAccount;
use App\DataTables\Admin\PlanDataTable;
use App\Models\Follower;
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

    public function myPlan(PlanDataTable $dataTable)
    {
        if (Auth::user()->can('manage-plan')) {
            if (Auth::user()->type != 'Follower') {
                return $dataTable->render('admin.plans.my-plans');
            } else {
                $plans = Plan::where('tenant_id', null)->get();
                $user  = User::where('tenant_id', tenant('id'))->where('type', 'Admin')->first();
                return view('admin.plans.index', compact('user', 'plans'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
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

    public function store(Request $request)
    {

        if (Auth::user()->can('create-plan')) {
            request()->validate([
                'name'         => 'required|unique:plans,name|max:50',
                'price'        => 'required',
                'duration'     => 'required',
                'durationtype' => 'required',
                'max_users'    => 'required',
                'lesson_limit' => 'required|integer',
            ]);
            $paymentTypes = UtilityFacades::getpaymenttypes();

            // if (! $paymentTypes) {
            //     return redirect()->back()->with('errors', __('Please select at least one payment type from Settings > Payment Settings.'))->withInput();
            // }

            $influencerId = Auth::user()->type === Role::ROLE_INFLUENCER ? Auth::user()->id : null;
            $tenantId     = Auth::user()->type === Role::ROLE_INFLUENCER ? tenant()->id : null;

            if ($influencerId) {
                $exists = Plan::where('influencer_id', $influencerId)
                    ->where('is_chat_enabled', $request->chat == '1' ? 1 : 0)
                    ->where('is_feed_enabled', $request->feed == '1' ? 1 : 0)
                    ->exists();

                if ($exists) {
                    return redirect()->route('plans.myplan')->with('failed', __('You already have a plan with the same chat and feed settings.'));
                }
            }
            $user  = Auth::user();
            if (empty($user->stripe_account_id)) {
                return redirect()->route('plans.myplan')
                    ->with('failed', __('Please first connect your Stripe account.'));
            }

            $instructor = $influencerId ? User::find($influencerId) : null;

            // $serviceplane = SubscriptionService::createStripePlan($request, $user);
            $stripeAccountId = $instructor->stripe_account_id ?? null;

            Stripe::setApiKey(config('services.stripe.secret'));

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
            ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

            // 2️⃣ Create a Recurring Price
            $price = Price::create([
                'unit_amount' => round($request->price * 100), // Stripe expects cents
                'currency' => 'usd',
                'recurring' => [
                    // 'interval' =>  strtolower($request->durationtype), // "month" or "year"
                    'interval' =>  'month', // "month" or "year"
                ],
                'product' => $product->id,
            ], $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []);

            $this->saveCentralizedStripeData($stripeAccountId, tenant()->id);

            Plan::create([
                'name'            => $request->name,
                'price'           => $request->price,
                'duration'        => $request->duration,
                'durationtype'    => $request->durationtype,
                'tenant_id'       => $tenantId,
                'max_users'       => $request->max_users,
                'description'     => $_POST['description'],
                'is_chat_enabled' => $request->chat == '1' ? 1 : 0,
                'is_feed_enabled' => $request->feed == '1' ? 1 : 0,
                'influencer_id'   => $influencerId,
                // 'stripe_product_id' => $serviceplane['product_id'] ?? null,
                // 'stripe_price_id' => $serviceplane['price_id'] ?? null,
                'stripe_product_id' => $product->id, // store Stripe IDs!
                'stripe_price_id'   => $price->id,
                'lesson_limit' => $request->lesson_limit,
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
        $user  = Auth::user();
        if (empty($user->stripe_account_id)) {
            return redirect()->route('plans.myplan')
                ->with('failed', __('Please first connect your Stripe account.'));
        }
        if (Auth::user()->can('edit-plan')) {
            // if (Auth::user()->type == 'Super Admin') {
            //     request()->validate([
            //         'name'        => 'required|max:50|unique:plans,name,' . $id,
            //         'price'       => 'required',
            //         'duration'    => 'required',
            //         'description' => 'max:100',
            //     ]);
            //     $plan               = Plan::find($id);
            //     $serviceplane = SubscriptionService::updateStripePlan($request, $user, $plan);
            //     $plan->name         = $request->input('name');
            //     $plan->price        = $request->input('price');
            //     $plan->duration     = $request->input('duration');
            //     $plan->durationtype = $request->input('durationtype');
            //     $plan->description  = $request->input('description');
            //     $plan->stripe_product_id = $serviceplane['product_id'];
            //     $plan->stripe_price_id = $serviceplane['price_id'];
            //     $plan->save();
            // } else {


            request()->validate([
                'name'      => 'required|max:50|unique:plans,name,' . $id,
                'price'     => 'required',
                'duration'  => 'required',
                'max_users' => 'required',
                'lesson_limit' => 'required|integer',

            ]);
            $plan                  = Plan::find($id);

            $instructorId = Auth::user()->type === Role::ROLE_INFLUENCER ? Auth::user()->id : null;


            $instructor = $instructorId ? User::find($instructorId) : null;
            $stripeAccountId = $instructor->stripe_account_id ?? null;

            // 🔹 Initialize Stripe
            Stripe::setApiKey(config('services.stripe.secret'));

            try {
                /**
                 * 1️⃣ Update or Create Stripe Product
                 */
                if ($plan->stripe_product_id) {
                    $product = Product::update(
                        $plan->stripe_product_id,
                        [
                            'name' => $request->name,
                            'description' => $request->description,
                        ],
                        $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                    );
                } else {
                    $product = Product::create(
                        [
                            'name' => $request->name,
                            'description' => $request->description,
                        ],
                        $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                    );
                    $plan->stripe_product_id = $product->id;
                }

                $price = Price::create(
                    [
                        'unit_amount' => round($request->price * 100),
                        'currency' => 'usd',
                        'recurring' => [
                            // 'interval' => strtolower($request->durationtype),
                            'interval' => 'month',

                        ],
                        'product' => $plan->stripe_product_id,
                    ],
                    $stripeAccountId ? ['stripe_account' => $stripeAccountId] : []
                );

                $plan->stripe_price_id = $price->id;
            } catch (\Exception $e) {
                return redirect()->back()->with('failed', __('Stripe Error: ') . $e->getMessage());
            }

            $plan->name            = $request->input('name');
            $plan->price           = $request->input('price');
            $plan->duration        = $request->input('duration');
            $plan->durationtype    = $request->input('durationtype');
            $plan->max_users       = $request->input('max_users');
            $plan->description     = $_POST['description'];
            $plan->is_chat_enabled = $request->input('chat') ? true : false;
            $plan->is_feed_enabled = $request->input('feed') ? true : false;
            $plan->lesson_limit    = $request->lesson_limit;
            // $plan->stripe_product_id = $serviceplane['product_id'];
            // $plan->stripe_price_id = $serviceplane['price_id'];
            $plan->save();
            // }
            return redirect()->route('plans.myplan')->with('success', __('Plan updated successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

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

    public function payment($code)
    {
        $plan_id = \Illuminate\Support\Facades\Crypt::decrypt($code);

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
        }

        if ($plan) {
            return view('admin.plans.payment', compact('plan', 'adminPaymentSetting', 'paymentTypes'));
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
