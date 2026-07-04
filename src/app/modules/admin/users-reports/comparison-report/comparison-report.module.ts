import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { ComparisonReportComponent } from './comparison-report.component';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: ComparisonReportComponent,
  }
];

@NgModule({
  declarations: [ComparisonReportComponent],
  imports: [
    RouterModule.forChild(routes), AdminModule
  ]
})
export class ComparisonReportModule { }
