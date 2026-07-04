import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { SMSPortalHistoryComponent } from './smsportal-history.component';
import { MaterialModule } from 'app/shared/material.module';
const exampleRoutes: Route[]=[
  {
    path:'',
    component: SMSPortalHistoryComponent
  }]

@NgModule({
  declarations: [SMSPortalHistoryComponent],
  imports: [
    RouterModule.forChild(exampleRoutes),MaterialModule
  ]
})
export class SMSPortalHistoryModule { }
