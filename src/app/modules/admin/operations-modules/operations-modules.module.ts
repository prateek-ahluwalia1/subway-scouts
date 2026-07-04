import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { OperationsModulesRoutingModule } from './operations-modules-routing.module';
import { MaterialModule } from 'app/shared/material.module';
import { OperationsComponent } from './components/operations/operations.component';
import { VisaDetailManualCheckComponent } from './components/visa-detail-manual-check/visa-detail-manual-check.component';
import { SelectMultipleGuardsModule } from '../component/select-multiple-guards/select-multiple-guards.module';
import { VisaDetailAutoCheckComponent } from './components/visa-detail-auto-check/visa-detail-auto-check.component';
import { AdminModule } from "../admin.module";


@NgModule({
  declarations: [
    OperationsComponent,
    VisaDetailManualCheckComponent,
    VisaDetailAutoCheckComponent
  ],
  imports: [
    OperationsModulesRoutingModule, MaterialModule, SelectMultipleGuardsModule,
    AdminModule
]
})
export class OperationsModulesModule { }
