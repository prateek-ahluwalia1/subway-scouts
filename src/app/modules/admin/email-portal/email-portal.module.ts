import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { EmailPortalComponent } from './email-portal.component';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[] =[
  {
    path: '',
    component: EmailPortalComponent
  }
]

@NgModule({
  declarations: [EmailPortalComponent],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),AdminModule
  ]
})
export class EmailPortalModule { }
