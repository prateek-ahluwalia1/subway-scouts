import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PnlReportComponent } from './pnl-report.component';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';


const routes: Routes = [
  {
    path: '',
    component: PnlReportComponent,
  }
];
@NgModule({
  declarations: [
    PnlReportComponent
  ],
  imports: [
    CommonModule,RouterModule.forChild(routes), AdminModule
  ]
})
export class PnlReportModule { }
