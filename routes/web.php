<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DasboardController;
use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\RolespermissionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\MaindashboardController;
use App\http\Controllers\CompaignController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/',[LoginController::class,'login']);
Route::post('logout', [LoginController::class,'logoutUser'])->name('logout-user');
Route::get('/register',[RegisterController::class,'register']);
Route::post('/register-user', [RegisterController::class, 'registerUser'])->name('register-user');
Route::get('/dashboard',[DasboardController::class,'dashboard'])->name('dashobardz');
Route::get('/blacklist',[BlacklistController::class,'blacklist']);
Route::get('/team',[TeamController::class,'team']);
Route::get('/invoice',[InvoiceController::class,'invoice']);
Route::get('/rolesandpermission',[RolespermissionController::class,'rolespermission']);
Route::get('/setting',[SettingController::class,'setting']);
Route::get('/accdashboard',[MaindashboardController::class,'maindasboard']);
Route::get('/compaign',[CompaignController::class,'compaign']);
Route::get('/createcompaign',[CompaignController::class,'compaigncreate']);
Route::get('/compaigninfo',[CompaignController::class,'compaigninfo']);
Route::post('/check-credentials', [LoginController::class, 'checkCredentials'])->name('checkCredentials');