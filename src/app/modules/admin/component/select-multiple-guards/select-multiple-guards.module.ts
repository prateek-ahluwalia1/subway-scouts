import { NgModule } from '@angular/core';
import { SelectMultipleGuardsComponent } from './select-multiple-guards.component';
import { MaterialModule } from 'app/shared/material.module';
import { NgxMatSelectSearchModule } from 'ngx-mat-select-search';



@NgModule({
  declarations: [SelectMultipleGuardsComponent],
  imports: [
    MaterialModule,NgxMatSelectSearchModule
  ],
  exports: [SelectMultipleGuardsComponent]
})
export class SelectMultipleGuardsModule { }
