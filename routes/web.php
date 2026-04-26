<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TenantLoginSelectorController;
use App\Http\Controllers\Central\DeploymentCandidateController;
use App\Http\Controllers\Central\DeploymentRunController;
use App\Http\Controllers\Central\FeatureLabController;
use App\Http\Controllers\Central\GlobalUpdateController;
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\Central\RealtimeController as CentralRealtimeController;
use App\Http\Controllers\Central\ReleaseChangelogTestController;
use App\Http\Controllers\Central\ReleaseController as CentralReleaseController;
use App\Http\Controllers\Central\ReleaseFlowTestController;
use App\Http\Controllers\Central\ReleaseSmokeTestController;
use App\Http\Controllers\Central\SubscriptionIntentReviewController;
use App\Http\Controllers\Central\SupportTicketController as CentralSupportTicketController;
use App\Http\Controllers\Central\SystemVersionController;
use App\Http\Controllers\Central\TenantActivityAuditLogController;
use App\Http\Controllers\Central\TenantApplicationController;
use App\Http\Controllers\Central\TenantApplicationReviewController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\TenantSubscriptionIntentController;
use App\Http\Controllers\Central\UpdateAnnouncementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Tenant\FacilityController;
use App\Http\Controllers\Tenant\RealtimeController as TenantRealtimeController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\ReservationController;
use App\Http\Controllers\Tenant\RoleManagementController;
use App\Http\Controllers\Tenant\SettingController;
use App\Http\Controllers\Tenant\SupportTicketController;
use App\Http\Controllers\Tenant\TenantAnnouncementController;
use App\Http\Controllers\Tenant\UpdateFeedController;
use App\Http\Controllers\Tenant\UserAppearanceController;
use App\Http\Controllers\Tenant\UserManagementController;
use App\Http\Middleware\EnsureCentralHost;
use App\Http\Middleware\EnsureTenantHost;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', IdentifyTenant::class])->group(function () {

    Route::get('/', HomeController::class)->name('home');

    Route::middleware('guest.context')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('register', [RegisteredUserController::class, 'store']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    });

    Route::middleware('auth.context')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });

    Route::middleware([EnsureCentralHost::class])->group(function () {
        Route::get('/tenant-login', [TenantLoginSelectorController::class, 'index'])->name('tenant.login.selector');
        Route::post('/tenant-login', [TenantLoginSelectorController::class, 'redirect'])->name('tenant.login.selector.redirect');

        Route::get('/apply', [TenantApplicationController::class, 'create'])->name('central.apply');
        Route::post('/apply', [TenantApplicationController::class, 'store'])->name('central.apply.store');

        Route::get('/subscription-intent/thanks', [TenantSubscriptionIntentController::class, 'thanks'])
            ->name('central.subscription-intent.thanks');

        Route::middleware('signed')->group(function () {
            Route::get('/subscription-intent/{tenant}', [TenantSubscriptionIntentController::class, 'show'])
                ->name('central.subscription-intent.show');
            Route::post('/subscription-intent/{tenant}', [TenantSubscriptionIntentController::class, 'store'])
                ->name('central.subscription-intent.store');
        });

        require __DIR__.'/auth.php';
    });

    Route::middleware([EnsureCentralHost::class, 'auth:web'])->prefix('central')->name('central.')->group(function () {

        Route::resource('tenants', TenantController::class)->except(['show']);
        Route::resource('plans', PlanController::class)->except(['show']);

        Route::get('support-tickets', [CentralSupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::put('support-tickets/{supportTicket}', [CentralSupportTicketController::class, 'update'])->name('support-tickets.update');

        Route::get('system-versions', fn () => redirect()->route('central.releases.index'))->name('system-versions.index');
        Route::post('system-versions', [SystemVersionController::class, 'store'])->name('system-versions.store');

        Route::get('audit-logs', [TenantActivityAuditLogController::class, 'index'])
            ->middleware('super.admin')
            ->name('audit-logs.index');

        Route::get('update-announcements', [UpdateAnnouncementController::class, 'index'])->name('update-announcements.index');
        Route::post('update-announcements', [UpdateAnnouncementController::class, 'store'])->name('update-announcements.store');

        Route::get('tenant-applications', [TenantApplicationReviewController::class, 'index'])->name('tenant-applications.index');
        Route::post('tenant-applications/{application}/approve', [TenantApplicationReviewController::class, 'approve'])->name('tenant-applications.approve');
        Route::post('tenant-applications/{application}/reject', [TenantApplicationReviewController::class, 'reject'])->name('tenant-applications.reject');

        Route::get('subscription-intents', [SubscriptionIntentReviewController::class, 'index'])->name('subscription-intents.index');
        Route::post('subscription-intents/{intent}/approve', [SubscriptionIntentReviewController::class, 'approve'])->name('subscription-intents.approve');
        Route::post('subscription-intents/{intent}/reject', [SubscriptionIntentReviewController::class, 'reject'])->name('subscription-intents.reject');

        Route::get('realtime/dashboard', [CentralRealtimeController::class, 'dashboard'])->name('realtime.dashboard');
        Route::get('realtime/tenants', [CentralRealtimeController::class, 'tenants'])->name('realtime.tenants');
        Route::get('realtime/subscription-intents', [CentralRealtimeController::class, 'subscriptionIntents'])->name('realtime.subscription-intents');
        Route::get('realtime/support-tickets', [CentralRealtimeController::class, 'supportTickets'])->name('realtime.support-tickets');
        Route::get('realtime/deployment-candidates', [CentralRealtimeController::class, 'deploymentCandidates'])->name('realtime.deployment-candidates');

        Route::get('feature-lab', [FeatureLabController::class, 'index'])->name('feature-lab.index');
        Route::get('release-flow-test', [ReleaseFlowTestController::class, 'index'])->name('release-flow-test.index');
        Route::get('release-smoke-test', [ReleaseSmokeTestController::class, 'index'])->name('release-smoke-test.index');
        Route::get('release-changelog-test', [ReleaseChangelogTestController::class, 'index'])->name('release-changelog-test.index');

        // ✅ NEW SUPER ADMIN BUTTON ROUTE
        Route::middleware('super.admin')->get('test-button', function () {
            return view('central.test-button');
        })->name('test-button');

        Route::middleware('super.admin')->prefix('releases')->name('releases.')->group(function () {
            Route::get('/', [CentralReleaseController::class, 'index'])->name('index');
            Route::post('/detect-and-store', [CentralReleaseController::class, 'detectAndStore'])->name('detect-and-store');
            Route::get('/detect', [CentralReleaseController::class, 'detect'])->name('detect');
            Route::post('/{release}/approve', [CentralReleaseController::class, 'approve'])->name('approve');
            Route::post('/{release}/reject', [CentralReleaseController::class, 'reject'])->name('reject');
            Route::post('/{release}/save-version', [CentralReleaseController::class, 'saveVersion'])->name('save-version');
        });
    });

});
