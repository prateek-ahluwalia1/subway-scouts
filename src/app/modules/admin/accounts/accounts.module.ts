import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AccountsRoutingModule } from './accounts-routing.module';
import { PayRatesComponent } from './pay-rates/pay-rates.component';
import { ChargeRatesComponent } from './charge-rates/charge-rates.component';
import { InvoiceComponent } from './invoice/invoice.component';
import { AccountsComponent } from './accounts.component';
import { ChildComponent } from './child/child.component';
import { AddPayrateComponent } from './add-payrate/add-payrate.component';
import { AddChargeRateComponent } from './add-charge-rate/add-charge-rate.component';
import { AdminModule } from '../admin.module';
import { AwardRateComponent } from './award-rate/award-rate.component';
import { AddAwardRateComponent } from './add-award-rate/add-award-rate.component';
import { UploadPayslipComponent } from './upload-payslip/upload-payslip.component';



@NgModule({
  declarations: [
    PayRatesComponent,
    ChargeRatesComponent,
    InvoiceComponent,
    AccountsComponent,
    ChildComponent,
    AddPayrateComponent,
    AddChargeRateComponent,
    AwardRateComponent,
    AddAwardRateComponent,
    UploadPayslipComponent
  ],
  imports: [
    CommonModule,
    AccountsRoutingModule, AdminModule
  ]
})
export class AccountsModule { }
