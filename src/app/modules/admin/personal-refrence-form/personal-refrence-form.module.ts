import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { PersonalRefrenceFormComponent } from './personal-refrence-form.component';
import { MaterialModule } from 'app/shared/material.module';

const exampleRoute: Route[]=[
  {
    path:'',
    component: PersonalRefrenceFormComponent
  }
]


@NgModule({
  declarations: [],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoute), MaterialModule
  ]
})
export class PersonalRefrenceFormModule { }
