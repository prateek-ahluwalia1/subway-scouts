import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { DashboardComponent } from './dashboard.component';
import { AdminModule } from '../admin.module';
import { StaffConfirmationComponent } from './staff-confirmation/staff-confirmation.component';
import { SecurityLicenseComponent } from './security-license/security-license.component';
import { FuseCardModule } from '@fuse/components/card';
import { WelfareCheckComponent } from './welfare-check/welfare-check.component';
import { ReminderComplianceComponent } from './reminder-compliance/reminder-compliance.component';
import { LiveOperationComponent } from './live-operation/live-operation.component';
import { PublishUnpublishedComponent } from './publish-unpublished/publish-unpublished.component';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { LeaveManagementComponent } from './leave-management/leave-management.component';
import { SigninoutComponent } from './live-operation/signinout/signinout.component';
import { JournalComponent } from './journal/journal.component';
import { SubContractorRemindersComponent } from './sub-contractor-reminders/sub-contractor-reminders.component';
import { StaffResponseComponent } from './staff-confirmation/staff-response/staff-response.component';
import { AdminsRemarkComponent } from './welfare-check/admins-remark/admins-remark.component';
const exampleRoutes: Route[] = [
  {
    path: '',
    component: DashboardComponent
  }
]


@NgModule({
  declarations: [
    DashboardComponent,
    StaffConfirmationComponent,
    SecurityLicenseComponent,
    WelfareCheckComponent,
    ReminderComplianceComponent,
    LiveOperationComponent,
    PublishUnpublishedComponent,
    LeaveManagementComponent,
    SigninoutComponent,
    JournalComponent,
    SubContractorRemindersComponent,
    StaffResponseComponent,
    AdminsRemarkComponent,
  ],
  imports: [
    RouterModule.forChild(exampleRoutes), AdminModule, FuseCardModule, NgbModule
  ]
})
export class DashboardModule { }
