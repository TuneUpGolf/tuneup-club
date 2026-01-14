<?php

namespace App\Http\Controllers\Admin;

use Error;
use Exception;
use Carbon\Carbon;
use Stripe\Stripe;
use App\Models\Plan;
use App\Models\Role;
use App\Models\User;
use App\Models\Slots;
use App\Models\Coupon;
use App\Models\Lesson;
use App\Models\Follower;
use App\Models\Purchase;
use App\Actions\SendEmail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Services\ChatService;
use App\Traits\ConvertVideos;
use App\Traits\PurchaseTrait;
use App\Mail\Admin\VideoAdded;
use App\Models\PurchaseVideos;
use App\Models\FeedbackContent;
use App\Models\ClientSubscription;
use App\Http\Controllers\Controller;
use App\Mail\Admin\PurchaseFeedback;
use Illuminate\Support\Facades\Auth;
use App\Actions\SendPushNotification;
use App\Mail\Admin\PurchaseCompleted;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Support\Facades\Storage;
use App\DataTables\Admin\PurchaseDataTable;
use App\Http\Resources\PurchaseAPIResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\PurchaseVideoAPIResource;
use App\DataTables\Admin\PurchaseLessonDataTable;
use App\DataTables\Admin\UpcomingLessonDataTable;
use App\DataTables\Admin\FollowerPurchasesDataTable;
use App\DataTables\Admin\PurchaseLessonVideoDataTable;

/**
 * Controller for managing lesson purchases and related flows (checkout, feedback, videos).
 *
 * Includes endpoints to create/confirm purchases, upload videos, manage feedback,
 * and render listings for admins, influencers, and followers.
 *
 * @package App\Http\Controllers\Admin
 */
class PurchaseController extends Controller
{
    use PurchaseTrait;
    use ConvertVideos;

    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Display the purchases index page with follower purchases datatable.
     *
     * @param PurchaseDataTable $dataTable
     * @param FollowerPurchasesDataTable $followerPurchasesDataTable
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(PurchaseDataTable $dataTable, FollowerPurchasesDataTable $followerPurchasesDataTable)
    {

        if (Auth::user()->can('manage-purchases')) {
            return $dataTable->render('admin.purchases.index', [
                'followerPurchasesDataTable' => $followerPurchasesDataTable->html(),
            ]);
        }
    }

