<?php

use App\Actions\SendEmail;
use App\DataTables\Superadmin\RequestDemoDataTable;
use App\Http\Controllers\Admin\FollowController;
use App\Http\Controllers\Admin\Payment\StripeController as PaymentStripeController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\PurchasePostController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Superadmin\HomeController;
use App\Http\Controllers\Superadmin\LanguageController;
use App\Http\Controllers\Superadmin\LoginSecurityController;
use App\Http\Controllers\Superadmin\ModuleController;
use App\Http\Controllers\Superadmin\OfflineRequestController;
use App\Http\Controllers\Superadmin\PlanController;
use App\Http\Controllers\Superadmin\ProfileController;
use App\Http\Controllers\Superadmin\ChangeDomainController;
use App\Http\Controllers\Superadmin\ConversionsController;
use App\Http\Controllers\Superadmin\EmailTemplateController;
use App\Http\Controllers\Superadmin\RequestDomainController;
use App\Http\Controllers\Superadmin\RoleController;
use App\Http\Controllers\Superadmin\SupportTicketController;
use App\Http\Controllers\Superadmin\SettingsController;
use App\Http\Controllers\Superadmin\UserController;
use App\Http\Controllers\Superadmin\CouponController;
use App\Http\Controllers\Superadmin\FaqController;
use App\Http\Controllers\Superadmin\LandingPageController;
use App\Http\Controllers\Superadmin\SmsTemplateController;
use App\Http\Controllers\Superadmin\TestimonialController;
use App\Http\Controllers\Superadmin\NotificationsSettingController;
use App\Http\Controllers\Superadmin\PageSettingController;
use App\Http\Controllers\Superadmin\Payment\CoingateController;
use App\Http\Controllers\Superadmin\Payment\ToyyibpayController;
use App\Http\Controllers\Superadmin\Payment\SSPayController;
use App\Http\Controllers\Superadmin\Payment\StripeController;
use App\Http\Controllers\Superadmin\Payment\RazorpayPaymentController;
use App\Http\Controllers\Superadmin\Payment\SkrillPaymentController;
use App\Http\Controllers\Superadmin\Payment\PaytmController;
use App\Http\Controllers\Superadmin\Payment\PayuMoneyController;
use App\Http\Controllers\Superadmin\Payment\PaystackController;
use App\Http\Controllers\Superadmin\Payment\PaytabController;
use App\Http\Controllers\Superadmin\Payment\PayPalController;
use App\Http\Controllers\Superadmin\Payment\PayfastController;
use App\Http\Controllers\Superadmin\Payment\MercadoController;
use App\Http\Controllers\Superadmin\Payment\MolliePaymentController;
use App\Http\Controllers\Superadmin\Payment\FlutterwaveController;
use App\Http\Controllers\Superadmin\Payment\AamarpayController;
use App\Http\Controllers\Superadmin\Payment\CashFreeController;
use App\Http\Controllers\Superadmin\Payment\EasebuzzPaymentController;
use App\Http\Controllers\Superadmin\Payment\IyziPayController;
use App\Http\Controllers\Superadmin\Payment\BenefitPaymentController;
use App\Mail\Admin\RequestDemoMail;
use App\Models\RequestDemo;
use AWS\CRT\HTTP\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Spatie\MailTemplates\Models\MailTemplate;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__ . '/auth.php';
Route::group(['middleware' => ['Setting', 'xss']], function () {
    // request Domain
    Route::get('terms-conditions', [RequestDomainController::class, 'termsAndConditions'])->name('terms.and.conditions');
    Route::get('contactus', [RequestDomainController::class, 'contactUs'])->name('contact.us');
    Route::get('all/faqs', [RequestDomainController::class, 'faqs'])->name('faqs.pages');
    Route::post('contact-mail', [RequestDomainController::class, 'contactMail'])->name('add.contact.mail');
    Route::get('request-domain/create/{id}', [RequestDomainController::class, 'create'])->name('request.domain.create');
    Route::get('request-domain/payment/{id}', [RequestDomainController::class, 'payment'])->name('request.domain.payment');
    Route::post('request-domain/store', [RequestDomainController::class, 'store'])->name('request.domain.store');
    Route::post('offline-paysuccess', [RequestDomainController::class, 'offlinePaymentEntry'])->name('offline.payment.entry');

    Route::get('apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon');
    Route::post('coupon-status/{id}', [CouponController::class, 'couponStatus'])->name('coupon.status');

    Route::resource('email-template', EmailTemplateController::class);
    Route::resource('sms-template', SmsTemplateController::class);
    Route::get('pages/{slug}', [LandingPageController::class, 'pageDescription'])->name('description.page');
});

