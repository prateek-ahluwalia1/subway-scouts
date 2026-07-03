<?php 
use App\Http\Controllers\crm\CrmTasksController;
use App\Http\Controllers\crm\EmailController;
use App\Http\Controllers\crm\QuotationController;
use App\Http\Controllers\crm\ScrumboardController;
use App\Http\Controllers\GeneralController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\crm\SalesPerson;
use App\Http\Controllers\crm\Customers;
use App\Http\Controllers\crm\StageController;
use App\Http\Controllers\CrmCommonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;


Route::group(['middleware' => ['check.db']], function ($router) {

Route::prefix("crm")->group(function(){
   Route::get("unseen-notification/{id}", [CrmCommonController::class, 'getNotifications']);
   Route::get("getSalesPersonList", [SalesPerson::class, 'getSalesPersonList']);
   Route::post("addSalesPerson", [SalesPerson::class, 'addSalesPerson']);
   Route::post("updateSalesPerson", [SalesPerson::class, 'updateSalesPerson']);
   Route::post("getSalesPersonDetails/{id}", [SalesPerson::class, 'getSalesPersonDetails']);
   Route::get("deleteSalesPerson/{id}", [SalesPerson::class, 'deleteSalesPerson']);
   Route::get("getRoleUsers", [UserController::class, 'getRoleUsers']);
   
   Route::post("dashboardCustomersList", [Customers::class, 'dashboardCustomersList']);
   Route::post("getCustomersList", [Customers::class, 'getCustomersList']);
   Route::get("getCustomersDetails/{id}", [Customers::class, 'getCustomersDetails']);
   Route::post("archiveCustomer", [Customers::class, 'archiveCustomer']);
   Route::post("addCustomer", [Customers::class, 'addCustomer']);
   Route::post("updateCustomer", [Customers::class, 'updateCustomer']);
   Route::post("deleteCustomer", [Customers::class, 'deleteCustomer']);
   Route::post("get-crm-customer-timeline", [Customers::class, 'getCrmCustomerTimeline']);
   Route::post("lead-status-count", [Customers::class, 'leadStatusCount']);
   Route::post("total-lead", [Customers::class, 'Totallead']);


   // CRM Graph API
   Route::any("getPieGraphData", [Customers::class, 'getPieGraphData']);
   Route::any("getGraphData", [Customers::class, 'getGraphData']);
   Route::any("getMonthlyData", [Customers::class, 'getMonthlyData']);
   Route::any("get-company-list", [Customers::class, 'getCrmCompanyList']);


   //staga
   Route::any('add-and-update-scrumboard', [ScrumboardController::class, 'addAndUpdateScrumboard']);
   Route::any('edit-scrumboard', [ScrumboardController::class, 'editScrumboard']);
   Route::any('delete-scrumboard', [ScrumboardController::class, 'deleteScrumboard']);
   Route::any('get-all-scrumboards', [ScrumboardController::class, 'getAllScrumboard']);
  


// stages crud
   Route::any('add-update-stage', [StageController::class, 'addUpdateStage']);
   Route::any('get-all-stages', [StageController::class, 'getStages']);
   Route::any('delete-stage', [StageController::class, 'deleteStage']);

 // Stages Card Crud
   Route::any('add-update-card', [StageController::class, 'addAndUpdateCard']);
   Route::any('update-card-position', [StageController::class, 'updateCardPosition']);
   Route::any('update-card-saleperson', [StageController::class, 'updateCardSalePerson']);
   Route::any('add-card-comment', [StageController::class, 'addComment']);
   Route::any('get-comments', [StageController::class, 'getComment']);
   Route::any('get-card-details', [StageController::class, 'getData']);
   # CUSTOMER COMMENT
   Route::post('add-customer-comment', [StageController::class, 'addCustomerComment']);
   Route::get('get-customers-comments/{customer_id}', [StageController::class, 'getCustmerComment']);
   // complete data of specific scrumboard
   Route::any('getScrumboardDetail', [ScrumboardController::class, 'getScrumboardDetail']);

   Route::any('dashboard-lead-percentage',  [DashboardController::class, 'dashboardLeadPercentage'])->name('dashboard.customer.percentage');
   Route::any('dashboard-customers-count',  [DashboardController::class, 'dashboardCustomerCount'])->name('dashboard.customer.count');
   Route::post('dashboard-customers-loss-revenue',  [DashboardController::class, 'dashboardCustomerLossRevenue'])->name('dashboard.customer.loss_revenue');

   Route::post('get-specific-leads', [CrmCommonController::class, 'getSpecificLeads']);
   Route::post('get-contact-leads', [CrmCommonController::class, 'getContactLeads']);
   Route::post('get-won-leads', [CrmCommonController::class, 'getWonLeads']);
   Route::post('get-lost-leads', [CrmCommonController::class, 'getLostLeads']);
   //Qoutation routes
   Route::any('add-update-quotation', [QuotationController::class, 'addAndUpdateQuotation']);
   Route::any('get-all-quotations', [QuotationController::class, 'getAllQuotation']);
   Route::any('edit-quotation', [QuotationController::class, 'edit']);
   Route::any('delete-quotation', [QuotationController::class, 'deleteQuotation']);
   Route::any('get-quotations/{id}', [QuotationController::class, 'getQuotations']);
   Route::post('convert-quote-into-invoice', [QuotationController::class, 'convetQuoteIntoInvoice']);
   //Email
   Route::any('send-email', [EmailController::class, 'sendEmail']);
   Route::get('send-email-history/{id}', [EmailController::class, 'sendEmailHistory']);
   Route::post('save-image-base64', [CrmCommonController::class, 'saveBase64Image']);
   //tasks
   Route::any('add-update-crm-task', [CrmTasksController::class, 'addAndUpdateCrmTask']);
   Route::any('get-all-crm-task', [CrmTasksController::class, 'getAllCrmTasks']);
   Route::any('edit-crm-task', [CrmTasksController::class, 'editCrmTasks']);
   Route::any('delete-crm-task', [CrmTasksController::class, 'deleteCrmTasks']);
   //customer file delete
   Route::any('delete-crm-customer-file', [GeneralController::class, 'deleteCRMCustomerFile']);
   Route::get('get-reminder/{lead_id}', [CrmCommonController::class, 'getReminder']);
   Route::post('add-reminder', [CrmCommonController::class, 'addReminder']);
   Route::get('changes-reminder-status/{reminder_id}', [CrmCommonController::class, 'changeReminderStatus']);
   Route::get('reminder-delete/{reminder_id}', [CrmCommonController::class, 'deleteReminder']);
   Route::any('generateCrmLeadData', [CrmCommonController::class, 'generateCrmLead'])->name('generateCrmLead');

   
});


});

?>