<?php
namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\LessonDataTable;
use App\DataTables\Admin\SalesDataTable;
use App\Facades\UtilityFacades;
use App\Http\Controllers\Controller;
use App\Models\DocumentGenrator;
use App\Models\Event;
use App\Models\Lesson;
use App\Models\Plan;
use App\Models\Posts;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\Follower;
use App\Models\SupportTicket;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DatePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{

    public function landingPage()
    {
        $plans  = tenancy()->central(function ($tenant) {
            return Plan::where('active_status', 1)->get();
        });
        return view('welcome', compact('plans'));
    }
    public function index(LessonDataTable $dataTable)
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

        if ($userType == Role::ROLE_STUDENT) {
            return $this->studentDashboard($user, $paymentTypes, $documents, $documentsDatas, $posts, $events, $supports);
        }

        // Fetch Plan Expiration
        $planExpiredDate = $userType == AuthServiceProvider::ADMIN_TYPE
            ? tenancy()->central(fn($tenant) => User::where('email', $user->email)->first()->plan_expired_date)
            : User::where('email', $user->email)->first()->plan_expired_date ?? '';

        // Fetch Instructor Count
        $instructor = User::where('tenant_id', $tenantId)->where('type', Role::ROLE_INSTRUCTOR)->count();
        $students = Follower::where('tenant_id', $tenantId)->where('active_status', true)->where('isGuest', false)->count();

        // Fetch Lessons Count
        $lessons = ($userType == "Admin")
        ? Lesson::where('tenant_id', $tenantId)->count()
        : Lesson::where('tenant_id', $tenantId)->where('created_by', $user->id)->count();

        $influencerLesson = Lesson::where('tenant_id', $tenantId)->where('created_by', $user->id)->get();

        // Fetch Earnings
        $earning = ($userType === Role::ROLE_INSTRUCTOR)
            ? Purchase::where('instructor_id', $user->id)->where('status', 'complete')->sum('total_amount')
            : Purchase::where('status', 'complete')->sum('total_amount');

        // Fetch Instructor Statistics for Admins (Without Student Count)
        $instructorStats = [];
        if ($userType == "Admin") {
            $instructorStats = User::where('tenant_id', $tenantId)
                ->where('type', Role::ROLE_INFLUENCER)
                ->withCount([
                    'lessons as lesson_count',
                    'purchase as completed_online_lessons'   => fn($query)   => $query->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', true)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_ONLINE)),
                    'purchase as completed_inperson_lessons' => fn($query) => $query->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', true)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_INPERSON)),
                    'purchase as pending_online_lessons'     => fn($query)     => $query->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', false)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_ONLINE)),
                    'purchase as pending_inperson_lessons'   => fn($query)   => $query->where('isFeedbackComplete', false)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_INPERSON)),
                ])
                ->get();
        }

        [$purchaseComplete, $purchaseInprogress] = $this->fetchPurchaseStats($user, Lesson::LESSON_TYPE_ONLINE);
        [$inPersonCompleted, $inPersonPending]   = $this->fetchPurchaseStats($user, Lesson::LESSON_TYPE_INPERSON);
        // return $dataTable->render('admin.dashboard.home', compact(
        //     'user',
        //     'userType',
        //     'instructor',
        //     'students',
        //     'lessons',
        //     'planExpiredDate',
        //     'earning',
        //     'paymentTypes',
        //     'documents',
        //     'documentsDatas',
        //     'posts',
        //     'events',
        //     'supports',
        //     'purchaseComplete',
        //     'purchaseInprogress',
        //     'inPersonCompleted',
        //     'inPersonPending',
        //     'instructorStats'
        // ));
        // dd($lessons);
        return view('admin.dashboard.home', compact(
            'user',
            'userType',
            'instructor',
            'students',
            'lessons',
            'planExpiredDate',
            'earning',
            'paymentTypes',
            'documents',
            'documentsDatas',
            'posts',
            'events',
            'influencerLesson',
            'supports',
            'purchaseComplete',
            'purchaseInprogress',
            'inPersonCompleted',
            'inPersonPending',
            'instructorStats'
        ));
    }

    // Fetch purchase counts based on lesson type
    private function fetchPurchaseStats($user, $lessonType)
    {
        $query = Purchase::whereHas('lesson', fn($q) => $q->where('type', $lessonType));

        if ($user->type == "Instructor") {
            $query->where('influencer_id', $user->id);
        }

        if ($lessonType == Lesson::LESSON_TYPE_ONLINE) {
            $query->where('status', Purchase::STATUS_COMPLETE);
        }

        $completed = (clone $query)->where('isFeedbackComplete', true)->count();
        $inprogress = $query->where('isFeedbackComplete', false)->count();

        return [$completed, $inprogress];
    }

    // Student Dashboard
    private function studentDashboard($user, $paymentTypes, $documents, $documentsDatas, $posts, $events, $supports)
    {
        $purchaseComplete = Purchase::where('follower_id', $user->id)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_ONLINE))->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', true)->count();
        $purchaseInprogress = Purchase::where('follower_id', $user->id)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_ONLINE))->where('status', Purchase::STATUS_COMPLETE)->where('isFeedbackComplete', false)->count();
        $inPersonCompleted = Purchase::where('follower_id', $user->id)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_INPERSON))->where('isFeedbackComplete', true)->count();
        $inPersonPending = Purchase::where('follower_id', $user->id)->whereHas('lesson', fn($q) => $q->where('type', Lesson::LESSON_TYPE_INPERSON))->where('isFeedbackComplete', false)->count();
        return view('admin.dashboard.home', compact(
            'user',
            'paymentTypes',
            'documents',
            'documentsDatas',
            'posts',
            'events',
            'supports',
            'purchaseComplete',
            'purchaseInprogress',
            'inPersonCompleted',
            'inPersonPending'
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
        $dateRange  = new DatePeriod($startDate, $interval, $endDate);
        switch ($timeType) {
            case 'date':
                $format = 'Y-m-d';
                $labelFormat = 'd M';
                break;
            case 'month':
                $format = 'Y-m';
                $labelFormat = 'M Y';
                break;
            default:
                $format = 'Y';
                $labelFormat = 'Y';
                break;
        }
        foreach ($dateRange as $date) {
            $foundReport = false;
            $Date = Carbon::parse($date->format('Y-m-d'));
            foreach ($userReaports as $orderReaport) {
                if ($orderReaport[$timeType] == $date->format($format)) {
                    $arrLable[] = $Date->format($labelFormat);
                    $arrValue[] = $orderReaport['userCount'];
                    $foundReport = true;
                    break;
                }
            }
            if (!$foundReport) {
                $arrLable[] = $Date->format($labelFormat);
                $arrValue[] = 0.0;
            } else if (!$userReaports) {
                $arrLable[] = $Date->format($labelFormat);
                $arrValue[] = 0.0;
            }
        }
        return response()->json(
            [
                'lable'    => $arrLable,
                'value'     => $arrValue
            ],
            200
        );
    }

    public function readNotification()
    {
        $user   = User::where('tenant_id', tenant('id'))->first();
        $user->notifications->markAsRead();
        return response()->json(['is_success' => true], 200);
    }

    public function changeThemeMode()
    {
        $user   = \Auth::user();
        if ($user->dark_layout == 1) {
            $user->dark_layout = 0;
        } else {
            $user->dark_layout = 1;
        }
        $user->save();
        $data   = [
            'dark_mode' => ($user->dark_layout == 1) ? 'on' : 'off',
        ];
        foreach ($data as $key => $value) {
            UtilityFacades::storesettings([
                'key'   => $key,
                'value' => $value
            ]);
        }
        return response()->json(['mode' => $user->dark_layout]);
    }
}
