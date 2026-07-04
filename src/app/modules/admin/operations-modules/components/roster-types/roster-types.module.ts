import { RosterTypesComponent } from './roster-types.component';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Route, RouterModule } from '@angular/router';
import {MatButtonModule} from '@angular/material/button';
import {MatTabsModule} from '@angular/material/tabs';
import { MatIconModule } from "@angular/material/icon";
import {MatMenuModule} from '@angular/material/menu';
import {MatFormFieldModule} from '@angular/material/form-field';
import {MatSelectModule} from '@angular/material/select';
import { FormsModule } from '@angular/forms';

const exampleRoutes: Route[]=[
  {
    path:'',
    component:RosterTypesComponent
  }
]

@NgModule({
  declarations: [RosterTypesComponent],
  imports: [
    CommonModule,
    RouterModule.forChild(exampleRoutes),
    MatButtonModule,
    MatTabsModule, FormsModule,
    MatIconModule,MatMenuModule, MatFormFieldModule, MatSelectModule
  ]
})
export class RosterTypesModule { }
