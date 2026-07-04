import { NgModule } from '@angular/core';
import { WeeklySelectionComponent } from './weekly-selection.component';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { HttpClientJsonpModule } from '@angular/common/http';
import { NgbTimepickerModule } from '@ng-bootstrap/ng-bootstrap';
import { MaterialModule } from 'app/shared/material.module';

@NgModule({
  declarations: [WeeklySelectionComponent],
  imports: [
    NgbTimepickerModule,
    HttpClientJsonpModule,
    MatDatepickerModule, MaterialModule
  ],
  exports: [
    WeeklySelectionComponent,
  ]
})
export class WeeklySelectionModule { }
