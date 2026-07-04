import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AuditReportComponent } from './audit-report.component';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: AuditReportComponent,
  }
];

@NgModule({
  declarations: [
    AuditReportComponent
  ],
  imports: [
    CommonModule, RouterModule.forChild(routes), AdminModule
  ]
})
export class AuditReportModule { }
