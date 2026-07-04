import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { OperationsComponent } from './components/operations/operations.component';
import { VisaDetailManualCheckComponent } from './components/visa-detail-manual-check/visa-detail-manual-check.component';
import { VisaDetailAutoCheckComponent } from './components/visa-detail-auto-check/visa-detail-auto-check.component';

const routes: Routes = [
  { path: '', component: OperationsComponent },
  { path: 'roster-type', loadChildren: () => import('./components/roster-types/roster-types.module').then(m => m.RosterTypesModule) },
  { path: 'location', loadChildren: () => import('./components/locations/locations.module').then(m => m.LocationsModule) },
  { path: 'job-roster/:slug/:id', loadChildren: () => import('./components/job-roster/job-roster.module').then(m => m.JobRosterModule) },
  { path: 'staff-documentation', loadChildren: () => import('./components/staff-documents-detail/staff-documents-detail.module').then(m => m.StaffDocumentsDetailModule) },
  { path: 'staff-documentation', loadChildren: () => import('./components/staff-documents-detail/staff-documents-detail.module').then(m => m.StaffDocumentsDetailModule) },
  { path: 'leave-management', loadChildren: () => import('./components/leave-management/leave-management.module').then(m => m.LeaveManagementModule) },
  { path: 'app-status', loadChildren: () => import('./components/app-status/app-status.module').then(m => m.AppStatusModule) },
  { path: 'time-clock', loadChildren: () => import('./components/time-clock/time-clock.module').then(m => m.TimeClockModule) },
  { path: 'activity-log', loadChildren: () => import('./components/activity-log/activity-log.module').then(m => m.ActivityLogModule) },
  { path: 'runsheet', loadChildren: () => import('./components/runsheet/runsheet.module').then(m => m.RunsheetModule) },
  { path: 'runsheet-roster', loadChildren: () => import('./components/runsheet-roster/runsheet-roster.module').then(m => m.RunsheetRosterModule) },
  { path: 'alarm-dispatch-system', loadChildren: () => import('./components/alarm-dispatch-system/alarm-dispatch-system.module').then(m => m.AlarmDispatchSystemModule) },
  { path: 'check-visa-status', component: VisaDetailManualCheckComponent },
  { path: 'check-visa-status-auto', component: VisaDetailAutoCheckComponent },
  { path: '', redirectTo: '', pathMatch: 'full' }, // Default route
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class OperationsModulesRoutingModule { }
