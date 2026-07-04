import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { ProfitLossReportComponent } from './profit-loss-report.component';
import { AdminModule } from '../../admin.module';


const exampleRoutes: Route[]=[
  {
    path:'',
    component:ProfitLossReportComponent
  }
]

@NgModule({
  declarations: [
    ProfitLossReportComponent
  ],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes),AdminModule
  ]
})
export class ProfitLossReportModule { }
