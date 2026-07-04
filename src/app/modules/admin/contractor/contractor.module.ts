import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { ContractorComponent } from './contractor.component';
import { MatIconModule } from "@angular/material/icon";
import { MatChipsModule } from "@angular/material/chips";
import {MatListModule} from '@angular/material/list'; 
import { MatTableModule } from '@angular/material/table';
import {MatBottomSheetModule} from '@angular/material/bottom-sheet';
import { MatCheckboxModule } from '@angular/material/checkbox';
import {MatSlideToggleModule} from '@angular/material/slide-toggle';
import {MatMenuModule} from '@angular/material/menu';
import { MaterialModule } from 'app/shared/material.module';
const exampleRoutes: Route[] =[
  {
    path: '',
    component: ContractorComponent
  }
]

@NgModule({
  declarations: [ContractorComponent],
  imports: [
    RouterModule.forChild(exampleRoutes), MaterialModule
  ]
})
export class ContractorModule { }
