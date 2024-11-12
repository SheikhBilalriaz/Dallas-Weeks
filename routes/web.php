<?php

use App\Http\Controllers\AccountHealthController;
use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignElementController;
use App\Http\Controllers\CsvController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailIntegrationController;
use App\Http\Controllers\GlobalLimitController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\LinkedinIntegrationController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RolesPermissionController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\SeatDashboardController;
use App\Http\Controllers\SeatMessageController;
use App\Http\Controllers\SeatSettingController;
use App\Http\Controllers\SeatWebhookController;
use App\Http\Controllers\UnipileController;
use App\Http\Controllers\UnipileEmailController;
use App\Http\Controllers\UnipileLinkedinController;
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

Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook']);
Route::post('/unipile/linkedin/webhook', [UnipileLinkedinController::class, 'handleWebhook']); //Done
Route::post('/unipile/email/webhook', [UnipileEmailController::class, 'handleWebhook']); //Done

/* These are home pages url which does not require any authentication */
Route::get('/', [HomeController::class, 'home'])->name('homePage'); //Done
Route::get('/about', [HomeController::class, 'about'])->name('aboutPage'); //Done
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricingPage'); //Done
Route::get('/faq', [HomeController::class, 'faq'])->name('faqPage'); //Done

/* These are signup url which does not require any authentication */
Route::get('/register', [RegisterController::class, 'register'])->name('registerPage'); //Done
Route::post('/register-user', [RegisterController::class, 'registerUser'])->name('registerUser'); //Done
Route::get('/verify-an-Email/{email}/{token}', [RegisterController::class, 'verifyAnEmail'])->name('verifyAnEmail'); //Done
Route::post('/forgot-password', [LoginController::class, 'forgotPassword'])->name('forgotPassword'); //Done
Route::post('/update-password', [LoginController::class, 'updatePassword'])->name('updatePassword'); //Done

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
        Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboardPage'); //Done
        
        Route::post('/check-promo', [StripePaymentController::class, 'checkPromoCode'])->name('checkPromoCode');

        Route::get('/seat-dashboard', [SeatDashboardController::class, 'toSeatDashboard'])->name('seatDashboard'); //Done

        Route::prefix('/seat/{seat_slug}')->middleware(['seatAccessChecker'])->group(function () {
            Route::middleware(['linkedinAccountChecker'])->group(function () {
                Route::get('/', [SeatDashboardController::class, 'seatDashboard'])->name('seatDashboardPage'); //Done

                Route::middleware(['isCampaignAllowed'])->group(function () {
                    Route::prefix('campaign')->group(function () {
                        Route::get('/', [CampaignController::class, 'campaign'])->name('campaignPage'); //Done
                        Route::middleware('isManageCampaignAllowed')->group(function () {
                            Route::get('/create-campaign', [CampaignController::class, 'createCampaign'])->name('createCampaignPage'); //Done
                            Route::post('/campaign-info', [CampaignController::class, 'campaignInfo'])->name('campaignInfoPage'); //Done
                            Route::post('/campaign-from-scratch', [CampaignController::class, 'fromscratch'])->name('campaignFromScratchPage'); //Done
                            Route::post('/create-schedule', [ScheduleController::class, 'createSchedule'])->name('createSchedule'); //Done
                            Route::get('/get-campaign-element-by-slug/{element_slug}', [CampaignElementController::class, 'campaignElement'])->name('getCampaignElementBySlug'); //Done
                            Route::get('/get-property-datatype/{id}/{element_slug}', [PropertiesController::class, 'getPropertyDatatype'])->name('getPropertyDatatype'); //Done
                            Route::get('/get-property-required/{id}', [PropertiesController::class, 'getPropertyRequired'])->name('getPropertyRequired'); //Done
                            Route::post('/post-campaign', [CampaignElementController::class, 'createCampaign'])->name('postCampaign'); //Done
                            Route::get('/campaign-details/{campaign_id}', [CampaignController::class, 'getCampaignDetails'])->name('campaignDetailsPage'); //Done
                            Route::get('/edit-campaign/{campaign_id}', [CampaignController::class, 'editCampaign'])->name('editCampaign'); //Done
                            Route::post('/edit-campaign-info/{campaign_id}', [CampaignController::class, 'editCampaignInfo'])->name('editCampaignInfo'); //Done
                            Route::post('/edit-campaign-sequence/{campaign_id}', [CampaignController::class, 'editCampaignSequence'])->name('editCampaignSequence');
                            Route::get('delete-campaign/{campaign_id}', [CampaignController::class, 'deleteCampaign'])->name('deleteCampaign'); //Done
                            Route::get('/change-campaign-status/{campaign_id}', [CampaignController::class, 'changeCampaignStatus'])->name('changeCampaignStatus'); //Done
                            Route::get('/archive/{campaign_id}', [CampaignController::class, 'archiveCampaign'])->name('archiveCampaign'); //Done
                            Route::post('/update-campaign/{campaign_id}', [CampaignController::class, 'updateCampaign'])->name('updateCampaign'); //Done
                            Route::get('/get-campaign-element-by-id/{element_id}', [CampaignElementController::class, 'getcampaignelementbyid'])->name('getcampaignelementbyid'); //Done
                        });
                    });
                    Route::prefix('leads')->group(function () {
                        Route::get('/', [LeadsController::class, 'leads'])->name('dash-leads'); //Done
                        Route::get('/getLeadsByCampaign/{id}/{search}', [LeadsController::class, 'getLeadsByCampaign'])->name('getLeadsByCampaign'); //Done
                        Route::post('/sendLeadsToEmail', [LeadsController::class, 'sendLeadsToEmail'])->name('sendLeadsToEmail'); //Done
                    });
                    Route::get('/filter-campaign/{filter}/{search}', [CampaignController::class, 'filterCampaign'])->name('filterCampaign'); //Done
                    Route::get('/filter-schedule/{search}', [ScheduleController::class, 'filterSchedule'])->name('filterSchedule'); //Done
                    Route::get('/filter-team-schedule/{search}', [ScheduleController::class, 'filterTeamSchedule'])->name('filterTeamSchedule'); //Done
                    Route::get('/get-elements/{campaign_id}', [CampaignElementController::class, 'getElements'])->name('getElements'); //Done
                });
                Route::prefix('webhook')->middleware(['isWebhookAllowed'])->group(function () {
                    Route::get('/', [SeatWebhookController::class, 'webhook'])->name('webhookPage'); //Done
                    Route::middleware(['isManageWebhookAllowed'])->group(function () {
                        Route::delete('/delete-webhook/{id}', [SeatWebhookController::class, 'deleteWebhook'])->name('deleteWebhook'); //Done
                        Route::post('/create-webhook', [SeatWebhookController::class, 'createWebhook'])->name('createWebhook'); //Done
                    });
                });
                Route::middleware(['isChatAllowed'])->group(function () {
                    Route::prefix('message')->group(function () {
                        Route::get('/', [MessageController::class, 'message'])->name('dash-messages'); //Done
                        Route::get('/chat/profile/{profile_id}/{chat_id}', [MessageController::class, 'get_profile_and_latest_message'])->name('get_profile_and_latest_message'); //Done
                        Route::get('/latest/{chat_id}', [MessageController::class, 'get_latest_Mesage_chat_id'])->name('get_latest_Mesage_chat_id'); //Done
                        Route::get('/chat/latest/{chat_id}/{count}', [MessageController::class, 'get_latest_message_in_chat'])->name('get_latest_message_in_chat'); //Done
                        Route::get('/chat/profile/{profile_id}', [MessageController::class, 'get_chat_Profile'])->name('get_chat_Profile'); //Done
                        Route::get('/chat/receiver/{chat_id}', [MessageController::class, 'get_chat_receive'])->name('get_chat_receive'); //Done
                        Route::get('/chat/sender', [MessageController::class, 'get_chat_sender'])->name('get_chat_sender'); //Done
                        Route::get('/chat/{chat_id}', [MessageController::class, 'get_messages_chat_id'])->name('get_messages_chat_id'); //Done
                        Route::get('/chat/{chat_id}/{cursor}', [MessageController::class, 'get_messages_chat_id_cursor'])->name('get_messages_chat_id_cursor'); //Done
                        Route::get('/chats/{cursor}', [MessageController::class, 'get_remain_chats'])->name('get_remain_chats'); //Done
                        Route::post('/send/chat', [MessageController::class, 'send_a_message'])->name('send_a_message'); //Done
                        Route::post('/search', [MessageController::class, 'message_search'])->name('message_search'); //Done
                        Route::get('/unread', [MessageController::class, 'unread_message'])->name('unread_message'); //Done
                        Route::get('/profile/{profile_id}', [MessageController::class, 'profile_by_id'])->name('profile_by_id'); //Done
                        Route::post('/retrieve/message/attachment', [UnipileController::class, 'retrieve_an_attachment_from_a_message'])->name('retrieve_an_attachment_from_a_message'); //Done
                    });
                });
                Route::middleware(['isReportAllowed'])->group(function () {
                    Route::get('/report', [ReportController::class, 'report'])->name('reportPage'); //Done
                    Route::get('/generate-report', [ReportController::class, 'generate_report'])->name('generateReport'); //Done
                    Route::get('/generate-pdf', [ReportController::class, 'generate_pdf'])->name('generatePdf'); //Done
                });
                Route::post('/import-csv', [CsvController::class, 'import_csv'])->name('importCsv'); //Done
                Route::post('/disconnect-linkedin-account', [LinkedinIntegrationController::class, 'disconnectLinkedinAccount'])->name('disconnectLinkedinAccount'); //Done
                Route::get('/seat-messages', [SeatMessageController::class, 'seatMessageController'])->name('seatMessageController'); //Done
                Route::get('/get-profile-and-latest-messages/{profile_id}/{chat_id}', [MessagingController::class, 'getProfileAndLatestMessage'])->name('getProfileAndLatestMessage'); //Done
            });
            Route::get('/seat-setting', [SeatSettingController::class, 'seatSetting'])->name('seatSettingPage'); //Done
            Route::put('/update-seat-limit', [GlobalLimitController::class, 'updateSeatLimit'])->name('updateSeatLimit'); //Done
            Route::put('/update-account-health', [AccountHealthController::class, 'updateAccountHealth'])->name('updateAccountHealth'); //Done
            Route::post('/create-linkedin-account', [LinkedinIntegrationController::class, 'createLinkedinAccount'])->name('createLinkedinAccount'); //Done
            Route::post('/create-email-account', [EmailIntegrationController::class, 'createEmailAccount'])->name('createEmailAccount'); //Done
            Route::post('/disconnect-email-account/{email_id}', [EmailIntegrationController::class, 'disconnectEmailAccount'])->name('disconnectEmailAccount'); //Done
            Route::get('/search-email-account/{search}', [EmailIntegrationController::class, 'searchEmailAccount'])->name('searchEmailAccount'); //Done
        });

        Route::prefix('seat')->group(function () {
            Route::get('/filter-seat/{search}', [SeatController::class, 'filterSeat'])->name('filterSeat'); //Done
            Route::get('/get-seat-access/{id}', [SeatController::class, 'getSeatAccess'])->name('getSeatAccess'); //Done
            Route::get('/get-seat-by-id/{id}', [SeatController::class, 'getSeatById'])->name('getSeatById'); //Done
            Route::get('/update-seat-name/{id}/{seat_name}', [SeatController::class, 'updateName'])->name('updateName'); //Done
            Route::get('/cancel-seat-subscription/{id}', [SeatController::class, 'cancelSubscription'])->name('cancelSubscription'); //Done
            Route::get('/delete-seat/{id}', [SeatController::class, 'deleteSeat'])->name('deleteSeat'); //Done
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
            Route::delete('/delete-account', [SettingController::class, 'deleteAccount'])->name('deleteAccount'); //Done
        });
    });
});
