<?php

namespace App\Http\Controllers\Admin;

use Error;
use Exception;
use Stripe\Stripe;
use Stripe\Account;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Models\Album;
use App\Models\LikeAlbum;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Models\AlbumCategory;
use App\Models\PurchaseAlbum;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\DataTables\Admin\AlbumCategoryDataTable;

class AlbumCategoryController extends Controller
{

    public function index(AlbumCategoryDataTable $dataTable)
    {
        if (Auth::user()->can('manage-blog')) {
            return $dataTable->render('admin.album.category.index');
        }
    }

    public function create()
    {
        if (Auth::user()->can('create-blog')) {
            return  view('admin.album.category.create');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function store(Request $request)
    {
        if (Auth::user()->can('create-blog')) {
            try {
                request()->validate([
                    'title' => 'required|string',
                    'description' => 'required|string',
                    'filePath' => 'required',
                    'fileType' => 'required'
                ]);
                $album_category = new AlbumCategory();
                $album_category->instructor_id = Auth::user()->id;
                $album_category->tenant_id = tenant('id');
                $album_category->title = $request->title;
                $album_category->slug = Str::slug($request->title);
                $album_category->description = $_POST['description'];
                $album_category->payment_mode = array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' ? "paid" : "un-paid") : "un-paid";
                $album_category->price =  array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' && !empty($request?->price) ? $request?->price : 0) : 0;
                // $album_category->file_type = Str::contains($request->file('file')->getMimeType(), 'video') ? 'video' : 'image';

                $album_category->image = $request->filePath; // Temporary chunk path
                $album_category->file_type = $request->fileType;
                // if ($request->hasfile('file')) {
                //     // $file = $request->file('file')->store('album_category');
                //     // $album_category->image = $file ?? null;
                //     $tenantId = tenant()->id; // e.g. 3
                //     $destination = public_path("{$tenantId}/album_category");
                //     if (!file_exists($destination)) {
                //         mkdir($destination, 0777, true);
                //     }
                //     $filename = time() . '_' . $request->file('file')->getClientOriginalName();
                //     $request->file('file')->move($destination, $filename);
                //     $album_category->image = "{$tenantId}/album_category/{$filename}";

                //     $mimeType = $request->file('file')->getClientOriginalExtension();
                //     $video_types = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];
                //     $album_category->file_type = in_array($mimeType, $video_types) ? 'video' : 'image';
                // }
                $album_category->status = 'active';
                $album_category->save();
                return redirect()->route('album.category.manage')->with('success', __('Album Category created successfully.'));
            } catch (ValidationException $e) {
                dd($e->getMessage());
                return redirect()->back()->withErrors($e->errors())->withInput();
            } catch (\Exception $e) {
                dd($e->getMessage());
                return redirect()->back()->with('danger', $e->getMessage())->withInput();
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (Auth::user()->can('delete-blog')) {
            $post = AlbumCategory::find($id);
            $post->delete();
            return redirect()->route('album.category.manage')->with('success', __('Album Category deleted successfully.'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->can('edit-blog')) {
            request()->validate([
                'title'         => 'required|max:50',
                'description'   => 'required',
            ]);
            $album_category   = AlbumCategory::find($id);
            $album_category->image = $request->filePath; // Temporary chunk path
            $album_category->file_type = $request->fileType;
            // if ($request->hasFile('file') && $request->file('file')->isValid()) {
            //     // $path           = $request->file('file')->store('album_category');
            //     // $album_category->image    = $path;
            //     $tenantId = tenant()->id; // e.g. 3
            //     $destination = public_path("{$tenantId}/album_category");
            //     if (!file_exists($destination)) {
            //         mkdir($destination, 0777, true);
            //     }
            //     $filename = time() . '_' . $request->file('file')->getClientOriginalName();
            //     $request->file('file')->move($destination, $filename);
            //     $album_category->image = "{$tenantId}/album_category/{$filename}";
            //     $mimeType = $request->file('file')->getClientOriginalExtension();
            //     $video_types = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv', 'webm', 'mpeg', '3gp'];
            //     $album_category->file_type = in_array($mimeType, $video_types) ? 'video' : 'image';
            // }
            $album_category->instructor_id = Auth::user()->id;
            $album_category->tenant_id = tenant('id');
            $album_category->title = $request->title;
            $album_category->slug = Str::slug($request->title);
            $album_category->description = $_POST['description'];
            $album_category->payment_mode = array_key_exists('paid', $request->all()) ? ($request?->paid == 'on' ? 'paid' : 'un-paid') : 'un-paid';
            $album_category->price = array_key_exists('paid', $request->all()) && $request?->paid == 'on' && !empty($request?->price) ? $request?->price : 0;
            $album_category->save();
            return redirect()->route('album.category.manage')->with('success', __('Album Category updated successfully'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function edit($id)
    {
        if (Auth::user()->can('edit-blog')) {
            $posts      = AlbumCategory::find($id);
            if (!is_null($posts)) {
                return  view('admin.album.category.edit', compact('posts'));
            } else {
                return redirect()->back()->with('failed', __('Album Category not found.'));
            }
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
    public function getCategories()
    {
        $album_categories = AlbumCategory::with('purchaseAlbum')
            ->where([
                ['tenant_id', tenant()->id],
                ['status', 'active'],
            ]);
        if (Auth::user()->can('manage-blog')) {
            switch (request()->query('filter')) {
                case ('free'):
                    $album_categories = $album_categories->where('payment_mode', 'un-paid');
                    break;
                case ('paid'):
                    $album_categories = $album_categories->where('payment_mode', 'paid');
                    break;
            }
            $album_categories = $album_categories->orderBy('created_at', 'desc')->paginate(6);
            return view('admin.posts.student_album_category', compact('album_categories'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function getCategoryAlbums($id)
    {
        if (Auth::user()->can('manage-blog')) {
            $album_category = AlbumCategory::find($id);
            $albums = Post::where('album_category_id', $id)->orderBy('column_order', 'asc')->get();
            return view('admin.posts.album', compact('albums', 'album_category'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function change_order($id)
    {
        if (Auth::user()->can('manage-blog')) {
            $albums = Post::where('album_category_id', $id)
                ->orderBy('column_order', 'asc')
                ->get();
            return view('admin.posts.album-change-order', compact('albums', 'id'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function reorder(Request $request, $categoryId)
    {
        if (!Auth::user()->can('manage-blog')) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied.'
            ], 403);
        }

        try {
            $order = $request->input('order', []);

            foreach ($order as $item) {
                Post::where('id', $item['id'])
                    ->where('album_category_id', $categoryId)
                    ->update(['column_order' => $item['position']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Album order updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function likeAlbum()
    {
        try {
            $post = Post::find(request()->post_id);
            if (!!$post) {
                $postLike = Auth::user()->likeAlbum->firstWhere('album_id', $post->id);

                if (!!$postLike) {
                    $postLike->delete();
                    return redirect()->back()->with('success', __('Unliked'));
                }

                $postLike = new LikeAlbum();
                $postLike->album_id = $post->id;
                if (Auth::user()->type === Role::ROLE_FOLLOWER)
                    $postLike->student_id = Auth::user()->id;
                else
                    $postLike->instructor_id = Auth::user()->id;
                $postLike->save();
                return redirect()->back()->with('success', __('Album Liked Successfully'));
            } else
                return redirect()->back()->with('failed', __('UnSuccessfull'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function purchaseAlbumCategory(Request $request)
    {
        $request->validate([
            'post_id' => 'required'
        ]);

        try {
            $post = AlbumCategory::where('payment_mode', 'paid')->where('id', $request->post_id)->where('status', 'active')->first();
            $purchasePost = PurchaseAlbum::firstOrCreate(
                [
                    'student_id' => Auth::user()->id,
                    'album_category_id' => $post->id,
                ],
                [
                    'active_status' => false,
                ]
            );
            $stripe_account_id = User::where('id', $post->instructor_id)->value('stripe_account_id');

            Stripe::setApiKey(config('services.stripe.secret'));

            // $session = Session::create(
            //     [
            //         'line_items'            => [[
            //             'price_data'    => [
            //                 'currency'      => config('services.stripe.currency'),
            //                 'product_data'  => [
            //                     'name'      => "$post->title",
            //                 ],
            //                 'unit_amount'   => $post->price * 100,
            //             ],
            //             'quantity'      => 1,
            //         ]],
            //         'customer' => Auth::user()?->stripe_cus_id,
            //         'mode' => 'payment',
            //         'success_url' => route('purchase-album-success', [
            //             'purchase_post_id' => $purchasePost?->id,
            //             'student_id' => Auth::user()->id,
            //             'redirect' => $request->redirect
            //         ]),
            //         'cancel_url' => route('subscription-unsuccess'),
            //     ]
            // );

             $tenantId = tenancy()->tenant->id;
            tenancy()->central(function () use (&$application_fee_percentage, &$application_currency, $tenantId) {
                $userData = User::where('tenant_id', $tenantId)
                    ->select('application_fee_percentage', 'currency')
                    ->first();
                $application_fee_percentage = $userData?->application_fee_percentage;
                $application_currency = $userData?->currency ?? 'usd';
            });

            $instructor = $post->instructor;

            // Check if instructor is from USA (same logic as first function)
            $isInstructorUSA = $instructor?->country == 'United States';

            // Platform fee percentage
            $platformPercent = $application_fee_percentage; // e.g., 10

            // Calculate base price in cents
            $basePrice = $post->price * 100;

            // Initialize variables
            $convertedAmount = $basePrice;
            $applicationFeeAmount = 0;

            // **Scenario 1: Instructor pays both fees**
            if (
                $instructor?->stripe_transaction_fee == 'instructor' &&
                $instructor?->stripe_tuneup_percentage_fee == 'instructor'
            ) {
                // Student pays: base price only
                $convertedAmount = $basePrice;
                $platformFeeAmount = $basePrice * ($platformPercent / 100);
                $applicationFeeAmount = $platformFeeAmount;

                // No Stripe fee recovery needed as instructor pays it
            }

            // **Scenario 2: Student pays Stripe fee, Instructor pays Platform fee**
            elseif (
                $instructor?->stripe_transaction_fee == 'student' &&
                $instructor?->stripe_tuneup_percentage_fee == 'instructor'
            ) {
                // Student pays: base price + Stripe fees
                $stripePerc = 0.029;       // 2.9%
                $stripeFixed = 30;         // $0.30 → 30 cents

                $gross = ($basePrice + $stripeFixed) / (1 - $stripePerc);
                $convertedAmount = round($gross);

                // Platform fee is X% of base price (paid by instructor)
                $platformFeeAmount = $basePrice * ($platformPercent / 100);
                $applicationFeeAmount = $platformFeeAmount;
            }

            // **Scenario 3: Student pays Platform fee, Instructor pays Stripe fee**
            elseif (
                $instructor?->stripe_transaction_fee == 'instructor' &&
                $instructor?->stripe_tuneup_percentage_fee == 'student'
            ) {
                // Student pays: base price + Platform fee
                $convertedAmount = $basePrice * (1 + ($platformPercent / 100));
                $convertedAmount = round($convertedAmount);

                // Platform fee is X% of base price
                $platformFeeAmount = $basePrice * ($platformPercent / 100);
                $applicationFeeAmount = $platformFeeAmount;

                // No Stripe fee recovery needed as instructor pays it
            }

            // **Scenario 4: Student pays both fees**
            elseif (
                $instructor?->stripe_transaction_fee == 'student' &&
                $instructor?->stripe_tuneup_percentage_fee == 'student'
            ) {
                // First: Add platform fee to base price
                $priceWithPlatformFee = $basePrice * (1 + ($platformPercent / 100));

                // Then: Add Stripe fees on top
                $stripePerc = 0.029;       // 2.9%
                $stripeFixed = 30;         // $0.30 → 30 cents

                $gross = ($priceWithPlatformFee + $stripeFixed) / (1 - $stripePerc);
                $convertedAmount = round($gross);

                // Platform fee is X% of base price
                $platformFeeAmount = $basePrice * ($platformPercent / 100);
                $applicationFeeAmount = $platformFeeAmount;
            }

            // Round to nearest integer (cents)
            $convertedAmount = round($convertedAmount);
            $applicationFeeAmount = round($applicationFeeAmount);

            // Apply minimum amount check if needed
             // Apply minimum amount check
            $currency = 'usd';
            $minimumCents = 0.50;
            $finalAmountInCents = max($convertedAmount, $minimumCents);

            // Prepare session data - SIMPLIFIED like your working example
            $sessionData = [
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $post->title,
                        ],
                        'unit_amount' => $finalAmountInCents,
                    ],
                    'quantity' => 1,
                ]],
                'payment_intent_data' => [
                    'application_fee_amount' => $applicationFeeAmount,
                    // 'transfer_data' => ['destination' => $instructor->stripe_account_id], // REMOVED
                ],
                'customer' => Auth::user()?->stripe_cus_id,
                'mode' => 'payment',
                'success_url' => route('purchase-album-success', [
                    'purchase_post_id' => $purchasePost?->id,
                    'student_id' => Auth::user()->id,
                    'redirect' => $request->redirect
                ]),
                'cancel_url' => route('subscription-unsuccess'),
            ];

            // Apply on_behalf_of for non-US instructors
            // if (!$isInstructorUSA && $instructor && $instructor->stripe_account_id) {
            //     $sessionData['payment_intent_data']['on_behalf_of'] = $instructor->stripe_account_id;
            // }

            // Verify account and create session with connected account
            if ($instructor && $instructor->stripe_account_id) {
                $account = Account::retrieve($instructor->stripe_account_id);

                if (
                    $instructor?->active_status &&
                    !empty($account->id) &&
                    !empty($account->capabilities['card_payments'])
                ) {
                    // Create session with connected account options - SIMPLER APPROACH
                    $session = Session::create($sessionData, [
                        'stripe_account' => $instructor->stripe_account_id
                    ]);
                } else {
                    throw new Exception('There is a problem with purchasing this album. Kindly contact admin.');
                }
            } else {
                // Fallback: create regular session without Connect (to platform account)
                $session = Session::create($sessionData);
            }



            if (!empty($session?->id)) {
                $purchasePost->session_id = $session?->id;
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
    public function createAlbum($id)
    {
        if (Auth::user()->can('create-blog')) {
            $album_category = AlbumCategory::where('id', $id)
                ->where('instructor_id', Auth::user()->id)
                ->first(['id', 'title']);

            if (!$album_category) {
                return redirect()->back()->with('failed', __('Category not found.'));
            }

            // convert to array for Form::select
            $album_categories = [$album_category->id => $album_category->title];

            return view('admin.album.create', compact('album_categories', 'album_category'));
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }
}
