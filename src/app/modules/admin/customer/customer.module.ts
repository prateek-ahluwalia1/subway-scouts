import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { CustomerComponent } from './customer.component';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../admin.module';
const exampleRoutes: Route[] =[
  {
    path: '',
    component: CustomerComponent
  }
]

@NgModule({
  declarations: [
    CustomerComponent
  ],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),AdminModule
  ]
})
export class CustomerModule { }