Route::group(['middleware' => [
    InitializeTenancyByDomainOrSubdomain::class,
]], function () {
    Route::get('purchase/successful', [PurchaseController::class, 'purchaseSuccess'])->name('purchase-success');
    Route::get('purchase/unsuccessful', [PurchaseController::class, 'purchaseCancel'])->name('purchase-cancel');
    Route::get('follow/subscription/success', [FollowController::class, 'subscriptionSuccess'])->name('subscription-success');
    Route::get('follow/subscription/unsuccessful ', [FollowController::class, 'subscriptionUnsuccess'])->name('subscription-unsuccess');
    Route::get('purchase/post/success', [PurchasePostController::class, 'purchasePostSuccess'])->name('purchase-post-success');
    Route::get('purchase/post/unsuccessful ', [PurchasePostController::class, 'purchasePostUnsuccess'])->name('purchase-post-unsuccess');
    Route::get('/stripe/success', [PaymentStripeController::class, 'redirectFromCreate'])->name('stripe-redirect-create');
    Route::get('stripe/connect/refresh', [PaymentStripeController::class, 'refreshAccountLink'])->name('stripe.refresh');
});

Route::group(['middleware' => ['auth', 'Setting', 'xss', '2fa']], function () {
    // home
    Route::get('home', [HomeController::class, 'index'])->name('home');
    Route::post('chart', [HomeController::class, 'chart'])->name('get.chart.data');
    Route::post('read/notification', [HomeController::class, 'readNotification'])->name('read.notification');
    Route::get('sales', [HomeController::class, 'sales'])->name('sales.index');
    Route::post('change/theme/mode', [HomeController::class, 'changeThemeMode'])->name('change.theme.mode');

    Route::post('notification/status/{id}', [NotificationsSettingController::class, 'changeStatus'])->name('notification.status.change');
    Route::resource('support-ticket', SupportTicketController::class);
    Route::resource('modules', ModuleController::class);
    Route::post('support-ticket/{id}/conversion', [ConversionsController::class, 'store'])->name('conversion.store');
    Route::resource('faqs', FaqController::class);
    Route::resource('pagesetting', PageSettingController::class);

    Route::resource('roles', RoleController::class);
    Route::post('role-permission/{id}', [RoleController::class, 'assignPermission'])->name('role.permission');

    Route::resource('plans', PlanController::class);
    Route::post('plan-status/{id}', [PlanController::class, 'planStatus'])->name('plan.status');
    Route::get('myplans', [PlanController::class, 'myPlan'])->name('plans.myplan');

    Route::resource('testimonial', TestimonialController::class);
    Route::post('testimonial-status/{id}', [TestimonialController::class, 'testimonialStatus'])->name('testimonial.status');
    Route::get('/request-demo-view', function (RequestDemoDataTable $dataTable) {
        return $dataTable->render('superadmin.request-demo.index');
    })->name('request-demo-view');

    //user
    Route::resource('users', UserController::class);
    Route::get('users/{id}/impersonate', [UserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('user-status/{id}', [UserController::class, 'userStatus'])->name('user.status');


    // coupon
    Route::resource('coupon', CouponController::class);
    Route::get('coupon/show', [CouponController::class, 'show'])->name('coupons.show');
    Route::get('coupon/csv/upload', [CouponController::class, 'uploadCsv'])->name('coupon.upload');
    Route::post('coupon/csv/upload/store', [CouponController::class, 'uploadCsvStore'])->name('coupon.upload.store');
    Route::get('coupon/mass/create', [CouponController::class, 'massCreate'])->name('coupon.mass.create');
    Route::post('coupon/mass/store', [CouponController::class, 'massCreateStore'])->name('coupon.mass.store');

    //request-domain
    Route::get('request-domain/{id}/edit', [RequestDomainController::class, 'edit'])->name('request.domain.edit');
    Route::post('request-domain/{id}/update', [RequestDomainController::class, 'requestDomainUpdate'])->name('request.domain.update');
    Route::delete('request-domain/{id}/delete', [RequestDomainController::class, 'destroy'])->name('request.domain.delete');
    Route::post('user/update/{id}', [RequestDomainController::class, 'update'])->name('create.user');
    Route::get('request-domain', [RequestDomainController::class, 'index'])->name('request.domain.index');
    Route::get('request-domain/approve/{id}', [RequestDomainController::class, 'approveStatus'])->name('request.domain.approve.status');
    Route::get('request-domain/disapprove/{id}', [RequestDomainController::class, 'disApproveStatus'])->name('request.domain.disapprove.status');
    Route::post('request-domain/disapprove-status-update/{id}', [RequestDomainController::class, 'updateStatus'])->name('status.update');

    // offline
    Route::post('offline-payment', [OfflineRequestController::class, 'offlinePaymentEntry'])->name('offline.payment.request');
    Route::resource('offline', OfflineRequestController::class);
    Route::get('offline-request/{id}', [OfflineRequestController::class, 'offlineRequestStatus'])->name('offline.request.status');
    Route::get('offline-request/disapprove/{id}', [OfflineRequestController::class, 'disApproveStatus'])->name('offline.disapprove.status');
    Route::post('offline-request/disapprove-update/{id}', [OfflineRequestController::class, 'offlineDisApprove'])->name('request.user.disapprove.update');

    // change domain
    Route::get('change-domain', [ChangeDomainController::class, 'changeDomainIndex'])->name('changedomain');
    Route::post('change-domain/approve/{id}', [ChangeDomainController::class, 'changeDomainApprove'])->name('change.domain.approve');
    Route::get('change-domain/disapprove/{id}', [ChangeDomainController::class, 'domainDisApprove'])->name('change.domain.disapprove');
    Route::post('change-domain/disapprove-status-update/{id}', [ChangeDomainController::class, 'domainDisApproveUpdate'])->name('change.domain.update');

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

    //profile
    Route::get('profile/edit', [ProfileController::class, 'index'])->name('profile.view');
    Route::delete('/profile-destroy/delete', [ProfileController::class, 'destroy'])->name('profile.delete');
    Route::get('profile-status', [ProfileController::class, 'profileStatus'])->name('profile.status');
    Route::post('update-avatar', [ProfileController::class, 'updateAvatar'])->name('update.avatar');
    Route::post('profile/basicinfo/update/', [ProfileController::class, 'BasicInfoUpdate'])->name('profile.update.basicinfo');
    Route::post('update-login', [ProfileController::class, 'LoginDetails'])->name('update.login.details');

    //setting
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('settings/app-name/update', [SettingsController::class, 'appNameUpdate'])->name('settings.appname.update');
    Route::post('settings/s3-setting/update', [SettingsController::class, 's3SettingUpdate'])->name('settings.s3.setting.update');
    Route::post('settings/domain-config-setting/update', [SettingsController::class, 'domainConfigSettingUpdate'])->name('settings.domain.config.setting.update');
    Route::post('settings/email-setting/update', [SettingsController::class, 'emailSettingUpdate'])->name('settings.email.setting.update');
    Route::post('settings/payment-setting/update', [SettingsController::class, 'paymentSettingUpdate'])->name('settings.payment.setting.update');
    Route::post('settings/auth-settings/update', [SettingsController::class, 'authSettingsUpdate'])->name('settings.auth.settings.update');
    Route::post('test-mail', [SettingsController::class, 'testSendMail'])->name('test.send.mail');
    Route::post('ckeditor/upload', [SettingsController::class, 'upload'])->name('ckeditor.upload');
    Route::post('settings/cookie-setting/update', [SettingsController::class, 'cookieSettingUpdate'])->name('settings.cookie.setting.update');
    Route::post('setting/seo/save', [SettingsController::class, 'SeoSetting'])->name('setting.seo.save');
    Route::get('test-mail', [SettingsController::class, 'testMail'])->name('test.mail');

    //froentend
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

        // footer
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

        // header
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

        Route::get('plan-setting', [LandingPageController::class, 'planSetting'])->name('landing.plan.index');
        Route::post('plan-setting/store', [LandingPageController::class, 'planSettingStore'])->name('landing.plan.store');

        Route::get('testimonial-setting', [LandingPageController::class, 'testimonialSetting'])->name('landing.testimonial.index');
        Route::post('testimonial-setting/store', [LandingPageController::class, 'testimonialSettingStore'])->name('landing.testimonial.store');

        Route::get('login-setting', [LandingPageController::class, 'loginSetting'])->name('landing.login.index');
        Route::post('login-setting/store', [LandingPageController::class, 'loginSettingStore'])->name('landing.login.store');

        Route::get('recaptcha-setting', [LandingPageController::class, 'recaptchaSetting'])->name('landing.recaptcha.index');
        Route::post('recaptcha-setting/store', [LandingPageController::class, 'recaptchaSettingStore'])->name('landing.recaptcha.store');

        Route::get('page-background-setting', [LandingPageController::class, 'pageBackground'])->name('landing.page.background.index');
        Route::post('page-background-setting/store', [LandingPageController::class, 'pageBackgroundstore'])->name('landing.page.background.tore');
    });

    //language
    Route::get('change-language/{lang}', [LanguageController::class, 'changeLanguage'])->name('change.language');
    Route::get('manage-language/{lang}', [LanguageController::class, 'manageLanguage'])->name('manage.language');
    Route::post('store-language-data/{lang}', [LanguageController::class, 'storeLanguageData'])->name('store.language.data');
    Route::get('create-language', [LanguageController::class, 'createLanguage'])->name('create.language');
    Route::post('store-language', [LanguageController::class, 'storeLanguage'])->name('store.language');
    Route::delete('lang/{lang}', [LanguageController::class, 'destroyLang'])->name('lang.destroy');
});

