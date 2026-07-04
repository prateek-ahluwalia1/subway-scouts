import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';

import { MatProgressBarModule } from "@angular/material/progress-bar";
import { MatSnackBarModule } from "@angular/material/snack-bar";
import { MatInputModule } from "@angular/material/input";
import { MatChipsModule } from "@angular/material/chips";
import { MatTabsModule } from "@angular/material/tabs";
import { MatIconModule } from "@angular/material/icon";
import { MatFormFieldModule } from "@angular/material/form-field";
import { MatSelectModule } from "@angular/material/select";
import { MatCardModule } from "@angular/material/card";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { NgMultiSelectDropDownModule } from "ng-multiselect-dropdown";
import { DragDropModule } from '@angular/cdk/drag-drop';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatMenuModule } from '@angular/material/menu';
import { MatTableModule } from '@angular/material/table';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatDialogModule } from '@angular/material/dialog';
import { MatTooltipModule } from '@angular/material/tooltip';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { HttpClientModule, HttpClientJsonpModule } from '@angular/common/http';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { MatSlideToggleModule } from "@angular/material/slide-toggle";
import { NgbTimepickerModule } from '@ng-bootstrap/ng-bootstrap';
import { MatRadioModule } from '@angular/material/radio';
import { NgxMaterialTimepickerModule } from 'ngx-material-timepicker';
import { NgxMatSelectSearchModule } from 'ngx-mat-select-search';
import { GooglePlaceModule } from "ngx-google-places-autocomplete";
import { MatButtonModule } from '@angular/material/button';
import { MatBottomSheetModule } from '@angular/material/bottom-sheet';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatListModule } from '@angular/material/list';
import { NgbNavModule } from '@ng-bootstrap/ng-bootstrap';
import { EntoDesignWeeklyComponent } from './ento-design-weekly.component';



@NgModule({
  declarations: [EntoDesignWeeklyComponent],
  imports: [
    CommonModule,
    MatIconModule,
    MatProgressBarModule,
    MatSnackBarModule,
    MatSelectModule,
    FormsModule,
    MatInputModule,
    MatChipsModule,
    MatTabsModule,
    CommonModule,
    MatFormFieldModule,
    ReactiveFormsModule,
    NgMultiSelectDropDownModule.forRoot(),
    MatCardModule,
    DragDropModule,
    MatSlideToggleModule,
    MatNativeDateModule,
    MatMenuModule,
    MatTooltipModule,
    MatTableModule,

    NgbTimepickerModule,
    MatCheckboxModule, FontAwesomeModule, NgbModule, MatDialogModule, HttpClientModule,
    HttpClientJsonpModule, ReactiveFormsModule, MatRadioModule, NgxMaterialTimepickerModule,
    NgxMatSelectSearchModule, MatDatepickerModule, GooglePlaceModule, MatButtonModule, MatBottomSheetModule, MatSidenavModule,
    MatListModule, NgbNavModule
  ]
})
export class EntoDesignWeeklyModule { }
