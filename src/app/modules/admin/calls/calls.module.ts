import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { CallsComponent } from './calls.component';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[] =[
  {
    path: '',
    component: CallsComponent
  }
]



@NgModule({
  declarations: [CallsComponent],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),AdminModule
  ]
})
export class CallsModule { }
