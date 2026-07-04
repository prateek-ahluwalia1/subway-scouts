import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { CompanyDocComponent } from './company-doc.component';
import { AdminModule } from '../admin.module';

const exampleRoutes: Route[] = [
  {
    path: '',
    component: CompanyDocComponent
  }
]



@NgModule({
  declarations: [CompanyDocComponent],
  imports: [
    RouterModule.forChild(exampleRoutes), AdminModule
  ]
})
export class CompanyDocModule { }
