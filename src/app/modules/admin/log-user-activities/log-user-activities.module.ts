import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LogUserActivitiesComponent } from './log-user-activities.component';
import { Route, RouterModule } from '@angular/router';


const exampleRoutes: Route[] = [
  {
    path: '',
    component: LogUserActivitiesComponent
  }
]
@NgModule({
  declarations: [LogUserActivitiesComponent],
  imports: [
    CommonModule,
    RouterModule.forChild(exampleRoutes)
  ]
})
export class LogUserActivitiesModule { }
