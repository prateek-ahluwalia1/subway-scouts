import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { UsersReportsComponent } from './users-reports.component';
import { CardsComponent } from './cards/cards.component';
import { MultiComponent } from './multi/multi.component';


const routes: Routes = [
  {
    path: '',
    component: UsersReportsComponent,
    children: [
      {
        path: '',
        component: CardsComponent
      },
      { path: 'task-report', loadChildren: () => import('./task/task.module').then(m => m.TaskModule) },
      { path: 'staff-report', loadChildren: () => import('./staff-report/staff-report.module').then(m => m.StaffReportModule) },
      { path: 'invoice', loadChildren: () => import('./invoice/invoice.module').then(m => m.InvoiceModule) },
      { path: 'incident-report', loadChildren: () => import('./incident/incident.module').then(m => m.IncidentModule) },
      { path: 'sign-in-out-report', loadChildren: () => import('./sign-in-out/sign-in-out.module').then(m => m.SignInOutModule) },
      { path: 'quick-paysheet', loadChildren: () => import('./quick-paysheet/quick-paysheet.module').then(m => m.QuickPaysheetModule) },
      { path: 'payroll-paysheet', loadChildren: () => import('./payroll-paysheet/payroll-paysheet.module').then(m => m.PayrollPaysheetModule) },
      { path: 'old-complete-paysheet', loadChildren: () => import('./old-complete-paysheet/old-complete-paysheet.module').then(m => m.OldCompletePaysheetModule) },
      { path: 'complete-paysheet', loadChildren: () => import('./complete-paysheet/complete-paysheet.module').then(m => m.CompletePaysheetModule) },
      { path: 'admin-log', loadChildren: () => import('./task-log/task-log.module').then(m => m.TaskLogModule) },
      { path: 'time-sheet', loadChildren: () => import('./time-sheet/time-sheet.module').then(m => m.TimeSheetModule) },
      { path: 'job-tracker', loadChildren: () => import('./amg-job-tracker/amg-job-tracker.module').then(m => m.AmgJobTrackerModule) },
      { path: 'green-welfare-call', loadChildren: () => import('./green-welfare-call-report/green-welfare-call-report.module').then(m => m.GreenWelfareCallReportModule) },
      { path: 'audit-report', loadChildren: () => import('./audit-report/audit-report.module').then(m => m.AuditReportModule) },
      { path: 'monthly-report', loadChildren: () => import('./monthly-report/monthly-report.module').then(m => m.MonthlyReportModule) },
      { path: 'profit-loss-report', loadChildren: () => import('./profit-loss-report/profit-loss-report.module').then(m => m.ProfitLossReportModule) },
      { path: 'multi-report', component: MultiComponent },
      { path: 'leave-report', loadChildren: () => import('./leave-report/leave-report.module').then(m => m.LeaveReportModule) },
      { path: 'crm-customer', loadChildren: () => import('./crm-customer/crm-customer.module').then(m => m.CrmCustomerModule) },
      { path: 'crm-report', loadChildren: () => import('./crm-report/crm-report.module').then(m => m.CrmReportModule) },
      { path: 'pnl-report', loadChildren: () => import('./pnl-report/pnl-report.module').then(m => m.PnlReportModule) },
      { path: 'midnight-hours-report', loadChildren: () => import('./midnight-hours-report/midnight-hours-report.module').then(m => m.MidnightHoursReportModule) },
      { path: 'fourty-hours-shift-report', loadChildren: () => import('./fourty-hours-shift-report/fourty-hours-shift-report.module').then(m => m.FourtyHoursShiftReportModule) },
      { path: 'ten-hours-shift-report', loadChildren: () => import('./ten-hours-shift-report/ten-hours-shift-report.module').then(m => m.TenHoursShiftReportModule) },
      { path: 'thirtysix-hours-shift-report', loadChildren: () => import('./thirtysix-hours-shift-report/thirtysix-hours-shift-report.module').then(m => m.ThirtysixHoursShiftReportModule) },
      { path: 'twelve-hours-shift-report', loadChildren: () => import('./twelve-hours-shift-report/twelve-hours-shift-report.module').then(m => m.TwelveHoursShiftReportModule) },
      { path: 'adhoc-hours-shift-report', loadChildren: () => import('./adhoc-hours-shift-report/adhoc-hours-shift-report.module').then(m => m.AdhocHoursShiftReportModule) },
      { path: 'employee-training-matrix', loadChildren: () => import('./employee-training-matrix/employee-training-matrix.module').then(m => m.EmployeeTrainingMatrixModule) },
      { path: 'guest-auth-report', loadChildren: () => import('./guest-auth-report/guest-auth-report.module').then(m => m.GuestAuthReportModule) },
      { path: 'comparison-report', loadChildren: () => import('./comparison-report/comparison-report.module').then(m => m.ComparisonReportModule) },
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class UsersReportsRoutingModule { }
