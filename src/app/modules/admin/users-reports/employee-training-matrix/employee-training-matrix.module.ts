import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { EmployeeTrainingMatrixComponent } from './employee-training-matrix.component';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: EmployeeTrainingMatrixComponent,
  }
];

@NgModule({
  declarations: [EmployeeTrainingMatrixComponent],
  imports: [
    RouterModule.forChild(routes), AdminModule
  ]
})

export class EmployeeTrainingMatrixModule { }
