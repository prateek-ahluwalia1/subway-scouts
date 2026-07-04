import { NgModule } from '@angular/core';
import { CrmReportComponent } from './crm-report.component';
import { RouterModule, Routes } from '@angular/router';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: CrmReportComponent,
  }
];

@NgModule({
  declarations: [CrmReportComponent],
  imports: [RouterModule.forChild(routes), AdminModule]
})
export class CrmReportModule { }
