import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { NewDashboardComponent } from './new-dashboard.component';
import { MaterialModule } from 'app/shared/material.module';
import { NgApexchartsModule } from 'ng-apexcharts';
import { AbsNumberPipe } from 'app/shared/abs-number.pipe';
import { DashbardReportPdfService } from './dashboard-report-pdf.service';
import { CrmStatusCardComponent } from './crm-status-card/crm-status-card.component';
const exampleRoute: Route[] = [
  {
    path: '',
    component: NewDashboardComponent
  }
]


@NgModule({
  declarations: [NewDashboardComponent, AbsNumberPipe, CrmStatusCardComponent],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoute), MaterialModule, NgApexchartsModule
  ],
  providers: [DashbardReportPdfService],
})
export class NewDashboardModule { }
