import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { TaskLogComponent } from './task-log.component';
import { AdminModule } from '../../admin.module';
import { SelectMultipleGuardsModule } from '../../component/select-multiple-guards/select-multiple-guards.module';

const exampleRoutes: Routes = [
  {
    path: '',
    component: TaskLogComponent
  }
];



@NgModule({
  declarations: [TaskLogComponent],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes), AdminModule, SelectMultipleGuardsModule
  ]
})
export class TaskLogModule { }
