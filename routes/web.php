<?php

use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\RolesPermissionController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SeatDashboardController;
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

Route::get('/test', function () {
    return view('email.subscribe_success');
});
Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);

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

    /* To dashboard requires authentication */
    Route::get('/dashboard', [DashboardController::class, 'toDashboard'])->name('dashboard'); //Done
    Route::get('/no-team-page')->name('noTeamPage');

    Route::prefix('/team/{slug}')->middleware(['teamChecker'])->group(function () {
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboardPage');

        Route::get('/seat-dashboard', [SeatDashboardController::class, 'toSeatDashboard'])->name('seatDashboard');

        Route::prefix('/seat/{seat_slug}')->middleware(['seatAccessChecker'])->group(function () {
            Route::get('/', [SeatDashboardController::class, 'seatDashboard'])->name('seatDashboardPage');
        });

        Route::prefix('seat')->group(function () {
            Route::get('/filter-seat/{search}', [SeatController::class, 'filterSeat'])->name('filterSeat'); //Done
            Route::get('/get-seat-access/{id}', [SeatController::class, 'getSeatAccess'])->name('getSeatAccess'); //Done
            Route::get('/get-seat-by-id/{id}', [SeatController::class, 'getSeatById'])->name('getSeatById'); //Done
            Route::get('/update-seat-name/{id}/{seat_name}', [SeatController::class, 'updateName'])->name('updateName'); //Done
            Route::get('/cancel-seat-subscription/{id}', [SeatController::class, 'cancelSubscription'])->name('cancelSubscription');
            Route::get('/delete-seat/{id}', [SeatController::class, 'deleteSeat'])->name('deleteSeat');
        });

        /* These are for blacklist requires access blacklist manage */
        Route::prefix('/blacklist')->middleware(['blacklistAccessChecker'])->group(function () {
            Route::get('/', [BlacklistController::class, 'blacklist'])->name('globalBlacklistPage'); //Done
            Route::post('/save-global-blacklist', [BlacklistController::class, 'saveGlobalBlacklist'])->name('saveGlobalBlacklist'); //Done
            Route::post('/save-email-blacklist', [BlacklistController::class, 'saveEmailBlacklist'])->name('saveEmailBlacklist'); //Done
            Route::delete('/delete-global-blacklist/{id}', [BlacklistController::class, 'deleteGlobalBlacklist'])->name('deleteGlobalBlacklist'); //Done
            Route::delete('/delete-email-blacklist/{id}', [BlacklistController::class, 'deleteEmailBlacklist'])->name('deleteEmailBlacklist'); //Done
            Route::get('/search-global-blacklist/{search}', [BlacklistController::class, 'searchGlobalBlacklist'])->name('searchGlobalBlacklist'); //Done
            Route::get('/search-email-blacklist/{search}', [BlacklistController::class, 'searchEmailBlacklist'])->name('searchEmailBlacklist'); //Done
            Route::post('/filter-global-blacklist', [BlacklistController::class, 'filterGlobalBlacklist'])->name('filterGlobalBlacklist'); //Done
            Route::post('/filter-email-blacklist', [BlacklistController::class, 'filterEmailBlacklist'])->name('filterEmailBlacklist'); //Done
        });

        /* These are for team member */
        Route::prefix('/member')->group(function () {
            Route::get('/', [TeamController::class, 'team'])->name('teamPage'); //Done
            Route::prefix('role')->group(function () {
                Route::get('/role-and-permission', [RolesPermissionController::class, 'rolesPermission'])->name('rolesPermissionPage'); //Done
                Route::post('/custom-role', [RolesPermissionController::class, 'customRole'])->name('customRole'); //Done
                Route::get('/get-role/{id}', [RolesPermissionController::class, 'getRole'])->name('getRole'); //Done
                Route::put('/edit-role/{id}', [RolesPermissionController::class, 'editRole'])->name('editRole'); //Done
                Route::delete('/delete-role/{id}', [RolesPermissionController::class, 'deleteRole'])->name('deleteRole'); //Done
            });
            Route::get('/search-team-member/{search}', [TeamController::class, 'searchTeamMember'])->name('searchTeamMember'); //Done
            Route::delete('/delete-team-member/{id}', [TeamController::class, 'deleteTeamMember'])->name('deleteTeamMember'); //Done
            Route::post('/invite-team-member', [TeamController::class, 'inviteTeamMember'])->name('inviteTeamMember'); //Done
            Route::get('/get-team-member/{id}', [TeamController::class, 'getTeamMember'])->name('getTeamMember'); //Done
            Route::put('/edit-team-member/{id}', [TeamController::class, 'editTeamMember'])->name('editTeamMember'); //Done
        });

        Route::prefix('/invoice')->middleware(['invoiceAccessChecked'])->group(function () {
            Route::get('/', [InvoiceController::class, 'invoice'])->name('globalInvoicePage'); //Done
            Route::get('/seat/{id}', [InvoiceController::class, 'invoiceBySeat'])->name('invoiceBySeat'); //Done
            Route::get('/download/{id}', [InvoiceController::class, 'downloadInvoice'])->name('downloadInvoice'); //Done
        });

        Route::prefix('/stripe')->group(function () {
            Route::post('/payment', [StripePaymentController::class, 'stripePayment'])->name('stripePayment'); //Done
        });

        Route::prefix('/settings')->group(function () {
            Route::get('/', [SettingController::class, 'globalSetting'])->name('globalSetting'); //Done
            Route::put('/change-password', [SettingController::class, 'changePassword'])->name('changePassword'); //Done
        });
    });
});
