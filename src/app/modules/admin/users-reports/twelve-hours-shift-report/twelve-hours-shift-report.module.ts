import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';
import { TwelveHoursShiftReportComponent } from './twelve-hours-shift-report.component';

const routes: Routes = [
  {
    path: '',
    component: TwelveHoursShiftReportComponent,
  }
];

@NgModule({
  declarations: [TwelveHoursShiftReportComponent],
  imports: [
    CommonModule, RouterModule.forChild(routes), AdminModule
  ]
})
export class TwelveHoursShiftReportModule { }
