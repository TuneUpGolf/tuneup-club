<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\PurchaseDataTable;
use App\DataTables\Admin\SalesDataTable;
use App\Facades\Utility;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Models\DocumentGenrator;
use App\Models\Event;
use App\Models\Follow;
use App\Models\Follower;
use App\Models\Lesson;
use App\Models\Plan;
use App\Models\Post;
use App\Models\Posts;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\SupportTicket;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use App\Services\ChatService;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    protected $chatService;
    protected $utility;
    public function __construct(ChatService $chatService, Utility $utility)
    {
        $this->chatService = $chatService;
        $this->utility = $utility;
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
        $paymentTypes   = UtilityFacades::getpaymenttypes();
        $documents      = DocumentGenrator::where('tenant_id', $tenantId)->count();
        $documentsDatas = DocumentGenrator::where('tenant_id', $tenantId)->latest()->take(5)->get();
        $posts          = Posts::latest()->take(6)->get();
        $events         = Event::latest()->take(5)->get();
        $supports       = tenancy()->central(fn($tenant) => SupportTicket::where('tenant_id', $tenant->id)->latest()->take(7)->get());

        if ($userType == Role::ROLE_FOLLOWER) {
            if ($request->tab == 'chat') {
                if (!$this->utility->chatEnabled($user)) {
                    return redirect()->route('home')->with('error', 'Chat feature not available!');
                }

                $influencer   = User::where('tenant_id', tenant('id'))->where('id', $user->follows->first()->influencer_id)->first();
                $token        = $this->chatService->getChatToken($user->chat_user_id);
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
                'influencer'     => $influencer ?? null,
                'token'          => $token ?? null,
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
        // dd($influencerStats);

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
            'influencerStats'
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
        $influencer     = $data['influencer'];
        $token          = $data['token'];

        $influencer   = User::where('type', Role::ROLE_INFLUENCER)->first();
        $totalLessons = Lesson::where('created_by', $influencer->id)->count();
        $posts        = Post::where('influencer_id', $influencer->id);
        $posts        = $posts->orderBy('created_at', 'desc')->paginate(6);
        $section      = $request->section;
        $follow       = Follow::where('influencer_id', $influencer->id);
        $isFollowing  = $follow->where('follower_id', Auth::user()->id)
            ->where('active_status', 1)
            ->exists();
        $plans             = Plan::where('influencer_id', $influencer->id)->get();
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
            'token'
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
}
