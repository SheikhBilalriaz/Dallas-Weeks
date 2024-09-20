<?php

use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/* These are home pages url which does not require any authentication */

Route::get('/', [HomeController::class, 'home'])->name('homePage'); //Done
Route::get('/about', [HomeController::class, 'about'])->name('aboutPage'); //Done
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricingPage'); //Done
Route::get('/faq', [HomeController::class, 'faq'])->name('faqPage'); //Done

/* These are signup url which does not require any authentication */
Route::get('/register', [RegisterController::class, 'register'])->name('registerPage'); //Done
Route::post('/register-user', [RegisterController::class, 'registerUser'])->name('registerUser'); //Done
Route::get('/verify-an-Email/{email}/{token}', [RegisterController::class, 'verifyAnEmail'])->name('verifyAnEmail'); //Done

/* These are login url which does not require any authentication */
Route::get('/login', [LoginController::class, 'login'])->name('loginPage'); //Done
Route::post('/check-credentials', [LoginController::class, 'checkCredentials'])->name('checkCredentials'); //Done

/* These are for dashboard which requires authentication */
Route::middleware(['userAuth'])->group(function () {
    /* Resending an email requires authentication */
    Route::get('/resend-an-Email', [RegisterController::class, 'resendAnEmail'])->name('resendAnEmail'); //Done

    /* Before logout it reuiqres authentication */
    Route::get('/logout', [LoginController::class, 'logoutUser'])->name('logoutUser'); //Done

    /*  */
    Route::get('/dashboard', [DashboardController::class, 'toDashboard'])->name('dashboardPage'); //There is some requirement to add
    Route::prefix('/team/{slug}')->middleware(['teamChecker'])->group(function () {
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard'); //Done
        Route::get('/blacklist', [BlacklistController::class, 'blacklist'])->name('globalBlacklist');
        Route::post('/save-global-blacklist', [BlacklistController::class, 'saveGlobalBlacklist'])->name('saveGlobalBlacklist');
        Route::post('/save-email-blacklist', [BlacklistController::class, 'saveEmailBlacklist'])->name('saveEmailBlacklist');
        Route::prefix('/team')->group(function () {
            Route::get('/', [TeamController::class, 'team'])->name('team');
            Route::get('/team-roles-and-permission')->name('rolesPermission');
        });
        Route::get('/invoice', [InvoiceController::class, 'invoice'])->name('globalInvoice');
        Route::get('/roles-and-permission-setting', [SettingController::class, 'settingRolesPermission'])->name('settingRolesPermission');
    });
});
