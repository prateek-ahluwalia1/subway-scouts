
import { EntoFortnightDesignComponent } from './ento-fortnight-design.component';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';

import { MatSelectModule } from "@angular/material/select";
import { MatIconModule } from "@angular/material/icon";
import { MatFormFieldModule } from "@angular/material/form-field";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { MatMenuModule } from '@angular/material/menu';
import { MatChipsModule } from "@angular/material/chips";
import { HttpClientModule } from '@angular/common/http';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import {NgxMaterialTimepickerModule} from 'ngx-material-timepicker';
import { FontAwesomeModule } from '@fortawesome/angular-fontawesome';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { MatTableModule } from '@angular/material/table';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatDialogModule } from '@angular/material/dialog';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MatRadioModule} from '@angular/material/radio';
import { NgbTimepickerModule } from '@ng-bootstrap/ng-bootstrap';
import { MatCardModule } from "@angular/material/card";
import { NgMultiSelectDropDownModule } from "ng-multiselect-dropdown";
import { MatDatepickerModule } from '@angular/material/datepicker';

 

import {MatInputModule} from '@angular/material/input';

const exampleRoutes: Route[]=[
  {
    path:'',
    component:EntoFortnightDesignComponent
  }
]

@NgModule({
    declarations: [EntoFortnightDesignComponent,],
    imports: [
        RouterModule.forChild(exampleRoutes),
        // WeeklySelectionModule,
        CommonModule,
        MatSelectModule,
        MatIconModule,
        FormsModule,
        ReactiveFormsModule,
        MatMenuModule,
        MatChipsModule, HttpClientModule,
        NgbModule,
        MatFormFieldModule,
        MatInputModule,
        NgxMaterialTimepickerModule,
        FontAwesomeModule,
        DragDropModule,
        MatTableModule,
        MatCheckboxModule,
        MatDialogModule,
        MatTooltipModule,
        MatRadioModule,
        NgbTimepickerModule,
        MatCardModule,
        NgMultiSelectDropDownModule,
        MatDatepickerModule,
          
    ]
})
export class EntoFortnightDesignModule { }
