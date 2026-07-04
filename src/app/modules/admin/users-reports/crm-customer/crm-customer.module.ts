import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { CrmCustomerComponent } from './crm-customer.component';
import { AdminModule } from '../../admin.module';

const routes: Routes = [
  {
    path: '',
    component: CrmCustomerComponent,
  }
];

@NgModule({
  declarations: [CrmCustomerComponent],
  imports: [
    RouterModule.forChild(routes), AdminModule
  ]
})
export class CrmCustomerModule { }
