<?php

use App\Http\Controllers\UnipileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/create/messaging/webhook', [UnipileController::class, 'create_messaging_webhook']);
Route::post('/create/email/webhook', [UnipileController::class, 'create_email_webhook']);
Route::post('/create/email_track/webhook', [UnipileController::class, 'create_tracking_webhook']);
