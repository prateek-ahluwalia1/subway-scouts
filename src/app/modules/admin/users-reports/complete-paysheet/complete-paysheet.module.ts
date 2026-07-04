import { NgModule } from '@angular/core';
import { CompletePaysheetComponent } from './complete-paysheet.component';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: CompletePaysheetComponent,
  }
];

@NgModule({
  declarations: [CompletePaysheetComponent],
  imports: [
    RouterModule.forChild(routes), AdminModule
  ]
})
export class CompletePaysheetModule { }
