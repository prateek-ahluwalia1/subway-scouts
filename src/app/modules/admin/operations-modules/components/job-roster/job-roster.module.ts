import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { JobRosterComponent } from './job-roster.component';
import { NgMultiSelectDropDownModule } from "ng-multiselect-dropdown";
import { AddCustomTemplateComponent } from 'app/modules/admin/models/add-custom-template/add-custom-template.component';
import { TwoWeeksComponent } from './two-weeks/two-weeks.component';
import { OneWeeksComponent } from './one-weeks/one-weeks.component';
import { PopoverModule } from "ngx-smart-popover";
import { ShortNamePipe } from 'app/shared/short-name.pipe';
import { FilterPipe } from './filter.pipe';
import { AddStaffOnLocationComponent } from './add-staff-on-location/add-staff-on-location.component';
import { SearchStaffPipe } from './search-staff.pipe';
import { ShiftStatusDirective } from './shift-status.directive';
import { BroadcastJobComponent } from './broadcast-job/broadcast-job.component';
import { AdvanceShiftComponent } from './advance-shift/advance-shift.component';
import { ShiftDelReasonComponent } from './shift-del-reason/shift-del-reason.component';
import { ViewDeletedShiftsComponent } from './view-deleted-shifts/view-deleted-shifts.component';
import { BasicShiftPopoverComponent } from './basic-shift-popover/basic-shift-popover.component';
import { ShiftsForPublishComponent } from './shifts-for-publish/shifts-for-publish.component';
import { siteFilterPipe } from './site-filter.pipe';
import { SplitComponent } from './split/split.component';
import { AdminModule } from 'app/modules/admin/admin.module';
import { AddMultipleShiftsComponent } from 'app/modules/admin/models/add-multiple-shifts/add-multiple-shifts.component';
import { CopyDaywiseShiftsComponent } from './copy-daywise-shifts/copy-daywise-shifts.component';

const exampleRoutes: Route[] = [
  {
    path: '',
    component: JobRosterComponent
  }
]

@NgModule({
  declarations: [
    JobRosterComponent, TwoWeeksComponent, OneWeeksComponent, ShortNamePipe,
    FilterPipe,
    ShiftStatusDirective,
    AddStaffOnLocationComponent,
    SearchStaffPipe, BroadcastJobComponent, AdvanceShiftComponent, ShiftDelReasonComponent, ViewDeletedShiftsComponent, BasicShiftPopoverComponent, ShiftsForPublishComponent,
    siteFilterPipe,
    SplitComponent, AddCustomTemplateComponent, AddMultipleShiftsComponent, CopyDaywiseShiftsComponent
  ],
  imports: [
    RouterModule.forChild(exampleRoutes), NgMultiSelectDropDownModule.forRoot(), PopoverModule, AdminModule,
  ]
})
export class JobRosterModule { }
