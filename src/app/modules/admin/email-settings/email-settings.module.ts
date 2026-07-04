import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { EmailSettingsComponent } from './email-settings.component';
import { Route, RouterModule } from '@angular/router';

const exampleRoutes: Route[]=[
  {
    path:'',
    component: EmailSettingsComponent
  }]

@NgModule({
  declarations: [
    EmailSettingsComponent
  ],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes)
  ]
})
export class EmailSettingsModule { }