    public function upcomingLessonsData(UpcomingLessonDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function view($id)
    {
        $lesson    = Lesson::findOrFail($id);
        $dataTable = new LessonDataTable($id); // Pass follower ID to the datatable
        return $dataTable->render('admin.purchases.show', compact('lesson', 'dataTable'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required',
        ]);

        try {
            $follower     = Auth::user();
            $lesson       = Lesson::find($request->lesson_id);
            $total_amount = $lesson->lesson_price;
            $coupon       = Coupon::find($request->coupon_id ?? null);
            $lesson->load('user');

            if (! empty($coupon)) {
                $coupon_discount_amount = ($lesson->lesson_price * $coupon->discount);
                $total_amount           = $lesson->lesson_price - ($coupon_discount_amount >= $coupon->limit ? $coupon->limit : $coupon_discount_amount);
            }

            if ($follower && $lesson && ! empty($lesson->user) && Auth::user()->can('create-purchases')) {
                try {
                    $newPurchase = new Purchase([
                        'follower_id'   => $follower->id,
                        'influencer_id' => $lesson->user->id,
                        'lesson_id'     => $lesson->id,
                        'coupon_id'     => $coupon,
                        'tenenat_id'    => Auth::user()->tenant_id,
                        'session_id' => $request->session_id

                    ]);
                    $newPurchase->total_amount = $total_amount;
                    $newPurchase->status       = Purchase::STATUS_INCOMPLETE;
                    $newPurchase->lessons_used = 0;
                    $newPurchase->save();

                    $newPurchase = $newPurchase->load('follower', 'influencer', 'lesson');
                } catch (\Illuminate\Database\QueryException $e) {
                    echo 'Database exception: ', $e->getMessage(), "\n";
                } catch (\Exception $e) {
                    echo 'Caught exception: ', $e->getMessage(), "\n";
                }

                // SendEmail::run($newPurchase->follower->email, new PurchaseCreated($newPurchase));

                // $message = __('Hello, :name, a purchase has been created for :ammount against your account.', [
                //     'name' => $follower['name'],
                //     'ammount' => $newPurchase->total_amount,
                // ]);
                // if (isset($newPurchase?->follower?->pushToken?->token))
                //     SendPushNotification::dispatch($newPurchase?->follower?->pushToken?->token, 'New Purchase Created', $message);
                // $userPhone = Str::of($follower['dial_code'])->append($follower['phone'])->value();
                // $userPhone = str_replace(array('(', ')'), '', $userPhone);
                // SendSMS::dispatch($userPhone, $message);

                return redirect()->route('purchase.video.index', ['purchase_id' => $newPurchase->id, 'checkout' => true])
                    // ->with('success', 'Purchase created successfully, please add video and proceed to checkout.')
                ;
            } else {
                return response("Something went wrong", 419);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([$e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $followers   = Follower::all(); // Adjust as needed
        $influencers = User::all();
        $lessons     = Lesson::all();
        $purchase    = Purchase::find($id);
        return view('admin.purchases.edit', compact('purchase', 'followers', 'influencers', 'lessons'));
    }

    public function create()
    {
        if (Auth::user()->can('create-purchases')) {
            $lessons = Lesson::where('tenant_id', tenant()->id)->get();
            return view('admin.purchases.create', compact('lessons'));
        }
    }

    public function addPurchase(Request $request)
    {

        try {
            $request->validate([
                'lesson_id' => 'required',
            ]);

            if (Auth::user()->type == Role::ROLE_FOLLOWER) {
                $follower     = Auth::user();
                $lesson       = Lesson::find($request->lesson_id);
                $total_amount = $lesson->lesson_price;
                $coupon       = Coupon::find($request->coupon_id ?? null);
                $lesson->load('user');

                if (! empty($coupon)) {
                    $coupon_discount_amount = ($lesson->lesson_price * $coupon->discount);
                    $total_amount           = $lesson->lesson_price - ($coupon_discount_amount >= $coupon->limit ? $coupon->limit : $coupon_discount_amount);
                }

                if ($follower && $lesson && ! empty($lesson->user) && $follower->active_status == true) {

                    try {
                        $newPurchase = new Purchase([
                            'follower_id'   => $follower->id,
                            'influencer_id' => $lesson->user->id,
                            'lesson_id'     => $lesson->id,
                            'coupon_id'     => $coupon,
                            'tenenat_id'    => Auth::user()->tenant_id,

                        ]);
                        $newPurchase->total_amount = $total_amount;
                        $newPurchase->status       = Purchase::STATUS_INCOMPLETE;
                        $newPurchase->lessons_used = 0;
                        $newPurchase->save();
                        // SendEmail::dispatch($newPurchase->follower->email, new PurchaseCreated($newPurchase));
                        // $message = __('Hello, :name, a purchase has been created for :ammount against your account.', [
                        //     'name' => $follower['name'],
                        //     'ammount' => $newPurchase->total_amount,
                        // ]);
                        // $userPhone = Str::of($follower['dial_code'])->append($follower['phone'])->value();
                        // $userPhone = str_replace(array('(', ')'), '', $userPhone);
                        // SendSMS::dispatch($userPhone, $message);
                        // SendPushNotification::dispatch($newPurchase?->follower?->pushToken?->token, 'New Purchase Created', $message);
                    } catch (\Illuminate\Database\QueryException $e) {
                        echo 'Database exception: ', $e->getMessage(), "\n";
                    } catch (\Exception $e) {
                        echo 'Caught exception: ', $e->getMessage(), "\n";
                    }
                    $newPurchase = $newPurchase->load('follower', 'influencer', 'lesson');
                    return response(new PurchaseAPIResource($newPurchase));
                } else {
                    return response("User disabled, kindly contact admin.", 419);
                }
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([$e->getMessage()]);
        }
    }

    public function confirmPurchase(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required',
        ]);

        try {
            if (Auth::user()->active_status == true) {
                $purchase = Purchase::find($request?->query('purchase_id'));

                if (! empty($purchase) && ! ! $purchase->influencer->is_stripe_connected) {
                    $session = $this->createSessionForPayment($purchase, false);
                    return response($session->url);
                } else {
                    return new Error("Purchase can't be confirmed");
                }
            } else {
                return response()->json(['error' => 'Follower is currently disabled, please contact admin.', 419]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function purchaseSuccess(Request $request)
    {
        $purchase = Purchase::find($request->query('purchase_id'));

        if (! ! $purchase && $purchase->lesson->type == Lesson::LESSON_TYPE_INPERSON) {
            $slot = Slots::find($purchase?->slot_id);
        }

        try {
            if (! empty($purchase)) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = Session::retrieve($purchase->session_id, [
                    'stripe_account' => $purchase->influencer->stripe_account_id,
                ]);

                if ($session->payment_status == "paid") {
                    $purchase->status = Purchase::STATUS_COMPLETE;
                    $purchase->save();

                    SendEmail::dispatch($purchase?->lesson?->user?->email, new VideoAdded($purchase));

                    $message = __('Hello, :name, has submitted an online submission.', [
                        'name' => $purchase->follower->name,
                    ]);
                    // SendPushNotification::dispatch($purchase?->lesson?->user?->pushToken?->token, 'Video Submitted', $message);

                    if (isset($slot)) {
                        // If the slot is a package lesson, attach follower and their friends
                        if (! ! $slot->lesson->is_package_lesson) {
                            $slots = $slot->lesson->slots; // Fetch all slots of the lesson

                            foreach ($slots as $lessonSlot) {
                                // Attach follower to all slots
                                $lessonSlot->follower()->attach($purchase->follower_id, [
                                    'isFriend'    => false,
                                    'friend_name' => null,
                                    'created_at'  => now(),
                                    'updated_at'  => now(),
                                ]);

                                // Attach friends if any were included in the purchase
                                $friendNames = json_decode($purchase->friend_names, true) ?? [];
                                foreach ($friendNames as $friendName) {
                                    $lessonSlot->follower()->attach($purchase->follower_id, [
                                        'isFriend'    => true,
                                        'friend_name' => $friendName,
                                        'created_at'  => now(),
                                        'updated_at'  => now(),
                                    ]);
                                }
                            }

                            // Send notification for package lessons
                            $this->sendSlotNotification(
                                $slot,
                                'Package Lesson Payment Successful',
                                'You have successfully paid for the package lesson. You are now eligible to attend all upcoming slots.',
                                null,
                            );
                        } else {
                            // Send standard notification for single-slot purchases
                            $this->sendSlotNotification(
                                $slot,
                                'Slot Payment Completed',
                                'Your lesson with :influencer, for :date has been marked as completed.',
                                null,
                            );
                        }

                        if (Purchase::where('slot_id', $slot->id)->where('status', Purchase::STATUS_INCOMPLETE)->doesntExist() && ! $slot->lesson->is_package_lesson) {
                            $slot->is_completed           = true;
                            $purchase->isFeedbackComplete = true;
                            $slot->save();
                            $this->sendSlotNotification(
                                $slot,
                                'Slot Completed',
                                null,
                                'Your Slot for the in-person lesson :lesson at :date has been completed.'
                            );
                        }
                    } else {
                        // Non-slot purchases
                        SendEmail::dispatch($purchase->follower->email, new PurchaseCompleted($purchase));
                        $message = __('Hello, :name, a purchase has been confirmed for :ammount against your account.', [
                            'name'    => $purchase->follower->name,
                            'ammount' => $purchase->total_amount,
                        ]);
                        // SendPushNotification::dispatch($purchase?->follower?->pushToken?->token, 'Purchase Confirmed', $message);
                    }
                }

                if ($request->query('redirect') == 1) {
                    return redirect(route('purchase.index'))->with('success', 'Payment Successful');
                }
                return response("Purchase Confirmed Successfully");
            }
        } catch (\Exception $e) {
            return redirect(route('purchase.index'))->with('errors', $e->getMessage());
        }
    }

    public function purchaseCancel(Request $request)
    {
        if ($request->query('redirect') == 1) {
            return redirect(route('home'))->with('success', 'Payment Cancelled');
        }
        return response("Payment Cancelled Successfully");
    }

    public function purchaseCancle()
    {
        return redirect(route('purchase.index'))->with('error', 'There was a problem with your payment');
    }

    // public function addVideo(Request $request)
    // {
    //     $request->validate([
    //         'video'       => 'required|mimetypes:video/avi,video/mpeg,video/quicktime,video/mov,video/mp4|max:102400',
    //         'video_2'     => 'mimetypes:mimetypes:video/avi,video/mpeg,video/quicktime,video/mov,video/mp4|max:102400',
    //         'purchase_id' => 'required',
    //     ]);
    //     $purchase      = Purchase::with('lesson')->find($request?->purchase_id);
    //     $currentDomain = tenant('domains');
    //     $currentDomain = $currentDomain[0]->domain;
    //     if (isset($purchase) && Auth::user()->type == Role::ROLE_FOLLOWER) {
    //         if ($purchase?->lesson->lesson_quantity > $purchase->lessons_used) {
    //             try {
    //                 $purchase_video = PurchaseVideos::create([
    //                     'tenant_id'   => Auth::user()->tenant_id,
    //                     'purchase_id' => $request?->purchase_id,
    //                     'note'        => $request?->note,
    //                 ]);
    //                 if ($request?->hasFile('video')) {
    //                     $file = $request->file('video');
    //                     if (Str::endsWith($file->getClientOriginalName(), '.mov')) {
    //                         $localPath = $request->file('video')->store('purchaseVideos');
    //                         $path      = $this->convertSingleVideo($localPath);
    //                     } else {
    //                         // Digital Ocean space storage
    //                         $file           = $request->file('video');
    //                         $extension      = $file->getClientOriginalExtension();
    //                         $randomFileName = Str::random(25) . '.' . $extension;
    //                         $filePath       = $currentDomain . '/' . $purchase->lesson_id . '/' . $purchase->follower_id . '/' . $randomFileName;
    //                         Storage::disk('spaces')->put($filePath, file_get_contents($file), 'public');
    //                         $path = Storage::disk('spaces')->url($filePath);
    //                     }

    //                     $purchase_video->video_url = $path;
    //                     $purchase_video->save();
    //                 }

    //                 if ($request?->hasFile('video_2')) {
    //                     $file2 = $request->file('video_2');
    //                     if (Str::endsWith($file2->getClientOriginalName(), '.mov')) {
    //                         $localPath = $request->file('video_2')->store('purchaseVideos');
    //                         $path      = $this->convertSingleVideo($localPath);
    //                     } else {
    //                         // Digital Ocean space storage
    //                         $extension      = $file2->getClientOriginalExtension();
    //                         $randomFileName = Str::random(25) . '.' . $extension;
    //                         $filePath       = $currentDomain . '/' . $purchase->lesson_id . '/' . $purchase->follower_id . '/' . $randomFileName;
    //                         Storage::disk('spaces')->put($filePath, file_get_contents($file2), 'public');
    //                         $path = Storage::disk('spaces')->url($filePath);
    //                     }

    //                     $purchase_video->video_url_2 = $path;
    //                     $purchase_video->save();
    //                 }

    //                 $purchase->lessons_used = $purchase->lessons_used + 1;
    //                 if ($purchase->lesson_used == $purchase?->lesson->lesson_quantity) {
    //                     $purchase->isFeedbackComplete = 1;
    //                 }
    //                 $purchase->save();

    //                 if ($purchase->status === Purchase::STATUS_COMPLETE) {
    //                     SendEmail::dispatch($purchase?->lesson?->user?->email, new VideoAdded($purchase));

    //                     $message = __('Hello, :name, has submitted an online submission.', [
    //                         'name' => $purchase->follower->name,
    //                     ]);

    //                     // SendPushNotification::dispatch($purchase?->lesson?->user?->pushToken?->token, 'Video Submitted', $message);
    //                 }

    //                 if ($request->checkout == 1) {
    //                     $request->merge(['purchase_id' => $purchase->id]);
    //                     $request->setMethod('POST');

    //                     // Check if subscriptiom
    //                     // Get login user
    //                     $student_user = Auth::user();

    //                     // if any active subscription
    //                     $student_subscription = ClientSubscription::where('follower_id', $student_user->id)
    //                         ->where('status', 'active')
    //                         ->latest()
    //                         ->first();

    //                     // Subscription exists
    //                     if ($student_subscription) {
    //                         // if ($student_subscription->influencer_id == $purchase->influencer_id) {
    //                         // Current monthly online lesson count
    //                         $student_monthly_purchase_count = Purchase::where('follower_id', $student_user->id)
    //                             // ->where('influencer_id', $purchase->influencer_id)
    //                             ->where('status', 'complete')
    //                             // ->where('type', 'online')
    //                             ->whereMonth('created_at', Carbon::now()->month)
    //                             ->whereYear('created_at', Carbon::now()->year)
    //                             ->count();

    //                         // get subscription plan
    //                         $plan = $student_subscription->plan;

    //                         // Check whats the lesson limit
    //                         if ($plan && ($plan->lesson_limit == -1 || $student_monthly_purchase_count < $plan->lesson_limit)) {
    //                             $purchase->status = Purchase::STATUS_COMPLETE;
    //                             $purchase->save();
    //                             return redirect()->route('home')->with('success', 'Video Successfully Added');
    //                         }
    //                         // }
    //                     }
    //                     return $this->confirmPurchaseWithRedirect($request);
    //                     // return redirect()
    //                     //     ->route('home')
    //                     //     ->with('success', 'Online Lesson purchased successfully and Video successfully added.');
    //                 } else if ($request->redirect == 1) {
    //                     return redirect()->route('purchase.index')->with('success', 'Video Successfully Added');
    //                 }
    //             } catch (\Exception $e) {
    //                 report($e);
    //                 return redirect()->back()->with('errors', $e->getMessage());
    //             } catch (Error $e) {
    //                 report($e);
    //                 return response($e, 419);
    //             }
    //         } else {
    //             return throw new ValidationException(['You dont have enough lessons remaining']);
    //         }
    //     } else {
    //         report($e);
    //         return throw new ValidationException(['No purchase found for this ID']);
    //     }
    // }

    public function addVideo(Request $request)
    {
        // dd($request->all());
        $request->validate([
            // 'video' => 'required|mimetypes:video/avi,video/mpeg,video/quicktime,video/mov,video/mp4',
            // 'video_2' => 'mimetypes:mimetypes:video/avi,video/mpeg,video/quicktime,video/mov,video/mp4',
            'video_path' => 'required',
            'purchase_id' => 'required'
        ]);
        $currentDomain = tenant('domains');
        $currentDomain = $currentDomain[0]->domain;
        $purchase = Purchase::with('lesson')->find($request?->purchase_id);
        if (isset($purchase) && Auth::user()->type == Role::ROLE_FOLLOWER) {
            // if ($purchase?->lesson->lesson_quantity > $purchase->lessons_used) {
            try {
                PurchaseVideos::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'purchase_id' => $request?->purchase_id,
                    'note' => $request?->note,
                    'video_url' => $request->video_path,
                    'video_url_2' => $request->video_2_path,
                ]);

                $purchase->lessons_used = $purchase->lessons_used + 1;
                if ($purchase->lesson_used == $purchase?->lesson->lesson_quantity) {
                    $purchase->isFeedbackComplete = 1;
                }
                $purchase->save();

                if ($purchase->status === Purchase::STATUS_COMPLETE) {
                    SendEmail::dispatch($purchase?->lesson?->user?->email, new VideoAdded($purchase));

                    $message = __('Hello, :name, has submitted an online submission.', [
                        'name' => $purchase->student->name,
                    ]);

                    SendPushNotification::dispatch($purchase?->lesson?->user?->pushToken?->token, 'Video Submitted', $message);
                }
                if ($request->checkout == 1) {
                    $request->merge(['purchase_id' => $purchase->id]);
                    $request->setMethod('POST');

                    // Check if subscriptiom
                    // Get login user
                    $student_user = Auth::user();

                    // // if any active subscription
                    $student_subscription = ClientSubscription::where('follower_id', $student_user->id)
                        ->where('status', 'active')
                        ->latest()
                        ->first();

                    // // Subscription exists
                    if ($student_subscription) {
                        if ($student_subscription->influencer_id == $purchase->influencer_id) {
                            // Determine subscription period boundaries
                            $startDate = Carbon::parse($student_subscription->created_at);
                            $now = Carbon::now();

                            // Figure out which monthly cycle the student is currently in
                            // e.g., if created_at = 2025-09-15, and today = 2025-11-10,
                            // current cycle started on 2025-10-15 and ends on 2025-11-15.
                            $cycleStart = $startDate->copy()->addMonthsNoOverflow(
                                $startDate->diffInMonths($now)
                            );
                            $cycleEnd = $cycleStart->copy()->addMonth();

                            // Count purchases made during this current subscription cycle
                            $student_monthly_purchase_count = Purchase::where('follower_id', $student_user->id)
                                ->where('influencer_id', $purchase->influencer_id)
                                ->where('status', 'complete')
                                // ->where('type', 'online')
                                ->whereBetween('created_at', [$cycleStart, $cycleEnd])
                                ->count();


                            // get subscription plan
                            $plan = $student_subscription->plan;

                            // Check whats the lesson limit
                            if ($plan && ($plan->lesson_limit == -1 || $student_monthly_purchase_count < $plan->lesson_limit)) {
                                $purchase->status = Purchase::STATUS_COMPLETE;
                                $purchase->does_user_have_subscription = $student_subscription->id;
                                $purchase->save();
                                return redirect()->route('home')->with('success', 'Video Successfully Added');
                            }
                        }
                    }

                    return $this->confirmPurchaseWithRedirect($request);
                } else if ($request->redirect == 1) {
                    return redirect()->route('home')->with('success', 'Video Successfully Added');
                }
            } catch (\Exception $e) {
                report($e);
                return redirect()->back()->with('errors', $e->getMessage());
            } catch (Error $e) {
                report($e);
                return response($e, 419);
            };
        } else {
            throw ValidationException::withMessages([
                'purchase_id' => 'No purchase found for this ID',
            ]);
        }
    }
    //API METHODS START
    public function addVideoAPI(Request $request)
    {
        try {
            $request->validate([
                'video'       => 'required|mimetypes:video/avi,video/mpeg,video/quicktime,video/mov,video/mp4',
                'video_2'     => 'mimetypes:mimetypes:video/avi,video/mpeg,video/quicktime,video/mov,video/mp4',
                'purchase_id' => 'required',
                'note'        => 'max:250',
            ]);

            $purchase = Purchase::with('lesson')->find($request?->purchase_id);

            if (isset($purchase) && Auth::user()->type == Role::ROLE_FOLLOWER) {
                if ($purchase?->lesson->lesson_quantity > $purchase->lessons_used) {
                    $purchase_video = PurchaseVideos::create([
                        'tenant_id'   => Auth::user()->tenant_id,
                        'purchase_id' => $request?->purchase_id,
                        'note'        => $request?->note,
                        'feedback'    => '',
                    ]);
                    if ($request->hasFile('thumbnail')) {
                        $purchase_video['thumbnail'] = $request->file('thumbnail')->store('purchaseVideos/thumbnails');
                        $purchase_video->save();
                    }
                    if ($request?->hasFile('video')) {
                        $path = $request->file('video')->store('purchaseVideos');
                        if (Str::endsWith($path, '.mov')) {
                            $path = $this->convertSingleVideo($path);
                        }

                        $purchase_video->video_url = $path;
                        $purchase_video->save();
                    }
                    if ($request?->hasFile('video_2')) {
                        $path = $request->file('video_2')->store('purchaseVideos');
                        if (Str::endsWith($path, '.mov')) {
                            $path = $this->convertSingleVideo($path);
                        }

                        $purchase_video->video_url_2 = $path;
                        $purchase_video->save();
                    }
                    $purchase->lessons_used = $purchase->lessons_used + 1;
                    $purchase->save();
                    if ($purchase->status === Purchase::STATUS_COMPLETE) {
                        SendEmail::dispatch($purchase?->lesson?->user?->email, new VideoAdded($purchase));

                        $message = __('Hello, :name, has submitted an online submission.', [
                            'name' => $purchase->follower->name,
                        ]);

                        // SendPushNotification::dispatch($purchase->lesson?->user?->pushToken?->token, 'Video Submitted', $message);
                    }
                    return response()->json(['message' => 'Lesson Video Added Successfully', 'lessons_used' => $purchase->lessons_used, 'lessons_remaing' => $purchase->lesson->lesson_quantity - $purchase->lessons_used], 200);
                } else {
                    return response()->json(['error' => 'Unable to add lessons, as lesson videos limit is full'], 422);
                }
            } else {
                return response()->json(['error' => 'Purchase doesnot exist or unauthorized'], 401);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }
    public function getAllPurchaseVideos(Request $request)
    {

        try {
            $request->validate([
                'purchase_id' => 'required',
            ]);

            if (Auth::user()->active_status == true) {
                $purchase = Purchase::find($request->purchase_id);
                if (isset($purchase)) {
                    return PurchaseVideoAPIResource::collection(PurchaseVideos::with('feedbackContent')->where('purchase_id', $request->purchase_id)->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'))->get());
                } else {
                    return response()->json(['error' => 'Purchase doesnot exist'], 420);
                }
            } else {
                return response()->json(['error' => 'User is currently disabled, please contact administror', 419]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function addFeedbackAPI(Request $request)
    {
        try {
            $request->validate([
                'purchase_id'       => 'required',
                'purchase_video_id' => 'required',
                'feedback'          => 'required',
                'fdbk_video'        => 'required',
            ]);

            if (Auth::user()->type == Role::ROLE_INFLUENCER) {

                $purchase = Purchase::find($request->purchase_id);
                if (isset($purchase)) {
                    $purchaseVideo = $purchase->videos()->find($request->purchase_video_id);
                    if (isset($purchaseVideo)) {
                        $purchaseVideo->feedback = $request->feedback;
                        if ($request?->hasFile('fdbk_video')) {
                            foreach ($request->file('fdbk_video') as $file) {
                                $path = $file->store('feedbackContent');
                                if (Str::endsWith($path, '.mov')) {
                                    $path = $this->convertSingleVideo($path);
                                }

                                $type = Str::contains($file->getMimeType(), 'video') ? 'video' : 'image';

                                FeedbackContent::create([
                                    'purchase_video_id' => $purchaseVideo->id,
                                    'url'               => $path,
                                    'type'              => $type,
                                ]);
                            }
                        }

                        $purchaseVideo->isFeedbackComplete = 1;
                        $purchaseVideo->save();
                        SendEmail::dispatch($purchase->follower->email, new PurchaseFeedback($purchase));
                        $message = __(':name, has sent feedback for your online submission.', [
                            'name' => $purchase->lesson->user->name,
                        ]);

                        if (isset($purchase->follower->pushToken->token)) {
                            // SendPushNotification::dispatch($purchase?->follower?->pushToken?->token, 'Feedback Recieved', $message);
                        }
                    }
                    $allPurchaseVideosFeedback = PurchaseVideos::where('purchase_id', $purchaseVideo->purchase->id)->where('isFeedbackComplete', 0)->get();
                    if (($purchaseVideo->purchase->lessons_used == $purchaseVideo->purchase->lesson->lesson_quantity) && ! ! isEmpty($allPurchaseVideosFeedback)) {
                        $purchase                     = Purchase::find($purchaseVideo->purchase_id);
                        $purchase->isFeedbackComplete = 1;
                        $purchase->save();
                    }
                    $purchase->isFeedbackComplete = 1;
                    $purchase->save();
                    return response()->json(['message' => 'Feedback Added Successfully', 'purchase Video' => new PurchaseVideoAPIResource($purchaseVideo)], 200);
                } else {
                    return response()->json(['error' => 'Purchase doesnot exist'], 420);
                }
            } else {
                return response()->json(['error' => 'Unauthorized', 401]);
            }
        } catch (\Exception $e) {
            return throw new Exception($e->getMessage());
        }
    }

    public function getAll()
    {
        try {
            if (Auth::user()->can('manage-purchases')) {
                if (Auth::user()->active_status == true) {
                    if (Auth::user()->type == Role::ROLE_INFLUENCER) {
                        $purchases                  = Purchase::where('influencer_id', Auth::user()->id)->where('status', Purchase::STATUS_COMPLETE);
                        request()->follower_request = true;
                    } else if (Auth::user()->type == Role::ROLE_FOLLOWER) {
                        $purchases = Purchase::where('follower_id', Auth::user()->id)->where('status', Purchase::STATUS_COMPLETE);
                    }

                    return PurchaseAPIResource::collection($purchases->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'))->paginate(request()->get('perPage')));
                } else {
                    return response()->json(['error' => 'User is currently disabled, please contact admin.', 419]);
                }
            } else {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (Error $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getFollowerAll(Request $request)
    {

        try {
            if (Auth::user()->can('manage-purchases')) {
                return PurchaseAPIResource::collection(Purchase::where('follower_id', $request->query('follower_id'))->orderBy(request()->get('sortKey', 'updated_at'), request()->get('sortOrder', 'desc'))->get());
            } else {
                throw new Exception('UnAuthorized', 419);
            }
        } catch (Error $e) {
            return response($e);
        }
    }

    //API METHODS END

    public function addVideoIndex(Request $request)
    {
        if (Auth::user()->can('create-purchases')) {
            $purchase = Purchase::find($request->purchase_id);
            return view('admin.purchases.video', ['purchase' => $purchase]);
        }
    }
    public function viewLesson(Request $request)
    {
        if (Auth::user()->can('create-purchases')) {
            $purchase = Purchase::find($request->purchase_id);
            return view('admin.purchases.lesson', ['purchase' => $purchase]);
        }
    }

    public function feedbackIndex(PurchaseLessonVideoDataTable $dataTable)
    {
        if (Auth::user()->can('manage-purchases')) {
            $purchase = Purchase::with(['lesson', 'follower', 'influencer', 'videos.feedbackContent'])
                ->find(request()->purchase_id);
            return view('admin.purchases.videos', compact('purchase'));
        }
    }


    // public function addFeedBack(Request $request)
    // {
    //     $request->validate([
    //         'feedback'          => 'required',
    //         'purchase_video_id' => 'required',
    //         'fdbk_video'        => 'required|array', // <-- validate as array
    //         'fdbk_video.*'      => 'file', // <-- each file should be a file
    //     ]);

    //     try {
    //         $purchaseVideo = PurchaseVideos::findOrFail($request->purchase_video_id);

    //         if (!Auth::user()->can('manage-purchases')) {
    //             abort(403, 'Unauthorized');
    //         }

    //         $purchaseVideo->feedback = $request->feedback;

    //         $currentDomain = tenant('domains')[0]->domain;

    //         $uploadedPaths = []; // to store multiple uploaded files

    //         if ($request->hasFile('fdbk_video')) {
    //             foreach ($request->file('fdbk_video') as $file) {

    //                 if (Str::endsWith($file->getClientOriginalName(), '.mov')) {
    //                     $localPath = $file->store('feedbackVideos');
    //                     $path      = $this->convertSingleVideo($localPath);
    //                 } else {
    //                     $extension      = $file->getClientOriginalExtension();
    //                     $randomFileName = Str::random(25) . '.' . $extension;
    //                     $filePath       = $currentDomain . '/' . $purchaseVideo->id . '/' . $randomFileName;
    //                     Storage::disk('spaces')->put($filePath, file_get_contents($file), 'public');
    //                     $path = Storage::disk('spaces')->url($filePath);
    //                 }

    //                 $type = Str::contains($file->getMimeType(), 'video') ? 'video' : 'image';

    //                 $uploadedPaths[] = [
    //                     'url' => $path,
    //                     'type' => $type,
    //                 ];
    //             }

    //             // Save all files as JSON in FeedbackContent
    //             FeedbackContent::updateOrCreate(
    //                 ['purchase_video_id' => $purchaseVideo->id],
    //                 ['url' => json_encode($uploadedPaths)] // <-- store JSON
    //             );
    //         }

    //         $purchaseVideo->isFeedbackComplete = 1;
    //         $purchaseVideo->save();

    //         SendEmail::dispatch($purchaseVideo->purchase->follower->email, new PurchaseFeedback($purchaseVideo->purchase));

    //         // Update purchase overall feedback status
    //         $allPurchaseVideosFeedback = PurchaseVideos::where('purchase_id', $purchaseVideo->purchase->id)
    //             ->where('isFeedbackComplete', 0)
    //             ->get();

    //         $purchase = Purchase::find($purchaseVideo->purchase_id);
    //         $purchase->isFeedbackComplete = 1;
    //         $purchase->save();

    //         if ($request->redirect == 1) {
    //             return redirect()->route('purchase.feedback.index', ['purchase_id' => $purchaseVideo->purchase_id])
    //                 ->with('success', 'Feedback Added Successfully');
    //         }
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('errors', $e->getMessage());
    //     }
    // }

    public function addFeedBack(Request $request)
    {
        $request->validate([
            'row_feedback'          => 'required|array',
            'row_feedback.*'        => 'string',
            'purchase_id'           => 'required',
            // 'fdbk_video'            => 'required|array',
            // 'fdbk_video.*'          => 'file',
            'fdbk_video_path'            => 'required|array',
            'fdbk_video_path.*'          => 'string',
        ]);

        try {

            $noteCount = count($request->row_feedback);
            $videoCount = count($request->fdbk_video_path);

            if ($noteCount !== $videoCount) {
                return redirect()->back()->with('failed', 'Each feedback note must have a corresponding video and each video must have a corresponding note.');
            }

            // Custom validation: Check if any feedback note is empty
            $emptyNotes = array_filter($request->row_feedback, function ($note) {
                return trim($note) === '';
            });

            if (!empty($emptyNotes)) {
                return redirect()->back()->with('failed', 'All feedback notes are required and cannot be empty.');
            }

            // Custom validation: Ensure no null or invalid files in videos
            $emptyvideos = array_filter($request->fdbk_video_path, function ($note) {
                return trim($note) === '';
            });

            if (!empty($emptyvideos)) {
                return redirect()->back()->with('failed', 'All video files must be valid.');
            }

            $purchaseVideo = PurchaseVideos::firstOrCreate(
                ['purchase_id' => $request->purchase_id],
                ['isFeedbackComplete' => 0]
            );

            if (Auth::user()->can('manage-purchases')) {
                // Handle feedback notes - ACCUMULATE instead of overwrite
                $existingFeedback = [];

                // Check if existing feedback is JSON or string and decode properly
                if (!empty($purchaseVideo->feedback)) {
                    if (is_string($purchaseVideo->feedback) && $this->isJson($purchaseVideo->feedback)) {
                        $existingFeedback = json_decode($purchaseVideo->feedback, true);
                    } elseif (is_string($purchaseVideo->feedback)) {
                        // It's a plain string, convert to array with single entry
                        $existingFeedback = [$purchaseVideo->feedback];
                    } elseif (is_array($purchaseVideo->feedback)) {
                        $existingFeedback = $purchaseVideo->feedback;
                    }
                }

                // Merge new feedback with existing
                $allFeedback = array_merge($existingFeedback, $request->row_feedback);
                $purchaseVideo->feedback = json_encode($allFeedback);

                $currentDomain = tenant('domains');
                $currentDomain = $currentDomain[0]->domain;

                $uploadedPaths = [];

                // if ($request->hasFile('fdbk_video')) {
                foreach ($request->fdbk_video_path as $index => $file) {

                    // $extension      = $file->getClientOriginalExtension();
                    // $randomFileName = Str::random(25) . '.' . $extension;
                    // $filePath       = $currentDomain . '/' . $purchaseVideo->id . '/' . $randomFileName;
                    // Storage::disk('spaces')->put($filePath, file_get_contents($file), 'public');
                    // $path = Storage::disk('spaces')->url($filePath);

                    // $type = Str::contains($file->getMimeType(), 'video') ? 'video' : 'image';

                    $uploadedPaths[] = [
                        'url' => $file,
                        'type' => $request->fdbk_video_type[$index],
                    ];
                }

                // Handle feedback content - ACCUMULATE instead of overwrite
                // Handle feedback content - ACCUMULATE instead of overwrite
                $existingFeedbackContent = FeedbackContent::where('purchase_video_id', $purchaseVideo->id)->first();
                $existingUrls = [];

                if ($existingFeedbackContent && !empty($existingFeedbackContent->url)) {
                    if (is_string($existingFeedbackContent->url) && $this->isJson($existingFeedbackContent->url)) {
                        $existingUrls = json_decode($existingFeedbackContent->url, true);
                    } elseif (is_string($existingFeedbackContent->url)) {
                        // It's a plain string, convert to array with single entry
                        $existingUrls = [['url' => $existingFeedbackContent->url, 'type' => 'unknown']];
                    } elseif (is_array($existingFeedbackContent->url)) {
                        $existingUrls = $existingFeedbackContent->url;
                    }
                }

                // Merge new URLs with existing
                $allUrls = array_merge($existingUrls, $uploadedPaths);

                FeedbackContent::updateOrCreate(
                    ['purchase_video_id' => $purchaseVideo->id],
                    ['url' => json_encode($allUrls)]
                );
                // }

                $purchaseVideo->isFeedbackComplete = 1;
                $purchaseVideo->save();

                $purchaseVideo->load('purchase');
                $allPurchaseVideosFeedback = PurchaseVideos::where('purchase_id', $purchaseVideo->purchase->id)
                    ->where('isFeedbackComplete', 0)
                    ->get();

                // Send email notification
                SendEmail::dispatch(
                    $purchaseVideo->purchase->student?->email,
                    new PurchaseFeedback($purchaseVideo->purchase)
                );

                // Send push notification
                $message = __(':name has sent feedback for your online submission.', [
                    'name' => $purchaseVideo->purchase->lesson->user->name,
                ]);

                if (isset($purchaseVideo->purchase->student->pushToken->token)) {
                    SendPushNotification::dispatch(
                        $purchaseVideo->purchase->student->pushToken->token,
                        'Feedback Received',
                        $message
                    );
                }

                // ✅ Mark purchase feedback complete if all done
                if (
                    $purchaseVideo->purchase->lessons_used == $purchaseVideo->purchase->lesson->lesson_quantity &&
                    $allPurchaseVideosFeedback->isEmpty()
                ) {
                    $purchase = Purchase::find($purchaseVideo->purchase_id);
                    $purchase->isFeedbackComplete = 1;
                    $purchase->save();
                }

                if ($request->redirect == 1) {
                    return redirect(session('previous_url', '/default'))->with('success', 'Feedback Added Successfully');
                }

                return back()->with('success', 'Feedback Added Successfully');
            }

            return back()->with('failed', 'You are not authorized to perform this action.');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('failed', $e->getMessage());
        }
    }

    // Helper function to check if string is JSON
    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }


    public function editFeedBackIndex(Request $request)
    {
        if (Auth::user()->can('manage-purchases')) {
            session()->put('previous_url', url()->previous());
            $purchase = Purchase::find($request->purchase_id);
            $purchaseVideo = $purchase->videos?->first();
            $feedbackContent = FeedbackContent::where('purchase_video_id', $purchaseVideo->id)->first();

            return view('admin.purchases.editFeedback', compact('purchase', 'purchaseVideo', 'feedbackContent'));
        }
    }

    // public function updateFeedBack(Request $request)
    // {
    //     $request->validate([
    //        'existing_row_feedback' => 'sometimes|array',
    //         'existing_row_feedback.*' => 'required|string',
    //         'new_row_feedback' => 'sometimes|array',
    //         'new_row_feedback.*' => 'required|string',
    //         'existing_fdbk_video_path' => 'sometimes|array',
    //         'existing_fdbk_video_path.*' => 'sometimes|url',
    //         'new_fdbk_video_path' => 'sometimes|array',
    //         'new_fdbk_video_path.*' => 'sometimes|url',
    //         'purchase_video_id' => 'required|exists:purchasevideos,id',
    //         'purchase_id' => 'required|exists:purchases,id',
    //     ]);

    //     try {
    //         $purchaseVideo = PurchaseVideos::findOrFail($request->purchase_video_id);
    //         $feedbackContent = FeedbackContent::where('purchase_video_id', $purchaseVideo->id)->first();

    //         // Get existing data
    //         $existingFeedback = json_decode($purchaseVideo->feedback, true) ?? [];
    //         $existingVideos = [];
    //         if ($feedbackContent && !empty($feedbackContent->url)) {
    //             $existingVideos = is_string($feedbackContent->url)
    //                 ? json_decode($feedbackContent->url, true)
    //                 : $feedbackContent->url;
    //         }

    //         // Handle deletions first
    //         if ($request->has('deleted_indexes')) {
    //             $deletedIndexes = $request->deleted_indexes;

    //             // Sort in descending order to avoid index issues when removing
    //             rsort($deletedIndexes);

    //             foreach ($deletedIndexes as $index) {
    //                 // Remove from feedback array
    //                 if (isset($existingFeedback[$index])) {
    //                     unset($existingFeedback[$index]);
    //                 }

    //                 // Remove video file from storage and array
    //                 if (isset($existingVideos[$index])) {
    //                     $videoUrl = $existingVideos[$index]['url'];
    //                     $this->deleteVideoFromStorage($videoUrl);
    //                     unset($existingVideos[$index]);
    //                 }
    //             }

    //             // Reindex arrays after deletion
    //             $existingFeedback = array_values($existingFeedback);
    //             $existingVideos = array_values($existingVideos);
    //         }

    //         // Update existing items
    //         if ($request->has('existing_row_feedback')) {
    //             foreach ($request->existing_row_feedback as $index => $feedback) {
    //                 if (isset($existingFeedback[$index])) {
    //                     $existingFeedback[$index] = $feedback;
    //                 }

    //                 // Handle video replacement if new file uploaded
    //                 if ($request->hasFile("existing_fdbk_video.{$index}")) {
    //                     $file = $request->file("existing_fdbk_video.{$index}");

    //                     // Delete old video from storage
    //                     if (isset($existingVideos[$index])) {
    //                         $oldVideoUrl = $existingVideos[$index]['url'];
    //                         $this->deleteVideoFromStorage($oldVideoUrl);
    //                     }

    //                     // Upload new video
    //                     $currentDomain = tenant('domains')[0]->domain;
    //                     $extension = $file->getClientOriginalExtension();
    //                     $randomFileName = Str::random(25) . '.' . $extension;
    //                     $filePath = $currentDomain . '/' . $purchaseVideo->id . '/' . $randomFileName;
    //                     Storage::disk('spaces')->put($filePath, file_get_contents($file), 'public');
    //                     $newPath = Storage::disk('spaces')->url($filePath);

    //                     $type = Str::contains($file->getMimeType(), 'video') ? 'video' : 'image';

    //                     if (isset($existingVideos[$index])) {
    //                         $existingVideos[$index] = ['url' => $newPath, 'type' => $type];
    //                     }
    //                 }
    //             }
    //         }

    //         // Add new items
    //         if ($request->has('new_row_feedback')) {
    //             foreach ($request->new_row_feedback as $index => $newFeedback) {
    //                 $existingFeedback[] = $newFeedback;

    //                 if ($request->hasFile("new_fdbk_video.{$index}")) {
    //                     $file = $request->file("new_fdbk_video.{$index}");

    //                     $currentDomain = tenant('domains')[0]->domain;
    //                     $extension = $file->getClientOriginalExtension();
    //                     $randomFileName = Str::random(25) . '.' . $extension;
    //                     $filePath = $currentDomain . '/' . $purchaseVideo->id . '/' . $randomFileName;
    //                     Storage::disk('spaces')->put($filePath, file_get_contents($file), 'public');
    //                     $newPath = Storage::disk('spaces')->url($filePath);

    //                     $type = Str::contains($file->getMimeType(), 'video') ? 'video' : 'image';
    //                     $existingVideos[] = ['url' => $newPath, 'type' => $type];
    //                 }
    //             }
    //         }

    //         // Save updated data
    //         $purchaseVideo->feedback = json_encode($existingFeedback);
    //         $purchaseVideo->save();

    //         if ($feedbackContent) {
    //             $feedbackContent->url = json_encode($existingVideos);
    //             $feedbackContent->save();
    //         } else {
    //             FeedbackContent::create([
    //                 'purchase_video_id' => $purchaseVideo->id,
    //                 'url' => json_encode($existingVideos)
    //             ]);
    //         }

    //         if ($request->redirect == 1) {
    //             return redirect(session('previous_url', '/default'))->with('success', 'Feedback Updated Successfully');
    //         }

    //         return back()->with('success', 'Feedback Updated Successfully');
    //     } catch (\Exception $e) {
    //         // dd($e);
    //         report($e);
    //         return redirect()->back()->with('failed', $e->getMessage());
    //     }
    // }

    public function updateFeedBack(Request $request)
    {
        $request->validate([
            'existing_row_feedback' => 'sometimes|array',
            'existing_row_feedback.*' => 'required|string',
            'new_row_feedback' => 'sometimes|array',
            'new_row_feedback.*' => 'required|string',
            'existing_fdbk_video_path' => 'sometimes|array',
            'existing_fdbk_video_path.*' => 'sometimes|url',
            'new_fdbk_video_path' => 'sometimes|array',
            'new_fdbk_video_path.*' => 'sometimes|url',
            'purchase_video_id' => 'required|exists:purchasevideos,id',
            'purchase_id' => 'required|exists:purchases,id',
        ]);

        try {
            $purchaseVideo = PurchaseVideos::findOrFail($request->purchase_video_id);
            $feedbackContent = FeedbackContent::where('purchase_video_id', $purchaseVideo->id)->first();

            // Get existing data
            $existingFeedback = json_decode($purchaseVideo->feedback, true) ?? [];
            $existingVideos = [];
            if ($feedbackContent && !empty($feedbackContent->url)) {
                $existingVideos = is_string($feedbackContent->url)
                    ? json_decode($feedbackContent->url, true)
                    : $feedbackContent->url;
            }

            // Handle deletions first
            if ($request->has('deleted_indexes')) {
                $deletedIndexes = $request->deleted_indexes;

                // Sort in descending order to avoid index issues when removing
                rsort($deletedIndexes);

                foreach ($deletedIndexes as $index) {
                    // Remove from feedback array
                    if (isset($existingFeedback[$index])) {
                        unset($existingFeedback[$index]);
                    }

                    // Remove video file from storage and array
                    if (isset($existingVideos[$index])) {
                        $videoUrl = $existingVideos[$index]['url'];
                        $this->deleteVideoFromStorage($videoUrl);
                        unset($existingVideos[$index]);
                    }
                }

                // Reindex arrays after deletion
                $existingFeedback = array_values($existingFeedback);
                $existingVideos = array_values($existingVideos);
            }

            // Update existing items
            if ($request->has('existing_row_feedback')) {
                foreach ($request->existing_row_feedback as $index => $feedback) {
                    if (isset($existingFeedback[$index])) {
                        $existingFeedback[$index] = $feedback;
                    }

                    // Handle video replacement if new file was uploaded via chunk upload
                    if (
                        $request->has("existing_fdbk_video_path.{$index}") &&
                        !empty($request->existing_fdbk_video_path[$index])
                    ) {

                        $newVideoUrl = $request->existing_fdbk_video_path[$index];
                        $newVideoType = $request->existing_fdbk_video_type[$index] ?? 'video';

                        // Delete old video from storage if it exists
                        if (isset($existingVideos[$index])) {
                            $oldVideoUrl = $existingVideos[$index]['url'];
                            // Only delete if it's a different URL (not the same file)
                            if ($oldVideoUrl !== $newVideoUrl) {
                                $this->deleteVideoFromStorage($oldVideoUrl);
                            }
                        }

                        // Update with new video URL
                        if (isset($existingVideos[$index])) {
                            $existingVideos[$index] = [
                                'url' => $newVideoUrl,
                                'type' => $newVideoType
                            ];
                        }
                    }
                    // If no new video uploaded, keep the existing video URL from hidden field
                    elseif (
                        $request->has("existing_video_url.{$index}") &&
                        !empty($request->existing_video_url[$index])
                    ) {
                        if (isset($existingVideos[$index])) {
                            $existingVideos[$index]['url'] = $request->existing_video_url[$index];
                            // Keep existing type or default to video
                            if (!isset($existingVideos[$index]['type'])) {
                                $existingVideos[$index]['type'] = 'video';
                            }
                        }
                    }
                }
            }

            // Add new items
            if ($request->has('new_row_feedback')) {
                foreach ($request->new_row_feedback as $index => $newFeedback) {
                    $existingFeedback[] = $newFeedback;

                    // Use the uploaded video URL from chunk upload
                    if (
                        $request->has("new_fdbk_video_path.{$index}") &&
                        !empty($request->new_fdbk_video_path[$index])
                    ) {

                        $newVideoUrl = $request->new_fdbk_video_path[$index];
                        $newVideoType = $request->new_fdbk_video_type[$index] ?? 'video';

                        $existingVideos[] = [
                            'url' => $newVideoUrl,
                            'type' => $newVideoType
                        ];
                    }
                }
            }

            // Save updated data
            $purchaseVideo->feedback = json_encode($existingFeedback);
            $purchaseVideo->save();

            if ($feedbackContent) {
                $feedbackContent->url = json_encode($existingVideos);
                $feedbackContent->save();
            } else {
                FeedbackContent::create([
                    'purchase_video_id' => $purchaseVideo->id,
                    'url' => json_encode($existingVideos)
                ]);
            }

            if ($request->redirect == 1) {
                return redirect(session('previous_url', '/default'))->with('success', 'Feedback Updated Successfully');
            }

            return back()->with('success', 'Feedback Updated Successfully');
        } catch (\Exception $e) {
            report($e);
            return redirect()->back()->with('failed', $e->getMessage());
        }
    }

    private function deleteVideoFromStorage($videoUrl)
    {
        try {
            // Extract the path from the full URL
            $path = parse_url($videoUrl, PHP_URL_PATH);
            // Remove leading slash if present
            $path = ltrim($path, '/');

            if (Storage::disk('spaces')->exists($path)) {
                Storage::disk('spaces')->delete($path);
            }
        } catch (\Exception $e) {
            report($e);
            // Log error but don't fail the entire process
        }
    }

    public function addFeedBackIndex(Request $request)
    {
        // if (Auth::user()->can('manage-purchases')) {
        //     $purchaseVideo = PurchaseVideos::where('video_url', $request->purchase_video)->first();

        //     return view('admin.purchases.feedbackForm', compact('purchaseVideo'));
        // }

        if (Auth::user()->can('manage-purchases')) {
            session()->put('previous_url', url()->previous());
            $purchase = Purchase::find($request->purchase_id);
            $purchaseVideo = $purchase->videos?->first();

            return view('admin.purchases.feedbackForm', compact('purchase', 'purchaseVideo'));
        }
    }

    public function deleteFeedback(PurchaseVideos $purchaseVideo)
    {
        if (Auth::user()->can('manage-purchases')) {
            // Clear the feedback field (if stored as a string)
            $purchaseVideo->feedback = null;
            $purchaseVideo->isFeedbackComplete = 0;
            $purchaseVideo->save();

            $feedback_content = FeedbackContent::where('purchase_video_id', $purchaseVideo->id)->delete();
            // $feedback_content->delete();

            return redirect()->back()->with('success', 'Feedback deleted successfully.');
        }
    }
    public function getFollowerPurchases(Request $request)
    {

        if (Auth::user()->can('manage-purchases')) {
            $request->validate([
                'follower_id' => 'required',
            ]);
            if ($follower = Follower::find($request?->follower_id)) {
                return Purchase::where('follower_id', $follower?->id);
            }
        }
    }

    public function getPurchaseVideos(Request $request)
    {

        try {
            if (\Auth::user()->can('manage-purchases')) {
                $request->validate([
                    'purchase_id' => 'required',
                ]);

                if ($purchase = Purchase::find($request?->purchase_id)) {
                    $purchase = $purchase->load('videos');
                    return $purchase->videos->all();
                }
            }
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed.', 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validatedData = $request->validate([
            'follower_id'    => 'required|exists:users,id',
            'influencer_id'  => 'required|exists:influencers,id',
            'lesson_id'      => 'required|exists:lessons,id',
            'payment_method' => 'required|string',
            'payment_date'   => 'required|date',
            'payment_status' => 'required|string',
            'video'          => 'nullable|string',
            'status'         => 'required|string',
        ]);

        $purchase->update($validatedData);
        return redirect()->route('admin.purchases.index', $purchase)->with('success', 'Purchase updated successfully.');
    }

    public function show(Purchase $purchase)
    {
        return view('admin.purchases.index', compact('purchase'));
    }
    public function showLesson(PurchaseLessonDataTable $dataTable, $lessonId)
    {
        $purchase          = Purchase::with('follower')->findOrFail($lessonId);
        $video             = Purchase::with('videos')->find(request()->purchase_id);
        $chatEnabledPlanId = Plan::where('influencer_id', $purchase->influencer_id)
            ->where('is_chat_enabled', true)->pluck('id')->toArray();
        $isSubscribed = in_array($purchase->follower->plan_id, $chatEnabledPlanId);
        $token        = $this->chatService->getChatToken($purchase->follower->chat_user_id);
        return $dataTable->with('purchase', $purchase)->render('admin.purchases.show', compact('purchase', 'video', 'token', 'isSubscribed'));
    }
    public function destroy($id)
    {
        $purchase = Purchase::findOrFail($id);

        // Optional: Additional logic before deletion, if needed
        $purchase->videos()->delete();
        $purchase->delete();

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
    }

    // Add other necessary methods like destroy() if needed
    //
    //
    //
    public function getVideo(PurchaseVideos $video)
    {
        $filePath = Storage::disk('local')->path($video->video_url);
        if (! file_exists($filePath)) {
            abort(404, 'Video not found');
        }
        $fileSize = filesize($filePath);
        $mimeType = 'video/mp4';

        // Standard Headers
        $headers = [
            'Content-Type'        => $mimeType,
            'Cache-Control'       => 'public, max-age=3600',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            'Accept-Ranges'       => 'bytes',
        ];

        // Handle Byte-Range Requests (For Safari & Chrome)
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = $_SERVER['HTTP_RANGE'];
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = intval($matches[1]);
                $end   = isset($matches[2]) && $matches[2] !== '' ? intval($matches[2]) : ($fileSize - 1);

                // Fix for Safari's initial 0-1 range request
                if ($start == 0 && $end == 1) {
                    // Just serve these two bytes as requested, don't modify the range
                    $length                    = 2; // Just the 2 bytes requested
                    $headers['Content-Length'] = $length;
                    $headers['Content-Range']  = "bytes 0-1/$fileSize";

                    return response()->stream(function () use ($filePath) {
                        $handle = fopen($filePath, 'rb');
                        echo fread($handle, 2); // Read only the first 2 bytes
                        fclose($handle);
                    }, 206, $headers);
                }

                // For normal range requests
                $length                    = ($end - $start) + 1;
                $headers['Content-Length'] = $length;
                $headers['Content-Range']  = "bytes $start-$end/$fileSize";

                if ($start > $end || $end >= $fileSize) {
                    header("HTTP/1.1 416 Requested Range Not Satisfiable");
                    header("Content-Range: bytes */$fileSize");
                    exit;
                }

                return response()->stream(function () use ($filePath, $start, $end) {
                    $handle = fopen($filePath, 'rb');
                    fseek($handle, $start);
                    $bufferSize = 8192;
                    $remaining  = ($end - $start) + 1;

                    while (! feof($handle) && $remaining > 0) {
                        $readSize = min($bufferSize, $remaining);
                        echo fread($handle, $readSize);
                        $remaining -= $readSize;
                        flush();
                    }

                    fclose($handle);
                }, 206, $headers);
            }
        }

        // Handle Full File Request (No Range Specified)
        $headers['Content-Length'] = $fileSize;
        return response()->stream(function () use ($filePath) {
            readfile($filePath);
        }, 200, $headers);
    }

    public function purchasePayment(Request $request)
    {
        try {
            // ✅ Validate that lesson_id is provided
            $request->validate([
                'lesson_id' => 'required|exists:lessons,id',
            ]);

            $lesson = Lesson::find($request->lesson_id);

            $student_user = Auth::user();


            // ✅ Create a Stripe Checkout Session
            $session = $this->createSessionForPaymentNew($lesson->id);


            // ✅ Check if session was successfully created
            if (empty($session) || empty($session->url)) {
                return redirect()->back()->withErrors('Failed to generate payment link. Please try again.');
            }

            // ✅ Redirect user to Stripe Checkout
            return redirect($session->url);
        } catch (\Exception $e) {
            // ✅ Handle exceptions gracefully
            \Log::error('Stripe payment session creation failed: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    public function data(Request $request)
    {


        if ($request->slot_id) {

            $slots = Slots::find($request->slot_id);

            $purchases = Purchase::with(['follower', 'lesson', 'lesson.user']) // eager load relationships if needed
                ->where('slot_id', $request->slot_id)
                ->where('lesson_id', $request->lesson_id)
                ->get();

            return  $purchases;
        }


        return [];
    }

     public function subscription_index(Request $request)
    {
        // dd("test");
        if (Auth::user()->can('manage-purchases')) {
            $query = ClientSubscription::with(['follower', 'plan'])
                ->where('influencer_id', Auth::id());

            if ($request->ajax()) {
                return datatables()
                    ->eloquent($query)
                    ->addIndexColumn()
                    ->addColumn('student_name', function ($subscription) {
                        return $subscription->follower ? e($subscription->follower->name) : 'N/A';
                    })
                    ->addColumn('plan_name', function ($subscription) {
                        return $subscription->plan ? e($subscription->plan->name) : 'N/A';
                    })
                  ->addColumn('subscription_history', function ($subscription) {
                        $history = '<ul class="subscription-history-list">';

                        // Current subscription info
                        if ($subscription->plan) {
                            $history .= '<li class="subscription-history-item">';
                            $history .= '<span class="history-date">' . $subscription->created_at->format('M d, Y h:i A') . '</span> - ';
                            $history .= '<span class="history-price">$' . number_format($subscription->plan->price, 2) . '</span>';
                            $history .= '</li>';
                        }

                        // Subscription details history
                        if ($subscription->details->isNotEmpty()) {
                            foreach ($subscription->details as $detail) {
                                $history .= '<li class="subscription-history-item">';
                                $history .= '<span class="history-date">' . $detail->created_at->format('M d, Y h:i A') . '</span> - ';

                                // Get price from associated plan
                                if ($detail->clientSubscription?->plan) {
                                    $history .= '<span class="history-price">$' . number_format($detail->clientSubscription?->plan->price, 2) . '</span>';
                                } elseif ($detail->old_plan_details) {
                                    $oldDetails = json_decode($detail->old_plan_details, true);
                                    $history .= '<span class="history-price">$' . number_format($oldDetails['price'] ?? 0, 2) . '</span>';
                                } else {
                                    $history .= '<span class="history-price">$0.00</span>';
                                }

                                $history .= '</li>';
                            }
                        }

                        $history .= '</ul>';
                        return $history;
                    })
                    ->addColumn('status_badge', function ($subscription) {
                        $badgeClass = match ($subscription->status) {
                            'active' => 'badge bg-success',
                            'pending' => 'badge bg-warning',
                            'cancelled' => 'badge bg-danger',
                            'expired' => 'badge bg-secondary',
                            default => 'badge bg-secondary'
                        };

                        return '<span class="' . $badgeClass . '">' . ucfirst($subscription->status) . '</span>';
                    })
                    ->rawColumns(['status_badge'])
                    ->make(true);
            }
            // dd("test");
            return view('admin.subscriptions.index');
        }
    }

     public function smartVideoDownload($id)
    {
        $purchase = Purchase::findOrFail($id);
        $url = trim($purchase->videos->first()?->video_url ?? '');

        if (empty($url)) {
            abort(404, 'Video URL not found');
        }

        // ── Filename handling ───────────────────────────────────────────────
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $originalFilename = basename($path);

        $baseName = pathinfo($originalFilename, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

        $baseName = preg_replace('/[^a-zA-Z0-9-_]/', '-', $baseName);
        $downloadName = $baseName ?: 'video';

        // ── Is m3u8? ─────────────────────────────────────────────────────────
        $isM3u8 = false;
        $lowerUrl = strtolower($url);

        if (
            str_ends_with($lowerUrl, '.m3u8') ||
            strpos($lowerUrl, '.m3u8?') !== false ||
            strpos($lowerUrl, '/playlist.m3u8') !== false ||
            strpos($lowerUrl, '/master.m3u8') !== false ||
            strpos($lowerUrl, '/index.m3u8') !== false
        ) {
            $isM3u8 = true;
            $downloadName .= '.mov';
        } else {
            $downloadName .= $extension ? '.' . $extension : '.mp4';
        }

        // Common headers
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        if ($isM3u8) {
            header('Content-Type: video/mp4');  // ← change to mp4 mime
            header('Content-Disposition: attachment; filename="' . str_replace('.mov', '.mp4', $downloadName) . '"');

            $cmd = "ffmpeg " .
                "-protocol_whitelist file,http,https,tcp,tls,crypto " .
                "-i " . escapeshellarg($url) . " " .
                "-map 0:v? -map 0:a? " .
                "-c:v copy " .
                "-c:a aac -b:a 160k -ar 48000 " .
                "-af aformat=channel_layouts=stereo " .
                "-bsf:a aac_adtstoasc " .
                "-f mp4 " .                                 // ← important: mp4 instead of mov
                "-movflags frag_keyframe+empty_moov+omit_tfhd_offset+default_base_moof " .  // better fragmentation flags
                "pipe:1 2>/dev/null";

            set_time_limit(0);
            passthru($cmd);
            exit;
        } else {
            // ── Direct files (mp4, mov, webm, etc.) ──────────────────────────
            header('Content-Type: video/mp4');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');

            set_time_limit(0);

            // Try simple readfile first
            if (@readfile($url) === false) {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 600,
                        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n" .
                            "Referer: " . request()->getSchemeAndHttpHost() . "\r\n"
                    ]
                ]);

                $stream = @fopen($url, 'rb', false, $context);
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                } else {
                    http_response_code(503);
                    echo "Cannot access the video file at the moment.";
                    exit;
                }
            }

            exit;
        }
    }
    public function smartVideoDownload2($id)
    {
        $purchase = Purchase::findOrFail($id);
        $url = trim($purchase->videos->first()?->video_url_2 ?? '');

        if (empty($url)) {
            abort(404, 'Video URL not found');
        }

        // ── Filename handling ───────────────────────────────────────────────
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'] ?? '';
        $originalFilename = basename($path);

        $baseName = pathinfo($originalFilename, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));

        $baseName = preg_replace('/[^a-zA-Z0-9-_]/', '-', $baseName);
        $downloadName = $baseName ?: 'video';

        // ── Is m3u8? ─────────────────────────────────────────────────────────
        $isM3u8 = false;
        $lowerUrl = strtolower($url);

        if (
            str_ends_with($lowerUrl, '.m3u8') ||
            strpos($lowerUrl, '.m3u8?') !== false ||
            strpos($lowerUrl, '/playlist.m3u8') !== false ||
            strpos($lowerUrl, '/master.m3u8') !== false ||
            strpos($lowerUrl, '/index.m3u8') !== false
        ) {
            $isM3u8 = true;
            $downloadName .= '.mov';
        } else {
            $downloadName .= $extension ? '.' . $extension : '.mp4';
        }

        // Common headers
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        if ($isM3u8) {
            header('Content-Type: video/mp4');  // ← change to mp4 mime
            header('Content-Disposition: attachment; filename="' . str_replace('.mov', '.mp4', $downloadName) . '"');

            $cmd = "ffmpeg " .
                "-protocol_whitelist file,http,https,tcp,tls,crypto " .
                "-i " . escapeshellarg($url) . " " .
                "-map 0:v? -map 0:a? " .
                "-c:v copy " .
                "-c:a aac -b:a 160k -ar 48000 " .
                "-af aformat=channel_layouts=stereo " .
                "-bsf:a aac_adtstoasc " .
                "-f mp4 " .                                 // ← important: mp4 instead of mov
                "-movflags frag_keyframe+empty_moov+omit_tfhd_offset+default_base_moof " .  // better fragmentation flags
                "pipe:1 2>/dev/null";

            set_time_limit(0);
            passthru($cmd);
            exit;
        } else {
            // ── Direct files (mp4, mov, webm, etc.) ──────────────────────────
            header('Content-Type: video/mp4');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');

            set_time_limit(0);

            // Try simple readfile first
            if (@readfile($url) === false) {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 600,
                        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n" .
                            "Referer: " . request()->getSchemeAndHttpHost() . "\r\n"
                    ]
                ]);

                $stream = @fopen($url, 'rb', false, $context);
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                } else {
                    http_response_code(503);
                    echo "Cannot access the video file at the moment.";
                    exit;
                }
            }

            exit;
        }
    }

}
