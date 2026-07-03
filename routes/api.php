<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\GuardLoginController;
use App\Http\Controllers\BusinessSettingController;
use App\Http\Controllers\ChargeRateController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailSignatureController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\FormTemplateController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\InductionController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\AlarmDispatchController;
use App\Http\Controllers\JobRosterActiviteController;
use App\Http\Controllers\JobRosterController;
use App\Http\Controllers\JobTrakerController;
use App\Http\Controllers\LoginActivityController;
use App\Http\Controllers\MoveDataBDController;
use App\Http\Controllers\OperationNoteController;
use App\Http\Controllers\PatrollingController;
use App\Http\Controllers\PayRateController;
use App\Http\Controllers\reports\ChargableHrsDaliyReport;
use App\Http\Controllers\reports\ChargableHrsReport;
use App\Http\Controllers\reports\EmpHrsReport;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\RunSheetJobRosterController;
use App\Http\Controllers\RunSheetRosterController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StaffUniFormController;
use App\Http\Controllers\TimeClockController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\clickSend;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\reports\ReportController;
use App\Http\Controllers\portal\PortalSettingController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\reports\AwardOverTimeReport;
use App\Models\JobRosterActivity;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteGroup;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\ValidateSession;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\reports\InvoiceReport;
use App\Http\Controllers\reports\MonthlyReport;
use App\Http\Controllers\reports\PaysheetReport;
use App\Http\Controllers\reports\AdhocHrsReport;
use App\Http\Controllers\reports\TenHrsShiftReport;
use App\Http\Controllers\reports\FourtyHrsShiftReport;
use App\Http\Controllers\reports\ThirtySixHrsShiftReport;
use App\Http\Controllers\reports\TwelveHrsShiftReport;
use App\Http\Controllers\reports\GuardMidnightReport;
use App\Http\Controllers\reports\TaskReport;
use App\Http\Controllers\reports\LeaveManagement;
use App\Http\Controllers\reports\RosterReport;
use App\Http\Controllers\reports\GuardReport;
use App\Http\Controllers\reports\IncidentReport;
use App\Http\Controllers\reports\MultiReport;
use App\Http\Controllers\reports\MultiCallReport;
use App\Http\Controllers\reports\NonChargableHoursReport;
use App\Http\Controllers\reports\NonPayableHrsReport;
use App\Http\Controllers\reports\SickLeaveReport;
use App\Http\Controllers\reports\CasualHrsWeeklyReport;
use App\Http\Controllers\reports\GreenCallReport;
use App\Http\Controllers\reports\GlobalSalesReport;
use App\Http\Controllers\reports\SiteLocationReport;
use App\Http\Controllers\reports\TrainingHoursReport;
use App\Http\Controllers\reports\CompleteComparisonReportController;
use App\Http\Controllers\TicketManagementController;
use App\Http\Controllers\AwardPayrates;
use App\Http\Controllers\ToDoController;
use App\Http\Controllers\XeroAuthController;
use App\Http\Controllers\XeroEmployeeSyncController;
use App\Http\Controllers\XeroPayrollSyncController;

// Step 1: User hits this → browser goes to Xero login page
Route::get('/xero/connect', [XeroAuthController::class, 'redirect']);
// Step 2: Xero sends the user back here with ?code=XYZ
Route::get('/xero/callback', [XeroAuthController::class, 'callback']);
// ── All API routes — protect with your existing auth middleware ──
Route::prefix('xero')->group(function () {
 
    // Xero connection status
    Route::get('/status', [XeroAuthController::class, 'status']);
 
    // Employee sync (guard → Xero)
    // IMPORTANT: status-all must be before {guardId} to avoid route conflict
    Route::get('/employees/status-all',         [XeroEmployeeSyncController::class, 'statusAll']);
    Route::post('/employees/{guardId}/sync',     [XeroEmployeeSyncController::class, 'sync']);
    Route::get('/employees/{guardId}/status',    [XeroEmployeeSyncController::class, 'status']);
 
    // Payroll sync
    Route::post('/sync-payroll', [XeroPayrollSyncController::class, 'sync']);
    Route::get('/sync-status',   [XeroPayrollSyncController::class, 'status']);
    Route::get('/sync-log',      [XeroPayrollSyncController::class, 'log']);
    Route::get('/pay-runs', [XeroPayrollSyncController::class, 'index']);
    Route::delete('/pay-runs/{id}', [XeroPayrollSyncController::class, 'destroy']);
});
 

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/notification', [NotificationController::class, 'receive']);

//login
Route::any('login', [LoginController::class, 'login'])->name('user.login');
Route::post('check_2fa_enable', [LoginController::class, 'check2FAEnable'])->name('user.check2FAEnable');
Route::any('forgot-password', [LoginController::class, 'forgotPassword'])->name('forgot.password');
Route::any('forgot-password-step-two', [LoginController::class, 'forgotPasswordStep2'])->name('forgot.password.step.two');

// Route::any('guard-email-verification', [LoginController::class,'guardEmailVerification'])->name('guard.email.verification');
Route::get('/guard-email-verification/{email}/{token}', [LoginController::class, 'guardEmailVerification'])->name('guard.email.verification');
Route::any('guards-customer-id-to-array', [GuardController::class, 'updateCustomerIds']);
Route::get('/admin-email-verification/{email}/{business_id}', [LoginController::class, 'adminEmailVerification'])->name('admin.email.verification');

