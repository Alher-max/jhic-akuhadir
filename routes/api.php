<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DeviceLogController;
use App\Http\Controllers\Api\V1\BiometricPushController;

Route::post('/v1/devices/push', [DeviceLogController::class, 'push']);

// Biometric Device Webhook Endpoints
Route::match(['get', 'post'], '/v1/biometric/push', [BiometricPushController::class, 'push']);
Route::get('/v1/biometric/status/{serialNumber}', [BiometricPushController::class, 'status']);
