
import { NgModule } from '@angular/core';
import { NgbActiveModal, NgbModule, NgbPopover, NgbPopoverModule } from '@ng-bootstrap/ng-bootstrap';
import { HttpClientModule, HttpClientJsonpModule } from '@angular/common/http';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { NgbTimepickerModule } from '@ng-bootstrap/ng-bootstrap';
import { NgxMaterialTimepickerModule } from 'ngx-material-timepicker';
import { NgxMatSelectSearchModule } from 'ngx-mat-select-search';
import { GooglePlaceModule } from "ngx-google-places-autocomplete";
import { MatListModule } from '@angular/material/list';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';
import { SelectMultipleSiteComponent } from '../admin/component/select-multiple-site/select-multiple-site.component';
import { SelectMultipleCustomerComponent } from './component/select-multiple-customer/select-multiple-customer.component';
import { WeeklySelectionModule } from './component/weekly-selection/weekly-selection.module';
import { RefrencesFormComponent } from './refrences-form/refrences-form.component';
import { PersonalRefrenceFormComponent } from './personal-refrence-form/personal-refrence-form.component';
import { ModalInvoiceComponent } from './modal-invoice/modal-invoice.component';
import { CallDashboardComponent } from './call-dashboard/call-dashboard.component';
import { SelectsSitesMultipleJobRostersComponent } from './selects-sites-multiple-job-rosters/selects-sites-multiple-job-rosters.component';
import { ChatDetailsComponent } from './chat-details/chat-details.component';
import { MyCkEditorComponent } from 'app/shared/ck-editor-module/my-ck-editor/my-ck-editor.component';
import { CKEditorModule } from '@ckeditor/ckeditor5-angular';
import { NgApexchartsModule } from 'ng-apexcharts';
import { MaterialModule } from 'app/shared/material.module';
import { ProfileCommentComponent } from './profile-comment/profile-comment.component';
import { SkeltonComponent } from './skelton/skelton.component';
import { PdfComponent } from './component/pdf/pdf.component';
import { NgxQRCodeModule } from 'ngx-qrcode2';

import { CustomeLoaderComponent } from './custome-loader/custome-loader.component';
import { TimePipe } from 'app/pipes/time.pipe';
import { CapitalizePipe } from 'app/shared/capitalize.pipe';
import { CreateCustomerComponent } from './component/create-customer/create-customer.component';
import { AddUnavailabilityComponent } from './models/add-unavailability/add-unavailability.component';
import { AddUniformDetailComponent } from './models/add-uniform-detail/add-uniform-detail.component';
import { AdminDetailComponent } from './models/admin-detail/admin-detail.component';
import { CheckAvailableStaffComponent } from './models/check-available-staff/check-available-staff.component';
import { CopyDaySiteComponent } from './models/copy-day-site/copy-day-site.component';
import { CopyShiftBulkComponent } from './models/copy-shift-bulk/copy-shift-bulk.component';
import { CreateAdminComponent } from './models/create-admin/create-admin.component';
import { CreateNewRunsheetComponent } from './models/create-new-runsheet/create-new-runsheet.component';
import { CreateRosterComponent } from './models/create-roster/create-roster.component';
import { CreateSiteComponent } from './models/create-site/create-site.component';
import { CustomerMainComponent } from './models/customer-main/customer-main.component';
import { DetailModelComponent } from './models/detail-model/detail-model.component';
import { EmploymentDetailComponent } from './models/employment-detail/employment-detail.component';
import { EntoAdvanceDetailComponent } from './models/ento-advance-detail/ento-advance-detail.component';
import { GreenCallComponent } from './models/green-call/green-call.component';
import { JobshiftActivityComponent } from './models/jobshift-activity/jobshift-activity.component';
import { LeaveDetailsComponent } from './models/leave-details/leave-details.component';
import { MessageTemplateComponent } from './models/message-template/message-template.component';
import { NotesForShiftComponent } from './models/notes-for-shift/notes-for-shift.component';
import { OnBoardingStaffComponent } from './models/on-boarding-staff/on-boarding-staff.component';
import { OtpVerificationComponent } from './models/otp-verification/otp-verification.component';
import { PersonDetailComponent } from './models/person-detail/person-detail.component';
import { RunsheetCustomTemplateComponent } from './models/runsheet-custom-template/runsheet-custom-template.component';
import { SignInOutComponent } from './models/sign-in-out/sign-in-out.component';
import { StaffActivityDetailsComponent } from './models/staff-activity-details/staff-activity-details.component';
import { TrackerComponent } from './models/tracker/tracker.component';
import { UpdateShiftPopoverComponent } from './models/update-shift-popover/update-shift-popover.component';
import { UpdateTimeComponent } from './models/update-time/update-time.component';
import { UserMenuComponent } from './models/user-menu/user-menu.component';
import { WelfareCallComponent } from './models/welfare-call/welfare-call.component';
import { DatePipe } from '@angular/common';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatMenuModule } from '@angular/material/menu';
import { MatSelectModule } from '@angular/material/select';
import { CreateNewAlarmComponent } from './models/create-new-alarm/create-new-alarm.component';
import { CreateNewPatrolCarDetailsComponent } from './models/create-new-patrol-car-details/create-new-patrol-car-details.component';
import { CreateNewMechanicComponent } from './models/create-new-mechanic/create-new-mechanic.component';
import { DateRangeComponent } from './component/date-range/date-range.component';
import { PayChargeHistoryComponent } from './models/pay-charge-history/pay-charge-history.component';
import { NameFilterPipe } from 'app/shared/name-filter.pipe';
import { firstLastNameFilterPipe } from 'app/shared/first-last-name-filter.pipe';
import { siteFilterPipe } from 'app/shared/site-filter.pipe';

