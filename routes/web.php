<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Central\DeploymentCandidateController;
use App\Http\Controllers\Central\DeploymentRunController;
use App\Http\Controllers\Central\GlobalUpdateController;
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\Central\RealtimeController as CentralRealtimeController;
use App\Http\Controllers\Central\SubscriptionIntentReviewController;
use App\Http\Controllers\Central\SupportTicketController as CentralSupportTicketController;
use App\Http\Controllers\Central\SystemVersionController;
use App\Http\Controllers\Central\TenantActivityAuditLogController;
use App\Http\Controllers\Central\TenantApplicationController;
use App\Http\Controllers\Central\TenantApplicationReviewController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\TenantSubscriptionIntentController;
use App\Http\Controllers\Central\UpdateAnnouncementController;
use App\Http\Controllers\Central\FeatureLabController;
use App\Http\Controllers\Central\ReleaseSmokeTestController; // ✅ NEW
use App\Http\Controllers\Central\ReleaseFlowTestController;

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

        Route::get('system-versions', [SystemVersionController::class, 'index'])->name('system-versions.index');
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

        // ✅ EXISTING FEATURE
        Route::get('feature-lab', [FeatureLabController::class, 'index'])->name('feature-lab.index');

        // ✅ NEW FEATURE (SMOKE TEST)
        Route::get('release-smoke-test', [ReleaseSmokeTestController::class, 'index'])->name('release-smoke-test.index');
    });

    Route::middleware([EnsureCentralHost::class, 'auth:web', 'super.admin'])->prefix('central/global-updates')->name('central.global-updates.')->group(function () {
        Route::get('/', [GlobalUpdateController::class, 'index'])->name('index');
        Route::post('/publish', [GlobalUpdateController::class, 'publish'])->name('publish');
        Route::post('/sync', [GlobalUpdateController::class, 'sync'])->name('sync');
        Route::get('/candidates', [DeploymentCandidateController::class, 'index'])->name('candidates.index');
        Route::get('/candidates/rejected', [DeploymentCandidateController::class, 'rejectedIndex'])->name('candidates.rejected');
        Route::post('/candidates/{candidate}/approve', [DeploymentCandidateController::class, 'approve'])->name('candidates.approve');
        Route::post('/candidates/{candidate}/reject', [DeploymentCandidateController::class, 'reject'])->name('candidates.reject');
        Route::post('/candidates/{candidate}/restore', [DeploymentCandidateController::class, 'restoreToCandidates'])->name('candidates.restore');
        Route::post('/candidates/{candidate}/validate', [DeploymentRunController::class, 'requestValidation'])->name('candidates.validate');
        Route::post('/runs/{run}/mark-validated', [DeploymentRunController::class, 'markValidated'])->name('runs.mark-validated');
        Route::post('/runs/{run}/deploy', [DeploymentRunController::class, 'deploy'])->name('runs.deploy');
        Route::post('/runs/{run}/undo', [DeploymentRunController::class, 'undo'])->name('runs.undo');
    });

    Route::middleware([EnsureTenantHost::class, 'auth:tenant'])->prefix('tenant')->name('tenant.')->group(function () {
        // unchanged
    });

});
