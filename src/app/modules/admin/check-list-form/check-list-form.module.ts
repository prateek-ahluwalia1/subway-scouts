import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { CheckListFormComponent } from './check-list-form.component';
import { MaterialModule } from 'app/shared/material.module';

const exampleRoute: Route[]=[
  {
    path:'',
    component: CheckListFormComponent
  }
]

@NgModule({
  declarations: [CheckListFormComponent],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoute), MaterialModule
  ]
})
export class CheckListFormModule { }
