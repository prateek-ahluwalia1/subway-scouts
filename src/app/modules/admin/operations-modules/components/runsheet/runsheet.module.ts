import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { RunsheetComponent } from './runsheet.component';
import { AdminModule } from 'app/modules/admin/admin.module';

const exampleRoutes: Route[] = [
  {
    path: '',
    component: RunsheetComponent
  }
]


@NgModule({
  declarations: [RunsheetComponent],
  imports: [
    RouterModule.forChild(exampleRoutes), AdminModule
  ]
})
export class RunsheetModule { }
