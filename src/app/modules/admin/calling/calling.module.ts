import { NgModule } from '@angular/core';
import { CallingComponent } from './calling.component';
import { Route, RouterModule } from '@angular/router';
import { MaterialModule } from 'app/shared/material.module';

const exampleRoutes: Route[] = [
  {
    path: '',
    component: CallingComponent
  }
]

@NgModule({
  declarations: [CallingComponent],
  imports: [
    RouterModule.forChild(exampleRoutes), MaterialModule
  ]
})
export class CallingModule { }
