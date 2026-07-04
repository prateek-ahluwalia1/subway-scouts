import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { AppStatusComponent } from './app-status.component';


const exampleRoutes: Route[] = [
  {
    path: '',
    component: AppStatusComponent
  }
];



@NgModule({
  declarations: [AppStatusComponent],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes)
  ]
})
export class AppStatusModule { }
