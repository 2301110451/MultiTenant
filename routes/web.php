<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TenantGoogleAuthController;
use App\Http\Controllers\Central\PlanController;
use App\Http\Controllers\Central\TenantApplicationReviewController;
use App\Http\Controllers\Central\TenantApplicationController;
use App\Http\Controllers\Central\SubscriptionIntentReviewController;
use App\Http\Controllers\Central\TenantSubscriptionIntentController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Tenant\FacilityController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\ReservationController;
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
        // Alias route to support /auth/google pattern.
        Route::get('auth/google', [TenantGoogleAuthController::class, 'redirect'])->name('tenant.google');
        Route::get('auth/google/redirect', [TenantGoogleAuthController::class, 'redirect'])->name('tenant.google.redirect');
        Route::get('auth/google/callback', [TenantGoogleAuthController::class, 'callback'])->name('tenant.google.callback');
        Route::get('auth/google/finalize', [TenantGoogleAuthController::class, 'finalize'])->name('tenant.google.finalize');

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    });

    Route::middleware('auth.context')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::middleware(['tenant.host', 'tenant.role:captain'])->group(function () {
            Route::get('/captain/dashboard', [DashboardController::class, 'index'])->name('tenant.captain.dashboard');
        });
        Route::middleware(['tenant.host', 'tenant.role:secretary'])->group(function () {
            Route::get('/secretary/dashboard', [DashboardController::class, 'index'])->name('tenant.secretary.dashboard');
        });
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
        Route::get('tenant-applications', [TenantApplicationReviewController::class, 'index'])->name('tenant-applications.index');
        Route::post('tenant-applications/{application}/approve', [TenantApplicationReviewController::class, 'approve'])->name('tenant-applications.approve');
        Route::post('tenant-applications/{application}/reject', [TenantApplicationReviewController::class, 'reject'])->name('tenant-applications.reject');

        Route::get('subscription-intents', [SubscriptionIntentReviewController::class, 'index'])->name('subscription-intents.index');
        Route::post('subscription-intents/{intent}/approve', [SubscriptionIntentReviewController::class, 'approve'])->name('subscription-intents.approve');
        Route::post('subscription-intents/{intent}/reject', [SubscriptionIntentReviewController::class, 'reject'])->name('subscription-intents.reject');
    });

    Route::middleware([EnsureTenantHost::class, 'auth:tenant'])->prefix('tenant')->name('tenant.')->group(function () {
        Route::resource('facilities', FacilityController::class)->except(['show']);
        Route::get('calendar', [ReservationController::class, 'calendar'])->name('calendar');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::resource('reservations', ReservationController::class)->except(['edit']);
    });
});
