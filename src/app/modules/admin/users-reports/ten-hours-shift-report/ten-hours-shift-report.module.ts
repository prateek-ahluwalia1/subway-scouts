import { TenHoursShiftReportComponent } from './ten-hours-shift-report.component';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: TenHoursShiftReportComponent,
  }
];

@NgModule({
  declarations: [TenHoursShiftReportComponent],
  imports: [
    CommonModule, RouterModule.forChild(routes), AdminModule
  ]
})
export class TenHoursShiftReportModule { }
