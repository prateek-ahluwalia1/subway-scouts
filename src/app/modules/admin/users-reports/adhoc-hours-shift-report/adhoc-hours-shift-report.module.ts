import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';
import { AdhocHoursShiftReportComponent } from './adhoc-hours-shift-report.component';

const routes: Routes = [
  {
    path: '',
    component: AdhocHoursShiftReportComponent,
  }
];

@NgModule({
  declarations: [AdhocHoursShiftReportComponent],
  imports: [
    CommonModule, RouterModule.forChild(routes), AdminModule
  ]
})
export class AdhocHoursShiftReportModule { }