Route::group(['middleware' => ['check.db']], function ($router) {

  Route::get('/chart', [GuardController::class, 'showChart']);
  Route::get('/generate-pdf', [GuardController::class, 'generatePDF']);

  Route::any('again-guard-email-verification', [GuardController::class, 'againGuardEmailVerify']);

  Route::post('enable-2fa', [UserController::class, 'enable2FA'])->name('get.enable2FA');
  Route::any('get-colors', [PortalSettingController::class, 'getColors'])->name('get.colors');
  Route::any('logout', [LoginController::class, 'logout'])->name('user.logout');
  Route::any('store-notification-token', [UserController::class, 'storeNotificationToken'])->name('store.notification.token');
  Route::any('sign-in-with-token', [LoginController::class, 'signInWithToken'])->name('signin.with.token');

  //admins
  Route::any('user/updateuser', [UserController::class, 'updateuser'])->name('user.updateuser');
  Route::any('user/store', [UserController::class, 'store'])->name('user.store');
  Route::any('user/update', [UserController::class, 'update'])->name('user.update');
  Route::any('user/delete', [UserController::class, 'deleteAdmin'])->name('user.delete');
  Route::any('get-admins-by-login_users', [UserController::class, 'getAdminsByLoginUsers'])->name('get-admins-by-login_users');

  //subadmin
  Route::post('change-password', [LoginController::class, 'changePassword'])->name('change-password');
  Route::post('get-sub-admins', [UserController::class, 'getSubAdmins'])->name('get-sub-admins');
  Route::post('get-all-sub-admins', [UserController::class, 'getAllSubAdmins']);
  Route::get('get-all-admins', [UserController::class, 'getAllAdmins'])->name('get.all.admins');
  Route::post('update-subadmin-status', [UserController::class, 'activeSubAdminStatus'])->name('update.subadmin.status');
  Route::post('get-admin', [LoginController::class, 'getAdmin'])->name('get-admin');
  Route::post('get-user-profile-traker', [LoginController::class, 'getUserProfileTraker'])->name('get-User.profile.traker');

  //Dashboard
  // Route::any('dashboard-jobs-count',  [DashboardController::class, 'dashboardJobsCount'])->name('dashboard.jobs.count');
  Route::any('app-usages',  [DashboardController::class, 'appUsages'])->name('app.usages');
  Route::any('store-dashboard-notes',  [DashboardController::class, 'storeDashboardNotes'])->name('store.dashboard.notes');
  Route::any('get-dashboard-notes',  [DashboardController::class, 'getDashboardNotes'])->name('get-dashboard-notes');
  Route::any('delete-dashboard-notes',  [DashboardController::class, 'deleteDashboardNotes'])->name('delete.dashboard.notes');
  Route::any('edit-dashboard-note',  [DashboardController::class, 'editDashboardNote'])->name('edit.dashboard.note');
  // Route::any('staff-count',  [DashboardController::class, 'countActiveStaff']);
  Route::any('dashboard-graph-data',  [DashboardController::class, 'dashboardGraphData']);
  //payrate routes
  Route::any('payrate-history/{site_id}',  [PayRateController::class, 'history'])->name('payrate.history');
  Route::any('payrate/store',  [PayRateController::class, 'store'])->name('payrate.store');
  Route::any('payrate/update',  [PayRateController::class, 'update'])->name('payrate.update');
  Route::any('payrate/store-payrate-of-next-level',  [ChargeRateController::class, 'storePayRateOfNextLevel'])->name('payrate.store.next.level');
  Route::any('get-all-payrates',  [PayRateController::class, 'getAllPayrate'])->name('get.all.payrates');
  Route::any('get-payrate',  [PayRateController::class, 'getPayrate'])->name('get.payrate');
  Route::any('get-all-archive-payrates',  [PayRateController::class, 'getAllArchivePayrate'])->name('get.all.archive_payrates');
  Route::any('payrate/remove',  [PayRateController::class, 'removePayrate'])->name('payrate.remove');
  Route::any('get-payrates-with-level-and-state',  [PayRateController::class, 'getPayrateWithLevelAndState'])->name('get.payrates.with.level.and.state');

  //Payrate CRUD
  Route::any('/create_payrate', [PayRateController::class, 'create_payrate']);
  // Route::any('/create_award_rate', [PayRateController::class, 'create_award_rate']);
  Route::any('/payrates', [PayRateController::class, 'payrates']);
  Route::any('/add_payrate', [PayRateController::class, 'add_payrate']);
  Route::any('/get_payrates/{id}', [PayRateController::class, 'get_payrates']);
  Route::any('/update_payrates', [PayRateController::class, 'update_payrates']);
  Route::any('/delete_payrate/{id}', [PayRateController::class, 'delete_payrate']);

  //award_Payrate CRUD
  Route::any('/award_payrates', [AwardPayrates::class, 'payrates']);
  Route::any('/get_award_payrates/{id}', [AwardPayrates::class, 'get_payrates']);
  Route::any('/create_award_payrate', [AwardPayrates::class, 'create_payrate']);
  Route::any('/update_award_payrates', [AwardPayrates::class, 'update_payrates']);
  Route::any('/delete_award_payrate/{id}', [AwardPayrates::class, 'delete_payrate']);

  //chargeRate routes
  Route::any('charge-rate-history/{site_id}',  [ChargeRateController::class, 'history'])->name('charge_rate.history');
  Route::any('charge_rate/store',  [ChargeRateController::class, 'store'])->name('charge_rate.store');
  Route::any('charge_rate/update',  [ChargeRateController::class, 'update'])->name('charge_rate.update');
  Route::any('charge_rate/store-charge-rate-of-next-level',  [ChargeRateController::class, 'storeChargeRateOfNextLevel'])->name('charge_rate.store.next.level');
  Route::any('get-all-chargerates',  [ChargeRateController::class, 'getAllChargeRate'])->name('get.all.charge_rate');
  Route::any('getSpecificChargeRateWithLevel',  [ChargeRateController::class, 'getSpecificChargeRateWithLevel'])->name('get.all.charge_rate');
  Route::any('get-all-archive-chargerates',  [ChargeRateController::class, 'getAllArchiveChargeRate'])->name('get.all.archive_charge_rate');
  Route::any('charge_rate/remove',  [ChargeRateController::class, 'removeChargeRate'])->name('charge_rate.remove');

  //policy routes
  Route::any('policy/store',  [PolicyController::class, 'store'])->name('job_roster');
  Route::any('policy/update', [PolicyController::class, 'update'])->name('policy.update');
  Route::any('policy/delete', [PolicyController::class, 'deletePolicy'])->name('policy.delete');

  // Guard routes
  Route::any('guard/quick-onboarding-staff', [GuardController::class, 'quickOnboardingStaff'])->name('guard.quick.onboarding.staff');
  Route::any('guard/get_guard_avability', [GuardController::class, 'get_guard_avability'])->name('guard.get_guard_avability');
  Route::any('guard/update_guard_avability', [GuardController::class, 'update_guard_avability'])->name('guard.update_guard_avability');
  Route::any('guard/create-new-staff', [GuardController::class, 'createNewStaff'])->name('create.new.staff');
  Route::any('guard/delete-document', [GuardController::class, 'deleteDocs']);
  Route::any('guard/active-deactive', [GuardController::class, 'activateGuard'])->name('guard.active.deactive');
  Route::any('guard/available', [GuardController::class, 'guardAvailable'])->name('guard.available');
  Route::any('guard/restore', [GuardController::class, 'guardRestore'])->name('guard.restore');
  Route::any('get-all-guards', [GuardController::class, 'getAllGuards'])->name('get.all.guards');
  Route::any('import-guards', [GuardController::class, 'importGuards'])->name('import.guards');
  Route::any('get-guard-docs-name', [GuardController::class, 'getGuardDocumentName']);
  Route::any('staff-status-acitvity', [GuardController::class, 'staffStatusActivity']);
  Route::any('guard/covid_19/status', [GuardController::class, 'onCovid_19'])->name('covid.status');
  //Route::any('guard/edit', [GuardController::class, 'guardEdit'])->name('guard.edit');
  Route::any('guard-update-employment-details', [GuardController::class, 'updateEmploymentDetail'])->name('guard.update.employment.details');
  Route::any('edit-employment-details', [GuardController::class, 'editEmploymentDetail'])->name('edit-employment-details');
  Route::any('guard-add-documents', [GuardController::class, 'addGuardDocuments'])->name('guard.add.documents');
  Route::any('guard-update-documents', [GuardController::class, 'updateGuardDocuments'])->name('guard.update.documents');
  Route::any('guard-delete-documents', [GuardController::class, 'deleteGuardDocument'])->name('guard.delete.documents');
  Route::any('guard-edit-documents', [GuardController::class, 'editGuardDocument'])->name('guard.edit.documents');
  Route::any('guard-all-documents', [GuardController::class, 'getAllGuardDocument'])->name('guard.all.documents');
  Route::any('guard-document-type', [GuardController::class, 'getDocumentType'])->name('guard.document.type');
  Route::any('guard/update', [GuardController::class, 'update'])->name('guard.update');
  Route::any('guard/update-leaves', [GuardController::class, 'updateLeaves'])->name('guard.leaves');
  Route::any('guard/delete', [GuardController::class, 'deleteGuard'])->name('guard.delete');
  Route::any('get-all-customer-guards', [GuardController::class, 'getAllCustomerGuards'])->name('get.all.customerGuards');
  Route::any('get-guards-sites', [GuardController::class, 'getGuardSites'])->name('get.guards.sites');
  Route::any('add-guard-internal-external-ids', [GuardController::class, 'addInternalAndExternalIds'])->name('add.guard.internal.external.ids');
  Route::any('edit-guard-internal-external-ids', [GuardController::class, 'editInternalAndExternalIds'])->name('edit.guard.internal.external.ids');
  Route::any('get-staff-data', [GuardController::class, 'getStaffData'])->name('get.staff.data');
  Route::any('delete-external-id', [GuardController::class, 'deleteExternalId'])->name('delete.external.id');
  Route::any('get-all-system-guards', [GuardController::class, 'getAllSystemGuards'])->name('get.all.system.guards');
  Route::any('filter-guard-license', [GuardController::class, 'filterGuardLicense'])->name('filter.guard.license');
  Route::any('filter-add-guard-on-site', [GuardController::class, 'filterAddGuardOnSite'])->name('filter.add.guard.on.site');
  Route::any('filter-add-guard-on-runsheet', [GuardController::class, 'filterAddGuardOnRunSheet']);
  Route::any('add-customer-site-guard', [GuardController::class, 'addCustomerSiteGuard'])->name('add.customer.site.guard');
  Route::any('add-customer-runsheet-guard', [GuardController::class, 'addCustomerRunSheetGuard'])->name('add.customer.site.guard');
  Route::any('get-guard-by-site', [GuardController::class, 'getGuardbySite'])->name('add.customer.site.guard');
  Route::any('get-full-timer-guard-by-site', [JobRosterController::class, 'getFullTimeGuardbySite'])->name('add.customer.site.full-guard');
  Route::any('get-guard-by-runsheet', [GuardController::class, 'getGuardbyRunsheet'])->name('add.customer.runsheet.guard');
  Route::any('inradius-guards', [GuardController::class, 'inRadiusGuards'])->name('guard.inradius');
  Route::any('get-guard-profile-traker', [GuardController::class, 'getGuardProfileTracker'])->name('get.guard.profile.tracker');
  Route::any('get-guard-leaves', [GuardController::class, 'getGuardLeaves'])->name('get.guard.leaves');
  Route::any('get-guards-before-and-after-week-shift', [GuardController::class, 'getGuardsBeforeAndAfterWeekShift'])->name('get.guards.before.and.after.week-shift');
  Route::any('get-active-guards', [GuardController::class, 'getActiveGuards'])->name('get.active.guards');
  Route::any('get-staff-by-staff-type', [DashboardController::class, 'getStaffByStaffType']);
  Route::any('documents-online-verification', [GuardController::class, 'documentsOnlineVerification']);
  Route::any('get-available-guards', [GuardController::class, 'getAavailableGuards']);

  Route::any('save-update-trained-guard-on-site', [GuardController::class, 'saveAndUpdateTrainedGuardOnSite']);
  Route::any('edit-trained-guard-on-site', [GuardController::class, 'editTrainedGuardOnSite']);
  Route::any('delete-trained-guard-on-site', [GuardController::class, 'deleteTrainedGuardOnSite']);
  Route::any('get-all-trained-guard-on-site', [GuardController::class, 'getAllTrainedGuardOnSite']);
  Route::any('find-guard', [GuardController::class, 'findGuard']);

  //For Complaince Required Doc Route Start
  Route::any('guard-all-req-documents', [GuardController::class, 'getAllReqGuardDocument'])->name('guard.all.req.documents');
  Route::any('guard-update-req-documents', [GuardController::class, 'updateGuardReqDocuments'])->name('guard.update.req.documents');
  Route::any('guard/status-active-deactive', [GuardController::class, 'guardStatus'])->name('guard.status.active.deactive');
  Route::any('get-guard-document', [GuardController::class, 'getGuardDocument'])->name('get.guard.document');
  Route::any('update-document-category', [GuardController::class, 'updateDocumentCategory'])->name('get.document.category');
  Route::any('guard/create-new-staff-save', [GuardController::class, 'createNewStaffSave'])->name('create.new.staff.save');
  Route::any('guard/quick-onboarding-staff-save', [GuardController::class, 'quickOnboardingStaffSave'])->name('guard.quick.onboarding.staff.save');

  //For Complaince Required Doc Route End 
  //guard forms
  Route::any('add-employment-pack-checklist', [GuardController::class, 'addEmploymentPackChecklist'])->name('add-employment-pack-checklist');
  Route::any('get-employment-pack-checklist', [GuardController::class, 'getEmploymentPackChecklist'])->name('get-employment-pack-checklist');
  Route::any('get-emp-details', [GuardController::class, 'getEmpDetails'])->name('get.emp.details');
  Route::any('update-emp-details', [GuardController::class, 'updateEmpDetails'])->name('update.emp.details');
  Route::any('get-uniform-details', [GuardController::class, 'getUniFormDetails'])->name('get.uniform.details');
  Route::any('update-uniform-details', [GuardController::class, 'updateUniFormDetails'])->name('update.uniform.details');
  Route::any('get-guard-emergency-contact-details', [GuardController::class, 'getGuardEmergencyContactDetails'])->name('get.guard.emergency.contact.details');
  Route::any('update-guard-emergency-contact-details', [GuardController::class, 'updateGuardEmergencyContactDetails'])->name('update.guard.emergency.contact.details');
  Route::any('get-personal-refrences', [GuardController::class, 'getPersonalRefrences'])->name('get.personal.refrences');
  Route::any('update-personal-refrences', [GuardController::class, 'updatePersonalRefrences'])->name('update.personal.refrences');
  Route::any('update-refrence', [GuardController::class, 'updateRefrence'])->name('update.refrence');
  Route::any('get-refrence', [GuardController::class, 'getRefrence'])->name('get.refrence');
  //new 
  Route::any('get-staff-contractor-details', [GuardController::class, 'getStaffContractorDetails']);
  Route::any('download-staff-contractor-form', [GuardController::class, 'downloadStaffContractorForm']);
  Route::any('save-and-update-staff-contractor-details', [GuardController::class, 'saveAndUpdateStaffContractorDetails']);

  // guard Uniform
  Route::any('save-staff-uniform-detials', [StaffUniFormController::class, 'saveStaffUniformDetials'])->name('save.staff.uniform.detials');
  Route::any('update-staff-uniform-detials', [StaffUniFormController::class, 'updateStaffUniformDetials'])->name('update.staff.uniform.detials');
  Route::any('get-staff-uniform-detials', [StaffUniFormController::class, 'getStaffUniformDetials'])->name('get.staff.uniform.detials');
  Route::any('edit-staff-uniform-detials', [StaffUniFormController::class, 'editStaffUniformDetials'])->name('edit.staff.uniform.detials');
  Route::any('staffUniFormActivity', [StaffUniFormController::class, 'staffUniFormActivity']);

  //subscription Plan & Payment
  Route::any('get-all-plans', [SubscriptionPlanController::class, 'getAllPlans'])->name('get.all.plans');
  Route::any('subscription-plan/store', [SubscriptionPlanController::class, 'store'])->name('subscription.plan.store');
  Route::any('subscription-plan/update', [SubscriptionPlanController::class, 'update'])->name('subscription.plan.update');
  Route::any('subscription-plan/edit', [SubscriptionPlanController::class, 'edit'])->name('subscription.plan.edit');
  Route::any('subscription-plan/delete', [SubscriptionPlanController::class, 'delete'])->name('subscription.plan.delete');
  Route::any('subscription-plan/poli-payment', [SubscriptionPlanController::class, 'polipayment'])->name('subscription.plan.polipayment');
  Route::any('subscription-plan/payment/success', [SubscriptionPlanController::class, 'paymentsuccess'])->name('subscription.plan.paymentsuccess');
  Route::any('demo-view', [SubscriptionPlanController::class, 'demofunction'])->name('demo.view');

  //Cars 
  Route::any('get-all-cars', [CarController::class, 'getAllCars'])->name('get.all.Cars');
  Route::any('store-car', [CarController::class, 'storeCar'])->name('car.store');
  Route::any('update-car', [CarController::class, 'updateCar'])->name('car.update');
  Route::any('edit-car', [CarController::class, 'editCar'])->name('car.edit');
  Route::any('delete-car', [CarController::class, 'deleteCar'])->name('car.delete');

  //Alarm Dispatch
  Route::any('get-all-alarm-dispatch', [AlarmDispatchController::class, 'getAllAlarmDispatch'])->name('get.all.alarm');
  Route::any('store-alarm-dispatch', [AlarmDispatchController::class, 'storeAlarmDispatch'])->name('alarm.store');
  Route::any('update-alarm-dispatch', [AlarmDispatchController::class, 'updateAlarmDispatch'])->name('alarm.update');
  Route::any('edit-alarm-dispatch', [AlarmDispatchController::class, 'editAlarmDispatch'])->name('alarm.edit');
  Route::any('delete-alarm-dispatch', [AlarmDispatchController::class, 'deleteAlarmDispatch'])->name('alarm.delete');

  //guard Feedback
  Route::any('save-feedback', [GuardController::class, 'saveFeedback'])->name('save.feedback');
  Route::any('update-feedback', [GuardController::class, 'updateFeedback'])->name('update.feedback');
  Route::any('show-guard-feedback', [GuardController::class, 'showGuardFeedBack'])->name('show.guard.feedback');
  Route::any('delete-feedback', [GuardController::class, 'deketeFeedback'])->name('delete-feedback');

  //customers routes
  Route::any('customer/store',  [CustomerController::class, 'store'])->name('customer.store');
  Route::any('customer/update', [CustomerController::class, 'update'])->name('customer.update');
  Route::any('customer/delete', [CustomerController::class, 'deleteCustomer'])->name('customer.delete');
  Route::any('get-customers',   [CustomerController::class, 'getCustomers'])->name('get.customers');
  // Route::any('get-customers-name',   [CustomerController::class, 'getCustomersName'])->name('get.customers.name');
  // Route::any('get-customer-by-id',   [CustomerController::class, 'getCustomerById'])->name('get.customer.by.id');
  Route::any('get-sites-by-customers',  [CustomerController::class, 'getSitesByCustomers'])->name('get.sites.by.customers');
  Route::any('get-runsheet-by-customers',  [CustomerController::class, 'getRunSheetByCustomers']);
  Route::any('delete-customer-more-contact',  [CustomerController::class, 'deleteCustomerMoreContact'])->name('delete.customer.more.contact');
  Route::any('active-customer-status',  [CustomerController::class, 'activeCustomerStatus'])->name('active.customer.status');
  Route::any('get-customer-profile-traker',  [CustomerController::class, 'getCustomerProfileTraker'])->name('get-customer-profile-traker');

  //contractor routes
  Route::any('contractor/store',  [ContractorController::class, 'store'])->name('contractor.store');
  Route::any('contractor/update', [ContractorController::class, 'update'])->name('contractor.update');
  Route::any('contractor/delete', [ContractorController::class, 'deleteContractor'])->name('contractor.delete');
  Route::any('get-contractors', [ContractorController::class, 'getContractors'])->name('get.contractors');
  Route::any('active-contractors-status', [ContractorController::class, 'activeContractorStatus'])->name('get.contractors');
  // Route::any('get-contractor-by-id', [ContractorController::class, 'getContractorById'])->name('get.contractor.by.id');
  Route::any('delete-contractor-more-contact',  [ContractorController::class, 'deleteContractorMoreContact'])->name('delete.contractor.more.contact');

  //site routes
  Route::any('site/save-and-update',   [SiteController::class, 'saveAndUpdate'])->name('site.save.and.update');
  Route::any('site/edit', [SiteController::class, 'editSite'])->name('site.edit');
  Route::any('find-sites', [SiteController::class, 'findSites']);
  Route::any('get-all-sites', [SiteController::class, 'getAllSites'])->name('get.all.sites');
  Route::any('active-and-inactive-sites-by-customer',  [SiteController::class, 'activeAndInactiveSitesByCustomer'])->name('active.and.inactive.sites.by.customer');
  Route::any('site/delete',  [SiteController::class, 'deleteSite'])->name('site.delete');
  Route::any('get-payrate-and-level',  [SiteController::class, 'getPayRateAndLevel'])->name('get.payrate.and.level');
  Route::any('fetch-site-by-status', [GeneralController::class, 'fetchSitesByStatus'])->name('fetch.site.by.status');
  Route::any('get-delete-site-reasons', [SiteController::class, 'getDeleteSiteReasons']);
  Route::any('create-business-folder', [GeneralController::class, 'createBusinessFolder']);
  Route::any('create-business-file', [GeneralController::class, 'createBusinessFile']);
  Route::any('get-business-folder', [GeneralController::class, 'getBusinessFolder']);
  Route::any('get-all-business-files', [GeneralController::class, 'getAllBusinessfile']);
  Route::any('get-specific-business-files', [GeneralController::class, 'getSpecificBusinessfile']);
  Route::any('delete-business-files', [GeneralController::class, 'deleteBusinessFile']);
  Route::any('rename-folder-name', [GeneralController::class, 'renameFolderName']);
  Route::any('delete-folder', [GeneralController::class, 'deleteFolder']);
  // Route::any('get-chargerate-and-level',  [SiteController::class, 'getChargeRateAndLevel'])->name('get-chargerate-and-level');
  Route::any('/roster-bulk-delete', [JobRosterController::class, 'rosterBulkDelete']);
  Route::any('getSelectedListPublish', [JobRosterController::class, 'getSelectedListPublish']);
  Route::any('copyShiftNew', [JobRosterController::class, 'copyShiftNew']);
  Route::any('getPublishList', [JobRosterController::class, 'getPublishList']);
  Route::any('publishRosterNew', [JobRosterController::class, 'publishRosterNew']);
  Route::any('asap-job', [JobRosterController::class, 'createAsapJob'])->name('guard.asapjob');
  Route::any('job-new-roster/store', [JobRosterController::class, 'store'])->name('job-roster.store');
  // Route::any('job-new-roster/get-all', [JobRosterController::class, 'getAllJobNewRosters'])->name('get.all.job.new.roster');
  Route::any('update-jobRoster-status', [JobRosterController::class, 'updateJobRosterStatus'])->name('update-jobRoster-status');
  Route::any('call-toggle', [JobRosterController::class, 'calltoggledata'])->name('call-toggle');
  Route::any('edit-job-new-roster', [JobRosterController::class, 'editJobNewRoster'])->name('edit.job.new.roster');
  Route::any('get-roaster-hour-sum', [JobRosterController::class, 'getrosterhoursum'])->name('get.roster.hours.sum');
  Route::any('update-job-new-roster', [JobRosterController::class, 'updateJobNewRoster'])->name('update-job-new-roster');
  //Route::any('delete-new-jobRoster', [JobRosterController::class, 'deleteNewJobRoster'])->name('delete.new.jobRoster');
  Route::any('job-new-roster/get-sites-by-customes', [JobRosterController::class, 'getSitesByCustomes'])->name('jobNewRoster.get-sites-by-customes');
  Route::any('get-newjobroster-customers-name', [JobRosterController::class, 'newJobRosterCustomers'])->name('get.newjobroster.customers.name');

  //jobRoster routes
  Route::any('customer-invoice-store',   [JobRosterController::class, 'customerinvoicestore'])->name('customer.invoice.store');
  Route::any('download-invoice-form', [JobRosterController::class, 'downloadInvoiceForm']);
  Route::any('download-shift-activity', [JobRosterController::class, 'downloadShiftActivity']);
  Route::any('fetch-customer-sites', [JobRosterController::class, 'fetchCustomerSites'])->name('fetch.customer.sites');
  Route::any('fetch-customer-unpublish-sites', [JobRosterController::class, 'fetchCustomerUnpublishSites'])->name('fetch.customer.unpublish.sites');
  Route::any('fetch-customer-updated-sites', [JobRosterController::class, 'fetchCustomerUpdatedSites'])->name('fetch.customer.sites_updated');
  Route::any('publish-shifts', [JobRosterController::class, 'publishShifts'])->name('publish.shifts');
  Route::any('split-shift', [JobRosterController::class, 'splitShift'])->name('add.spilt.shift');
  Route::any('add-new-shift', [JobRosterController::class, 'addNewShift'])->name('add.new.shift');
  Route::any('check-guards-availibilty', [JobRosterController::class, 'checkGuardsAvailibilty'])->name('add.check.availibilty');
  Route::any('get-full-timer-guards-availibilty', [JobRosterController::class, 'getFullTimerAvailableGuards'])->name('add.full-timer.availibilty');
  Route::any('update-shift', [JobRosterController::class, 'updateShift'])->name('add.update.shift');
  Route::any('delete-shift', [JobRosterController::class, 'deleteShift'])->name('delete.shift');
  Route::any('edit-job-roster', [JobRosterController::class, 'editJobRoster'])->name('edit.job.roster');
  Route::any('update-roster-time', [JobRosterController::class, 'updateRosterTime'])->name('update.roster.time');
  Route::any('shift-drop-and-copy', [JobRosterController::class, 'shiftDropAndCopy'])->name('shift.drop.and.copy');
  Route::any('fetch-template-shifts', [JobRosterController::class, 'fetchTemplateShift'])->name('fetch.template.shifts');
  Route::any('delete-job-roster-task', [JobRosterController::class, 'deleteJobRosterTask'])->name('delete.job.roster.task');
  Route::any('available-guards', [JobRosterController::class, 'getAllAavailableGuards']);
  Route::post('get-roster-deleted-shifts', [JobRosterController::class, 'getRosterDeletedShifts']);

  // JobRosterActivity
  Route::any('get-jobSignIn-jobSignOut', [JobRosterActiviteController::class, 'JobSignInSignOut'])->name('job.signIn.signout');
  Route::any('guard-break-details', [JobRosterActiviteController::class, 'guardBreakDetails'])->name('guard.break.details');
  Route::any('guard-welfarecall', [JobRosterActiviteController::class, 'guardWelfareCall'])->name('guard.welfarecall');
  Route::any('guard-greencall', [JobRosterActiviteController::class, 'guardGreenCallDetails'])->name('guard.greencall');
  Route::any('guard-jobTraker', [JobRosterActiviteController::class, 'guardJobTraker'])->name('guard.jobTraker');
  Route::any('guard-incident-report', [JobRosterActiviteController::class, 'guardIncidentReport'])->name('guard.incident.report');
  Route::any('guard-foot-patrol-report', [JobRosterActiviteController::class, 'guardFootPatrolReport'])->name('guard.foot_patrol.report');
  Route::any('guard-patrolling-report', [JobRosterActiviteController::class, 'guardPatrollingReport'])->name('guard.patrolling.report');
  Route::any('guard-greencall-details', [JobRosterActiviteController::class, 'guardGreenCallDetails'])->name('guard.greencall.details');
  Route::any('jobroster-give-rating', [JobRosterActiviteController::class, 'giveRatingJobRoster'])->name('giveRatingJobRoster');
  Route::any('get-jobroster-rating', [JobRosterActiviteController::class, 'getJobrosterRating'])->name('getJobrosterRating');
  Route::any('store-operation-notes', [JobRosterActiviteController::class, 'storeOperationNotes'])->name('store.operation.notes');
  Route::any('get-operation-notes', [JobRosterActiviteController::class, 'getOperationNotes'])->name('get.operation.notes');
  Route::any('get-shift-activity', [JobRosterActiviteController::class, 'getShiftActivity'])->name('get.shift.activity');
  Route::any('get-job-tasks', [JobRosterActiviteController::class, 'getJobTasks'])->name('get.job.tasks');
  Route::any('generateJobTaskReport', [JobRosterActiviteController::class, 'generateJobTaskReport']);
  Route::any('job-traker-jobs', [JobTrakerController::class, 'jobTrakerJobs'])->name('guard.jobTrakerJobs');
  Route::any('jobs-count', [JobTrakerController::class, 'jobsCount'])->name('guard.jobsCount');
  Route::post('update-shift-task', [JobRosterController::class, 'updateShfitTask'])->name('guard.updateShfitTask');
  Route::post('update-incident-report', [JobRosterController::class, 'updateIncidentReport'])->name('guard.updateIncidentReport');
  Route::post('update-foot-patrol-report', [JobRosterController::class, 'updateFootPatrolReport'])->name('guard.updateFootPatrolReport');

  //click send routes
  Route::any('sendSMS', [clickSend::class, 'sendSMS'])->name('sendSMS');
  Route::any('getChat', [clickSend::class, 'getChat'])->name('getChat');
  Route::any('getChatUser', [clickSend::class, 'getChatUser'])->name('getChatUser');
  Route::post('isNewMessage', [clickSend::class, 'isNewMessage'])->name('isNewMessage');
  Route::any('filter-guards', [clickSend::class, 'filterGuards'])->name('filter-guards');

  // EMail Routes
  Route::any('sendEmail', [EmailController::class, 'sendEmail'])->name('sendEmail');
  Route::any('email-signature-store', [EmailSignatureController::class, 'storeEmailSignature'])->name('email.signature.store');
  Route::any('get-all-email-signature', [EmailSignatureController::class, 'getAllEmailSignature'])->name('get.all.email.signature');
  Route::any('get-all-email-signature-title-id', [EmailSignatureController::class, 'getAllEmailSignatureTitleAndID'])->name('get.all.email.signature.title.id');
  Route::any('delete-email-signature', [EmailSignatureController::class, 'deleteEmailSignature'])->name('delete.email.signature');
  Route::any('edit-email-signature', [EmailSignatureController::class, 'editEmailSignature'])->name('edit.email.signature');
  Route::any('update-email-signature', [EmailSignatureController::class, 'updateEmailSignature'])->name('edit.email.signature');

  //Email Templates
  Route::any('store-email-template', [EmailTemplateController::class, 'storeEmailTemplate'])->name('store.email.template');
  Route::any('update-email-template', [EmailTemplateController::class, 'updateEmailTemplate'])->name('update.email.template');
  Route::any('delete-email-template', [EmailTemplateController::class, 'deleteEmailTemplate'])->name('delete.email.template');
  Route::any('get-email-templates', [EmailTemplateController::class, 'getEmailTemplates'])->name('get.email.templates');
  Route::any('get-email-templates-title-id', [EmailTemplateController::class, 'getEmailTemplatesTitleAndId'])->name('get.email.templates.title.id');
  Route::any('get-single-email-template', [EmailTemplateController::class, 'getSingleEmailTemplate'])->name('get.email.single_template');

  //Form Templates
  Route::any('store-form-template', [FormTemplateController::class, 'store'])->name('store.form.template');
  Route::any('get-all-form-templates', [FormTemplateController::class, 'getAllFormTemplate'])->name('get.all.form.templates');
  Route::any('edit-form-templates', [FormTemplateController::class, 'editFormTemplate'])->name('edit.form.templates');
  Route::any('update-form-templates', [FormTemplateController::class, 'updateFormTemplate'])->name('update.form.templates');
  Route::any('insert-form-data', [FormTemplateController::class, 'insertFormData'])->name('insert.form.data');
  Route::any('delete-form-templates', [FormTemplateController::class, 'deleteFormTemplate'])->name('delete.form.templates');
  Route::any('form-template-guard-filter', [FormTemplateController::class, 'formTemplateGuardFilter'])->name('form.template.guard.filter');
  Route::any('submit-dynamic-form', [FormTemplateController::class, 'submitDynamicForm'])->name('form.template.submitDynamicForm');
  Route::any('history-dynamic-form/{form_id}', [FormTemplateController::class, 'historyDynamicForm'])->name('form.template.historyDynamicForm');

  //Sms Templates
  Route::post('getMessageHistory', [clickSend::class, 'getMessageHistory'])->name('getMessageHistory');
  Route::post('receiveMessage', [clickSend::class, 'receiveMessage'])->name('receiveMessage');
  Route::get('getSMSTemplates', [SmsTemplateController::class, 'getSMSTemplates'])->name('getSMSTemplates');
  Route::post('addSMSTemplate', [SmsTemplateController::class, 'addSMSTemplate'])->name('addSMSTemplate');
  Route::post('updateSMSTemplate', [SmsTemplateController::class, 'updateSMSTemplate'])->name('updateSMSTemplate');
  Route::post('deleteSMSTemplate', [SmsTemplateController::class, 'deleteSMSTemplate'])->name('deleteSMSTemplate');

  // Reports here
  Route::any('customer_report_search', [ReportController::class, 'customer_report_search']);
  Route::any('report/get_guard_document_report', [ReportController::class, 'get_guard_document_report']);
  Route::any('getTimesheet', [ReportController::class, 'getTimesheet'])->name('getTimesheet');
  Route::any('generateJobTrackerReport', [ReportController::class, 'generateJobTrackerReport']);
  Route::any('generate-timesheet', [ReportController::class, 'generateTimesheetReport'])->name('generateTimesheetReport');
  Route::any('get-timesheet-details', [ReportController::class, 'getTimeSheetDetails'])->name('get.timesheet.details');
  Route::any('store-guard-timesheet-comments', [ReportController::class, 'storeGuardTimeSheetComments'])->name('store-guard-timesheet-comments');
  Route::any('get-task-report', [ReportController::class, 'getTaskReport'])->name('get.task.report');
  Route::get('generate-task-pdf', [ReportController::class, 'generateTaskPDF'])->name('generate.task.pdf');
  Route::any('generate-patrolling-pdf', [ReportController::class, 'generatePatrollingPDF'])->name('generate.task.patrolling');
  Route::any('generate-induction-report', [ReportController::class, 'generateInductionReport']);

  // job Traker
  Route::any('get-jobTraker', [JobTrakerController::class, 'getJobTraker'])->name('get.jobTraker');
  Route::any('get-jobTraker-details', [JobTrakerController::class, 'getJobTrakerDetails'])->name('get.jobTraker.details');
  Route::any('job-status-manual-approved', [JobTrakerController::class, 'jobStatusManualApproved'])->name('job.status.manual.approved');

  //login Activity
  Route::any('get-admin-activity', [LoginActivityController::class, 'getAdminActivity'])->name('get.admin.activity');

  //time clock
  Route::any('get-time-clock-jobs', [TimeClockController::class, 'getTimeClockJobs'])->name('get.time.clock.jobs');

  //Announcement
  Route::any('add-announcement', [AnnouncementController::class, 'addAnnouncement'])->name('add.announcement');
  Route::any('get-all-announcement', [AnnouncementController::class, 'getAllAnnouncement'])->name('get.all.announcement');
  Route::any('delete-announcement', [AnnouncementController::class, 'deleteAnnouncement'])->name('delete.announcement');
  Route::any('announcement-history', [AnnouncementController::class, 'getAnnouncementhistory'])->name('announcement.history');
  Route::any('edit-announcement', [AnnouncementController::class, 'editAnnounce'])->name('edit.announcement');
  Route::get('regenerate-all-certificates', [AnnouncementController::class, 'regenerateAllCertificates']);


  //inductions
  Route::any('add-induction', [InductionController::class, 'addInduction'])->name('add.induction');
  Route::any('get-all-induction', [InductionController::class, 'getAllInduction'])->name('get.all.induction');
  Route::any('delete-induction', [InductionController::class, 'deleteInduction'])->name('delete.induction');
  Route::any('edit-induction', [InductionController::class, 'editInduction'])->name('edit.induction');

  //upload image
  Route::any('upload-image', [GeneralController::class, 'uploadImage'])->name('upload.image');
  Route::any('upload-file-with-size', [GeneralController::class, 'fileUploadWithSize']);
  Route::get('/get-customers-files/{customer_id}', [GeneralController::class, 'getCRMFileData']);
  Route::any('upload-file', [GeneralController::class, 'uploadFile'])->name('upload.file');

  //public holiday
  Route::any('add-public-holiday', [PortalSettingController::class, 'addPH'])->name('add-public-holiday');
  Route::any('update-public-holiday', [PortalSettingController::class, 'updatePH'])->name('update-public-holiday');
  Route::any('delete-public-holiday', [PortalSettingController::class, 'deletePH'])->name('delete-public-holiday');
  Route::any('get-public-holiday', [PortalSettingController::class, 'getPH'])->name('get-public-holiday');

  // Portal settings
  Route::any('portal-colors', [PortalSettingController::class, 'updatePortalColors'])->name('updatePortalColors');
  Route::any('copy-shifts-sites', [JobRosterController::class, 'getCopyShiftSites'])->name('shifts.copy.sites');
  Route::any('copy-shifts-sites-by-customer', [JobRosterController::class, 'getCopyShiftSitesByCustomer'])->name('shifts.copy.sites.by.customer');
  Route::any('copy-roster', [JobRosterController::class, 'copyRoster'])->name('copy.roster');
  Route::any('copy-roster-next-dates', [JobRosterController::class, 'copyRosterNextDates']);
  Route::any('roster-actions', [JobRosterController::class, 'rosterActions'])->name('roster.actions');
  Route::any('roster-multiple-shifts', [JobRosterController::class, 'createMultipleShifts'])->name('multiple.roster.shifts'); //....
  Route::post('/update-induction-status', [GuardController::class, 'runUpdateInductionStatus']);


  Route::any('get-guard-payslips', [GuardController::class, 'getGuardPayslips']);
  Route::any('auto-update-payslips', [GuardController::class, 'autoUpdatePayslipStatus']);
  Route::any('upload-payslips', [GuardController::class, 'uploadPayslips']);
  Route::any('getSpecificGuards', [GuardController::class, 'getSpecificGuards'])->name('getSpecificGuards');
  Route::any('update-announcement-read-status', [AnnouncementController::class, 'updateReadStatus'])->name('update-read-status');
  Route::any('share', [AnnouncementController::class, 'shareAnnouncement'])->name('shareAnnouncement');
  Route::any('taskReport', [TaskReport::class, 'getTaskReportData'])->name('getTaskReportData');
  Route::any('generateTaskReport', [TaskReport::class, 'generateTaskReport'])->name('generateTaskReport');
  Route::any('generatePaysheetReport', [PaysheetReport::class, 'generatePaysheetReport'])->name('generatePaysheetReport');
  Route::any('generateOldPaysheetReport', [PaysheetReport::class, 'generateOldPaysheetReport'])->name('generateOldPaysheetReport');
  Route::any('generatePayrollPaysheetReport', [PaysheetReport::class, 'generatePayrollPaysheetReport'])->name('generatePayrollPaysheetReport');
  Route::any('generateQuickPaysheetReport', [PaysheetReport::class, 'generateQuickPaysheetReport'])->name('generateQuickPaysheetReport');
  Route::any('getLeaveDetails', [LeaveManagement::class, 'getLeaveDetails'])->name('getLeaveDetails');
  Route::any('getPendingLeaveRequests', [LeaveManagement::class, 'getPendingLeaveRequests'])->name('getPendingLeaveRequests');
  Route::any('addAdminLeaveRequest', [LeaveManagement::class, 'addAdminLeaveRequest'])->name('addAdminLeaveRequest');
  Route::any('getLeaveGuards', [LeaveManagement::class, 'getLeaveGuards'])->name('getLeaveGuards');
  Route::any('approveLeave', [LeaveManagement::class, 'approveLeave'])->name('approveLeave');
  Route::any('guardOnLeave', [LeaveManagement::class, 'guardOnLeave'])->name('guardOnLeave');
  Route::any('guardOnLeavePatrolling', [LeaveManagement::class, 'guardOnLeavePatrolling'])->name('guardOnLeavePatrolling');
  Route::any('generateInvoiceReport', [InvoiceReport::class, 'generateInvoiceReport'])->name('generateInvoiceReport');
  Route::any('generateLeaveReport', [LeaveManagement::class, 'generateLeaveReport'])->name('generateLeaveReport');
  Route::any('generateCustomersExcel', [CustomerController::class, 'generateCustomersExcel'])->name('generateCustomersExcel');
  Route::any('generateProfitLossInvoice', [InvoiceReport::class, 'generateProfitLossInvoice'])->name('generateProfitLossInvoice');
  Route::any('sendInvoice', [InvoiceReport::class, 'sendInvoice'])->name('sendInvoice');
  Route::any('generateRosterReport', [RosterReport::class, 'generateRosterReport']);
  Route::any('/generateRosterReportEmail', [RosterReport::class , 'generateRosterReportEmail']);
  Route::any('generateRosterReportNormal', [RosterReport::class, 'generateRosterReportNormal']);
  Route::any('generateRosterReportDivNormal', [RosterReport::class, 'generateRosterReportDivNormal']);
  Route::any('generateSigninoutReport', [RosterReport::class, 'generateSigninoutReport']);
  Route::any('generateGuardReport', [GuardReport::class, 'generateGuardReport'])->name('generateGuardReport');
  Route::any('generateGuestAuthReport', [GuardReport::class, 'generateGuestAuthReport'])->name('generateGuestAuthReport');
  Route::any('getGuardReport', [GuardReport::class, 'getGuardReport'])->name('getGuardReport');
  Route::any('getIncidentReportData', [IncidentReport::class, 'get_incident_report'])->name('get_incident_report');
  Route::any('generateIncidentReport', [IncidentReport::class, 'generateIncidentReport'])->name('generateIncidentReport');
  Route::any('generateFootPatrolReport', [IncidentReport::class, 'generateFootPatrolReport'])->name('generateFootPatrolReport');

  //multirepo

  Route::any('generateChargeableReport', [ChargableHrsReport::class, 'generateChargeableReport']);
  Route::any('generateChargeableHrsDaliyReport', [ChargableHrsDaliyReport::class, 'generateChargeableHrsDaliyReport']);
  Route::any('generateEmpHrsReport', [EmpHrsReport::class, 'generateEmpHrsReport']);
  Route::any('generateAwardOvertimeReport', [AwardOverTimeReport::class, 'generateOvertimeReport']);
  Route::any('generateNonChargeableHoursReport', [NonChargableHoursReport::class, 'generateNonChargeableHoursReport']);
  Route::any('generateTrainingHoursReport', [TrainingHoursReport::class, 'generateTrainingHoursReport']);
  Route::any('generateNonPayableHrsReport', [NonPayableHrsReport::class, 'generateNonPayableHrsReport']);
  Route::any('generateSickLeaveReport', [SickLeaveReport::class, 'generateSickLeaveReport']);
  Route::any('generateCasualHoursWeeklyReport', [CasualHrsWeeklyReport::class, 'generateCasualHoursWeeklyReport']);
  Route::any('generateGreenCallReport', [GreenCallReport::class, 'generateGreenCallReport']);
  Route::any('generateGlobalSalesReport', [GlobalSalesReport::class, 'generateGlobalSalesReport']);
  Route::any('generateSiteLocationReport', [SiteLocationReport::class, 'generateSiteLocationReport']);
  Route::any('generateMultiReport', [MultiReport::class, 'generateMultiReport']);
  Route::any('generateMultiCallReport', [MultiCallReport::class, 'generateMultiCallReport']);

  //AMG Reports Route
  Route::any('generateAdhocHoursShiftReport', [AdhocHrsReport::class, 'generateAdhocHoursShiftReport']);
  Route::any('generateFourtyHoursShiftReport', [FourtyHrsShiftReport::class, 'generateFourtyHoursShiftReport']);
  Route::any('generateTenHoursShiftReport', [TenHrsShiftReport::class, 'generateTenHoursShiftReport']);
  Route::any('generateThirtySixHoursShiftReport', [ThirtySixHrsShiftReport::class, 'generateThirtySixHoursShiftReport']);
  Route::any('generateTwelveHoursShiftReport', [TwelveHrsShiftReport::class, 'generateTwelveHoursShiftReport']);
  Route::any('generateGuardMidnightReport', [GuardMidnightReport::class, 'generateGuardMidnightReport']);
  Route::any('generate-pdf-monthly-report', [MonthlyReport::class, 'generate_pdf_monthly_report']);

  Route::get('/download-guard-certificates', [GuardController::class, 'generateAllCertificatesZip']);


  Route::post('/staff-injury', [MonthlyReport::class, 'storeStaffInjury']);
  Route::post('/guard-leave', [MonthlyReport::class, 'storeGuardLeave']);
  Route::post('/point-of-contact', [MonthlyReport::class, 'storePointContact']);
  Route::post('/near-misses', [MonthlyReport::class, 'storeNearMisses']);




  Route::any('send-page-link', [FormTemplateController::class, 'sendPageLink'])->name('send.page.link');
  Route::any('send-bulitin-form', [FormTemplateController::class, 'sendBuiltIn'])->name('send.built.in');
  Route::any('history-bulitin-form', [FormTemplateController::class, 'historyBuiltIn'])->name('history.built.in');
  Route::any('bulitin-form-link-clicked', [FormTemplateController::class, 'builtInFormLinkClicked'])->name('send.builtInFormLinkClicked');

  Route::any('liveDashabordData',  [DashboardController::class, 'liveDashabordData'])->name('app.liveDashabordData');
  // Route::any('reminders-compliance-exp-docs',  [DashboardController::class, 'remindersComplianceExpDocs'])->name('app.remindersComplianceExpDocs');

  Route::any('getNearToExpireLicenseGuard', [DashboardController::class, 'getNearExpireLicenseGuard'])->name('app.getNearExpireLicenseGuard');
  Route::any('green-call-toggle', [DashboardController::class, 'greencalltoggle'])->name('green-call-toggle');
  Route::any('get-call-notes', [DashboardController::class, 'getcallnotes'])->name('get-call-notes');
  Route::any('call-notes-update', [DashboardController::class, 'callnotesupdate'])->name('call-notes-update');
  Route::any('getNearExpireVisaGuard',  [DashboardController::class, 'getNearExpireVisaGuard'])->name('app.getNearExpireVisaGuard');
  Route::any('getNearExpirePassportGuard',  [DashboardController::class, 'getNearExpirePassportGuard']);
  Route::any('get-staff-confirmation',  [DashboardController::class, 'getStaffConfirmation']);

  // Route::any('liveWelfareCallData',  [DashboardController::class, 'liveWelfareCallData'])->name('app.liveWelfareCallData');

  // new Dashbord
  Route::any('publish-and-unpublish-shift-count-one-week',  [DashboardController::class, 'publishAndUnpublishShiftCountOneWeek']);

  Route::any('get-customer-by-id',   [CustomerController::class, 'getCustomerById'])->name('get.customer.by.id');
  Route::any('get-contractor-by-id', [ContractorController::class, 'getContractorById'])->name('get.contractor.by.id');
  Route::any('guard/edit', [GuardController::class, 'guardEdit'])->name('guard.edit');
  Route::any('get-chargerate-and-level',  [SiteController::class, 'getChargeRateAndLevel'])->name('get-chargerate-and-level');
  #without middleware samad
  Route::any('job-new-roster/get-all', [JobRosterController::class, 'getAllJobNewRosters'])->name('get.all.job.new.roster');
  Route::any('get-customers-name',   [CustomerController::class, 'getCustomersName'])->name('get.customers.name');
  Route::post('get-customers-name-timesheet',   [CustomerController::class, 'getCustomersNameTimesheet'])->name('get.customers.name');
  # USER LOGGING TRACK
  Route::post('track-user-activity',   [GeneralController::class, 'trackUserActivity']);
  Route::post('get-user-activity',   [GeneralController::class, 'getUserActivity']);
  # TODO RESOURCE API
  Route::resource('/todos', ToDoController::class);
  Route::post('/todos/update/{id}', [ToDoController::class, 'update']);
  Route::get('/todos/change-status/{id}', [ToDoController::class, 'changeStatus']);

  Route::get('/app-status', [GeneralController::class, 'appStatus']);

  Route::any('close-app-notification', [GeneralController::class, 'closeAppNotification']);


  Route::post('guard-leave-count', [DashboardController::class, 'guardLeaveCount']);

  //Route::any('test-one-signal',   [GeneralController::class, 'testOneSignal']);
  Route::any('testNotification/{any}', [JobRosterController::class, 'testNotification'])->name('testNotification');

  Route::get('get-current-time/{guard_id}', [GuardController::class, 'getCurrentTime'])->name('getCurrentTime');

  // Roles And Permissions
  Route::any('get-all-role-permissions', [RolePermissionController::class, 'getAllRolePermissions']);
  Route::any('save-and-update-role-permissions', [RolePermissionController::class, 'saveAndUpdateRolePermissions']);
  Route::any('edit-role-permissions', [RolePermissionController::class, 'editRolePermissions']);
  Route::any('delete-role-permissions', [RolePermissionController::class, 'deleteRolePermissions']);

  Route::any('/guard/manual-visa-verification', [GeneralController::class, 'manualVisaVarification']);
  Route::get('/guard/get-visa-details', [GuardController::class, 'getVisaDetails']);
  Route::post('/save-third-party-apis', [GeneralController::class, 'saveThirdPartyApis']);
  Route::get('/get-third-party-apis', [GeneralController::class, 'getThirdPartyApis']);
  Route::get('/get-guard-leaves/{guard_id}', [LeaveManagement::class, 'getGuardLeave']);

  //operation notes
  Route::any('operation-notes-store', [OperationNoteController::class, 'store']);
  Route::any('get-all-operation-notes', [OperationNoteController::class, 'getAllNotes']);
  Route::any('operation-notes-mark-as-read', [OperationNoteController::class, 'markAsRead']);
  Route::any('delete-operation-notes', [OperationNoteController::class, 'deleteOperationNotes']);

  /******************************* QUESTIONNAIRE ***********************************/
  Route::post('questionnaire-save', [QuestionnaireController::class, 'save']);
  Route::post('assign-questionnaire', [QuestionnaireController::class, 'assignQuestionnair']);
  Route::get('questionnaire-delete/{id}', [QuestionnaireController::class, 'delete']);
  Route::get('questionnaire-list', [QuestionnaireController::class, 'list']);
  /******************************* QUESTIONNAIRE END ***********************************/
  /******************************* TICKETS MANAGEMENT ***********************************/
  Route::post('gaurd/open-ticket', [TicketManagementController::class, 'openATicket']);
  Route::post('get-tickets', [TicketManagementController::class, 'getTickets']);
  Route::get('get-tickets-details/{ticket_id}', [TicketManagementController::class, 'getTicketDetails']);
  Route::post('reply-ticket', [TicketManagementController::class, 'replyTicket']);
  Route::get('admin/close-ticket/{ticket_id}', [TicketManagementController::class, 'closeTicket']);
  /******************************* END TICKETS MANAGEMENT ***********************************/
  Route::get('/authenticationQr', [GeneralController::class, 'authenticationQr'])->name('authenticationQr');

  Route::any('operation-note-show', [OperationNoteController::class, 'showNotes']);
  Route::post('get-admin-shift-activity', [JobRosterController::class, 'getAdminShiftActivity']);
  Route::get('delete-admin-shift-activity/{id}', [JobRosterController::class, 'deleteAdminShiftActivity']);
  Route::post('save-admin-shift-activity', [JobRosterController::class, 'saveAdminShiftActivity']);
  Route::get('unseen-notifications-chat/{id}', [GeneralController::class, 'getUnseenNotificationChat']);
  Route::get('read-notifications-chat/{id}', [GeneralController::class, 'readNotificationChat']);
  Route::post('read-single-notifications-chat', [GeneralController::class, 'readSingleNotificationChat']);
  Route::post('get-all-unread-notifications-chat', [GeneralController::class, 'getAllUnReadNotificationChat']);
  Route::get('unseen-notifications', [GeneralController::class, 'getUnseenNotification']);
  Route::get('read-notifications', [GeneralController::class, 'readNotification']);
  Route::post('read-single-notifications', [GeneralController::class, 'readSingleNotification']);
  Route::get('get-all-unread-notifications', [GeneralController::class, 'getAllUnReadNotification']);

  /******************************* RunSheetRoster ***********************************/

  Route::prefix('runsheet-roster')->group(function () {
    Route::any('all', [RunSheetRosterController::class, 'runsheets']);
    Route::any('store', [RunSheetRosterController::class, 'store']);
    Route::any('edit', [RunSheetRosterController::class, 'edit']);
    Route::any('update', [RunSheetRosterController::class, 'update']);
    Route::any('update-status', [RunSheetRosterController::class, 'updateStatus']);
    Route::any('delete-run-sheet-detail', [RunSheetRosterController::class, 'deleteRunsheetDetail'])->name('delete.runsheetDetail');
    Route::any('publish-shifts', [RunSheetJobRosterController::class, 'publishShifts']);
    Route::any('fetch-customer-unpublish-sites', [RunSheetJobRosterController::class, 'fetchCustomerUnpublishSites'])->name('fetch.customer.unpublish.sites');
    Route::any('delete-run-sheet-task', [RunSheetJobRosterController::class, 'deleteRunSheetTask'])->name('delete.run.sheet.task');
    Route::any('fetch-template-shifts', [RunSheetJobRosterController::class, 'fetchTemplateShift'])->name('fetch.template.shifts');
    Route::any('update-runsheet-time', [RunSheetJobRosterController::class, 'updateRunsheetTime'])->name('update.runsheet.time');
    Route::post('get-runsheet-deleted-shifts', [RunSheetJobRosterController::class, 'getRunsheetDeletedShifts']);
    Route::any('guardOnLeavePatrolling', [LeaveManagement::class, 'guardOnLeavePatrolling'])->name('guardOnLeavePatrolling');
    Route::any('copy-shifts-runsheet', [RunSheetJobRosterController::class, 'getCopyShiftRS'])->name('shifts.copy.runsheet');
    Route::any('copy-runsheet-next-dates', [RunSheetJobRosterController::class, 'copyRunsheetNextDates']);
    Route::any('runsheet-actions', [RunSheetJobRosterController::class, 'runsheetActions'])->name('runsheet.actions');
    Route::any('get-guards-runsheet', [RunSheetJobRosterController::class, 'getGuardRunsheet'])->name('get.guards.runsheet');
    Route::post('save-admin-shift-activity', [RunSheetJobRosterController::class, 'saveAdminShiftActivity']);
  });

  /******************************* RunSheetJobRoster ***********************************/

  /******************************* Mobile API routes ***********************************/

  Route::prefix('m')->group(function () {
    Route::any('get-today-roster-count', [JobRosterController::class, 'todayrostercount'])->name('get-today-roster-count');
    Route::any('get-today-roster-sites', [JobRosterController::class, 'todayrostersites'])->name('get-today-roster-sites');
    Route::any('start-patrolling/{id}', [JobRosterController::class, 'startPatrolling']);
    Route::any('get-scanner-history/{id}', [JobRosterController::class, 'getScannerHistory']);
    Route::any('scan-QRCode/{id}', [JobRosterController::class, 'scanQR']);
    Route::any('scan-QRCode-app/{id}', [JobRosterController::class, 'scanQRApp']);

  });

  /******************************* Mobile API routes ***********************************/

  Route::any('add-new-runsheet-shift', [RunSheetJobRosterController::class, 'addNewShift']);
  Route::any('update-runsheet-shift', [RunSheetJobRosterController::class, 'updateShift']);
  Route::any('fetch-runsheet-shifts', [RunSheetJobRosterController::class, 'fetchRunSheets']);
  Route::any('edit-runsheet-shift', [RunSheetJobRosterController::class, 'editRunsheetJobRoster']);
  Route::any('delete-runsheet-shift', [RunSheetJobRosterController::class, 'deleteShift']);
  Route::any('shift-drop-copy-runsheet-shift', [RunSheetJobRosterController::class, 'shiftDropAndCopy']);




  /*******************************PATROLLING MODULE***********************************/
  Route::post('get-patrolling-locations', [PatrollingController::class, 'getPatrollingLocations']);
  Route::post('create-update-run-sheet', [PatrollingController::class, 'createUpdateRunSheet']);
  Route::any('get-all-run-sheets', [PatrollingController::class, 'getAllRunSheets']);
  Route::get('edit-run-sheets/{id}', [PatrollingController::class, 'editRunSheets']);
  Route::any('delete-run-sheet', [PatrollingController::class, 'deleteRunSheet']);
  Route::any('get-delete-runsheet-reasons', [PatrollingController::class, 'getDeleteRunSheetReasons']);
  Route::any('unique-code-scan', [PatrollingController::class, 'uniquecodescan']);
  Route::any('get-runsheet-customers-name', [PatrollingController::class, 'runSheetRostersCustomers'])->name('get.newjobroster.customers.name');

  /*******************************PATROLLING MODULE END***********************************/
  /*******************************CHAT MODULE START***********************************/
  Route::post('send-message',   [GeneralController::class, 'sendMessage']);
  Route::post('get-messages',   [GeneralController::class, 'fetchChatHistory']);
  Route::get('get-previous-history-chat/{id}',   [GeneralController::class, 'getPreviousChatHistory']);
  # CHAT BETWEEN ADMINS
  Route::post('send-message-admin',   [GeneralController::class, 'sendMessageAdmin']);
  Route::post('get-messages-admin',   [GeneralController::class, 'fetchChatHistoryAdmin']);
  Route::get('get-previous-history-chat-admin/{id}',   [GeneralController::class, 'getPreviousChatHistoryAdmin']);
  # CHAT BETWEEN CUSTOMERS
  Route::post('send-message-customer',   [GeneralController::class, 'sendMessageCustomer']);
  Route::post('get-messages-customer',   [GeneralController::class, 'fetchChatHistoryCustomer']);
  Route::get('get-previous-history-chat-customer/{id}',   [GeneralController::class, 'getPreviousChatHistoryCustomer']);

  # CHAT BETWEEN CONTRACTOR
  Route::post('send-message-contractor',   [GeneralController::class, 'sendMessageContractor']);
  Route::post('get-messages-contractors',   [GeneralController::class, 'fetchChatHistoryContractor']);
  Route::get('get-previous-history-chat-contractor/{id}',   [GeneralController::class, 'getPreviousChatHistoryContractor']);
  # CHAT BETWEEN GUARDS
  Route::post('send-message-staff',   [GeneralController::class, 'sendMessageGuard']);
  Route::post('get-messages-staff',   [GeneralController::class, 'fetchChatHistoryGuard']);
  Route::get('get-previous-history-chat-staff/{id}',   [GeneralController::class, 'getPreviousChatHistoryGuard']);
  /*******************************CHAT MODULE END***********************************/
  Route::post('submit-audit-report',   [GeneralController::class, 'submitAuditReport']);
  Route::get('get-audits/{id}',   [GeneralController::class, 'getAllAudits']);
  Route::post('search-audits',   [ReportController::class, 'getAuditReport']);
  Route::get('delete-audit/{id}',   [ReportController::class, 'deleteAuditReport']);
  Route::get('generate-audit-report/{id}',   [GeneralController::class, 'generateAuditReport']);
  Route::post('/guard/visa-varification-recheck', [GuardController::class, 'visaVarificationRecheck']);
});

