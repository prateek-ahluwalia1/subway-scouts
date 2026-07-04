import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { FormComponent } from './form.component';
import { AdminModule } from '../admin.module';
import { SignaturePadModule } from 'angular2-signaturepad';

const exampleRoutes: Route[]=[
  {
    path:'',
    component:FormComponent
  }
]



@NgModule({
  declarations: [FormComponent],
  imports: [
    RouterModule.forChild(exampleRoutes),AdminModule,SignaturePadModule
  ]
})
export class FormModule { }
