import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { RatesComponent } from './rates.component';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[] =[
  {
    path: '',
    component: RatesComponent
  }
]



@NgModule({
  declarations: [RatesComponent],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),AdminModule
  ]
})
export class RatesModule { }
