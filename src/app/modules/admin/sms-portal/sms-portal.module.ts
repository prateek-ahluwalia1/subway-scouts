import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { SmsPortalComponent } from './sms-portal.component';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[] =[
  {
    path: '',
    component: SmsPortalComponent
  }
]

@NgModule({
  declarations: [SmsPortalComponent],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),AdminModule
  ]
})
export class SmsPortalModule { }
