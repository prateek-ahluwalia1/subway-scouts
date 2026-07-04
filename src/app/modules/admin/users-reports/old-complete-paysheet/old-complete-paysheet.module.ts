import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { OldCompletePaysheetComponent } from './old-complete-paysheet.component';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: OldCompletePaysheetComponent,
  }
];

@NgModule({
  declarations: [OldCompletePaysheetComponent],
  imports: [
    RouterModule.forChild(routes), AdminModule
  ]
})
export class OldCompletePaysheetModule { }