//stripe
Route::post('pre-stripe/pending', [StripeController::class, 'stripePostPending'])->name('pre.stripe.pending');
Route::post('pre-stripe', [StripeController::class, 'preStripeSession'])->name('pre.stripe.session');
Route::get('pre-payment-cancel/{id}', [StripeController::class, 'prePaymentCancel'])->name('pre.stripe.cancel.pay');
Route::get('pre-payment-success/{id}', [StripeController::class, 'prePaymentSuccess'])->name('pre.stripe.success.pay');

//razopay
Route::post('razorpay/payment', [RazorpayPaymentController::class, 'razorPayPayment'])->name('razorpay.payment');
Route::get('razorpay/callback/{orderId}/{transactionId}/{requestDomainId}/{couponId}', [RazorpayPaymentController::class, 'razorPayCallback']);

//flutterwave
Route::post('flutterwave/payment', [FlutterwaveController::class, 'flutterwavePayment'])->name('flutterwave.payment');
Route::get('flutterwave/callback/{orderId}/{transactionId}/{requestDomainId}/{couponId}', [FlutterwaveController::class, 'flutterwaveCallback']);

//paystack
Route::post('paystack/payment', [PaystackController::class, 'paystackPayment'])->name('paystack.payment');
Route::get('paystack/callback/{orderId}/{transactionId}/{requestDomainId}/{couponId}', [PaystackController::class, 'paystackCallback']);

