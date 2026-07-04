import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';
import { FourtyHoursShiftReportComponent } from './fourty-hours-shift-report.component';

const routes: Routes = [
  {
    path: '',
    component: FourtyHoursShiftReportComponent,
  }
];

@NgModule({
  declarations: [FourtyHoursShiftReportComponent],
  imports: [
    CommonModule, RouterModule.forChild(routes), AdminModule
  ]
})
export class FourtyHoursShiftReportModule { }
