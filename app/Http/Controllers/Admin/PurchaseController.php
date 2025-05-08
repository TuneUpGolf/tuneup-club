<?php
namespace App\Http\Controllers\Admin;

use App\Actions\SendEmail;
use App\Actions\SendPushNotification;
use App\DataTables\Admin\PurchaseDataTable;
use App\DataTables\Admin\PurchaseLessonDataTable;
use App\DataTables\Admin\PurchaseLessonVideoDataTable;
use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseAPIResource;
use App\Http\Resources\PurchaseVideoAPIResource;
use App\Mail\Admin\PurchaseCompleted;
use App\Mail\Admin\PurchaseFeedback;
use App\Mail\Admin\VideoAdded;
use App\Models\Coupon;
use App\Models\FeedbackContent;
use App\Models\Follower;
use App\Models\Lesson;
use App\Models\Purchase;
use App\Models\PurchaseVideos;
use App\Models\Role;
use App\Models\Slots;
use App\Models\User;
use App\Traits\ConvertVideos;
use App\Traits\PurchaseTrait;
use Error;
use Exception;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PurchaseController extends Controller
{
    use PurchaseTrait;
    use ConvertVideos;

    public function index(PurchaseDataTable $dataTable)
    {
        if (Auth::user()->can('manage-purchases')) {
            return $dataTable->render('admin.purchases.index');
        }

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

                return redirect()->route('purchase.video.index', ['purchase_id' => $newPurchase->id, 'checkout' => true])->with('success', 'Purchase created successfully, please add video and proceed to checkout.');
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
                $session = Session::retrieve($purchase->session_id);

                if ($session->payment_status == "paid") {
                    $purchase->status = Purchase::STATUS_COMPLETE;
                    $purchase->save();

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
                        SendPushNotification::dispatch($purchase?->follower?->pushToken?->token, 'Purchase Confirmed', $message);
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
            return redirect(route('purchase.index'))->with('success', 'Payment Cancelled');
        }
        return response("Payment Cancelled Successfully");
    }

    public function purchaseCancle()
    {
        return redirect(route('purchase.index'))->with('error', 'There was a problem with your payment');
    }

    public function addVideo(Request $request)
    {
        $request->validate([
            'video'       => 'required|mimetypes:video/avi,video/mpeg,video/quicktime,video/mov,video/mp4',
            'video_2'     => 'mimetypes:mimetypes:video/avi,video/mpeg,video/quicktime,video/mov,video/mp4',
            'purchase_id' => 'required',
        ]);
        $purchase      = Purchase::with('lesson')->find($request?->purchase_id);
        $currentDomain = tenant('domains');
        $currentDomain = $currentDomain[0]->domain;
        if (isset($purchase) && Auth::user()->type == Role::ROLE_FOLLOWER) {
            if ($purchase?->lesson->lesson_quantity > $purchase->lessons_used) {
                try {
                    $purchase_video = PurchaseVideos::create([
                        'tenant_id'   => Auth::user()->tenant_id,
                        'purchase_id' => $request?->purchase_id,
                        'note'        => $request?->note,
                    ]);
                    if ($request?->hasFile('video')) {
                        $file = $request->file('video');
                        if (Str::endsWith($file->getClientOriginalName(), '.mov')) {
                            $localPath = $request->file('video')->store('purchaseVideos');
                            $path      = $this->convertSingleVideo($localPath);
                        } else {
                            // Digital Ocean space storage
                            $file           = $request->file('video');
                            $extension      = $file->getClientOriginalExtension();
                            $randomFileName = Str::random(25) . '.' . $extension;
                            $filePath       = $currentDomain . '/' . $purchase->lesson_id . '/' . $purchase->follower_id . '/' . $randomFileName;
                            Storage::disk('spaces')->put($filePath, file_get_contents($file), 'public');
                            $path = Storage::disk('spaces')->url($filePath);
                        }

                        $purchase_video->video_url = $path;
                        $purchase_video->save();
                    }

                    if ($request?->hasFile('video_2')) {
                        $file2 = $request->file('video_2');
                        if (Str::endsWith($file2->getClientOriginalName(), '.mov')) {
                            $localPath = $request->file('video_2')->store('purchaseVideos');
                            $path      = $this->convertSingleVideo($localPath);
                        } else {
                            // Digital Ocean space storage
                            $extension      = $file2->getClientOriginalExtension();
                            $randomFileName = Str::random(25) . '.' . $extension;
                            $filePath       = $currentDomain . '/' . $purchase->lesson_id . '/' . $purchase->follower_id . '/' . $randomFileName;
                            Storage::disk('spaces')->put($filePath, file_get_contents($file2), 'public');
                            $path = Storage::disk('spaces')->url($filePath);
                        }

                        $purchase_video->video_url_2 = $path;
                        $purchase_video->save();
                    }

                    $purchase->lessons_used = $purchase->lessons_used + 1;
                    if ($purchase->lesson_used == $purchase?->lesson->lesson_quantity) {
                        $purchase->isFeedbackComplete = 1;
                    }
                    $purchase->save();

                    if ($purchase->status === Purchase::STATUS_COMPLETE) {
                        SendEmail::dispatch($purchase?->lesson?->user?->email, new VideoAdded($purchase));

                        $message = __('Hello, :name, has submitted an online submission.', [
                            'name' => $purchase->follower->name,
                        ]);

                        SendPushNotification::dispatch($purchase?->lesson?->user?->pushToken?->token, 'Video Submitted', $message);
                    }

                    if ($request->checkout == 1) {
                        $request->merge(['purchase_id' => $purchase->id]);
                        $request->setMethod('POST');
                        return $this->confirmPurchaseWithRedirect($request);
                    } else if ($request->redirect == 1) {
                        return redirect()->route('purchase.index')->with('success', 'Video Successfully Added');
                    }
                } catch (\Exception $e) {
                    return redirect()->back()->with('errors', $e->getMessage());
                } catch (Error $e) {
                    return response($e, 419);
                }
            } else {
                return throw new ValidationException(['You dont have enough lessons remaining']);
            }
        } else {
            return throw new ValidationException(['No purchase found for this ID']);
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

                        SendPushNotification::dispatch($purchase->lesson?->user?->pushToken?->token, 'Video Submitted', $message);
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
                            SendPushNotification::dispatch($purchase?->follower?->pushToken?->token, 'Feedback Recieved', $message);
                        }

                    }
                    $allPurchaseVideosFeedback = PurchaseVideos::where('purchase_id', $purchaseVideo->purchase->id)->where('isFeedbackComplete', 0)->get();
                    if (($purchaseVideo->purchase->lessons_used == $purchaseVideo->purchase->lesson->lesson_quantity) && ! ! isEmpty($allPurchaseVideosFeedback)) {
                        $purchase                     = Purchase::find($purchaseVideo->purchase_id);
                        $purchase->isFeedbackComplete = 1;
                        $purchase->save();
                    }
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
            $purchase = Purchase::with(['videos', 'lesson', 'follower', 'influencer'])
                ->find(request()->purchase_id);
            return view('admin.purchases.videos', compact('purchase'));
            
        }
    }
    public function addFeedBack(Request $request)
    {
        $request->validate([
            'feedback'          => 'required',
            'purchase_video_id' => 'required',
            'fdbk_video'        => 'required',
        ]);

        try {
            if (Auth::user()->can('manage-purchases') && $purchaseVideo = PurchaseVideos::find($request->purchase_video_id)) {
                $purchaseVideo->feedback = $request->feedback;

                if ($request?->hasFile('fdbk_video')) {
                    foreach ($request->file('fdbk_video') as $file) {
                        $path = $file->store('feedbackContent');
                        $type = Str::contains($file->getMimeType(), 'video') ? 'video' : 'image';
                        FeedbackContent::create([
                            'purchase_video_id' => $purchaseVideo->id,
                            'url'               => $path,
                            'type'              => $type,
                        ]);
                    }
                }

                $purchaseVideo->isFeedbackComplete = 1;
                $purchaseVideo->update();

                $purchaseVideo->load('purchase');
                $allPurchaseVideosFeedback = PurchaseVideos::where('purchase_id', $purchaseVideo->purchase->id)->where('isFeedbackComplete', 0)->get();
                SendEmail::dispatch($purchaseVideo->purchase->follower->email, new PurchaseFeedback($purchaseVideo->purchase));

                $message = __(':name, has sent feedback for your online submission.', [
                    'name' => $purchaseVideo->purchase->lesson->user->name,
                ]);

                SendPushNotification::dispatch($purchaseVideo->purchase->follower->pushToken->token, 'Feedback Recieved', $message);

                if (($purchaseVideo->purchase->lessons_used == $purchaseVideo->purchase->lesson->lesson_quantity) && ! ! isEmpty($allPurchaseVideosFeedback)) {
                    $purchase                     = Purchase::find($purchaseVideo->purchase_id);
                    $purchase->isFeedbackComplete = 1;
                    $purchase->save();
                }
                if ($request->redirect == 1) {
                    return redirect()->route('purchase.feedback.index', ['purchase_id' => $purchaseVideo->purchase_id])->with('success', 'Feedback Added Successfully');
                }
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('errors', $e->getMessage());
        }
        {
            $purchaseVideo = PurchaseVideos::find($request->purchase_video);
            return view('admin.purchases.feedbackForm', compact('purchaseVideo'));
        }
    }

    public function addFeedBackIndex(Request $request)
    {
        if (Auth::user()->can('manage-purchases')) {
            $purchaseVideo = PurchaseVideos::where('video_url', $request->purchase_video)->first();

            return view('admin.purchases.feedbackForm', compact('purchaseVideo'));
        }
    }

    public function deleteFeedback(PurchaseVideos $purchaseVideo)
    {
        if (Auth::user()->can('manage-purchases')) {
            // Clear the feedback field (if stored as a string)
            $purchaseVideo->feedback = null;
            $purchaseVideo->save();

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
        $purchase = Purchase::with('follower')->findOrFail($lessonId);
        $video    = Purchase::with('videos')->find(request()->purchase_id);
        return $dataTable->with('purchase', $purchase)->render('admin.purchases.show', compact('purchase', 'video'));
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
}
