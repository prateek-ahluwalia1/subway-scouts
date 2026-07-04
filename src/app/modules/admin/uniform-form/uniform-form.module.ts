import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { UniformFormComponent } from './uniform-form.component';
import { MaterialModule } from 'app/shared/material.module';

const exampleRoute: Route[]=[
  {
    path:'',
    component: UniformFormComponent
  }
]



@NgModule({
  declarations: [UniformFormComponent],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoute),MaterialModule
  ]
})
export class UniformFormModule { }
