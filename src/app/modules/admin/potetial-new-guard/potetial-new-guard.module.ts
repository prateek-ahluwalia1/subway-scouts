import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { PotetialNewGuardComponent } from './potetial-new-guard.component';
import { MaterialModule } from 'app/shared/material.module';

const exampleRoutes: Route[] = [
  {
    path: '',
    component: PotetialNewGuardComponent
  }
]

@NgModule({
  declarations: [PotetialNewGuardComponent],
  imports: [
    RouterModule.forChild(exampleRoutes), MaterialModule
  ]
})
export class PotetialNewGuardModule { }
