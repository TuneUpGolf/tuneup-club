<?php

namespace App\Http\Controllers\Admin;

use DatePeriod;
use Carbon\Carbon;
use App\Models\Plan;
use App\Models\Post;
use App\Models\Role;
use App\Models\User;
use App\Models\Album;
use App\Models\Event;
use App\Models\Order;
use App\Models\Posts;
use App\Models\Follow;
use App\Models\Lesson;
use App\Facades\Utility;
use App\Models\Follower;
use App\Models\Purchase;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use App\Models\AlbumCategory;
use App\Models\SupportTicket;
use App\Services\ChatService;
use App\Facades\UtilityFacades;
use App\Models\DocumentGenrator;
use Illuminate\Support\Facades\DB;
use App\Services\InfluncerServices;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Providers\AuthServiceProvider;
use App\DataTables\Admin\SalesDataTable;
use App\DataTables\Admin\PurchaseDataTable;

class HomeController extends Controller
{
    protected $chatService;
    protected $utility;
    public function __construct(ChatService $chatService, Utility $utility)
    {
        $this->chatService = $chatService;
        $this->utility = $utility;
    }

    public function testt($page = 1)
    {
        $chatBaseUrl = env("CHAT_BASE_URL");
        $user = auth()->user();
        $token        = $this->chatService->getChatToken($user->chat_user_id);
        $groupId = $user->group_id;
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->post("{$chatBaseUrl}/brainvire-chat-base-app/api/v1/chat/list", [
                'groupId' => $groupId,
                'userType' => 'onetoone',
                'perPage' => 15,
                'page' => $page,
            ]);

            if ($response->successful()) {
                dd($response, $response->json(), $groupId);
                return $response->json();
            } else {
                // Handle error response
                return [
                    'error' => true,
                    'status' => $response->status(),
                    'message' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            // Handle exception
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    public function landingPage()
    {
        $plans = tenancy()->central(function ($tenant) {
            return Plan::where('active_status', 1)->get();
        });

        return view('welcome', compact('plans'));
    }
    public function index(PurchaseDataTable $dataTable, Request $request)
    {

        $user     = Auth::user();
        $userType = $user->type;

        $tenantId = tenant('id');

        // Common Queries
        $chatEnabled    = $this->utility->chatEnabled($user);
        $paymentTypes   = UtilityFacades::getpaymenttypes();
        $documents      = DocumentGenrator::where('tenant_id', $tenantId)->count();
        $documentsDatas = DocumentGenrator::where('tenant_id', $tenantId)->latest()->take(5)->get();
        $events         = Event::latest()->take(5)->get();
        $supports       = tenancy()->central(fn($tenant) => SupportTicket::where('tenant_id', $tenant->id)->latest()->take(7)->get());

        $posts = Post::where('status', 'active');
        switch (request()->query('filter')) {
            case ('free'):
                $posts = $posts->where('paid', 0);
                break;
            case ('paid'):
                $posts = $posts->where('paid', 1);
                break;
        }
        $posts = $posts->orderBy('column_order', 'asc')->paginate(6);
     

        if ($userType == Role::ROLE_FOLLOWER) {

            $tab = $request->tab ?? 'lessons';
            if ($tab == 'chat') {
                $token        = $this->chatService->getChatToken($user->chat_user_id);
            }
            // dd($token, $user->chat_user_id);
            $category = $request->category ?? 'all_category';
            $albums = null;
            if ($category == 'all_category') {
                $albums = AlbumCategory::get();
            }

            $categoryAlbum = $request->category_album;
            if ($categoryAlbum) {
                $albumcategories = Album::where('album_category_id', $categoryAlbum)->orderBy('column_order', 'asc')->get();
            } else {
                $albumcategories = null;
            }
           

            return $this->followerDashboard([
                'dataTable'      => $dataTable,
                'user'           => $user,
                'paymentTypes'   => $paymentTypes,
                'documents'      => $documents,
                'documentsDatas' => $documentsDatas,
                'posts'          => $posts,
                'events'         => $events,
                'supports'       => $supports,
                'token'          => $token ?? null,
                'chatEnabled'    => $chatEnabled,
                'tab'            => $tab,
                'albums'         => $albums,
                'albumcategories' => $albumcategories,
            ], $request);
        }

        $userFromEmail = User::where('email', $user->email)->first();
        // Fetch Plan Expiration
        $planExpiredDate = $userType == AuthServiceProvider::ADMIN_TYPE
            ? tenancy()->central(fn($tenant) => $userFromEmail->plan_expired_date ?? null)
            : $userFromEmail->plan_expired_date ?? '';

        // Fetch influencer Count
        $influencer = User::where('tenant_id', $tenantId)->where('type', Role::ROLE_INFLUENCER)->count();
        $followers  = Follower::where('tenant_id', $tenantId)->where('active_status', true)->where('isGuest', false)->count();

        // Fetch Lessons Count
        $lessons = ($userType == "Admin")
            ? Lesson::where('tenant_id', $tenantId)->count()
            : Lesson::where('tenant_id', $tenantId)->where('created_by', $user->id)->count();

        $influencerLesson = Lesson::where('tenant_id', $tenantId)->where('created_by', $user->id)->get();

        // Fetch Earnings
        $earning = ($userType === Role::ROLE_INFLUENCER)
            ? Purchase::where('influencer_id', $user->id)->where('status', 'complete')->sum('total_amount')
            : Purchase::where('status', 'complete')->sum('total_amount');

        $order_earning = Order::sum('net_amount');
        $earning       = $earning + $order_earning;
        // Fetch Influencer Statistics for Admins (Without Follower Count)
        $influencerStats = [];
        if ($userType == "Admin" || $userType == "Influencer") {
            $influencerStats = User::where('tenant_id', $tenantId)
                ->where('type', Role::ROLE_INFLUENCER)
                ->withCount([
                    'lessons as lesson_count',
                    'purchase as completed_online_lessons'   => fn($query)   => $query->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', true)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_ONLINE)),
                    'purchase as completed_inperson_lessons' => fn($query) => $query->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', true)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_INPERSON)),
                    'purchase as pending_online_lessons'     => fn($query)     => $query->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', false)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_ONLINE)),
                    'purchase as pending_inperson_lessons'   => fn($query)   => $query->where('isFeedbackComplete', false)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_INPERSON)),
                ])
                ->with([
                    'pendingOnlinePurchases' => fn($query) => $query->with('lesson'),
                ])
                ->get();
        }


        [$purchaseComplete, $purchaseInprogress] = $this->fetchPurchaseStats($user, Lesson::LESSON_TYPE_ONLINE);
        [$inPersonCompleted, $inPersonPending]   = $this->fetchPurchaseStats($user, Lesson::LESSON_TYPE_INPERSON);

        return $dataTable->render('admin.dashboard.home', compact(
            'user',
            'userType',
            'influencer',
            'followers',
            'lessons',
            'planExpiredDate',
            'earning',
            'paymentTypes',
            'documents',
            'documentsDatas',
            'posts',
            'events',
            'supports',
            'purchaseComplete',
            'purchaseInprogress',
            'inPersonCompleted',
            'inPersonPending',
            'influencerStats',
            'chatEnabled',
        ));
    }

    // Fetch purchase counts based on lesson type
    private function fetchPurchaseStats($user, $lessonType)
    {
        $query = Purchase::whereHas('lesson', fn($q) => $q->where('type', $lessonType));

        if ($user->type == "Influencer") {
            $query->where('influencer_id', $user->id);
        }

        if ($lessonType == Lesson::LESSON_TYPE_ONLINE) {
            $query->where('status', Purchase::STATUS_COMPLETE);
        }

        $completed  = (clone $query)->where('isFeedbackComplete', true)->count();
        $inprogress = $query->where('isFeedbackComplete', false)->count();

        return [$completed, $inprogress];
    }

    // Follower Dashboard
    private function followerDashboard($data, $request)
    {
        $datatable      = $data['dataTable'];
        $user           = $data['user'];
        $paymentTypes   = $data['paymentTypes'];
        $documents      = $data['documents'];
        $documentsDatas = $data['documentsDatas'];
        $events         = $data['events'];
        $supports       = $data['supports'];
        $token          = $data['token'];
        $chatEnabled    = $data['chatEnabled'];
        $tab            = $data['tab'];
        $posts          = $data['posts'];
        $albums         = $data['albums'];
        $albumcategories = $data['albumcategories'];

        $influencer   = User::where('type', Role::ROLE_INFLUENCER)->first();
        $totalLessons = Lesson::where('created_by', $influencer->id)->count();
        $section      = $request->section;
        $follow       = Follow::where('influencer_id', $influencer->id);
        $isFollowing  = $follow->where('follower_id', Auth::user()->id)
            ->where('active_status', 1)
            ->exists();
        $plans             = Plan::where('influencer_id', $influencer->id)->orderBy('column_order', 'asc')->get();

        $isInfluencer      = Auth::user()->type === Role::ROLE_INFLUENCER;
        $feedEnabledPlanId = Plan::where('influencer_id', $influencer->id)
            ->where('is_feed_enabled', true)->pluck('id')->toArray();

        $isSubscribed = in_array(Auth::user()->plan_id, $feedEnabledPlanId);

        $purchaseComplete   = Purchase::where('follower_id', $user->id)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_ONLINE))->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', true)->count();
        $purchaseInprogress = Purchase::where('follower_id', $user->id)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_ONLINE))->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', false)->count();
        $inPersonCompleted  = Purchase::where('follower_id', $user->id)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_INPERSON))->where('isFeedbackComplete', true)->count();
        $inPersonPending    = Purchase::where('follower_id', $user->id)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_INPERSON))->where('isFeedbackComplete', false)->count();
     
        return $datatable->render('admin.dashboard.home', compact(
            'user',
            'paymentTypes',
            'documents',
            'documentsDatas',
            'events',
            'supports',
            'purchaseComplete',
            'purchaseInprogress',
            'inPersonCompleted',
            'inPersonPending',
            'influencer',
            'totalLessons',
            'section',
            'posts',
            'follow',
            'plans',
            'isInfluencer',
            'isSubscribed',
            'isFollowing',
            'influencer',
            'chatEnabled',
            'token',
            'tab',
            'albums',
            'albumcategories'
        ));
    }

    public function sales(SalesDataTable $dataTable)
    {
        if (Auth::user()->type == 'Super Admin' | Auth::user()->type == 'Admin') {
            return $dataTable->render('admin.sales.index');
        } else {
            return redirect()->back()->with('failed', __('Permission denied.'));
        }
    }

    public function chart(Request $request)
    {
        $arrLable   = [];
        $arrValue   = [];
        $startDate  = Carbon::parse($request->start);
        $endDate    = Carbon::parse($request->end);
        $monthsDiff = $endDate->diffInMonths($startDate);
        if ($monthsDiff >= 0 && $monthsDiff < 3) {
            $endDate    = $endDate->addDay();
            $interval   = CarbonInterval::day();
            $timeType   = "date";
            $dateFormat = "DATE_FORMAT(created_at, '%Y-%m-%d')";
        } elseif ($monthsDiff >= 3 && $monthsDiff < 12) {
            $interval   = CarbonInterval::month();
            $timeType   = "month";
            $dateFormat = "DATE_FORMAT(created_at, '%Y-%m')";
        } else {
            $interval   = CarbonInterval::year();
            $timeType   = "year";
            $dateFormat = "YEAR(created_at)";
        }
        $userReaports = User::select(DB::raw($dateFormat . ' AS ' . $timeType . ',COUNT(id) AS userCount'))
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->groupBy(DB::raw($dateFormat))
            ->get()
            ->toArray();
        $dateRange = new DatePeriod($startDate, $interval, $endDate);
        switch ($timeType) {
            case 'date':
                $format      = 'Y-m-d';
                $labelFormat = 'd M';
                break;
            case 'month':
                $format      = 'Y-m';
                $labelFormat = 'M Y';
                break;
            default:
                $format      = 'Y';
                $labelFormat = 'Y';
                break;
        }
        foreach ($dateRange as $date) {
            $foundReport = false;
            $Date        = Carbon::parse($date->format('Y-m-d'));
            foreach ($userReaports as $orderReaport) {
                if ($orderReaport[$timeType] == $date->format($format)) {
                    $arrLable[]  = $Date->format($labelFormat);
                    $arrValue[]  = $orderReaport['userCount'];
                    $foundReport = true;
                    break;
                }
            }
            if (! $foundReport) {
                $arrLable[] = $Date->format($labelFormat);
                $arrValue[] = 0.0;
            } else if (! $userReaports) {
                $arrLable[] = $Date->format($labelFormat);
                $arrValue[] = 0.0;
            }
        }
        return response()->json(
            [
                'lable' => $arrLable,
                'value' => $arrValue,
            ],
            200
        );
    }

    public function readNotification()
    {
        $user = User::where('tenant_id', tenant('id'))->first();
        $user->notifications->markAsRead();
        return response()->json(['is_success' => true], 200);
    }

    public function changeThemeMode()
    {
        $user = \Auth::user();
        if ($user->dark_layout == 1) {
            $user->dark_layout = 0;
        } else {
            $user->dark_layout = 1;
        }
        $user->save();
        $data = [
            'dark_mode' => ($user->dark_layout == 1) ? 'on' : 'off',
        ];
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value,
            ]);
        }
        return response()->json(['mode' => $user->dark_layout]);
    }

    public function subscribeServicePlan($id)
    {
        $response = InfluncerServices::subscribeInfluncerPlan($id);
        if (isset($response['url'])) {
            return redirect($response['url']);
        }
        return back()->withErrors($response['error']);
    }
    public function paymentSuccess(Request $request)
    {

        $sessionId = $request->get('session_id');

        $result = InfluncerServices::handleSuccess($sessionId);

        if (isset($result['success'])) {
            return redirect('/home')->with('success', 'Subscription successful!');
        }

        return redirect('/home')->with('error', $result['error'] ?? 'Something went wrong');
    }

    public function paymentCancel()
    {
        return redirect('/home')->with('error', 'Payment cancelled');
    }
}
