import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { JobTrackerComponent } from './job-tracker.component';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../admin.module';
const exampleRoutes: Route[] = [
  {
    path: '',
    component: JobTrackerComponent
  }
]

@NgModule({
  declarations: [
    JobTrackerComponent
  ],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes), AdminModule
  ],
  exports:[]
})
export class JobTrackerModule { }
