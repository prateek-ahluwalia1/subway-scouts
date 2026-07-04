import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { GuestAuthReportComponent } from './guest-auth-report.component';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: GuestAuthReportComponent,
  }
];

@NgModule({
  declarations: [GuestAuthReportComponent],
  imports: [
    RouterModule.forChild(routes), AdminModule
  ]
})
export class GuestAuthReportModule { }
