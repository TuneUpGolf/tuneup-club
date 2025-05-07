<?php

declare (strict_types = 1);

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ConversionsController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\DocumentGenratorController;
use App\Http\Controllers\Admin\DocumentMenuController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\FollowController;
use App\Http\Controllers\Admin\FollowerController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\InfluencerController;
use App\Http\Controllers\Admin\LandingController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LoginSecurityController;
use App\Http\Controllers\Admin\NotificationsSettingController;
use App\Http\Controllers\Admin\OfflineRequestController;
use App\Http\Controllers\Admin\PageSettingController;
use App\Http\Controllers\Admin\Payment\AamarpayController;
use App\Http\Controllers\Admin\Payment\BenefitPaymentController;
use App\Http\Controllers\Admin\Payment\CashFreeController;
use App\Http\Controllers\Admin\Payment\CoingateController;
use App\Http\Controllers\Admin\Payment\EasebuzzPaymentController;
use App\Http\Controllers\Admin\Payment\FlutterwaveController;
use App\Http\Controllers\Admin\Payment\IyziPayController;
use App\Http\Controllers\Admin\Payment\MercadoController;
use App\Http\Controllers\Admin\Payment\MolliePaymentController;
use App\Http\Controllers\Admin\Payment\PayfastController;
use App\Http\Controllers\Admin\Payment\PaypalController;
use App\Http\Controllers\Admin\Payment\PaystackController;
use App\Http\Controllers\Admin\Payment\PaytabController;
use App\Http\Controllers\Admin\Payment\PaytmController;
use App\Http\Controllers\Admin\Payment\PayuMoneyController;
use App\Http\Controllers\Admin\Payment\RazorpayController;
use App\Http\Controllers\Admin\Payment\SkrillPaymentController;
use App\Http\Controllers\Admin\Payment\SSPayController;
use App\Http\Controllers\Admin\Payment\StripeController;
use App\Http\Controllers\Admin\Payment\ToyyibpayController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PostsController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\PurchasePostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SmsController;
use App\Http\Controllers\Admin\SmsTemplateController;
use App\Http\Controllers\Admin\SocialLoginController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Resources\InstructorAPIResource;
use App\Http\Resources\StudentAPIResource;
use App\Models\Follower;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Stancl\Tenancy\Features\UserImpersonation;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    require __DIR__ . '/auth.php';
    Route::get('/tenant-impersonate/{token}', function ($token) {
        return UserImpersonation::makeResponse($token);
    });
    Route::group(['middleware' => ['Setting', 'xss']], function () {
        Route::get('redirect/{provider}', [SocialLoginController::class, 'redirect']);
        Route::get('callback/{provider}', [SocialLoginController::class, 'callback'])->name('social.callback');

        Route::get('contactus', [LandingController::class, 'contactUs'])->name('contact.us');
        Route::get('all/faqs', [LandingController::class, 'faqs'])->name('faqs.pages');
        Route::get('terms-conditions', [LandingController::class, 'termsAndConditions'])->name('terms.and.conditions');
        Route::post('contact-mail', [LandingController::class, 'contactMail'])->name('contact.mail');
        Route::post('get-category-blog', [LandingController::class, 'getCategoryPost'])->name('get.category.post');
        Route::get('blog-detail/{slug}', [LandingController::class, 'postDetails'])->name('post.details');

        //sms
        Route::get('sms/notice', [SmsController::class, 'smsNoticeIndex'])->name('smsindex.noticeverification');
        Route::post('sms/notice', [SmsController::class, 'smsNoticeVerify'])->name('sms.noticeverification');
        Route::get('sms/verify', [SmsController::class, 'smsIndex'])->name('smsindex.verification');
        Route::post('sms/verify', [SmsController::class, 'smsVerify'])->name('sms.verification');
        Route::post('sms/verifyresend', [SmsController::class, 'smsResend'])->name('sms.verification.resend');

        //Blogs pages
        Route::get('blog/{slug}', [PostsController::class, 'viewBlog'])->name('view.blog');
        Route::get('see/blogs', [PostsController::class, 'seeAllBlogs'])->name('see.all.blogs');
        Route::get('view-blog', [PostsController::class, 'allPost'])->name('view.post');

        Route::get('pages/{slug}', [LandingPageController::class, 'pageDescription'])->name('description.page');
    });

    Route::group(['middleware' => ['auth:web,student', 'Setting', 'xss', '2fa', 'verified', 'verified_phone']], function () {

        Route::impersonate();
        // category
        Route::resource('category', CategoryController::class);
        Route::post('category-status/{id}', [CategoryController::class, 'categoryStatus'])->name('category.status');

        Route::resource('faqs', FaqController::class);
        Route::resource('blogs', PostsController::class)->except(['show']);
        Route::get('blogs/manage/posts', [PostsController::class, 'managePosts'])->name('blogs.manage');
        Route::get('blogs/manage/report', [PostsController::class, 'manageReportedPosts'])->name('blogs.report');
        Route::post('notification/status/{id}', [NotificationsSettingController::class, 'changeStatus'])->name('notification.status.change');
        Route::resource('support-ticket', SupportTicketController::class);
        Route::resource('email-template', EmailTemplateController::class);
        Route::resource('sms-template', SmsTemplateController::class);
        Route::get('change-language/{lang}', [LanguageController::class, 'changeLanquage'])->name('change.language');
        Route::post('support-ticket/{id}/conversion', [ConversionsController::class, 'store'])->name('conversion.store');
        Route::resource('pagesetting', PageSettingController::class);

        // user
        Route::resource('users', UserController::class);
        Route::get('user-emailverified/{id}', [UserController::class, 'userEmailVerified'])->name('user.email.verified');
        Route::get('user-phoneverified/{id}', [UserController::class, 'userPhoneVerified'])->name('user.phone.verified');
        Route::post('user-status/{id}', [UserController::class, 'userStatus'])->name('user.status');

        //influencer
        Route::get('/influencer/import', [InfluencerController::class, 'import'])->name('influencer.import');
        Route::resource('influencer', InfluencerController::class);
        Route::get('influencer/profile/get', [InfluencerController::class, 'viewProfile'])->name('influencer.profile');
        Route::get('influencer-emailverified/{id}', [InfluencerController::class, 'userEmailVerified'])->name('influencer.email.verified');
        Route::get('influencer-phoneverified/{id}', [InfluencerController::class, 'userPhoneVerified'])->name('influencer.phone.verified');
        ROute::get('influencer/profiles/all', [InfluencerController::class, 'instructorProfile'])->name('influencer.profiles');
        Route::post('influencer-status/{id}', [InfluencerController::class, 'userStatus'])->name('influencer.status');
        Route::post('/import_influencers', [InfluencerController::class, 'importfun'])->name('influencer.import_influencers');
        Route::get('get-video/{video}', [PurchaseController::class, 'getVideo'])->name('getVideo');

        Route::get('/follower/import', [FollowerController::class, 'import'])->name('follower.import');
        Route::resource('follower', FollowerController::class);
        Route::get('follower-emailverified/{id}', [FollowerController::class, 'userEmailVerified'])->name('follower.email.verified');
        Route::get('follower-phoneverified/{id}', [FollowerController::class, 'userPhoneVerified'])->name('follower.phone.verified');
        Route::post('follower-status/{id}', [FollowerController::class, 'userStatus'])->name('follower.status');
        Route::post('/import_followers', [FollowerController::class, 'importfun'])->name('follower.import_followers');
        Route::get('follower/{id}', [FollowerController::class, 'show'])->name('follower.show');

        Route::resource('lesson', LessonController::class);
        Route::get('lesson/manage/slot', [LessonController::class, 'manageSlots'])->name('slot.manage');
        Route::get('lesson/purchase/all', [LessonController::class, 'availableLessons'])->name('lesson.available');
        Route::get('get/lesson/influencer/', [LessonController::class, 'getAllByInstructor']);
        Route::get('get/all', [LessonController::class, 'getAll']);
        Route::get('lesson/slot/create', [LessonController::class, 'createSlot'])->name('slot.create');
        Route::post('lesson/slot/add', [LessonController::class, 'addConsectuiveSlots'])->name('slot.add');
        Route::get('lesson/slot/view', [LessonController::class, 'viewSlots'])->name('slot.view');
        Route::post('lesson/slot/done', [LessonController::class, 'completeSlot'])->name('slot.complete');
        Route::post('lesson/slot/booking', [LessonController::class, 'bookSlotApi'])->name('slot.book');
        Route::post('lesson/slot/admin', [LessonController::class, 'bookAdminSlot'])->name('slot.admin');
        Route::post('lesson/slot/update', [LessonController::class, 'updateSlot'])->name('slot.update');

        //purchase
        Route::resource('purchase', PurchaseController::class);
        Route::post('purchase/store', [PurchaseController::class, 'store'])->name('purchase.store');
        Route::post('purchase/confirm/redirect', [PurchaseController::class, 'confirmPurchaseWithRedirect'])->name('purchase-confirm-redirect');
        Route::post('purchase/video', [PurchaseController::class, 'addVideo'])->name('purchase.video.add');
        Route::get('purchase/get/follower', [PurchaseController::class, 'getStudentPurchases']);
        Route::get('purchase/get/video', [PurchaseController::class, 'getPurchaseVideos']);
        Route::get('purchase/add/follower/video', [PurchaseController::class, 'addVideoIndex'])->name('purchase.video.index');
        Route::get('purchase/feedback/all', [PurchaseController::class, 'feedbackIndex'])->name('purchase.feedback.index');
        Route::get('purchase/add/feedback/create', [PurchaseController::class, 'addFeedBackIndex'])->name('purchase.feedback.create');
        Route::post('purchase/add/feedback/add', [PurchaseController::class, 'addFeedBack'])->name('purchase.feedback.add');
        Route::delete('/purchase-feedback/{purchaseVideo}', [PurchaseController::class, 'deleteFeedback'])->name('purchase.feedback.delete');
        Route::get('purchase/lesson/{id}', [PurchaseController::class, 'showLesson'])->name('purchase.show');

        //follow
        Route::post('follow/influencer', [FollowController::class, 'followInstructor'])->name('follow.influencer');
        Route::post('follow/subscribe/influencer', [FollowController::class, 'subscribeInst'])->name('follow.sub.influencer');
        Route::get('follow/subscriptions/follower', [FollowController::class, 'mySubscriptions'])->name('follow.subsctiptions');

        //purchasePost
        Route::post('purchase/post/influencer', [PurchasePostController::class, 'purchasePost'])->name('purchase.post.index');
        Route::post('purchase/like', [PostsController::class, 'likePost'])->name('purchase.like');

        // role
        Route::resource('roles', RoleController::class);
        Route::post('role-permission/{id}', [RoleController::class, 'assignPermission'])->name('role.permission');

        // home
        Route::post('change/theme/mode', [HomeController::class, 'changeThemeMode'])->name('change.theme.mode');
        Route::get('home', [HomeController::class, 'index'])->name('home');
        Route::post('chart', [HomeController::class, 'chart'])->name('get.chart.data');
        Route::post('read/notification', [HomeController::class, 'readNotification'])->name('admin.read.notification');
        Route::get('sales', [HomeController::class, 'sales'])->name('sales.index');

        // coupon
        Route::get('apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon');
        Route::resource('coupon', CouponController::class);
        Route::post('coupon-status/{id}', [CouponController::class, 'couponStatus'])->name('coupon.status');
        Route::get('coupon/show', [CouponController::class, 'show'])->name('coupons.show');
        Route::get('coupon/csv/upload', [CouponController::class, 'uploadCsv'])->name('coupon.upload');
        Route::post('coupon/csv/upload/store', [CouponController::class, 'uploadCsvStore'])->name('coupon.upload.store');
        Route::get('coupon/mass/create', [CouponController::class, 'massCreate'])->name('coupon.mass.create');
        Route::post('coupon/mass/store', [CouponController::class, 'massCreateStore'])->name('coupon.mass.store');

        // testimonial
        Route::resource('testimonial', TestimonialController::class);
        Route::post('testimonial-status/{id}', [TestimonialController::class, 'testimonialStatus'])->name('testimonial.status');

        //stripe connect
        Route::post('stripe/connect/create', [StripeController::class, 'connectStripe'])->name('stripe.create');
        Route::post('profile/stripe/verify', [ProfileController::class, 'verifyStripe'])->name('profile.verify.stripe');
        //event
        Route::get('event', [EventController::class, 'index'])->name('event.index');
        Route::post('event/getdata', [EventController::class, 'getEventData'])->name('event.get.data');
        Route::get('event/create', [EventController::class, 'create'])->name('event.create');
        Route::post('event/store', [EventController::class, 'store'])->name('event.store');
        Route::get('event/edit/{event}', [EventController::class, 'edit'])->name('event.edit');
        Route::any('event/update/{event}', [EventController::class, 'update'])->name('event.update');
        Route::DELETE('event/delete/{event}', [EventController::class, 'destroy'])->name('event.destroy');

        // plans
        Route::resource('plans', PlanController::class);
        Route::get('myplans', [PlanController::class, 'myPlan'])->name('plans.myplan');
        Route::get('myplans-create', [PlanController::class, 'createMyPlan'])->name('plans.createmyplan');
        Route::get('myplans/{id}/edit', [PlanController::class, 'editMyplan'])->name('requestdomain.editplan');
        Route::post('myplan-status/{id}', [PlanController::class, 'planStatus'])->name('myplan.status');
        Route::get('payment/{code}', [PlanController::class, 'payment'])->name('payment');

        // offline request
        Route::resource('offline', OfflineRequestController::class);
        Route::get('offline-request/{id}', [OfflineRequestController::class, 'offlineRequestStatus'])->name('offline.request.status');
        Route::get('offline-request/disapprove/{id}', [OfflineRequestController::class, 'disApproveStatus'])->name('offline.disapprove.status');
        Route::post('offline-request/disapprove-update/{id}', [OfflineRequestController::class, 'offlineDisApprove'])->name('request.user.disapprove.update');
        Route::post('offline-payment', [OfflineRequestController::class, 'offlinePaymentEntry'])->name('offline.payment.request');

        //2fa
        Route::group(['prefix' => '2fa'], function () {
            Route::get('/', [LoginSecurityController::class, 'show2faForm']);
            Route::post('generateSecret', [LoginSecurityController::class, 'generate2faSecret'])->name('generate2faSecret');
            Route::post('enable2fa', [LoginSecurityController::class, 'enable2fa'])->name('enable2fa');
            Route::post('disable2fa', [LoginSecurityController::class, 'disable2fa'])->name('disable2fa');
            Route::post('2faVerify', function () {
                return redirect(route('home'));
                // return redirect(URL()->previous());
            })->name('2faVerify');
        });

        // profile
        Route::get('profile/edit', [ProfileController::class, 'index'])->name('profile.view');
        Route::delete('/profile-destroy/delete', [ProfileController::class, 'destroy'])->name('profile.delete');
        Route::get('profile-status', [ProfileController::class, 'profileStatus'])->name('profile.status');
        Route::post('update-avatar', [ProfileController::class, 'updateAvatar'])->name('update.avatar');
        Route::post('profile/basicinfo/update/', [ProfileController::class, 'BasicInfoUpdate'])->name('profile.update.basicinfo');
        Route::post('profile/socialmedia/update/', [ProfileController::class, 'SocialMediaUpdate'])->name('update.socialmedia');
        Route::post('update-login', [ProfileController::class, 'LoginDetails'])->name('update.login.details');
        Route::post('update-banner-image', [ProfileController::class, 'BannerDetails'])->name('update.banner.image');

        //setting
        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('settings/app-name/update', [SettingsController::class, 'appNameUpdate'])->name('settings.appname.update');
        Route::post('settings/pusher-setting/update', [SettingsController::class, 'pusherSettingUpdate'])->name('settings.pusher.setting.update');
        Route::post('settings/s3-setting/update', [SettingsController::class, 's3SettingUpdate'])->name('settings.s3.setting.update');
        Route::post('settings/email-setting/update', [SettingsController::class, 'emailSettingUpdate'])->name('settings.email.setting.update');
        Route::post('settings/sms-setting/update', [SettingsController::class, 'smsSettingUpdate'])->name('settings.sms.setting.update');
        Route::post('settings/payment-setting/update', [SettingsController::class, 'paymentSettingUpdate'])->name('settings.payment.setting.update');
        Route::post('settings/social-setting/update', [SettingsController::class, 'socialSettingUpdate'])->name('settings.social.setting.update');
        Route::post('settings/google-calender/update', [SettingsController::class, 'GoogleCalenderUpdate'])->name('settings.google.calender.update');
        Route::post('settings/auth-settings/update', [SettingsController::class, 'authSettingsUpdate'])->name('settings.auth.settings.update');
        Route::post('test-mail', [SettingsController::class, 'testSendMail'])->name('test.send.mail');
        Route::post('ckeditor/upload', [SettingsController::class, 'upload'])->name('ckeditor.upload');
        Route::post('settings/change-domain', [SettingsController::class, 'changeDomainRequest'])->name('settings.change.domain');
        Route::get('test-mail', [SettingsController::class, 'testMail'])->name('test.mail');
        Route::post('settings/cookie-setting/update', [SettingsController::class, 'cookieSettingUpdate'])->name('settings.cookie.setting.update');
        Route::post('setting/seo/save', [SettingsController::class, 'SeoSetting'])->name('setting.seo.save');

        //frontend
        Route::group(['prefix' => 'landingpage-setting'], function () {
            Route::get('app-setting', [LandingPageController::class, 'landingPageSetting'])->name('landingpage.setting');
            Route::post('app-setting/store', [LandingPageController::class, 'appSettingStore'])->name('landing.app.store');

            // menu
            Route::get('menu-setting', [LandingPageController::class, 'menuSetting'])->name('menusetting.index');
            Route::post('menu-setting-section1/store', [LandingPageController::class, 'menuSettingSection1Store'])->name('landing.menusection1.store');
            Route::post('menu-setting-section2/store', [LandingPageController::class, 'menuSettingSection2Store'])->name('landing.menusection2.store');
            Route::post('menu-setting-section3/store', [LandingPageController::class, 'menuSettingSection3Store'])->name('landing.menusection3.store');

            // feature
            Route::get('feature-setting', [LandingPageController::class, 'featureSetting'])->name('landing.feature.index');
            Route::post('feature-setting/store', [LandingPageController::class, 'featureSettingStore'])->name('landing.feature.store');
            Route::get('feature/create', [LandingPageController::class, 'featureCreate'])->name('feature.create');
            Route::post('feature/store', [LandingPageController::class, 'featureStore'])->name('feature.store');
            Route::get('feature/edit/{key}', [LandingPageController::class, 'featureEdit'])->name('feature.edit');
            Route::post('feature/update/{key}', [LandingPageController::class, 'featureUpdate'])->name('feature.update');
            Route::get('feature/delete/{key}', [LandingPageController::class, 'featureDelete'])->name('feature.delete');

            // business growth
            Route::get('business-growth-setting', [LandingPageController::class, 'businessGrowthSetting'])->name('landing.business.growth.index');
            Route::post('business-growth-setting/store', [LandingPageController::class, 'businessGrowthSettingStore'])->name('landing.business.growth.store');

            Route::get('business-growth/create', [LandingPageController::class, 'businessGrowthCreate'])->name('business.growth.create');
            Route::post('business-growth/store', [LandingPageController::class, 'businessGrowthStore'])->name('business.growth.store');
            Route::get('business-growth/edit/{key}', [LandingPageController::class, 'businessGrowthEdit'])->name('business.growth.edit');
            Route::post('business-growth/update/{key}', [LandingPageController::class, 'businessGrowthUpdate'])->name('business.growth.update');
            Route::get('business-growth/delete/{key}', [LandingPageController::class, 'businessGrowthDelete'])->name('business.growth.delete');

            Route::get('business-growth-view/create', [LandingPageController::class, 'businessGrowthViewCreate'])->name('business.growth.view.create');
            Route::post('business-growth-view/store', [LandingPageController::class, 'businessGrowthViewStore'])->name('business.growth.view.store');
            Route::get('business-growth-view/edit/{key}', [LandingPageController::class, 'businessGrowthViewEdit'])->name('business.growth.view.edit');
            Route::post('business-growth-view/update/{key}', [LandingPageController::class, 'businessGrowthViewUpdate'])->name('business.growth.view.update');
            Route::get('business-growth-view/delete/{key}', [LandingPageController::class, 'businessGrowthViewDelete'])->name('business.growth.view.delete');

            //Footer
            Route::get('footer-setting', [LandingPageController::class, 'footerSetting'])->name('landing.footer.index');
            Route::post('footer-setting/store', [LandingPageController::class, 'footerSettingStore'])->name('landing.footer.store');

            Route::get('main/menu/create', [LandingPageController::class, 'footerMainMenuCreate'])->name('footer.main.menu.create');
            Route::post('main/menu/store', [LandingPageController::class, 'footerMainMenuStore'])->name('footer.main.menu.store');
            Route::get('main/menu/edit/{id}', [LandingPageController::class, 'footerMainMenuEdit'])->name('footer.main.menu.edit');
            Route::post('main/menu/update/{id}', [LandingPageController::class, 'footerMainMenuUpdate'])->name('footer.main.menu.update');
            Route::get('main/menu/delete/{id}', [LandingPageController::class, 'footerMainMenuDelete'])->name('footer.main.menu.delete');

            Route::get('sub/menu/create', [LandingPageController::class, 'footerSubMenuCreate'])->name('footer.sub.menu.create');
            Route::post('sub/menu/store', [LandingPageController::class, 'footerSubMenuStore'])->name('footer.sub.menu.store');
            Route::get('sub/menu/edit/{id}', [LandingPageController::class, 'footerSubMenuEdit'])->name('footer.sub.menu.edit');
            Route::post('sub/menu/update/{id}', [LandingPageController::class, 'footerSubMenuUpdate'])->name('footer.sub.menu.update');
            Route::get('sub/menu/delete/{id}', [LandingPageController::class, 'footerSubMenuDelete'])->name('footer.sub.menu.delete');

            //Header
            Route::get('header-setting', [LandingPageController::class, 'headerSetting'])->name('landing.header.index');

            Route::get('headersub/menu/create', [LandingPageController::class, 'headerSubMenuCreate'])->name('header.sub.menu.create');
            Route::post('headersub/menu/store', [LandingPageController::class, 'headerSubMenuStore'])->name('header.sub.menu.store');
            Route::get('headersub/menu/edit/{id}', [LandingPageController::class, 'headerSubMenuEdit'])->name('header.sub.menu.edit');
            Route::post('headersub/menu/update/{id}', [LandingPageController::class, 'headerSubMenuUpdate'])->name('header.sub.menu.update');
            Route::get('headersub/menu/delete/{id}', [LandingPageController::class, 'headerSubMenuDelete'])->name('header.sub.menu.delete');

            Route::get('start-view-setting', [LandingPageController::class, 'startViewSetting'])->name('landing.start.view.index');
            Route::post('start-view-setting/store', [LandingPageController::class, 'startViewSettingStore'])->name('landing.start.view.store');

            Route::get('faq-setting', [LandingPageController::class, 'faqSetting'])->name('landing.faq.index');
            Route::post('faq-setting/store', [LandingPageController::class, 'faqSettingStore'])->name('landing.faq.store');

            Route::get('contactus-setting', [LandingPageController::class, 'contactusSetting'])->name('landing.contactus.index');
            Route::post('contactus-setting/store', [LandingPageController::class, 'contactusSettingStore'])->name('landing.contactus.store');

            Route::get('login-setting', [LandingPageController::class, 'loginSetting'])->name('landing.login.index');
            Route::post('login-setting/store', [LandingPageController::class, 'loginSettingStore'])->name('landing.login.store');

            Route::get('recaptcha-setting', [LandingPageController::class, 'recaptchaSetting'])->name('landing.recaptcha.index');
            Route::post('recaptcha-setting/store', [LandingPageController::class, 'recaptchaSettingStore'])->name('landing.recaptcha.store');

            Route::get('blog-setting', [LandingPageController::class, 'blogSetting'])->name('landing.blog.index');
            Route::post('blog-setting/store', [LandingPageController::class, 'blogSettingStore'])->name('landing.blog.store');

            Route::get('testimonial-setting', [LandingPageController::class, 'testimonialSetting'])->name('landing.testimonial.index');
            Route::post('testimonial-setting/store', [LandingPageController::class, 'testimonialSettingStore'])->name('landing.testimonial.store');

            Route::get('page-background-setting', [LandingPageController::class, 'pageBackground'])->name('landing.page.background.index');
            Route::post('page-background-setting/store', [LandingPageController::class, 'pageBackgroundStore'])->name('landing.page.background.tore');
        });

        //document
        Route::resource('document', DocumentGenratorController::class);
        Route::get('document/design/{id}', [DocumentGenratorController::class, 'design'])->name('document.design');
        Route::post('document/design-menu/{id}', [DocumentGenratorController::class, 'documentDesignMenu'])->name('document.design.menu');

        //status drag-drop
        Route::post('document/designmenu', [DocumentGenratorController::class, 'updateDesign'])->name('updatedesign.document');
        Route::get('document-status/{id}', [DocumentGenratorController::class, 'documentStatus'])->name('document.status');

        // menu
        Route::get('docmenu/index', [DocumentMenuController::class, 'index'])->name('docmenu.index');
        Route::get('docmenu/create/{docmenuId}', [DocumentMenuController::class, 'create'])->name('docmenu.create');
        Route::post('docmenu/store', [DocumentMenuController::class, 'store'])->name('docmenu.store');
        Route::delete('document/menu/{id}', [DocumentMenuController::class, 'destroy'])->name('document.designdelete');

        // submenu
        Route::get('docsubmenu/create/{id}/{docMenuId}', [DocumentMenuController::class, 'subMenuCreate'])->name('docsubmenu.create');
        Route::post('docsubmenu/store', [DocumentMenuController::class, 'subMenuStore'])->name('docsubmenu.store');
        Route::get('document/submenu/{id}', [DocumentMenuController::class, 'subMenuDestroy'])->name('document.submenu.designdelete');

        //stripe
        Route::get('stripe', [StripeController::class, 'stripe'])->name('stripe.pay');
        Route::post('stripe/pending', [StripeController::class, 'stripePostPending'])->name('stripe.pending');
        Route::post('stripe/session', [StripeController::class, 'stripeSession'])->name('stripe.session');
        Route::get('payment-success/{id}', [StripeController::class, 'paymentSuccess'])->name('stripe.success.pay');
        Route::get('payment-cancel/{id}', [StripeController::class, 'paymentCancel'])->name('stripe.cancel.pay');

        //razorpay
        Route::post('razorpay/payment', [RazorpayController::class, 'razorpayPayment'])->name('payrazorpay.payment');
        Route::get('razorpay/transaction/callback/{transactionId}/{couponId}/{plansId}', [RazorpayController::class, 'RazorpayCallback']);

        //flutterwave
        Route::post('flutterwave/payment', [FlutterwaveController::class, 'flutterwavePayment'])->name('pay.flutterwave.payment');
        Route::get('flutterwave/transaction/callback/{transactionId}/{couponId}/{plansId}', [FlutterwaveController::class, 'FlutterwaveCallback']);

        //paystack
        Route::post('paystack/payment', [PaystackController::class, 'paystackPayment'])->name('paypaystack.payment');
        Route::get('paystack/transaction/callback/{transactionId}/{couponId}/{plansId}', [PaystackController::class, 'paystackCallback']);

        //coingate
        Route::post('coingate/prepare', [CoingateController::class, 'coingatePrepare'])->name('coingate.payment.prepare');
        Route::get('coingate-success/{id}', [CoingateController::class, 'coingateCallback'])->name('coingate.payment.callback');

        //mercado
        Route::post('mercado/prepare', [MercadoController::class, 'mercadoPrepare'])->name('mercado.payment.prepare');
        Route::any('mercado-payment-callback/{id}', [MercadoController::class, 'mercadoCallback'])->name('mercado.payment.callback');

        //payfast
        Route::post('payfast/prepare', [PayfastController::class, 'payfastPrepare'])->name('payfast.payment.prepare');
        Route::get('payfast/callback/{id}', [PayfastController::class, 'payfastCallback'])->name('payfast.payment.callback');

        //Toyyibpay
        Route::post('toyyibpay/prepare', [ToyyibpayController::class, 'charge'])->name('toyyibpay.payment.charge');
        Route::get('toyyibpay/callback/{planid}/{orderid}/{coupon}', [ToyyibpayController::class, 'toyyibpayCallback'])->name('toyyibpay.payment.callback');

        //Iyzipay
        Route::post('iyzipay/prepare', [IyziPayController::class, 'initiatePayment'])->name('iyzipay.payment.init');
        Route::post('iyzipay/callback', [IyzipayController::class, 'iyzipayCallback'])->name('iyzipay.payment.callback');

        // paytab
        Route::post('plan-pay-with-paytab', [PaytabController::class, 'planPayWithPaytab'])->name('plan.pay.with.paytab');
        Route::any('paytab-success/plan', [PaytabController::class, 'paytabGetPayment'])->name('plan.paytab.success');

        // Mollie
        Route::post('plan-pay-with-mollie', [MolliePaymentController::class, 'planPayWithMollie'])->name('plan.pay.with.mollie');
        Route::get('plan/mollie/{plan}', [MolliePaymentController::class, 'getPaymentStatus'])->name('plan.mollie');
    });

    //API CALLS FOR APP
    Route::group(['middleware' => [
        'auth:sanctum',
        InitializeTenancyByDomainOrSubdomain::class,
        PreventAccessFromCentralDomains::class,
    ]], function () {
        Route::get('users/get/all', [UserController::class, 'getAllUsers']);

        Route::get('influencer/get/all', [InfluencerController::class, 'getAllUsers']);
        Route::post('influencer/update/bio', [InfluencerController::class, 'updateInstructorBio']);
        Route::post('influencer/update/dp', [InfluencerController::class], 'setProfilePicture');
        Route::get('influencer/get/stats', [InfluencerController::class, 'getStats']);
        Route::post('influencer/delete/{id}', [InfluencerController::class, 'deleteAPI']);
        Route::post('influencer/report', [InfluencerController::class, 'reportInstructor']);
        Route::post('influencer/review', [InfluencerController::class, 'addReview']);
        Route::get('influencer/get/review', [InfluencerController::class, 'getReviews']);
        Route::post('influencer/annotate', [InfluencerController::class, 'annotate']);

        Route::get('follower/get/all', [FollowerController::class, 'getAllUsers']);
        Route::post('follower/update/bio', [FollowerController::class, 'updateStudentBio']);
        Route::post('follower/update/dp', [FollowerController::class, 'updateProfilePicture']);
        Route::post('follower/delete/{id}', [FollowerController::class, 'deleteAPI']);

        Route::post('profile/password', [ProfileController::class, 'changePasswordAPI']);
        Route::post('profile/update', [ProfileController::class, 'updateProfileAPI']);

        Route::get('lesson/get/all', [LessonController::class, 'getAll']);
        Route::get('lesson/get/influencer', [LessonController::class, 'getInstructorAll']);
        Route::post('lesson/add', [LessonController::class, 'addLessonApi']);
        Route::post('lesson/add/slot', [LessonController::class, 'addSlot']);
        Route::get('lesson/get/slots', [LessonController::class, 'getSlots']);
        Route::post('lesson/update/slots', [LessonController::class, 'updateSlot']);
        Route::post('lesson/update', [LessonController::class, 'updateLessonApi']);
        Route::post('lesson/delete', [LessonController::class, 'deleteLessonApi']);
        Route::post('lesson/slot/book', [LessonController::class, 'bookSlotApi']);
        Route::post('lesson/slot/complete', [LessonController::class, 'completeSlot']);
        Route::post('lesson/slot/auto', [LessonController::class, "addConsectuiveSlots"]);
        Route::get('lesson/slot/influencer', [LessonController::class, 'getAllSlotsInstructor']);

        Route::get('purchase/get/all', [PurchaseController::class, 'getAll']);
        Route::get('purchase/get/follower', [PurchaseController::class, 'getStudentAll']);
        Route::get('purchase/all/videos', [PurchaseController::class, 'getAllPurchaseVideos']);
        Route::post('purchase/video/feedback', [PurchaseController::class, 'addFeedbackAPI']);
        Route::post('purchase/add/feedback', [PurchaseController::class, 'addFeedBack']);
        Route::post('purchase/create', [PurchaseController::class, 'addPurchase']);
        Route::post('purchase/confirm', [PurchaseController::class, 'confirmPurchase']);
        Route::post('purchase/influencer/post', [PurchasePostController::class, 'purchasePost']);
        Route::post('purchase/add/video', [PurchaseController::class, 'addVideoAPI']);

        Route::post('follow', [FollowController::class, 'followInstructorApi']);
        Route::get('follow/followers', [FollowController::class, 'getStudents']);

        Route::get('post', [PostsController::class, 'getAllPosts']);
        Route::get('post/influencer', [PostsController::class, 'getInstructorPosts']);
        Route::post('post/report', [PostsController::class, 'reportPost']);
        Route::post('post', [PostsController::class, 'createPost']);
        Route::get('post/like', [PostsController::class, 'getAllLikedPostApi']);
        Route::post('post/{id}', [PostsController::class, 'updatePostApi']);
        Route::post('post/like/{id}', [PostsController::class, 'likePostAPi']);

        Route::get('/profile', function () {
            try {
                if (isset(Auth::user()->id)) {
                    if (Auth::user()->type === Role::ROLE_ADMIN || Auth::user()->type === Role::ROLE_INFLUENCER) {
                        return response(new InstructorAPIResource(Auth::user()), 200);
                    } else {
                        return response(new StudentAPIResource(Auth::user()), 200);
                    }

                }
            } catch (\Exception $e) {
                return abort(301, $e->getMessage());
            }
        });

        Route::post('users/set/token', [ProfileController::class, 'setPushToken']);
    });

    //paytm
    Route::post('paypayment', [PaytmController::class, 'pay'])->name('paypaytm.payment');
    Route::post('paypayment/callback', [PaytmController::class, 'paymentCallback'])->name('paypaytm.callback');

    // payu
    Route::any('payumoney/payment', [PayuMoneyController::class, 'PayUmoneyPayment'])->name('payumoney.payment.init');
    Route::any('payumoney/success/{id}', [PayUMoneyController::class, 'payuSuccess'])->name('payu.success');
    Route::any('payumoney/failure/{id}', [PayUMoneyController::class, 'payuFailure'])->name('payu.failure');

    //sspay
    Route::post('sspay/payment', [SSPayController::class, 'initPayment'])->name('sspay.payment.init');
    Route::get('sspay/transaction/callback', [SSPayController::class, 'sspayCallback'])->name('sspay.payment.callback');

    //cashfree
    Route::post('cashfree/payment', [CashFreeController::class, 'cashfreePayment'])->name('cashfree.payment.prepare');
    Route::get('cashfree/transaction/callback', [CashFreeController::class, 'cashfreeCallback'])->name('cashfree.payment.callback');

    // Aamarpay
    Route::post('aamarpay/payment', [AamarpayController::class, 'planPayWithAamarpay'])->name('plan.pay.with.aamarpay');
    Route::any('aamarpay/success/{data}', [AamarpayController::class, 'getPaymentAamarpayStatus'])->name('plan.aamarpay');

    // cookie
    Route::get('cookie/consent', [SettingsController::class, 'CookieConsent'])->name('cookie.consent');

    // Benefit
    Route::any('payment/initiate', [BenefitPaymentController::class, 'initiatePayment'])->name('benefit.initiate');
    Route::any('call/back', [BenefitPaymentController::class, 'callBack'])->name('benefit.callback');

    // Skrill
    Route::any('plan-pay-with-skrill', [SkrillPaymentController::class, 'planPayWithSkrill'])->name('plan.pay.with.skrill');
    Route::get('plan/skrill/{data}', [SkrillPaymentController::class, 'getPayWithSkrillCallback'])->name('plan.skrill');

    //Easebuzz
    Route::post('plan-easebuzz', [EasebuzzPaymentController::class, 'planPayWithEasebuzz'])->name('plan.pay.with.easebuzz');
    Route::any('plan/easebuzz/{id}', [EasebuzzPaymentController::class, 'planWithEasebuzzCallback'])->name('plan.easebuzz.callback');

    //paypal
    Route::post('process-transaction', [PaypalController::class, 'processTransaction'])->name('pay.process.transaction');
    Route::get('success-transaction/{data}', [PaypalController::class, 'successTransaction'])->name('pay.success.transaction');
    Route::get('cancel-transaction/{data}', [PaypalController::class, 'cancelTransaction'])->name('pay.cancel.transaction');
    Route::post('process-transactionadmin', [PaypalController::class, 'processTransactionAdmin'])->name('pay.process.transaction.admin');

    // cache
    Route::any('config-cache', function () {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize:clear');
        return redirect()->back()->with('success', __('Cache Clear Successfully'));
    })->name('config.cache');

    // public document
    Route::get('document/public/{slug}', [DocumentGenratorController::class, 'documentPublic'])->name('document.public')->middleware(['xss']);
    Route::get('documents/{slug}/{changelog?}', [DocumentGenratorController::class, 'documentPublicMenu'])->name('document.menu.menu')->middleware(['xss']);
    Route::get('document/{slug}/{slugmenu}', [DocumentGenratorController::class, 'documentPublicSubmenu'])->name('document.sub.menu')->middleware(['xss']);
    Route::get('/', [LandingController::class, 'landingPage'])->name('landingpage');
    Route::get('changeLang/{lang?}', [LandingController::class, 'changeLang'])->name('change.lang');
    //Route::middleware('auth:sanctum')->get('users/get/all', [UserController::class, 'getAllUsers']);
});