import { NgxStarRatingModule } from 'ngx-star-rating';
import { SelectMultipleGuardsModule } from './component/select-multiple-guards/select-multiple-guards.module';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { SiteFormComponent } from './operations-modules/components/locations/site-form/site-form.component';

@NgModule({
  declarations: [
    SelectMultipleSiteComponent,
    SelectMultipleCustomerComponent,
    RefrencesFormComponent, PersonalRefrenceFormComponent, ModalInvoiceComponent,
    CallDashboardComponent, SelectsSitesMultipleJobRostersComponent, ChatDetailsComponent,
    MyCkEditorComponent, ProfileCommentComponent, SkeltonComponent, PdfComponent, CustomeLoaderComponent,


    // 
    EmploymentDetailComponent, DetailModelComponent, UserMenuComponent,
    CustomerMainComponent, CreateRosterComponent, UpdateTimeComponent, UpdateShiftPopoverComponent,
    EntoAdvanceDetailComponent, NotesForShiftComponent, AdminDetailComponent, CreateAdminComponent,
    MessageTemplateComponent, LeaveDetailsComponent,
    OtpVerificationComponent, PersonDetailComponent, StaffActivityDetailsComponent,
    CopyShiftBulkComponent, CopyDaySiteComponent, WelfareCallComponent, TrackerComponent, GreenCallComponent,
    OnBoardingStaffComponent,
    SignInOutComponent, JobshiftActivityComponent, CreateSiteComponent, AddUniformDetailComponent, AddUnavailabilityComponent,
    CapitalizePipe, CreateCustomerComponent, CheckAvailableStaffComponent, TimePipe, CreateNewRunsheetComponent, RunsheetCustomTemplateComponent,
    CreateNewAlarmComponent, CreateNewPatrolCarDetailsComponent, CreateNewMechanicComponent, DateRangeComponent,
    PayChargeHistoryComponent, NameFilterPipe, firstLastNameFilterPipe, siteFilterPipe, SiteFormComponent

  ],
  imports: [
    MaterialModule,
    NgbPopoverModule,
    NgbTimepickerModule, FontAwesomeModule, NgbModule, HttpClientModule,
    HttpClientJsonpModule, NgxMaterialTimepickerModule,
    NgxMatSelectSearchModule, GooglePlaceModule,
    MatListModule, NgbNavModule, WeeklySelectionModule, CKEditorModule, NgApexchartsModule,
    NgxQRCodeModule, NgxStarRatingModule, SelectMultipleGuardsModule, MatCheckboxModule
  ],
  providers: [MatDatepickerModule, MatMenuModule, NgbPopover, NgbActiveModal, MatSelectModule, DatePipe],

  exports: [
    MaterialModule,
    SelectMultipleSiteComponent, SelectMultipleCustomerComponent,
    WeeklySelectionModule, SelectsSitesMultipleJobRostersComponent,
    MyCkEditorComponent,
    NgbPopoverModule,
    NgbTimepickerModule,
    FontAwesomeModule, NgbModule, HttpClientModule,
    HttpClientJsonpModule, NgxMaterialTimepickerModule,
    NgxMatSelectSearchModule, GooglePlaceModule,
    NgbNavModule, WeeklySelectionModule,
    CKEditorModule, NgApexchartsModule, SkeltonComponent, CustomeLoaderComponent,


    // 
    EmploymentDetailComponent, DetailModelComponent, UserMenuComponent,
    CustomerMainComponent, CreateRosterComponent, UpdateTimeComponent, UpdateShiftPopoverComponent,
    EntoAdvanceDetailComponent, NotesForShiftComponent, AdminDetailComponent, CreateAdminComponent,
    MessageTemplateComponent, LeaveDetailsComponent,
    OtpVerificationComponent, PersonDetailComponent, StaffActivityDetailsComponent,
    CopyShiftBulkComponent, CopyDaySiteComponent, WelfareCallComponent, TrackerComponent, GreenCallComponent,
    OnBoardingStaffComponent,
    SignInOutComponent, JobshiftActivityComponent, CreateSiteComponent, AddUniformDetailComponent, AddUnavailabilityComponent,
    CapitalizePipe, CreateCustomerComponent, CheckAvailableStaffComponent, TimePipe, CreateNewRunsheetComponent, RunsheetCustomTemplateComponent,
    CreateNewAlarmComponent, CreateNewPatrolCarDetailsComponent, CreateNewMechanicComponent, DateRangeComponent, PayChargeHistoryComponent, NameFilterPipe,
    firstLastNameFilterPipe, siteFilterPipe, SelectMultipleGuardsModule, SiteFormComponent
  ]
})
export class AdminModule { }