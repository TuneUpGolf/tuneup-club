<?php
namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\SubscriptionsDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Follow;
use App\Models\Role;
use App\Models\Follower;
use App\Models\User;
use Error;
use Exception;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;

class FollowController extends Controller
{
    public function followInstructorApi(Request $request)
    {
        $request->validate([
            'influencer_id' => 'required',
        ]);

        try {

            $instructorId = User::where('type', Role::ROLE_INFLUENCER)->where('id', $request?->influencer_id)->first()?->id;

            if (Follow::where('follower_id', Auth::user()->id)->where('isPaid', Follow::FOLLOW)->where('influencer_id', $instructorId)->exists()) {
                return response()->json(['message' => 'Student already follows this instructor'], 422);
            }

            if (Follow::where('follower_id', Auth::user()->id)->where('isPaid', Follow::SUBSCRIPTION)->where('influencer_id', $instructorId)->exists()) {
                return response()->json(['message' => 'Student is already subscribed to this instructor'], 422);
            }

            Follow::create([
                'follower_id' => Auth::user()->id,
                'influencer_id' => $instructorId,
                'isPaid' => 0,
                'active_status' => 1,
            ]);

            return response()->json(['message' => 'Student is now following the instructor'], 200);
        } catch (Error $e) {
            return response($e, 419);
        }
    }

    public function followInstructor(Request $request)
    {
        $request->validate([
            'influencer_id' => 'required',
        ]);

        try {

            $instructorId = User::where('type', Role::ROLE_INFLUENCER)->where('id', $request?->influencer_id)->first()?->id;

            if ($request?->follow === "follow") {
                Follow::updateOrCreate(
                    [
                        'follower_id'    => Auth::user()->id,
                        'influencer_id' => $instructorId,
                    ],
                    [
                        'active_status' => true,
                        'isPaid'        => false,
                    ]
                );
            } else if ($request?->follow === "unfollow") {
                Follow::updateOrCreate(
                    [
                        'follower_id'    => Auth::user()->id,
                        'influencer_id' => $instructorId,
                    ],
                    [
                        'active_status' => false,
                        'isPaid'        => false,
                    ]
                );
                return redirect()->back()->with('success', __('Instructor successfully unfollowed'));
            }
            return redirect()->back()->with('success', __('Instructor successfully followed'));
        } catch (Error $e) {
            return response($e, 419);
        }
    }

