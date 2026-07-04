import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { EmergencyContactComponent } from './emergency-contact.component';
import { MaterialModule } from 'app/shared/material.module';

const exampleRoute: Route[]=[
  {
    path:'',
    component: EmergencyContactComponent
  }
]

@NgModule({
  declarations: [EmergencyContactComponent],
  imports: [
  RouterModule.forChild(exampleRoute), MaterialModule
  ]
})
export class EmergencyContactModule { }
