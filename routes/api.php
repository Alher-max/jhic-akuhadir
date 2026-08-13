<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\DeviceLogController;
use App\Http\Controllers\Api\V1\BiometricPushController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/v1/devices/push', [DeviceLogController::class, 'push']);

// Biometric Device Webhook Endpoints
Route::match(['get', 'post'], '/v1/biometric/push', [BiometricPushController::class, 'push']);
Route::get('/v1/biometric/status/{serialNumber}', [BiometricPushController::class, 'status']);
