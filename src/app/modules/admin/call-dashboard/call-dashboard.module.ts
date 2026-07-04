import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CallDashboardComponent } from './call-dashboard.component';
import { Route, RouterModule } from '@angular/router';


const exampleRoutes: Route[] =[
  {
    path: '',
    component: CallDashboardComponent
  }
]
@NgModule({
  declarations: [
    
  ],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes),
  ]
})
export class CallDashboardModule { }
