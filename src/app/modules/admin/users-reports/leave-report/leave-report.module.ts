import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LeaveReportComponent } from './leave-report.component';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../../admin.module';



const exampleRoutes: Route[]=[
  {
    path:'',
    component:LeaveReportComponent
  }
]
@NgModule({
  declarations: [
    LeaveReportComponent
  ],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes), AdminModule
  ]
})
export class LeaveReportModule { }
