import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { RefrencesFormComponent } from './refrences-form.component';
import { MaterialModule } from 'app/shared/material.module';

const exampleRoute: Route[]=[
  {
    path:'',
    component: RefrencesFormComponent
  }
]



@NgModule({
  declarations: [],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoute), MaterialModule
  ]
})
export class RefrencesFormModule { }
