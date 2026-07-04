import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { AllUsersComponent } from './all-users.component';
import { Route, RouterModule } from '@angular/router';
import { AdminModule } from '../admin.module';
import { MatMenuModule } from '@angular/material/menu';

const exampleRoutes: Route[] =[
  {
    path: '',
    component: AllUsersComponent
  }
]

@NgModule({
  declarations: [AllUsersComponent],
  imports: [
    CommonModule,RouterModule.forChild(exampleRoutes),AdminModule,MatMenuModule
  ]
})
export class AllUsersModule { }