//paytm
Route::post('payment', [PaytmController::class, 'pay'])->name('paytm.payment');
Route::post('payment/callback', [PaytmController::class, 'paymentCallback'])->name('paytm.callback');

//coingate
Route::post('coingate/payment', [CoingateController::class, 'coingatePayment'])->name('coingate.payment');
Route::get('coingate-payment/{id}', [CoingateController::class, 'coingatePlanGetPayment'])->name('coingate.callback');

//mercado
Route::post('mercadopago/payment', [MercadoController::class, 'mercadoPagoPayment'])->name('mercadopago.payment');
Route::any('mercadopago-callback/{id}', [MercadoController::class, 'mercadoPagoPaymentCallback'])->name('mercado.callback');

//payfast
Route::post('payfast/prepare', [PayfastController::class, 'payfastPrepare'])->name('payfast.prepare');
Route::get('payfast/callback/{id}', [PayfastController::class, 'payfastCallback'])->name('payfast.callback');

//Toyyibpay
Route::post('toyyibpay/prepare', [ToyyibpayController::class, 'charge'])->name('toyyibpay.charge');
Route::get('toyyibpay/callback/{domainrequestid}/{orderid}/{coupon}', [ToyyibpayController::class, 'toyyibpayCallback'])->name('toyyibpay.callback');

//Iyzipay
Route::post('iyzipay/prepare', [IyziPayController::class, 'initiatePayment'])->name('iyzipay.init');
Route::post('iyzipay/callback', [IyziPayController::class, 'iyzipayCallback'])->name('iyzipay.callback');

//paypal
Route::post('process-transactions', [PayPalController::class, 'processTransaction'])->name('process.transaction');
Route::get('success-transactions/{data}', [PayPalController::class, 'successTransaction'])->name('success.transaction');
Route::get('cancel-transactions/{data}', [PayPalController::class, 'cancelTransaction'])->name('cancel.transaction');

//sspay
Route::post('sspay/payment/init', [SSPayController::class, 'sspayInitPayment'])->name('sspay.init');
Route::get('sspay/payment/callback', [SSPayController::class, 'sspayCallback'])->name('sspay.callback');

