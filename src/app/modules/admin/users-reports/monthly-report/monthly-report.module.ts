import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { MonthlyReportComponent } from './monthly-report.component';
import { AdminModule } from '../../admin.module';


const routes: Routes = [
  {
    path: '',
    component: MonthlyReportComponent,
  }
];

@NgModule({
  declarations: [MonthlyReportComponent],
  imports: [
    CommonModule, RouterModule.forChild(routes), AdminModule
  ]
})
export class MonthlyReportModule { }
