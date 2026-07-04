import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { PatrolCarDetailsComponent } from './patrol-car-details.component';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[] = [
  {
      path     : '',
      component: PatrolCarDetailsComponent
  }
];

@NgModule({
  declarations: [PatrolCarDetailsComponent],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes), AdminModule
  ]
})
export class PatrolCarDetailsModule { }
