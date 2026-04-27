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

        Route::middleware('super.admin')->prefix('releases')->name('releases.')->group(function () {
            Route::get('/', [CentralReleaseController::class, 'index'])->name('index');
            Route::post('/detect-and-store', [CentralReleaseController::class, 'detectAndStore'])->name('detect-and-store');
            Route::get('/detect', [CentralReleaseController::class, 'detect'])->name('detect');
            Route::post('/{release}/approve', [CentralReleaseController::class, 'approve'])->name('approve');
            Route::post('/{release}/reject', [CentralReleaseController::class, 'reject'])->name('reject');
            Route::post('/{release}/save-version', [CentralReleaseController::class, 'saveVersion'])->name('save-version');
        });
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
        Route::get('account/display', [UserAppearanceController::class, 'edit'])->name('account.display.edit');
        Route::put('account/display', [UserAppearanceController::class, 'update'])->name('account.display.update');

        Route::get('users', [UserManagementController::class, 'index'])->name('users.index')->middleware('tenant.permission:users.view');
        Route::get('users/create', [UserManagementController::class, 'create'])->name('users.create')->middleware('tenant.permission:users.create');
        Route::post('users', [UserManagementController::class, 'store'])->name('users.store')->middleware('tenant.permission:users.create');
        Route::get('users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit')->middleware('tenant.permission:users.update');
        Route::put('users/{user}', [UserManagementController::class, 'update'])->name('users.update')->middleware('tenant.permission:users.update');
        Route::patch('users/{user}', [UserManagementController::class, 'update'])->middleware('tenant.permission:users.update');
        Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy')->middleware('tenant.permission:users.delete');

        Route::get('roles', [RoleManagementController::class, 'index'])->name('roles.index')->middleware('tenant.permission:users.update');
        Route::get('roles/create', [RoleManagementController::class, 'create'])->name('roles.create')->middleware('tenant.permission:users.update');
        Route::post('roles', [RoleManagementController::class, 'store'])->name('roles.store')->middleware('tenant.permission:users.update');
        Route::get('roles/{role}/edit', [RoleManagementController::class, 'edit'])->name('roles.edit')->middleware('tenant.permission:users.update');
        Route::put('roles/{role}', [RoleManagementController::class, 'update'])->name('roles.update')->middleware('tenant.permission:users.update');
        Route::patch('roles/{role}', [RoleManagementController::class, 'update'])->middleware('tenant.permission:users.update');
        Route::delete('roles/{role}', [RoleManagementController::class, 'destroy'])->name('roles.destroy')->middleware('tenant.permission:users.update');
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit')->middleware('tenant.permission:settings.view');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update')->middleware('tenant.permission:settings.update');
        Route::get('support', [SupportTicketController::class, 'index'])->name('support.index')->middleware('tenant.permission:support.view');
        Route::post('support', [SupportTicketController::class, 'store'])->name('support.store')->middleware('tenant.permission:support.view');
        Route::get('updates', [UpdateFeedController::class, 'index'])->name('updates.index')->middleware('tenant.permission:updates.view');
        Route::post('updates/announcements', [TenantAnnouncementController::class, 'store'])->name('announcements.store')->middleware('tenant.permission:updates.manage');

        Route::get('facilities/{facility}/image', [FacilityController::class, 'image'])->name('facilities.image')->middleware('tenant.permission:facilities.view');
        Route::get('facilities', [FacilityController::class, 'index'])->name('facilities.index')->middleware('tenant.permission:facilities.view');
        Route::get('facilities/create', [FacilityController::class, 'create'])->name('facilities.create')->middleware('tenant.permission:facilities.create');
        Route::post('facilities', [FacilityController::class, 'store'])->name('facilities.store')->middleware('tenant.permission:facilities.create');
        Route::get('facilities/{facility}/edit', [FacilityController::class, 'edit'])->name('facilities.edit')->middleware('tenant.permission:facilities.update');
        Route::put('facilities/{facility}', [FacilityController::class, 'update'])->name('facilities.update')->middleware('tenant.permission:facilities.update');
        Route::patch('facilities/{facility}', [FacilityController::class, 'update'])->middleware('tenant.permission:facilities.update');
        Route::delete('facilities/{facility}', [FacilityController::class, 'destroy'])->name('facilities.destroy')->middleware('tenant.permission:facilities.delete');

        Route::get('calendar', [ReservationController::class, 'calendar'])->name('calendar')->middleware('tenant.permission:reservations.view');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index')->middleware('tenant.permission:reports.view');
        Route::get('reports/download', [ReportController::class, 'download'])->name('reports.download')->middleware('tenant.permission:reports.view');
        Route::get('reports/download/csv', [ReportController::class, 'downloadCsv'])
            ->name('reports.download.csv')
            ->middleware(['tenant.permission:reports.view', 'tenant.plan:export_reports_csv']);
        Route::get('reports/download/excel', [ReportController::class, 'downloadExcel'])
            ->name('reports.download.excel')
            ->middleware(['tenant.permission:reports.view', 'tenant.plan:export_reports_excel']);
        Route::post('reservations/{reservation}/mark-returned', [ReservationController::class, 'markReturned'])->name('reservations.mark-returned')->middleware('tenant.permission:reservations.update');
        Route::post('reservations/{reservation}/damage', [ReservationController::class, 'markDamage'])->name('reservations.damage')->middleware(['tenant.permission:reservations.update', 'tenant.plan:damage_penalty_tracking']);
        Route::post('reservations/{reservation}/payments/{payment}/paid', [ReservationController::class, 'markPaymentPaid'])->name('reservations.payments.paid')->middleware(['tenant.permission:reservations.update', 'tenant.plan:damage_penalty_tracking']);
        Route::get('reservations', [ReservationController::class, 'index'])->name('reservations.index')->middleware('tenant.permission:reservations.view');
        Route::get('reservations/create', [ReservationController::class, 'create'])->name('reservations.create')->middleware('tenant.permission:reservations.create');
        Route::post('reservations', [ReservationController::class, 'store'])->name('reservations.store')->middleware('tenant.permission:reservations.create');
        Route::get('reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show')->middleware('tenant.permission:reservations.view');
        Route::put('reservations/{reservation}', [ReservationController::class, 'update'])->name('reservations.update')->middleware('tenant.permission:reservations.update');
        Route::patch('reservations/{reservation}', [ReservationController::class, 'update'])->middleware('tenant.permission:reservations.update');
        Route::delete('reservations/{reservation}', [ReservationController::class, 'destroy'])->name('reservations.destroy')->middleware('tenant.permission:reservations.delete');

        Route::get('realtime/dashboard', [TenantRealtimeController::class, 'dashboard'])->name('realtime.dashboard')->middleware('tenant.permission:reservations.view');
        Route::get('realtime/reports', [TenantRealtimeController::class, 'reports'])->name('realtime.reports')->middleware('tenant.permission:reports.view');
        Route::get('realtime/reservations', [TenantRealtimeController::class, 'reservations'])->name('realtime.reservations')->middleware('tenant.permission:reservations.view');
        Route::get('realtime/users', [TenantRealtimeController::class, 'users'])->name('realtime.users')->middleware('tenant.permission:users.view');
        Route::get('realtime/roles', [TenantRealtimeController::class, 'roles'])->name('realtime.roles')->middleware('tenant.permission:users.update');
        Route::get('realtime/support', [TenantRealtimeController::class, 'support'])->name('realtime.support')->middleware('tenant.permission:support.view');
        Route::get('realtime/updates', [TenantRealtimeController::class, 'updates'])->name('realtime.updates')->middleware('tenant.permission:updates.view');

        Route::resource('modules', \App\Http\Controllers\ModuleController::class);
    });

});
