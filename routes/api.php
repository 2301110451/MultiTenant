<?php

use App\Http\Controllers\Api\ReservationApiController;
use App\Http\Middleware\EnsureTenantHost;
use App\Http\Middleware\IdentifyTenant;
use Illuminate\Support\Facades\Route;

Route::middleware([IdentifyTenant::class, EnsureTenantHost::class])->group(function () {
    Route::get('availability/{facility}', [ReservationApiController::class, 'availability']);
});
