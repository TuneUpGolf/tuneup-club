<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\Post;
use App\Models\PurchasePost;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PurchasePostController extends Controller
{
    public function purchasePost(Request $request)
    {
        $request->validate([
            'post_id' => 'required',
        ]);

        try {
            $post         = Post::where('paid', true)->where('id', $request->post_id)->where('status', 'active')->first();
            $purchasePost = PurchasePost::firstOrCreate(
                [
                    'follower_id' => Auth::user()->id,
                    'post_id'     => $post->id,
                ],
                [
                    'active_status' => false,
                ]
            );

            Stripe::setApiKey(config('services.stripe.secret'));

            $session = Session::create(
                [
                    'line_items'  => [[
                        'price_data' => [
                            'currency'     => config('services.stripe.currency'),
                            'product_data' => [
                                'name' => "$post->title",
                            ],
                            'unit_amount'  => $post->price * 100,
                        ],
                        'quantity'   => 1,
                    ]],
                    'customer'    => Auth::user()?->stripe_cus_id,
                    'mode'        => 'payment',
                    'success_url' => route('blogs.index'),
                    // route('purchase-post-success', [
                    //     'purchase_post_id' => $purchasePost?->id,
                    //     'follower_id'      => Auth::user()->id,
                    //     'redirect'         => $request->redirect,
                    // ]),
                    'cancel_url'  => route('subscription-unsuccess'),
                ]
            );
            if (! empty($session?->id)) {
                $purchasePost->session_id = $session?->id;
                $purchasePost->active_status = true;
                $purchasePost->save();
            }
            if ($request->redirect == 1) {
                return response($session->url);
            }
            return redirect($session->url);
        } catch (Error $e) {
            return response($e, 419);
        }
    }

    public function purchasePostSuccess(Request $request)
    {
        $purchasePost = PurchasePost::find($request->query('purchase_post_id'));
        try {
            if (! ! $purchasePost) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = Session::retrieve($purchasePost->session_id);

                if ($session->payment_status == "paid") {
                    $purchasePost->active_status = true;
                    $purchasePost->session_id    = $session->id;
                    $purchasePost->save();
                    $follower = Follower::find($request->query('follower_id'));
                    if (! isset($follower->stripe_cus_id)) {
                        $follower->stripe_cus_id = $session->customer;
                        $follower->save();
                    }
                }

                if ($request->redirect == 1) {
                    return response('Post Purchased Successfully');
                }

                return redirect()->back()->with('success', 'Post Purchased Successfully');
            }
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        }
    }
}