Route::post('/guard/visaVarification', [GuardController::class, 'visaVarification']);
/*******************************CRONJOBS START***********************************/
Route::get('/auto_sign_out', [GuardController::class, 'auto_sign_out']);
Route::get('check-guard-document-status',   [GeneralController::class, 'checkGuardDocumentStatus']);
Route::get('delete-user-previous-activity',   [GeneralController::class, 'deleteUserActivity']);
Route::get('delete-portal-notification',   [GeneralController::class, 'deletePortalNotifications']);
// Route::get('/guard/auto-visa-verification',   [GeneralController::class, 'autoVisaVarification']);

Route::get('sendUncoverdShiftMailToAdmins', [JobRosterController::class, 'sendUncoverdShiftMailToAdmins']);
Route::get('deleteTransientFiles', [GeneralController::class, 'deleteTransientFiles']);

// Route::post('/guard_location_at_job/{id}', [GuardController::class, 'saveGuardLocation']);
// $router->post('/guard_location_at_job/{id}', 'Api\JobController@saveGuardLocation');

/*******************************CRONJOBS END***********************************/


/*******************************ATTENDANCE APP START APIS***********************************/
Route::get('guest_login', [GeneralController::class, 'guestSignin']);
Route::get('guest_logout/{id}', [GeneralController::class, 'guestSignout']);
Route::get('guest_emplyees', [GeneralController::class, 'guestEmplyess']);
/*******************************ATTENDANCE APP END APIS***********************************/


