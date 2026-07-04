import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { StaffLogsComponent } from './staff-logs.component';
import { MaterialModule } from 'app/shared/material.module';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[] = [
  {
    path: '',
    component: StaffLogsComponent
  }
]

@NgModule({
  declarations: [StaffLogsComponent],
  imports: [
    RouterModule.forChild(exampleRoutes), AdminModule
  ]
})
export class StaffLogsModule { }
