import { NgModule } from '@angular/core';
import { UsersReportsRoutingModule } from './users-reports-routing.module';
import { CardsComponent } from './cards/cards.component';
import { UsersReportsComponent } from './users-reports.component';
import { AdminModule } from '../admin.module';
import { MultiComponent } from './multi/multi.component';
import { MidnightHoursReportComponent } from './midnight-hours-report/midnight-hours-report.component';
import { FourtyHoursShiftReportComponent } from './fourty-hours-shift-report/fourty-hours-shift-report.component';
import { TenHoursShiftReportComponent } from './ten-hours-shift-report/ten-hours-shift-report.component';
import { ThirtysixHoursShiftReportComponent } from './thirtysix-hours-shift-report/thirtysix-hours-shift-report.component';
import { TwelveHoursShiftReportComponent } from './twelve-hours-shift-report/twelve-hours-shift-report.component';
import { AdhocHoursShiftReportComponent } from './adhoc-hours-shift-report/adhoc-hours-shift-report.component';
import { PayrollPaysheetComponent } from './payroll-paysheet/payroll-paysheet.component';
import { EmployeeTrainingMatrixComponent } from './employee-training-matrix/employee-training-matrix.component';
import { GuestAuthReportComponent } from './guest-auth-report/guest-auth-report.component';
import { ComparisonReportComponent } from './comparison-report/comparison-report.component';
import { OldCompletePaysheetComponent } from './old-complete-paysheet/old-complete-paysheet.component';
import { AmgJobTrackerComponent } from './amg-job-tracker/amg-job-tracker.component';

@NgModule({
  declarations: [
    UsersReportsComponent,
    CardsComponent,
    MultiComponent,
  ],
  imports: [
    UsersReportsRoutingModule, AdminModule
  ]
})
export class UsersReportsModule { }
