import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { LocationsComponent } from './locations.component';
import { RouterModule, Routes } from '@angular/router';
// import { SiteFormComponent } from './site-form/site-form.component';
import { MaterialModule } from 'app/shared/material.module';
import { AdminModule } from 'app/modules/admin/admin.module';
import { ExportComponent } from './export/export.component';

const routes: Routes = [
  { path: '', component: LocationsComponent }
]

@NgModule({
  declarations: [
    LocationsComponent,
    // SiteFormComponent,
    ExportComponent
  ],
  imports: [
    CommonModule,
    RouterModule.forChild(routes),AdminModule
  ]
})
export class LocationsModule { }