//follower/influencer/admin login sanctum
Route::middleware([
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
])->post('/sanctum/token', function () {

    try {
        $user = User::where('email', $_REQUEST['email'])->first();
        if (! isset($user)) {
            $user = Follower::where('email', $_REQUEST['email'])->where('active_status', true)->first();
        }

        if (! $user || ! Hash::check($_REQUEST['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
    } catch (\Exception $th) {
        return response()->json([
            'message' => 'Login Failed, ' . $th->getMessage(),
        ], 403);
    }
    return $user->createToken($_REQUEST['device_name'])->plainTextToken;
});

// //follower login
// Route::middleware([
//     InitializeTenancyByDomainOrSubdomain::class,
//     PreventAccessFromCentralDomains::class,
// ])->post('follower/sanctum/token', function () {

//     try {
//         $user = Follower::where('email', $_REQUEST['email'])->where('active_status', true)->first();

//         if (!$user || !Hash::check($_REQUEST['password'], $user->password)) {
//             throw ValidationException::withMessages([
//                 'email' => 'The provided credentials are incorrect',
//             ]);
//         }
//     } catch (\Exception $e) {
//         return abort(300, $e->getMessage());
//     }
//     return $user->createToken($_REQUEST['device_name'])->plainTextToken;
// });

//public routes
Route::group(['middleware' => [
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
]], function () {
    Route::post('/follower/signup', [FollowerController::class, 'signup']);
});
