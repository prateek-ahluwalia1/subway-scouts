import { NgModule } from '@angular/core';
import { Route, RouterModule } from '@angular/router';
import { RunsheetRosterComponent } from './runsheet-roster.component';
import { MaterialModule } from 'app/shared/material.module';
import { CreateRunsheetRosterComponent } from 'app/modules/admin/models/create-runsheet-roster/create-runsheet-roster.component';
import { SelectMultipleGuardsModule } from 'app/modules/admin/component/select-multiple-guards/select-multiple-guards.module';

const exampleRoutes: Route[] = [
  {
    path: '',
    component: RunsheetRosterComponent
  }
]

@NgModule({
  declarations: [RunsheetRosterComponent, CreateRunsheetRosterComponent],
  imports: [
    RouterModule.forChild(exampleRoutes),
    MaterialModule, SelectMultipleGuardsModule
  ]
})
export class RunsheetRosterModule { }
