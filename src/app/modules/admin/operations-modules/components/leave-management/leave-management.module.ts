import { NgModule } from '@angular/core';
import { LeaveManagementComponent } from './leave-management.component';
import { Route, RouterModule } from '@angular/router';
import { MaterialModule } from 'app/shared/material.module';
import { FilterPipe } from './filter.pipe';

const Routes: Route[] = [
  {
    path: '',
    component: LeaveManagementComponent,
  }
]

@NgModule({
  declarations: [LeaveManagementComponent,FilterPipe],
  imports: [
    RouterModule.forChild(Routes), MaterialModule
  ]
})
export class LeaveManagementModule { }
