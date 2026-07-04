import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { ChildComponent } from './child/child.component';
import { AccountsComponent } from './accounts.component';
import { InvoiceComponent } from './invoice/invoice.component';
import { PayRatesComponent } from './pay-rates/pay-rates.component';
import { ChargeRatesComponent } from './charge-rates/charge-rates.component';
import { AwardRateComponent } from './award-rate/award-rate.component';
import { UploadPayslipComponent } from './upload-payslip/upload-payslip.component';
const routes: Routes = [
  {
    path: '',
    component: AccountsComponent,
    children: [{
      path: '',
      component: ChildComponent
    },
    {
      path: 'charge-rates',
      component: ChargeRatesComponent
    },
    {
      path: 'pay-rates',
      component: PayRatesComponent
    },
    {
      path: 'invoice',
      component: InvoiceComponent
    },
    {
      path: 'award-rate',
      component: AwardRateComponent
    },
    {
      path: 'payslip',
      component: UploadPayslipComponent
    },
    ]
  }
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule]
})
export class AccountsRoutingModule { }
