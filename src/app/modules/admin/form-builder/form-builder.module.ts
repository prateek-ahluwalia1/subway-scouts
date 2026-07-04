import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { FormBuilderComponent } from './form-builder.component';
import { AdminModule } from '../admin.module';
import { SharedModule } from 'app/shared/shared.module';


const exampleRoutes: Route[]=[
  {
    path:'',
    component: FormBuilderComponent
  }]

@NgModule({
  declarations: [FormBuilderComponent],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),AdminModule,SharedModule
  ]
})
export class FormBuilderModule { }
