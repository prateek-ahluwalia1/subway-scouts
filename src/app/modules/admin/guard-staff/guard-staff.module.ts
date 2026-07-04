import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { GuardStaffComponent } from './guard-staff.component';
import { AdminModule } from '../admin.module';


const exampleRoutes: Route[] =[
  {
    path: '',
    component: GuardStaffComponent
  }
]

@NgModule({
  declarations: [GuardStaffComponent],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),AdminModule
  ]
})
export class GuardStaffModule { }
