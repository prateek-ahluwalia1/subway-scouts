import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { QuickPaysheetComponent } from './quick-paysheet.component';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: QuickPaysheetComponent,
  }
];

@NgModule({
  declarations: [QuickPaysheetComponent],
  imports: [
   RouterModule.forChild(routes),AdminModule
  ]
})
export class QuickPaysheetModule { }
