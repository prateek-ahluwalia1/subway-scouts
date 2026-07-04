import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../admin.module';
import { InductionComponent } from './induction.component';

const exampleRoutes: Route[] =[
  {
    path: '',
    component: InductionComponent
  }
]



@NgModule({
  declarations: [InductionComponent],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes),
    AdminModule
  ]
})
export class InductionModule { }
