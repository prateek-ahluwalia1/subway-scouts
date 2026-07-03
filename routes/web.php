<?php

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\Auth\LoginController;
// use App\Http\Controllers\AdministratorController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\GuardLoginController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobRosterController;
use App\Http\Controllers\PayRateController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteGroup;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ValidateSession;
use App\Models\User;
use Illuminate\Support\Facades\Route;
 
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

Route::any('login', [LoginController::class, 'login'])->name('user.login');

Route::any('user/store', [UserController::class, 'store'])->name('user.store');

Route::any('customer-login', [CustomerLoginController::class, 'login'])->name('customer.login');
Route::any('forgot-password', [LoginController::class, 'forgotPassword'])->name('forgot.password');
Route::any('forgot-password-step-two', [LoginController::class, 'forgotPasswordStep2'])->name('forgot.password.step.two');
Route::any('sign-in-with-token', [LoginController::class, 'signInWithToken'])->name('signin.with.token');

Route::group(['middleware' => ['check.superAdmin']], function ($router) {
  Route::post('get-sub-admins', [UserController::class, 'getSubAdmins']);
  Route::post('update-subadmin-status', [UserController::class, 'activeSubAdminStatus']);
  Route::post('get-admin', [LoginController::class, 'getAdmin']);
});


Route::any('guard-login', [GuardLoginController::class, 'login'])->name('guard.login');


Route::group(['middleware' => ['check.admin']], function ($router) {
  Route::any('logout', [LoginController::class, 'logout'])->name('user.logout');
  Route::any('user/update', [UserController::class, 'update'])->name('user.update');
  Route::any('user/delete', [UserController::class, 'deleteAdmin'])->name('user.delete');
  // Route::any('get-admin-role', [UserController::class, 'getAdminRole'])->name('admin.role');

//assign permission to sub admins
  Route::post('insertAccess', [UserController::class, 'insertAccess']);
  Route::any('getAccess/{id}', [UserController::class, 'getAccess']);
  Route::post('editAccess/{id}', [UserController::class, 'editAccess']);
  Route::post('deleteAccess/{id}', [UserController::class, 'deleteAccess']);
  
//payrate routes
  Route::any('payrate/store',  [PayRateController::class, 'store'])->name('payrate.store');
  Route::any('payrate/remove',  [PayRateController::class, 'removePayrate'])->name('payrate.remove');


//policy routes
  // Route::group(['middleware' => ['admin_permissions']], function ($router) {
    Route::any('policy/store',  [PolicyController::class, 'store'])->name('job_roster');
    Route::any('policy/update', [PolicyController::class, 'update'])->name('policy.update');
    Route::any('policy/delete', [PolicyController::class, 'deletePolicy'])->name('policy.delete');
  // });
//guards routes
  Route::any('guard/store', [GuardController::class, 'store'])->name('guard.store');
  Route::any('guard/update', [GuardController::class, 'update'])->name('guard.update');
  Route::any('guard/delete', [GuardController::class, 'deleteGuard'])->name('guard.delete');

//customers routes
  Route::any('customer/store',  [CustomerController::class, 'store'])->name('customer.store');
  Route::any('customer/update', [CustomerController::class, 'update'])->name('customer.update');
  Route::any('customer/delete', [CustomerController::class, 'deleteCustomer'])->name('customer.delete');
  Route::any('get-customers',   [CustomerController::class, 'getCustomers'])->name('get.customers');
  Route::any('get-customer-by-id',   [CustomerController::class, 'getCustomerById'])->name('get.customer.by.id');
  Route::any('get-sites-by-customers',  [CustomerController::class, 'getSitesByCustomers'])->name('get.sites.by.customers');

//customers routes
  Route::any('contractor/store',  [ContractorController::class, 'store'])->name('contractor.store');
  Route::any('contractor/update', [ContractorController::class, 'update'])->name('contractor.update');
  Route::any('contractor/delete', [ContractorController::class, 'deleteContractor'])->name('contractor.delete');
  Route::any('get-contractors', [ContractorController::class, 'getContractors'])->name('get.contractors');

//customers routes
  Route::any('site/store',   [SiteController::class, 'store'])->name('site.store');
  Route::any('site/edit',    [SiteController::class, 'editSite'])->name('site.edit');
  Route::any('site/update',  [SiteController::class, 'update'])->name('site.update');
  Route::any('site/delete',  [SiteController::class, 'deleteSite'])->name('site.delete');
  

//jobRoster routes
  Route::group(['middleware' => ['check.site.permission']], function ($router) {
    Route::any('job-roster/store', [JobRosterController::class, 'store'])->name('job-roster.store');
  });
  //Route::any('job-roster/get-customers-jobs-list-filter', [JobRosterController::class, 'get_customers_jobs_list_filter'])->name('job-roster.filter');
  
});
   
// Route::group(['middleware' => ['jwt.role:admin|guard|customer']], function ($router) {
    Route::any('customer-logout', [CustomerLoginController::class, 'customerLogout'])->name('customer.logout');
    Route::any('guard-logout', [GuardLoginController::class, 'guardLogout'])->name('guard.logout');    
// });

//job roster route

//upload image 
Route::any('upload-image', [GeneralController::class, 'uploadImage'])->name('upload.image');
Route::any('checkTemp', function(){
  return view('mail.forgotPassword');
});







