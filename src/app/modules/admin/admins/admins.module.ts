import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import { AdminsComponent } from './admins.component';
import { MatIconModule } from "@angular/material/icon";
import { MatChipsModule } from "@angular/material/chips";
import {MatListModule} from '@angular/material/list'; 
import { MatTableModule } from '@angular/material/table';
import {MatBottomSheetModule} from '@angular/material/bottom-sheet';
import { MatCheckboxModule } from '@angular/material/checkbox';
import {MatSlideToggleModule} from '@angular/material/slide-toggle';
import {MatMenuModule} from '@angular/material/menu';
import { FormsModule } from '@angular/forms';
import {MatPaginatorModule} from '@angular/material/paginator';


const exampleRoutes: Route[] = [
  {
      path     : '',
      component: AdminsComponent
  }
];

@NgModule({
  declarations: [AdminsComponent],
  imports: [
    RouterModule.forChild(exampleRoutes),MatIconModule,MatChipsModule,MatListModule,MatTableModule,
    CommonModule,MatBottomSheetModule,MatCheckboxModule,MatSlideToggleModule,MatMenuModule,
    FormsModule,MatPaginatorModule
  ]
})
export class AdminsModule { }