    public function subscribeInst(Request $request)
    {
        $request->validate([
            'influencer_id' => 'required',
        ]);
        try {
            $instructor = User::where('type', Role::ROLE_INFLUENCER)->where('id', $request?->influencer_id)->where('active_status', true)->first();
            if (isset($instructor)) {
                $follow = Follow::firstOrCreate(
                    [
                        'follower_id'    => Auth::user()->id,
                        'influencer_id' => $instructor->id,
                    ],
                    [
                        'isPaid'        => false,
                        'active_status' => true,
                    ]
                );
                Stripe::setApiKey(config('services.stripe.secret'));
                if (!$follow->isPaid) {
                    $session = Session::create(
                        [
                            'line_items'            => [[
                                'price_data'    => [
                                    'currency'      => config('services.stripe.currency'),
                                    'product_data'  => [
                                        'name'      => "$instructor->name",
                                    ],
                                    'recurring' => ['interval' => 'month'],
                                    'unit_amount'   => $instructor->sub_price * 100,
                                ],
                                'quantity'      => 1,
                            ]],
                            'customer' => Auth::user()?->stripe_cus_id,
                            'mode' => 'subscription',
                            'success_url' => route('subscription-success', [
                                'follow_id' => $follow?->id,
                                'follower_id' => Auth::user()->id,
                                'redirect' => $request->redirect
                            ]),
                            'cancel_url' => route('subscription-unsuccess'),
                        ]
                    );
                    if (!empty($session?->id)) {
                        $follow->session_id = $session?->id;
                        $follow->save();
                    }
                    if ($request->redirect == 1) {
                        return response($session->url);
                    }
                    return redirect($session->url);
                } else {
                    $stripe = new StripeClient(config('services.stripe.secret'));
                    $subscription = $stripe->subscriptions->cancel($follow->subscription_id);
                    if ($subscription->status === 'canceled') {
                        $follow->isPaid = false;
                        $follow->save();
                        return redirect()->back()->with('success', __('Instructor Successfully Unsubscribed'));
                    }
                }
            } else {
                return response()->json(['error' => 'Instructot doesnot exist or disabled'], 419);
            }
        } catch (Error $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function subscriptionSuccess(Request $request)
    {
        $follow = Follow::find($request->query('follow_id'));
        try {
            if (!!$follow) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session  = Session::retrieve($follow->session_id);

                if ($session->payment_status == "paid") {
                    $follow->isPaid = true;
                    $follow->subscription_id = $session->subscription;
                    $follow->save();
                    $student = Follower::find($request->query('follower_id'));
                    $student->stripe_cus_id = $session->customer;
                    $student->save();
                }
                if ($request->redirect == 1) {
                    return response('Subscription Successfully Started');
                }
                return redirect()->back()->with('success', 'Subscription Successfully Started');
            }
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        }
    }

    public function subscriptionUnsuccess()
    {
        return redirect()->back()->with('error', 'Subscription Unsuccessfull, kindly try again later');
    }

    public function mySubscriptions(SubscriptionsDataTable $dataTable)
    {
        if (Auth::user()->type == Role::ROLE_STUDENT) {
            return $dataTable->render('admin.subscription.index');
        }
    }

    public function unfollowInstructor(Request $request)
    {
        $request->validate([
            'influencer_id' => 'required',
        ]);
        try {
            $studentId    = Auth::user()->id;
            $instructorId = $request?->influencer_id;

            Follow::where('follower_id', $studentId)->where('influencer_id', $instructorId)->delete();

            return response()->json(['message' => 'Student has unfollowed the instructor'], 200);
        } catch (Error $e) {
            return response($e->getMessage(), 419);
        }
    }

    public function getInstructors()
    {
        try {
            $studentId = Auth::user()->id;
            return  Follow::where('follower_id', $studentId)->get();
        } catch (Error $e) {
            return response($e, 419);
        }
    }

    public function subscribeInstructor(Request $request)
    {
        $request->validate([
            'influencer_id' => 'required',
        ]);

        $instructorId = User::where('type', Role::ROLE_INFLUENCER)->where('id', $request?->influencer_id)->first()?->id;

        try {
            if (Follow::where('follower_id', Auth::user()->id)->where('isPaid', Follow::FOLLOW)->where('influencer_id', $instructorId)->exists()) {
                $follow           = Follow::where('follower_id', Auth::user()->id)->where('influencer_id', $instructorId)->first();
                $follow['isPaid'] = Follow::SUBSCRIPTION;
                $follow->update();
                return response()->json(['message' => 'Student is now subscribed to this instructor'], 200);
            }
            if (Follow::where('follower_id', Auth::user()->id)->where('isPaid', Follow::SUBSCRIPTION)->where('influencer_id', $instructorId)->exists()) {
                return response()->json(['message' => 'Student is already subscribed to this instructor'], 422);
            }
            Follow::create([
                'follower_id'    => Auth::user()->id,
                'influencer_id' => $instructorId,
                'isPaid'        => Follow::SUBSCRIPTION,
            ]);
            return response()->json(['message' => 'Student is now subscribed to this instructor'], 200);
        } catch (Error $e) {
            return response($e, 419);
        }
    }

    public function getSubscribedInstructors()
    {
        try {
            $studentId = Auth::user()->id;
            return  Follow::where('follower_id', $studentId)->where('isPaid', Follow::SUBSCRIPTION)->get();
        } catch (Error $e) {
            return response($e, 419);
        }
    }

    public function getStudents()
    {
        try {
            if (Auth::user()->type === Role::ROLE_INFLUENCER && Auth::user()->active_status == 1) {
                return response()->json(Follow::where('influencer_id', Auth::user()->id)->with('follower')->paginate(request()->get('perPage')));
            } else {
                throw new Exception('UnAuthorized', 401);
            }
        } catch (Exception $e) {
            return response($e->getMessage(), $e->getCode());
        }
    }
}
