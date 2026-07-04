import { NgModule } from '@angular/core';
import { CrmComponent } from './crm.component';
import { RouterModule, Routes } from '@angular/router';
import { CustomerListComponent } from './crm-client/customer-list.component';
import { MaterialModule } from 'app/shared/material.module';
import { ConfirmDialog } from 'app/shared/dialog.component';
import { CustomerFormComponent } from './crm-client/customer-form.component';
import { SalePersonListComponent } from './crm-sale-persons/sale-person-list.component';
import { CustomerDetailComponent } from './crm-client/customer-detail.component';
import { NgbPopoverModule } from '@ng-bootstrap/ng-bootstrap';
import { QuotationListComponent } from './crm-client/quotation-list.component';
import { CreateQuotationComponent } from './crm-client/create-quotation.component';
import { PdfComponent } from './pdf/pdf.component';
import { AdminModule } from '../admin.module';
import { EmailModalComponent } from './crm-client/email-modal.component';
import { TasksComponent } from './tasks/tasks.component';
import { TasksFormComponent } from './tasks/tasks-form.component';
import { AllLeadsComponent } from './all-leads/all-leads.component';
import { SelectDropDownModule } from 'ngx-select-dropdown';
import { DecimalFormatPipe } from 'app/shared/decimal-format.pipe';
import { ViewFinacialComponent } from './view-finacial/view-finacial.component';
import { OutlookMailsComponent } from './outlook-mails/outlook-mails.component';
const routes: Routes = [
  {
    path: '',
    component: CrmComponent,
  },
  {
    path: 'lead',
    component: CustomerListComponent,
  },
  {
    path: 'quotation',
    component: QuotationListComponent,
  },
  {
    path: 'contacted',
    component: CustomerListComponent,
  },
  {
    path: 'won',
    component: CustomerListComponent,
  },
  {
    path: 'tasks',
    component: TasksComponent,
  },
  {
    path: 'lost',
    component: CustomerListComponent,
  },
  {
    path: 'sales-person',
    component: SalePersonListComponent,
  },
  {
    path: "create/lead",
    component: CustomerFormComponent
  },
  {
    path: "create/Quotes",
    component: CreateQuotationComponent
  },
  {
    path: "edit/Quotes/:id",
    component: CreateQuotationComponent
  },
  {
    path: "create/sale-person",
    component: CustomerFormComponent
  },
  {
    path: "edit/sale-person/:id",
    // canDeactivate: [CustomerEditGuard],
    component: CustomerFormComponent
  },
  {
    path: "edit/lead/:id",
    // canDeactivate: [CustomerEditGuard],
    component: CustomerFormComponent
  },
  {
    path: "customer-detail/:id",
    // canDeactivate: [CustomerEditGuard],
    component: CustomerDetailComponent
  },
  {
    path: "create/task",
    component: TasksFormComponent
  },
  {
    path: "edit/task/:id",
    component: TasksFormComponent
  },
  {
    path: "all/leads",
    component: AllLeadsComponent
  },
];
@NgModule({
  declarations: [
    CrmComponent, CustomerListComponent, ConfirmDialog, CustomerFormComponent, SalePersonListComponent, CustomerDetailComponent,
    QuotationListComponent, CreateQuotationComponent, PdfComponent, EmailModalComponent, TasksComponent,
    TasksFormComponent, DecimalFormatPipe,
    AllLeadsComponent,
    ViewFinacialComponent,
    OutlookMailsComponent
  ],
  imports: [
    AdminModule, RouterModule.forChild(routes), MaterialModule, NgbPopoverModule, SelectDropDownModule
  ],

})
export class CrmModule { }
