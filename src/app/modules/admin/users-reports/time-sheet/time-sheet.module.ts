import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { TimeSheetComponent } from './time-sheet.component';
import { Route, RouterModule, Routes } from '@angular/router';
import { MatInputModule } from "@angular/material/input";
import { MatChipsModule } from "@angular/material/chips";
import { MatTabsModule } from "@angular/material/tabs";
import { MatIconModule } from "@angular/material/icon";
import { MatSelectModule } from "@angular/material/select";
import { AdminModule } from '../../admin.module';
import { FormsModule } from '@angular/forms';
import { Ng2SearchPipeModule } from 'ng2-search-filter';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatExpansionModule } from '@angular/material/expansion';
import { MatTableModule } from '@angular/material/table'
import { SelectMultipleGuardsModule } from '../../component/select-multiple-guards/select-multiple-guards.module';



const exampleRoutes: Routes = [
  {
    path: '',
    component: TimeSheetComponent
  }
];


@NgModule({
  declarations: [
    TimeSheetComponent
  ],
  imports: [
    RouterModule.forChild(exampleRoutes), MatInputModule, MatChipsModule, MatTabsModule, MatIconModule, SelectMultipleGuardsModule,
    MatSelectModule, CommonModule, AdminModule, FormsModule, Ng2SearchPipeModule, MatDatepickerModule, MatSlideToggleModule, MatExpansionModule, MatTableModule
  ]
})
export class TimeSheetModule { }
