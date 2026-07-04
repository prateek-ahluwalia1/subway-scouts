import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { StaffReportComponent } from './staff-report.component';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: StaffReportComponent,
  }
];

@NgModule({
  declarations: [StaffReportComponent],
  imports: [RouterModule.forChild(routes),AdminModule]
})
export class StaffReportModule { }