//cashfree
Route::post('cashfree/payment/prepare', [CashFreeController::class, 'cashfreePayment'])->name('cashfree.prepare');
Route::get('cashfree/payment/callback', [CashFreeController::class, 'cashfreeCallback'])->name('cashfree.callback');

// Aamarpay
Route::post('aamarpaypayment/payment', [AamarpayController::class, 'planPayAamarpay'])->name('plan.pay.aamarpay');
Route::any('aamarpaypayment/success/{data}', [AamarpayController::class, 'getPayAamarpayStatus'])->name('plan.payment.aamarpay');

// payu
Route::any('payumoneypay/payment', [PayuMoneyController::class, 'PayUmoneyPayPayment'])->name('payumoney.pay.payment.init');
Route::any('payumoneypay/success/{id}', [PayUMoneyController::class, 'payuPaySuccess'])->name('payu.pay.success');
Route::any('payumoneypay/failure/{id}', [PayUMoneyController::class, 'payuPayFailure'])->name('payu.pay.failure');

// Benefit
Route::any('paymentpay/initiate', [BenefitPaymentController::class, 'initiatePayPayment'])->name('benefit.pay.initiate');
Route::any('call/backpay', [BenefitPaymentController::class, 'callBackpay'])->name('benefit.pay.callback');

// paytab
Route::post('plan-paytab', [PaytabController::class, 'planPaytab'])->name('plan.paytab');
Route::any('plan-paytab-success/plan', [PaytabController::class, 'PaytabPayment'])->name('paytab.success');

// Mollie
Route::post('plan-pay-mollie', [MolliePaymentController::class, 'planPayPaymentMollie'])->name('plan.pay.mollie');
Route::get('planpay/mollie/{plan}', [MolliePaymentController::class, 'getPayPaymentStatus'])->name('plan.with.mollie');

// skrill
Route::post('planpay-skrill', [SkrillPaymentController::class, 'planPaySkrill'])->name('plan.with.skrill');
Route::get('planpay/skrill/{data}', [SkrillPaymentController::class, 'getPaySkrillCallback'])->name('pay.plan.skrill');

//Easebuzz
Route::post('pay-easebuzz', [EasebuzzPaymentController::class, 'PayEasebuzz'])->name('pay.easebuzz');
Route::any('pay/easebuzz/{id}', [EasebuzzPaymentController::class, 'payEasebuzzCallback'])->name('pay.easebuzz.callback');

// cookie
Route::get('cookie/consent', [SettingsController::class, 'CookieConsent'])->name('cookie.consent');

// cache
Route::any('config-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('optimize:clear');
    return redirect()->back()->with('success', __('Cache Clear Successfully.'));
})->name('config.cache');
Route::get('/', [RequestDomainController::class, 'landingPage'])->name('landingpage');
Route::get('changeLang/{lang?}', [RequestDomainController::class, 'changeLang'])->name('change.lang');
Route::get('/request-demo', function () {
    try {
        return view('request-demo');
    } catch (\Exception $e) {
        return abort(300, $e->getMessage());
    }
});

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    return response()->json(['message' => 'Cache cleared successfully']);
});

Route::post('/request/submit', function () {

    $all = request()->validate([
        'name' => 'required',
        'email' => 'required',
        'phone' => 'required',
        'clubName' => 'required',
        'message' => 'required',
        'g-recaptcha-response' => 'required',
    ], [
        'g-recaptcha-response.required' => 'Please complete the CAPTCHA.', // Custom error message
    ]);


    RequestDemo::create([
        'name' => $all['name'],
        'email' => $all['email'],
        'phone' => $all['phone'],
        'clubName' => $all['clubName'],
        'message' => $all['message'],
    ]);

    MailTemplate::firstOrCreate([
        'mailable' => RequestDemoMail::class,
        'subject' => 'Demo Requested by , {{ name }}',
        'html_template' => '<p>Hello, a demo was requested by {{name}} , email : {{email}} , phone : {{phone_number}} , club name : {{clubName}}. Message : {{message}} </p>',
        'text_template' => 'Hello, {{ name }}.'
    ]);

    SendEmail::dispatch('matt@tuneup.golf', new RequestDemoMail(request()->all()));

    return redirect('/')->with('success', 'Request Submitted Successfully');
})->name('request.submit');


Route::get('/test-email', function () {
    $to = request()->get('email');

    \Illuminate\Support\Facades\Mail::raw('This is a test email sent from Laravel.', function ($message) use ($to) {
        $message->to($to)
            ->subject('Test Email from Laravel');
    });

    return 'Test email sent to ' . $to;
});
