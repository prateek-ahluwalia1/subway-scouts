import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { RosterThreeComponent } from './roster-three.component';
import { MatIconModule } from '@angular/material/icon';
import { MatSelectModule } from '@angular/material/select';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatMenuModule } from '@angular/material/menu';
import { NgxMatSelectSearchModule } from 'ngx-mat-select-search';
import { OneWeekComponent } from './one-week/one-week.component';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { WeeklySelectionModule } from '../component/weekly-selection/weekly-selection.module'
import { MatChipsModule } from '@angular/material/chips';
import { RosterThreeAddShiftComponent } from '../component/roster-three-add-shift/roster-three-add-shift.component'
import { NgxMaterialTimepickerModule } from 'ngx-material-timepicker';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { RosterThreePublishShiftComponent } from '../component/roster-three-publish-shift/roster-three-publish-shift.component';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatCardModule } from '@angular/material/card';
import { StaffFortNightDetailComponent } from '../component/staff-fort-night-detail/staff-fort-night-detail.component'
import { AdminModule } from '../admin.module';
const exampleRoutes: Route[] = [
  {
    path: '',
    component: RosterThreeComponent
  }
]


@NgModule({
  declarations: [RosterThreeComponent, OneWeekComponent, RosterThreeAddShiftComponent, RosterThreePublishShiftComponent, StaffFortNightDetailComponent],
  imports: [
    CommonModule, RouterModule.forChild(exampleRoutes), MatChipsModule, NgxMaterialTimepickerModule,
    MatIconModule, MatSelectModule, FormsModule, ReactiveFormsModule, MatMenuModule, MatCardModule, AdminModule,
    NgxMatSelectSearchModule, FontAwesomeModule, WeeklySelectionModule, MatSlideToggleModule, MatCheckboxModule
  ]
})
export class RosterThreeModule { }
