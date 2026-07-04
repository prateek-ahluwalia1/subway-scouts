import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { EmployeeDetailsComponent } from './employee-details.component';
import { MaterialModule } from 'app/shared/material.module';
import { SignaturePadModule } from 'angular2-signaturepad';


const exampleRoute: Route[] = [
  {
    path: '',
    component: EmployeeDetailsComponent
  }
]


@NgModule({
  declarations: [EmployeeDetailsComponent],
  imports: [
    RouterModule.forChild(exampleRoute), MaterialModule, SignaturePadModule
  ]
})
export class EmployeeDetailsModule { }
