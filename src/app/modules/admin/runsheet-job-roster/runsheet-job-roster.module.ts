import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { RunsheetJobRosterComponent } from './runsheet-job-roster.component';
import { NgMultiSelectDropDownModule } from 'ng-multiselect-dropdown';
import { PopoverModule } from 'ngx-smart-popover';
import { AdminModule } from '../admin.module';
import { RunsheetOneWeekComponent } from './runsheet-one-week/runsheet-one-week.component';
import { RunsheetBasicShiftComponent } from './runsheet-basic-shift/runsheet-basic-shift.component';
import { RunsheetAdvanceShiftComponent } from './runsheet-advance-shift/runsheet-advance-shift.component';
import { ShiftStatusDirective } from './shift-status.directive';
import { RunsheetTwoWeekComponent } from './runsheet-two-week/runsheet-two-week.component';
import { FilterPipe } from './filter.pipe';

const exampleRoutes: Route[] = [
  {
    path: '',
    component: RunsheetJobRosterComponent
  }
]

@NgModule({
  declarations: [RunsheetJobRosterComponent, RunsheetOneWeekComponent, RunsheetOneWeekComponent, RunsheetBasicShiftComponent, 
    RunsheetAdvanceShiftComponent, ShiftStatusDirective, RunsheetTwoWeekComponent,FilterPipe],
  imports: [
    RouterModule.forChild(exampleRoutes), NgMultiSelectDropDownModule.forRoot(), PopoverModule, AdminModule
  ]

})
export class RunsheetJobRosterModule { }
