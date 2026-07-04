import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { GreenWelfareCallReportComponent } from './green-welfare-call-report.component';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';
import { SelectMultipleGuardsModule } from '../../component/select-multiple-guards/select-multiple-guards.module';


const exampleRoutes: Routes = [
  {
    path: '',
    component: GreenWelfareCallReportComponent
  }
]

@NgModule({
  declarations: [
    GreenWelfareCallReportComponent
  ],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes), AdminModule,SelectMultipleGuardsModule
  ]
})
export class GreenWelfareCallReportModule { }
