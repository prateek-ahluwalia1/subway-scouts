import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MidnightHoursReportComponent } from './midnight-hours-report.component';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: MidnightHoursReportComponent,
  }
];

@NgModule({
  declarations: [MidnightHoursReportComponent],
  imports: [
    CommonModule, RouterModule.forChild(routes), AdminModule
  ]
})
export class MidnightHoursReportModule { }
