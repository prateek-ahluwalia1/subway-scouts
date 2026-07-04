import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { PayrollPaysheetComponent } from './payroll-paysheet.component';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: PayrollPaysheetComponent,
  }
];

@NgModule({
  declarations: [PayrollPaysheetComponent],
  imports: [
    RouterModule.forChild(routes), AdminModule
  ]
})
export class PayrollPaysheetModule { }