// business_settings
Route::any('business-setting/all', [BusinessSettingController::class, 'getAll'])->name('business.setting.get.all');
Route::any('business-setting/store', [BusinessSettingController::class, 'store'])->name('business.setting.store');
Route::any('business-setting/edit', [BusinessSettingController::class, 'edit'])->name('business.setting.edit');
Route::any('business-setting/update', [BusinessSettingController::class, 'update'])->name('business.setting.update');
Route::any('business-setting/delete', [BusinessSettingController::class, 'delete'])->name('business.setting.delete');
Route::any('add-and-update-business-permission', [BusinessSettingController::class, 'addAndUpdateBusinessPermissions'])->name('add-and-update-business-permission');
Route::any('get-add-and-update-business-permission', [BusinessSettingController::class, 'getAddAndUpdateBusinessPermissions'])->name('get-add-and-update-business-permission');
Route::any('get-business-settings', [BusinessSettingController::class, 'getBusinessSettings'])->name('get.business.settings');

Route::any('get-location', [GeneralController::class, 'getLocation']);
Route::any('test-mail', [GeneralController::class, 'testMail']);

Route::get('/send-birthday-mail', [GeneralController::class, 'sendBirthdayMail'])->name('sendBirthdayMail');

// Route::any('assigninduction', [MoveDataBDController::class, 'assignInductionsToRemaningGuards']);
// Route::any('getAdmins247SecurityGroupe', [MoveDataBDController::class, 'getAdmins247SecurityGroupe']);
// Route::any('getLocation247SecurityGroupe', [MoveDataBDController::class, 'getLocation247SecurityGroupe']);
// Route::any('getCustomers247SecurityGroupe', [MoveDataBDController::class, 'getCustomers247SecurityGroupe']);
// Route::any('getGuards247SecurityGroupe', [MoveDataBDController::class, 'getGuards247SecurityGroupe']);
// Route::any('getPayrate247SecurityGroupe', [MoveDataBDController::class, 'getPayrate247SecurityGroupe']);
// Route::any('getChargerate247SecurityGroupe', [MoveDataBDController::class, 'getCharagerte247SecurityGroupe']);
//add new for AMG
// Route::any('getGuardscpr', [MoveDataBDController::class, 'getGuardscpr']);
// Route::any('getGuardsfirstaid', [MoveDataBDController::class, 'getGuardsfirstaid']);
// Route::any('addworkwithchild', [MoveDataBDController::class, 'addworkwithchild']);
Route::any('assignInductionsToActiveGuards', [MoveDataBDController::class, 'assignInductionsToActiveGuards1']);
Route::any('fixMissingInductionHistory', [MoveDataBDController::class, 'fixMissingInductionHistory']);
Route::any('fixMissingInductionHistory1', [MoveDataBDController::class, 'fixMissingInductionHistory1']);
// Route::any('changeEbaToAwardRate', [MoveDataBDController::class, 'changeEbaToAwardRate']);
// Route::any('downloadAndUploadGuardDocFiles', [MoveDataBDController::class, 'downloadAndUploadGuardFiles']);
// Route::any('getGuardsIds247SecurityGroupe', [MoveDataBDController::class, 'getGuardsIds247SecurityGroupe']);
// Route::any('uploadmissingdoc', [MoveDataBDController::class, 'uploadmissingdoc']);
// Route::any('updatedocoldtonew', [MoveDataBDController::class, 'updatedocoldtonew']);
// Route::any('/getquestionnaires', [MoveDataBDController::class, 'getquestionnaires']);
// Route::any('/syncShiftsAmgToScouts', [JobRosterController::class, 'syncShiftsAmgToScouts']);

//Chrage rate CRUD

Route::any('/get_charged_rates', [ChargeRateController::class, 'charged_rates']);
Route::any('/create_charged_rate', [ChargeRateController::class, 'create_charged_rate']);
Route::any('/get_charged_rates/{id}', [ChargeRateController::class, 'get_charged_rates']);
Route::any('/update_charged_rates/{id}', [ChargeRateController::class, 'update_charged_rates']);
Route::any('/delete_charged_rate/{id}', [ChargeRateController::class, 'delete_charged_rate']);
Route::any('/get_charged_rates_history', [ChargeRateController::class, 'get_charged_rates_history']);
Route::any('/get_complete_Comparison_Report', [CompleteComparisonReportController::class, 'getCompleteComparisonReportData']);
Route::any('/generateCompleteComparisonReport', [CompleteComparisonReportController::class, 'generateCompleteComparisonReport']);



// Remove this after testing
Route::post('/xero/employees/sync-all', [XeroEmployeeSyncController::class, 'syncAll']);