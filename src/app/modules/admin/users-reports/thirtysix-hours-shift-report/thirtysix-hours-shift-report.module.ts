import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';
import { ThirtysixHoursShiftReportComponent } from './thirtysix-hours-shift-report.component';

const routes: Routes = [
  {
    path: '',
    component: ThirtysixHoursShiftReportComponent,
  }
];

@NgModule({
  declarations: [ThirtysixHoursShiftReportComponent],
  imports: [
    CommonModule, RouterModule.forChild(routes), AdminModule
  ]
})
export class ThirtysixHoursShiftReportModule { }
